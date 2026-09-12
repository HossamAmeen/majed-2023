# AGENTS.md

Guidance for AI agents and new developers working in this repository.

---

## 1. What this application is

A single-tenant **Arabic (RTL) point-of-sale and inventory back office** for a
ready-made clothing shop (receipt header reads "جولدن فنجرز للملابس الجاهزه", tax
file and commercial-register numbers are hard-coded in the receipt template).

There is **no public front end and no API**. Everything lives under the `/admin`
URL prefix behind session auth. The whole product is:

| Capability | Where |
|---|---|
| Barcode-driven sale entry (fast keyboard/scanner flow) | [bills/create.blade.php](resources/views/back-end/bills/create.blade.php), [bills/form.blade.php](resources/views/back-end/bills/form.blade.php) |
| 58 mm thermal receipt printing | [bills/bill.blade.php](resources/views/back-end/bills/bill.blade.php), [bills/bill2.blade.php](resources/views/back-end/bills/bill2.blade.php) |
| Product catalogue + stock levels + auto barcode codes | [ProductController.php](app/Http/Controllers/BackEnd/ProductController.php) |
| Barcode label printing (Code 39E) | [products/printBarCode.blade.php](resources/views/back-end/products/printBarCode.blade.php) |
| Sales / returns / profit dashboard | [ConfigrationController::append()](app/Http/Controllers/BackEnd/ConfigrationController.php#L142), [configrations/edit.blade.php](resources/views/back-end/configrations/edit.blade.php) |
| Order (line-item) browsing, filtering, returns | [OrderController.php](app/Http/Controllers/BackEnd/OrderController.php) |
| Excel + PDF product exports | [ProductsExport.php](app/Exports/ProductsExport.php), `exportPDF()` |
| Users/roles, notes, shop settings, password reset by e-mail token | `UserController`, `NoteController`, `ConfigrationController` |
| Twice-daily `mysqldump` backup | [BackupDatabase.php](app/Console/Commands/BackupDatabase.php) |

All UI strings are Arabic and the layout is RTL (`dir="rtl"`, `bootstrap-rtl.css`).
**Keep new user-facing strings Arabic** — mixing English into the UI breaks
consistency with every existing screen.

---

## 2. Stack, and the two parallel versions of this codebase

This repo contains the *same application* at two framework generations. Know
which one you are editing before you touch anything.

| | Working tree / `update/bill-performance`, `main` | `DRF` / `update-to-last-version` |
|---|---|---|
| PHP | `^7.1.3` | `^8.1` |
| Laravel | `5.8.*` | `^10.10` |
| Routing style | string actions `'BackEnd\XController@method'` + `->namespace()` | `[XController::class, 'method']` |
| Factories/seeders | `$factory->define()`, `database/seeds/` | class-based factories, `database/seeders/` |
| Helpers | `str_random()`, `str_plural()` | `Str::random()`, `Str::plural()` |
| `db-backup` package | `haruncpi/laravel-db-backup` present | removed |
| Maintenance middleware | `CheckForMaintenanceMode` | `PreventRequestsDuringMaintenance` |
| Extras | — | `laravel/ui`, `config/cors.php`, `guzzle` |

History is **linear**, not divergent:

```
4966058  update to php version 8.2        <- DRF, update-to-last-version (Laravel 10 upgrade)
96ad69b  fix add button                   <- update/bill-performance  (usually the checked-out branch)
32c5b92  update/ bill performance
eb23aed  update pdf package               <- main, origin/main
bb5e5bc  initialise project
```

So `DRF` is exactly `update/bill-performance` **plus one commit** that performs
the entire Laravel 5.8 → 10 upgrade. `main` is two commits behind.

**Rules of thumb**

- Run `git branch --show-current` first. Match the idioms of that branch.
- A behaviour fix usually needs to be made on both tracks (the Laravel 10 commit
  copied logic almost verbatim, so fixes do not merge automatically if the
  branches drift).
- Do **not** "modernise" 5.8 syntax on the 5.8 branches — that is what the `DRF`
  branch exists for.
- `vendor/` and `node_modules/` are **not installed** in this checkout. Nothing
  can actually be executed until `composer install` / `npm install` is run, and
  on the 5.8 branch that requires a PHP 7.x runtime.

---

## 3. Commands

```bash
composer install                # PHP deps (PHP 7.x for 5.8 branches, 8.1+ for DRF)
npm install                     # or: yarn
cp .env.example .env && php artisan key:generate

php artisan migrate             # 12 migrations, MySQL
php artisan db:seed             # creates admin user: user_name "admin" / password "admin", role 1

php artisan serve               # http://localhost:8000/admin
npm run dev                     # Laravel Mix build (webpack)
npm run watch
npm run prod

php artisan test                # or: ./vendor/bin/phpunit  — only the two stock example tests exist
php artisan database:backup     # writes storage/backups/<Y-m-d>.sql via mysqldump
```

Style: `.editorconfig` (4 spaces, LF, UTF-8, final newline) and `.styleci.yml`
(`laravel` preset, `unused_use` disabled). There is no local linter configured —
StyleCI ran server-side. Match surrounding formatting rather than reformatting
files.

---

## 4. The core architectural pattern: convention-driven CRUD

Nearly every screen is generated by one abstract controller,
[`BackEndController`](app/Http/Controllers/BackEnd/BackEndController.php).
Understanding it is the single highest-value thing to know about this codebase.

A concrete controller only injects a model:

```php
class ProductController extends BackEndController
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }
}
```

From `class_basename($model)` the base controller derives **everything else**:

```
Model  Product
  -> getModelName()         "Product"          page titles
  -> pluralModelName()      "Products"         module name
  -> getClassNameFromModel() "products"        view folder AND route name
  -> view                   back-end.products.index / .create / .edit
  -> redirect               route('products.index')
```

This means **the model class name, the `resources/views/back-end/<folder>/`
directory, and the `Route::resource()` name must agree**, or you get a
`View not found` / `Route not defined` error at runtime. When adding a module:

1. `app/Models/Thing.php`
2. `app/Http/Controllers/BackEnd/ThingController.php` extending `BackEndController`
3. `resources/views/back-end/things/{index,create,edit,form}.blade.php`
4. `Route::resource('things', 'BackEnd\ThingController')` inside the `auth` group
5. a sidebar entry in [side-bar.blade.php](resources/views/back-end/layout/side-bar.blade.php)

### Provided actions

| Method | Behaviour |
|---|---|
| `index()` | `filter()` → optional `with()` eager loads → `orderBy('id','DESC')` → `take(request('limit'))` if `?limit=` else **`->get()` (no pagination)** → renders `<folder>.index` with `$rows`, `$pageTitle`, `$pageDes`, `$moduleName`, `$sModuleName`, `$routeName` plus whatever `appendIndex()` returns |
| `create()` | renders `<folder>.create` with `$folderName`, `$routeName`, plus `append(null)` |
| `edit($id)` | `findOrFail` → renders `<folder>.edit` with `$row` plus `append($row)` |
| `show($id)` | **redirects to index** (deliberately not implemented) |
| `destroy($id)` | soft-delete + `destoryRelationShip($id)` hook → redirect to index |
| `store`, `update` | **not provided** — every subclass writes its own |

### Extension hooks (override these, not `index()`)

```php
protected function filter($rows)        // apply query-string filters; return builder
protected function with()               // array of relations to eager load
protected function append($row)         // extra view data for create/edit
protected function appendIndex($rows)   // extra view data for index (totals, counters)
protected function destoryRelationShip($id)  // cascade cleanup on delete (note the typo)
```

Helper methods also inherited: `uploadImage($request, $h, $w)` (Intervention,
writes to `uploads/<folder>/`) and `storeFile($file)`. Both are currently
**commented out at every call site** — image upload is dormant, and `storeFile()`
is broken on the 5.8 branch (references the removed `Input` facade).

### Shared view components

- `@extends('back-end.layout.app')` → `nav`, `side-bar`, `@stack('css')`, `@stack('js')`
- `@component('back-end.shared.table')` — panel wrapper; also injects the global
  `check()` bootbox confirm used by every delete button
- `@component('back-end.shared.create')` — panel wrapper for forms
- `back-end.shared.buttons.delete` — edit + delete buttons, rendered **only for
  `role == 1`**
- `<folder>/form.blade.php` is `@include`d by both `create` and `edit`; fields use
  the `@php $input = "name"; @endphp` + `isset($row) ? $row->{$input} : ''` idiom
  so one partial serves both

---

## 5. Domain model and data flow

### Tables that matter

```
users ──┬─< products >── categories
        ├─< bills ──< orders >── products
        └─< notes
configrations   (single row, id = 1 — shop settings)
governorates ──< cities        (seeded, otherwise unused)
```

**`orders` is the line-item table.** One `bills` row is a receipt; its `orders`
rows are the items on it. Despite the names, `bills_products` and
`order_products` tables, and the `BillsProduct` / `Bills_Product` /
`OrderProduct` models, are **vestigial and never written to**. `Bill::product()`
(a `belongsToMany`) and `Bill::billedProducts()` are likewise dead — only
`Bill::orders()` is live. `bills.is_selling` and `bills.product_id` are never set.

Key columns:

- `orders.price` — actual unit price charged (may be overridden per line)
- `orders.product_price` — snapshot of `products.selling_price` at sale time
- `orders.product_name` — **denormalised snapshot**, so receipts still print
  after a product is deleted
- `orders.status` — string, defaults to `'sold-out'`; the dashboard filters on
  `where('status','sold-out')` and nothing ever writes another value
- `orders.date` — a `date` column distinct from `created_at`; **all order
  reporting filters on `date`**, while bill reporting filters on `created_at`
- `products.code` — the barcode, a random 5-digit number generated on create
- `users.role` — `1 = master`, `2 = admin`, `3 = employee`

`SoftDeletes` is used on users, products, categories, bills, orders.
**A soft-deleted order means a returned item** (`onlyTrashed()` powers the
"المرتجعات" / returns screens). Deletion is therefore business data, not
housekeeping — never hard-delete these rows and never `forceDelete()`.

### Selling: `BillController::store()`

`POST admin/bills` is submitted by AJAX from `bills/create.blade.php` with
parallel arrays `products[]`, `quantity[]`, `discounts[]`, (optionally `costs[]`)
indexed positionally.

```
1. attach Auth::user()->id, create the Bill row up front
2. for each products[i]:
     - skip null / "null" entries (blank rows from the dynamic form)
     - look up Product by CODE (not id)
     - if not found        -> destroy($bill->id)  [full rollback, restoring stock]
                              and return 400 {error: "كود هذا المنتج غير صالح ..."}
     - price = costs[i] ?? product->selling_price
     - qty   = quantity[i] ?? 1
     - if product->quantity < qty -> destroy($bill->id) and return 400
     - else decrement product->quantity and save
     - push an Order row (price, product_price, product_name, date, discount, ids)
3. Order::insert($data)          // single bulk insert — the "bill performance" work
4. reload bill with orders and RETURN THE RENDERED RECEIPT HTML (back-end.bills.bill2)
```

The response body is the **receipt markup**, which the page injects into
`#printDivBill`; the error path returns JSON `{error: ...}` with status 400 and
the JS reads `xhr.responseJSON.error`. If you change either shape, update
[bills/create.blade.php](resources/views/back-end/bills/create.blade.php)'s
`$("#addForm").submit()` handler with it.

There is **no transaction**. Rollback is manual: on any bad line the controller
calls `$this->destroy($bill->id)`, which re-adds stock for already-inserted
orders. Note the ordering hazard — `destroy()` re-increments from `orders` rows,
so lines processed before the failure are restored, but the in-loop stock
decrement of the failing iteration has not happened yet. This is the most
fragile logic in the app; if you refactor it, wrap it in `DB::transaction()`
rather than extending the manual compensation.

Bulk `Order::insert()` **bypasses Eloquent** — no timestamps, no model events,
no casts. Keep `created_at`/`updated_at` in mind if you add anything that reads
them on orders.

### Editing a bill: `BillController::update()`

Receives `orders[]` (existing order ids) and parallel `quantity[]`. Per line it
computes the stock delta as `(product->quantity + order->quantity) - new_qty`,
i.e. it returns the old quantity to stock and takes the new one. Special cases:

- `quantity[i] == 0` → the order is soft-deleted (the item is returned)
- the product no longer exists → a **replacement product is created** from
  `order->product_name` with a fresh random code and `abs(old - new)` quantity

### Deleting a bill: `BillController::destroy()`

Soft-deletes the bill, then for each of its orders restores stock. If the product
row was soft-deleted it is **restored** (`withTrashed()->restore()`) and its
quantity is *set* (not incremented) to the order quantity. Then each order is
soft-deleted. Redirects `back()`.

### Deleting an order: `OrderController::destroy()`

Restores stock the same way, and **if that order was the last one on its bill,
soft-deletes the bill too** — so a receipt never survives with zero items.

### Products

- `store()` computes `total_price = purchasing_price * quantity` and generates a
  unique random 5-digit `code` via `generateRandomNumber(5)` + `checkNumber()`
  retry loop.
- **`show($code)` is an AJAX lookup by barcode, not a detail page.** `GET
  admin/products/{code}` returns `{product: {name, selling_price, quantity},
  message}` and is called by `getProduct()` in the bill form on every scan. Do
  not "fix" it to look up by id — the sale flow depends on the code lookup.
- `appendIndex()` supplies the index-page counters, using two raw aggregates:
  `Product::value(DB::raw("SUM(quantity * purchasing_price)"))` and the same for
  `selling_price`. `productsCount`/`totalQuantity` are computed from the
  already-loaded collection.
- `filter()` implements the low-stock view: `?quantity=5` → `where('quantity','<',5)`.
  The index also red-flags rows with `quantity < 3` in the view.

### Reporting filters (query string is the API)

`OrderController::filter()` — note it starts with a hard floor of
`whereDate('date','>=','2022-01-01')` on every request:

| Param | Effect |
|---|---|
| `?day=Y-m-d` | that day, within the current year+month |
| `?month=m` | that month of the current year |
| `?year=Y` | that year |
| `?returned=1` | switch to `onlyTrashed()` (returns) |
| `?returnedDay=Y-m-d` | returned-on-that-day |
| `?dateSearch=Y-m-d` | `date >= value`, **and remembers it in the session** |

`BillController::filter()` accepts `?dateSearch=` but applies it as
`whereYear('created_at','>=', value)` — so it expects a **year**, which is why
the sidebar links to `admin/bills?dateSearch=2026&limit=500`. Passing a full date
here will not do what the parameter name suggests.

`BillController::search()` (`GET admin/bills/search?search=`) returns JSON bills
matching `name LIKE %…%`. Its route **must stay declared before**
`Route::resource('bills')`, or `bills/{bill}` swallows it.

### Dashboard: `ConfigrationController`

`index()` redirects to `configrations.edit/1`, so the "dashboard" is the edit
screen of the single settings row, and all statistics are injected by
`append($row)`. It builds, for today / yesterday / this month / this year:

```
ordersMoney         = Σ(quantity * price) - Σ(discount)        over orders with status 'sold-out'
productBuyingPrice  = Σ(quantity * product->purchasing_price)  (needs the product relation)
billDiscount        = Σ(bills.discount)                        for the period
netProfit (view)    = dayOrdersMoney - dayProductBuyingPrice - dayBillDiscount
returns             = onlyTrashed() equivalents of the above
expiredProduct      = count of products with quantity == 0
```

Every one of these is a separate query that **loads whole collections into PHP
and sums in memory** (`->get()->sum(fn)`), with `with(['product'])` to avoid N+1
on purchasing price. `Order::product()` uses `withTrashed()` precisely so deleted
products still contribute a purchasing price. On a large `orders` table this page
is the main performance hazard — `BackEndController::__construct()` and a few
other places set `ini_set("memory_limit", "10056M")` to paper over it. If you
optimise, convert these to SQL aggregates (`selectRaw('SUM(quantity * price)')`)
rather than raising the limit further.

Discount math on the receipt ([bill2.blade.php](resources/views/back-end/bills/bill2.blade.php)):

```
sum      = Σ(order.quantity * order.price)
discount = Σ(order.discount)
total    = sum - discount - bill.discount
```

Per-line discounts and one bill-level discount are **both** subtracted. Keep
these consistent if you touch either the receipt or the dashboard.

---

## 6. Auth, roles, and the settings singleton

- Routes: `Auth::routes()` inside `prefix('admin')`; everything else is wrapped in
  `Route::middleware('auth')`.
- `LoginController` overrides `username()` to accept **either e-mail or
  `user_name`** in a single `login` field (`findUsername()` sniffs with
  `FILTER_VALIDATE_EMAIL` and merges the right key into the request). After login
  it redirects to `/admin/edit-account`.
- Password reset is **custom, not Laravel's**: `ConfigrationController::sendToken()`
  writes `md5(rand.microtime())` into `users.remember_token` and mails a link;
  `paswordreset/{id}/{token}` compares it and sets the new password. (Note the
  misspelled route, the sender address hard-coded to `info@tibaroyal.com`, and
  that this reuses `remember_token` for reset tokens.) The stock
  `ForgotPassword`/`ResetPassword` controllers and `password_resets` table exist
  but are unused.
- **`checkAdmin` middleware is registered in `Http/Kernel` but applied to no
  route.** Authorisation is enforced only in Blade: the sidebar and the shared
  delete button check `Auth::user()->role == 1` (and `== 2` for the dashboard
  link). Every route is therefore reachable by any logged-in user who types the
  URL. Treat this as a known gap, not a pattern to copy — if you add privileged
  actions, apply `->middleware('checkAdmin')` on the route.
- `AppServiceProvider::boot()` does `View::share('briefs', Configration::find(1))`
  inside a `try/catch` that swallows DB connection failures, and sets
  `Schema::defaultStringLength(191)`. `$briefs` is used by the layout, the
  sidebar and the receipt. **If `configrations` row 1 is missing, `$briefs` is
  null and those views throw** — the seeder creates it, so always seed.

---

## 7. Traps, dead code and committed cruft

Things that look like bugs, are, or will waste your time:

- **Unreachable `return` statements everywhere.** `BillController::store()` ends
  with five consecutive `return`s; only the first executes. The same pattern is
  in `update()`, `destroy()`, `UserController::editAccount()`. They are leftover
  alternative responses. Don't assume the last line is the live one.
- **`ProductsExport`** implements `FromArray`; its `array()` returns a header row
  plus **one nested array of all rows** (not a flat list), and the leftover
  `collection()` method references an unimported `Collection`. The Excel export
  is effectively broken/misshapen.
- **`ConfigrationController::test()`** (`GET admin/test`) calls
  `Artisan::call('db:backup')` — the `haruncpi/laravel-db-backup` command — while
  the scheduler calls `database:backup` (the local `BackupDatabase` command).
  `DRF` fixed `test()` to `database:backup` and dropped the package. Also note
  `BackupDatabase` catches `ProcessFailedException` without importing it, so a
  failing dump throws instead of logging.
- **`BriefController`** injects `App\Models\Briefs`, which does not exist, and
  references `Brief::find()` and routes (`pref.edit`, `adminPanel/login`) that
  do not exist. It is unroutable dead code.
- **`HomeController` (423 lines), `ClientController`, `MobileController`,
  `PrintController`** are unreferenced leftovers from the template this project
  was cloned from. So are `app/Models/{Article,City,Governorate}` (the latter two
  only seeded), most of `app/Http/Requests/BackEnd/` (`ClienicRequest`,
  `NewsRequest`, `DrugRequest`, `VideoRequest`, `GalleryRequest`,
  `QuestionRequest`, `OpertionRequest`, `ServiceRequest` — none are used by any
  controller), and the `front-end/`, `ajax/`, `qrcode`, `welcome`, `home` views.
- **No validation on the live write paths.** `UserRequest` exists but
  `UserController` type-hints plain `Request` and calls `$request->all()`; bills,
  products, orders and notes are mass-assigned unvalidated. Add a `FormRequest`
  if you touch these, but expect no existing example to follow.
- **`routes/api.php` is empty** and `web.php` imports a non-existent
  `App\Http\Controllers\API\UserController` (harmless — unused import).
- Naming typos are load-bearing and must be preserved: **`Configration`**
  (model, table, route, views), **`destoryRelationShip`**, **`paswordreset`**,
  **`ClienicRequest`**. Renaming any of them breaks the convention chain in §4.
- `Product::category()` references lowercase `category::class`; it resolves only
  because PHP class names are case-insensitive.
- Committed binaries and junk that should not be relied on: `error_log` and
  `public/error_log` (2021 stack traces from an unrelated shared-hosting deploy
  of a project called "majed"), `image for setting of printer.rar`,
  `xPrinter_old.rar`, an empty `xPrinter/xPrinter` directory, and `.idea/`.
- `tasks.txt` / `my tasks.txt` are the **template project's** backlog (news,
  articles, galleries, swagger) and describe features this app does not have.
  Do not treat them as requirements.
- `resources/views/back-end/bills/` contains `bill.blade.php`, `bill2.blade.php`,
  `bill23.blade.php`, `print.blade.php`, `printBill.blade.php` and
  `form_backup.php`. Only `bill.blade.php` (route `bills.print`) and
  `bill2.blade.php` (route `bills.print2`, and the `store()` response) are live.
- `bills/form.blade.php` has an **unbalanced Blade comment** in its hidden
  `#toggle` block (a stray `--}}` around the price input) and builds extra
  product rows by string-concatenating HTML inside `showDiv()`. It is delicate;
  edit with care and test the scanner flow.
- The barcode scanner flow depends on CSS class ordering:
  `this.className.split(' ')[2].slice(-1)` parses the row index out of
  `class="form-control bcode bcodess barcode1"`. Changing class order or names on
  those inputs breaks Enter-key advance.
- No pagination on `bills`, `products`, `users` index pages — `?limit=` is the
  only guard. `OrderController` overrides `index()` solely to
  `paginate(1000)`.

---

## 8. Conventions checklist for changes

- Business logic lives in the controller. There is no service/repository layer;
  don't introduce one for a small change.
- Flash messages use `session()->flash('action', 'تم التحديث بنجاح')` and are
  rendered by the index/create views.
- Success paths redirect to `route($this->getClassNameFromModel().'.index')`.
- Add filters via `filter()`, page totals via `appendIndex()`, form lookups via
  `append()` — not by overriding `index()`/`edit()`.
- Eager-load through `with()` (e.g. `BillController::with()` returns `['orders']`);
  index views freely touch `$item->user->user_name ?? " "`, so add relations
  there rather than accepting N+1.
- Asset paths are `asset('panel/assets/...')` (a vendored admin theme in
  `public/panel/`); RTL stylesheets are the `-rtl` variants.
- AJAX calls must send the CSRF header — the pattern in use is
  `$.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}})`.
- Tests: only `tests/Unit/ExampleTest.php` and `tests/Feature/ExampleTest.php`
  (stock stubs) exist. There is no test coverage of the sale/stock logic, so
  verify changes to §5 by hand: create a bill by code, check stock decremented,
  edit a quantity, delete a line, delete the bill, and confirm stock and the
  dashboard figures return to their previous values.
