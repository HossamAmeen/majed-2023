# AGENTS.md

Guidance for AI coding agents working in this repository.

## What this is

An Arabic (RTL) admin panel and point-of-sale system for a single retail shop. It covers
products and stock, bills (invoices), orders (bill line items), notes, users, and a daily,
monthly, and yearly sales statistics page. It is server-rendered Blade with jQuery, with no
SPA and no public API.

- **Stack:** Laravel 10, PHP 8.1+ (the most recent work targets PHP 8.2), MySQL.
- **Key packages:** `laravel/ui` (auth scaffolding), `maatwebsite/excel` (product export),
  `carlos-meneses/laravel-mpdf` (product PDF export), `milon/barcode` (product barcodes),
  `intervention/image`.
- **UI language:** Arabic. `config/app.php` sets `locale` to `ar`. Flash messages, validation
  errors, and labels are written in Arabic, so keep new user-facing strings in Arabic.

## Out of scope

- **`xPrinter/`** and `xPrinter_old.rar` hold separate thermal-printer tooling. Don't read,
  modify, or refactor them unless the user asks.
- `image for setting of printer.rar`, `error_log`, `public/error_log`, `tasks.txt`, and
  `my tasks.txt` are stray files, not source code.

## Setup and commands

```bash
composer install
cp .env.example .env && php artisan key:generate   # then set DB_* for MySQL
php artisan migrate --seed                          # seeds admin user + config row id=1
php artisan serve                                   # app lives under /admin
php artisan test                                    # PHPUnit (tests/Unit, tests/Feature)
php artisan database:backup                         # mysqldump -> storage/backups/Y-m-d.sql
```

- **Seeded login:** user name `admin`, password `admin`. The login form's `login` field
  accepts either an email or a `user_name` (see `LoginController::findUsername`).
- **Scheduler:** `database:backup` runs twice a day (`app/Console/Kernel.php`). It needs
  `mysqldump` on the PATH.
- **Frontend build:** `npm run dev` compiles `resources/js` and `resources/sass`, but the
  panel doesn't use that output. The real panel assets are static files under
  `public/panel/assets/`, loaded by `back-end/layout/app.blade.php`. Page-specific JS goes
  inline through `@push('js')` in the views.

## Architecture

### Routing (`routes/web.php`)

- Every route sits under the `/admin` prefix. `Auth::routes()` and the password-reset routes
  are public. Everything else uses the `auth` middleware.
- `routes/api.php` is empty.
- `GET admin/bills/search` must stay above `Route::resource('bills', ...)`. Otherwise
  `bills/{bill}` captures it.
- `RouteServiceProvider` keeps `$namespace = 'App\Http\Controllers'` because `Auth::routes()`
  registers string actions. Don't remove it.

### Generic CRUD base: `app/Http/Controllers/BackEnd/BackEndController.php`

Each resource controller passes its model to `parent::__construct()`. The base class then
provides `index`, `create`, `edit`, `show`, and `destroy`, and derives names from the model
class:

| Derived value | Example for `Product` |
|---|---|
| `getModelName()` | `Product` |
| `pluralModelName()` | `Products` |
| `getClassNameFromModel()` (route name and view folder) | `products` |

Views therefore follow `resources/views/back-end/{plural}/{index,create,edit,form}.blade.php`.
Subclasses customize the base through these hook methods, so override a hook before you
rewrite an action:

- `filter($rows)`: query-string filters.
- `with()`: eager loads.
- `append($row)`: extra data for the create and edit views.
- `appendIndex($rows)`: extra data for the index view.
- `destoryRelationShip($id)`: cleanup after a delete. The misspelling is the real method name.

Subclasses write their own `store` and `update`. The common pattern is
`$request->all()` → set `user_id = Auth::id()` → `create`/`update` → flash `action` → redirect
to `{plural}.index`. There are no FormRequests or `validate()` calls in the active
controllers, and writes rely on `$fillable`. **When you add a column, add it to the model's
`$fillable` too.**

### Domain model and stock logic

- **`Product`:** name, `purchasing_price`, `selling_price`, `quantity` (the stock level),
  `total_price` (purchasing price × quantity, computed in the controller), and `code` (a
  random unique 5-digit number generated on store). Uses soft deletes.
- **`Bill`:** customer name and phone, `discount`, `user_id`. It `hasMany` `Order`. Uses soft
  deletes.
- **`Order`:** one bill line. It snapshots `product_name`, `product_price`, `price`,
  `quantity`, `discount`, and `date`, and has `status` (default `sold-out`). `product()`
  includes trashed products. Uses soft deletes, and **a soft-deleted order or bill counts as
  "returned"** in the statistics and in the `?returned=` filter.
- **`Note`:** free-text notes. **`Configration`:** the single settings row, which must be
  `id = 1`.

Stock changes live in the controllers. Keep these paths consistent with each other:

- **`BillController::store`:** called over AJAX from `bills/create.blade.php`. It receives
  the parallel arrays `products[]` (product **codes**), `quantity[]`, `discounts[]`, and
  optionally `costs[]`. It decrements product stock and bulk-inserts `Order` rows.
  - On failure it deletes the bill (which restores stock) and returns HTTP 400 with an Arabic
    `{error: ...}` message.
  - On success it returns the rendered `back-end.bills.bill2` receipt HTML.
- **`BillController::update`:** adjusts stock by the quantity difference. It re-creates a
  missing product with a new code, and deletes an order whose quantity is set to 0.
- **`BillController::destroy` and `OrderController::destroy`:** return quantity to the
  product, restoring a soft-deleted product if needed. Deleting a bill's last order also
  deletes the bill.
- **`ProductController::show($code)`:** looks the product up by **code**, not id, and returns
  JSON. The bill form uses it for live product lookups.
- **Dashboard statistics:** `ConfigrationController::append()` feeds
  `back-end/configrations/edit.blade.php`. `ConfigrationController::index` redirects to
  `configrations/1/edit`.

### Roles and authorization

`users.role` takes three values: `1` = master, `2` = admin, `3` = employee.

- Access control lives **only in Blade views**. Examples: `@if(Auth::user()->role == 1)` in
  `layout/side-bar.blade.php`, and `shared/buttons/delete.blade.php`.
- The `checkAdmin` middleware (`role == 1`) is registered in `Http/Kernel.php` but isn't
  applied to any route. Routes only check `auth`.
- If a change needs real enforcement, add it server-side as well.

### Shared view data

`AppServiceProvider::boot` shares `$briefs = Configration::find(1)` with every view. It
wraps the call in try/catch so the app still boots without a database.
`app/helpers/helpers.php` (autoloaded through composer `files`) provides `is_active()` for
sidebar highlighting.

## Legacy and dead code

This project began as a copy of a clinic or news website project, and several leftovers are
not wired into any route. Several of them reference models that don't exist (`News`,
`Clinic`, `Drug`, `Briefs`, `App\Client`, and others). Don't try to "fix" or extend them
unless asked:

- `HomeController`, `MobileController`, `ClientController`, `PrintController`,
  `BackEnd/BriefController`.
- `app/Http/Requests/BackEnd/*`: these requests aren't used by any active controller.
- The models `Article`, `City`, `Governorate`, `OrderProduct`, and `Bills_Product`. The
  duplicate `BillsProduct` is only used by `Bill::billedProducts()`.
- The views `front-end/*`, `ajax/*`, `bills/bill23.blade.php`, `bills/form_backup.php`,
  `welcome`, `home`, `qrcode`, `print`, and `bills`.
- The `admin/test` route runs `database:backup` and then calls `dd()`. It is debug-only.
- `ConfigrationController::getAllOrder` and `getAllPrice` compute values but return nothing.

## Conventions and gotchas

- **Match the existing style.** `.editorconfig` sets 4-space indentation and LF line endings.
  StyleCI uses the `laravel` preset.
- **Keep the existing misspelled identifiers.** They are wired into routes, views, and table
  names: `Configration` / `configrations`, `destoryRelationShip`, `paswordreset`, `sendToken`.
- **Migrations are old-style named classes.** Several are wrapped in
  `if (!Schema::hasTable(...))` because they were run against an existing production
  database. For schema changes, add a new migration and don't edit old ones.
- `Schema::defaultStringLength(191)` is set to fit the index key length limits of older
  MySQL versions.
- Several controllers call `ini_set('memory_limit', ...)`. The orders index paginates 1000
  rows per page, and the bills sidebar link loads `?dateSearch=<year>&limit=500`. Keep large
  index pages bounded.
- After login, users go to `/admin/edit-account`, set by `RouteServiceProvider::HOME` and
  `LoginController::$redirectTo`.
- **Tests:** only the example tests exist, and `phpunit.xml` configures no test database.
  Any test that touches the database uses the connection in `.env`, so set up a separate
  database, such as SQLite in memory, before writing database tests.
- **Don't commit** `.env`, `vendor/`, `node_modules/`, database dumps in `storage/backups/`,
  or new archive files.
