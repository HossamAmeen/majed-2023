# AGENTS.md

Guidance for AI coding agents working in this repository.

## What this is

An Arabic (RTL) point-of-sale and admin panel for a single clothing shop: products and
stock, bills (invoices) with barcode scanning and thermal receipts, orders (bill lines) and
returns, notes, staff users with three roles, and sales statistics.

- **Backend (repo root):** Django 6.1, Django REST Framework, SimpleJWT, django-filter,
  drf-spectacular, SQLite, Celery + Redis for scheduled backups. Python 3.13+.
- **Frontend (`frontend/`):** Vue 3 + TypeScript, Vite, Vue Router, Pinia, axios,
  Bootstrap 5 RTL, JsBarcode.
- **UI language:** Arabic. API error `detail` messages and UI strings are Arabic; keep new
  user-facing text in Arabic. Code, comments and docs are English.

## Out of scope

- `legacy_laravel/` is the previous Laravel app, kept for reference. Don't modify it; read
  it only to check old behavior.
- `xPrinter/`, `xPrinter_old.rar`, `image for setting of printer.rar`, `error_log`,
  `tasks.txt` and `my tasks.txt` are unrelated tooling or stray files. Leave them alone.

## Commands

```bash
make help            # list all commands
make install         # .venv + requirements/development.txt + frontend npm ci
make run             # migrate, then Django on :8000 + Vite on :5173 together (Ctrl+C stops both)
make run-all         # run + Celery worker and beat (needs Redis)
make run-backend     # or run-frontend: one side only
make test            # pytest + vitest
make lint            # ruff, black --check, mypy, bandit, vue-tsc, oxlint, eslint
make check           # lint + test + makemigrations --check + OpenAPI validation
```

Run backend commands with `.venv/bin/python` / `.venv/bin/pytest`. If the system npm
fails with `Cannot read properties of null (reading 'edgesOut')` (old distro npm 9), use
`npx -y npm@11 install`. The Makefile switches to npm 11 automatically when npm is older
than 10.

## Architecture

Read `docs/architecture/overview.md` and the relevant `docs/workflows/*.md` before
changing business logic, and update the workflow doc when rules change.

Each Django app in `apps/` uses the same layers. Keep them separate:

- `views.py`: thin. Validate with a serializer, call a service, and return a serializer of
  the result. Permissions come from `apps/common/permissions.py`.
- `serializers.py`: validation and representation only. Plain CRUD models (notes,
  configuration) use `utils.serializers.BaseModelSerializer` for audit fields.
- `services.py`: business operations and `transaction.atomic`. Raise
  `apps.common.exceptions.BusinessRuleError` (400) with an Arabic message.
- `selectors.py`: querysets, `select_related`/`prefetch_related`, aggregates.
- `filters.py`: django-filter FilterSets. Lists use limit/offset pagination.

API routes live under `/api/v1/` (`config/urls.py`). Each app registers its ViewSets with
a `SimpleRouter`, not a `DefaultRouter`, because several routers are mounted at the same prefix.

## Domain rules that are easy to break

- **Soft delete is meaningful.** `Product`, `Bill`, `Order` and `User` inherit
  `SoftDeleteModel`. `Model.objects` hides deleted rows; `Model.all_objects` includes them.
  A soft-deleted order or bill **is a return**, and statistics and `?returned=true` read
  them. Never hard-delete sales.
- **Stock changes only through `apps.products.services.StockService`.** `take()` is a
  conditional `UPDATE ... quantity >= n` and raises `InsufficientStockError`.
  `give_back()` adds units, restores a soft-deleted product with only the returned units, or
  re-creates a vanished product under a new code. `BillService` and `OrderService` use it.
  Keep sales, bill edits and returns consistent with each other.
- **Services re-query orders** with `Order.objects.filter(bill=bill)`. They avoid
  `bill.orders`, because views may pass a bill with a stale prefetch cache.
- **Order lines copy** `product_name`, `product_price` and `price` at sale time. Line total is
  `price * quantity - discount` (the discount applies to the whole line). Bill net is the sum
  of line totals minus the bill discount.
- **Product `code`** is a server-generated 5-digit number, unique among alive products
  (partial unique constraint) and never reused (`ProductCodeService` checks `all_objects`).
- **`ShopConfiguration`** is a singleton (`pk=1`, created by migration
  `dashboard/0003`); use `ShopConfiguration.load()`.
- **Roles** (`utils.enums.Role`): 1 master (users, products, everything), 2 admin
  (statistics and configuration), 3 employee (bills, orders, notes). Enforce roles in
  permission classes. The Vue router meta and sidebar only hide pages.
- **`user_name` is the login field.** `UserNameOrEmailBackend` also accepts email. Unique
  checks on `user_name` must use `User.all_objects` (deleted users still hold their names).
- **Every model inherits `apps.common.models.BaseModel`** (`created_at`, `updated_at`,
  `created_by`, `updated_by`). Services set the audit users explicitly.
- **Error body** is always `{"detail": str, "errors": dict | null}`
  (`apps.common.exceptions.api_exception_handler`). The frontend reads it with
  `getErrorMessage` / `getFieldErrors` in `frontend/src/utils/errors.ts`.

## Conventions and gotchas

- **Formatting:** black and ruff (line length 110) for Python; prettier (no semicolons,
  single quotes, width 100) for the frontend. `.editorconfig` sets 4 spaces for Python and
  2 spaces for frontend files.
- **Type hints** on backend code; mypy runs with the django-stubs and DRF plugins.
- **Migrations:** add new migrations; don't edit applied ones. After model changes, run
  `makemigrations` and commit the result. `make check` fails on missing migrations.
- **Settings:** Django 6.1 email uses `MAILERS` (built by `build_mailers()` in
  `config/settings/base.py`), not the deprecated `EMAIL_*` settings.
- **Tests:** pytest with fixtures in the root `conftest.py` (`auth_client`, `master`,
  `admin_user`, `employee`) and factories in `apps/*/tests/factories.py`. Group tests in
  `Test*` classes with an autouse `setup` fixture. Keep the
  `import rest_framework.throttling` in `conftest.py`: without it, freezegun tests break
  throttling for later tests.
- **PDF export** uses WeasyPrint (needs Pango), and its templates use
  `{% localize off %}` so numbers are not formatted with Arabic decimal commas. The Docker
  image installs `fonts-noto-core` for Arabic glyphs.
- **Frontend list pages** use `usePaginatedList`. It returns plain refs inside an object,
  so templates need `.value` (for example `list.items.value`).
- **Printable pages** (`/bills/:id/receipt`, `/products/:id/barcode`) use `meta.blank`
  and auto-print with `?print=1`. Open them with `openPrintWindow()`.
- **Don't commit** `.env`, `.venv/`, `db.sqlite3*`, `backups/`, `logs/*.log`,
  `frontend/node_modules/` or `frontend/dist/`.
