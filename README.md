# 4MD Shop

Point of sale and admin panel for a single clothing shop (Arabic, RTL): products and stock,
bills with barcode scanning and thermal receipts, orders and returns, notes, staff accounts,
and daily / monthly / yearly sales statistics.

- **Backend:** Django 6.1, Django REST Framework, SimpleJWT, SQLite, Celery + Redis (scheduled backups)
- **Frontend:** Vue 3 + TypeScript, Vite, Vue Router, Pinia, Bootstrap 5 RTL (`frontend/`)

This replaces the previous Laravel app, which is kept untouched in `legacy_laravel/` for
reference. See [Changes from the Laravel version](#changes-from-the-laravel-version).

## Contents

- [Local setup](#local-setup)
- [Environment variables](#environment-variables)
- [Moving data from the Laravel database](#moving-data-from-the-laravel-database)
- [Docker](#docker)
- [Tests, linting, formatting](#tests-linting-formatting)
- [Deployment](#deployment)
- [Documentation](#documentation)

## Local setup

Requirements: Python 3.13+, Node 22+, and the Pango library for PDF export
(`libpango-1.0-0` on Debian/Ubuntu; already present on most desktop Linux systems).

Quick start with the Makefile (`make help` lists every command):

```bash
make install     # .venv, Python packages, frontend packages
make superuser   # first master account (role 1); runs on the database from DATABASE_URL
make run         # migrates, then starts the API (:8000) and the Vue app (:5173); Ctrl+C stops both
```

`make run` accepts `BACKEND_PORT=8001 FRONTEND_PORT=5174` (and `BACKEND_HOST` /
`FRONTEND_HOST`). `make run-all` also starts the Celery worker and beat (needs Redis), and
`make run-backend` / `make run-frontend` start one side only.

The same steps by hand:

```bash
# Backend
python3 -m venv .venv
.venv/bin/pip install -r requirements/development.txt
cp .env.example .env                # adjust if needed
.venv/bin/python manage.py migrate  # also creates the shop configuration row
.venv/bin/python manage.py createsuperuser   # first master account (role 1)
.venv/bin/python manage.py runserver         # http://127.0.0.1:8000

# Frontend (second terminal)
cd frontend
npm ci
npm run dev                          # http://localhost:5173, proxies /api to :8000
```

Sign in at <http://localhost:5173> with the superuser's user name (or email) and password.
Masters and admins land on the statistics page; employees land on the new bill screen.

API docs: <http://127.0.0.1:8000/api/docs/>. Django admin: <http://127.0.0.1:8000/admin/>.

Scheduled backups need Redis plus a worker and beat (`make run-all`, or `make worker` and
`make beat`). Without
them, run `make backup` from cron.

## Environment variables

Backend (`.env`, see `.env.example`):

| Variable | Default | Purpose |
|---|---|---|
| `SECRET_KEY` | dev key in development; **required** in production | Django secret |
| `DEBUG` | `True` (dev) / `False` (prod) | |
| `ALLOWED_HOSTS` | `localhost,127.0.0.1` (dev); **required** in production | |
| `DATABASE_URL` | `sqlite:///db.sqlite3` | SQLite file (use an absolute path in production) |
| `TIME_ZONE` | `Africa/Cairo` | Decides what "today" means in statistics |
| `BACKUP_DIR` | `backups/` | Where `backup_database` writes copies |
| `FRONTEND_URL` | `http://localhost:5173` | Used in password reset emails |
| `CORS_ALLOWED_ORIGINS` | Vite dev URLs in development | Only needed if the SPA is on another origin |
| `CSRF_TRUSTED_ORIGINS` | empty | For the Django admin behind HTTPS |
| `JWT_ACCESS_MINUTES` / `JWT_REFRESH_HOURS` | `30` / `12` | Token lifetimes |
| `REDIS_URL` | `redis://localhost:6379/0` | Celery broker |
| `EMAIL_BACKEND`, `EMAIL_HOST`, `EMAIL_PORT`, `EMAIL_HOST_USER`, `EMAIL_HOST_PASSWORD`, `EMAIL_USE_TLS`, `DEFAULT_FROM_EMAIL` | console backend in development | Password reset emails |
| `SECURE_SSL_REDIRECT` | `True` in production | Set `False` only if TLS is not terminated in front of the app |
| `LOG_LEVEL` | `INFO` | Logs go to the console and `logs/app.log`, `logs/error.log` |
| `LEGACY_DB_*` | | Only for the one-time Laravel import |

Frontend (`frontend/.env`, see `frontend/.env.example`):

| Variable | Default | Purpose |
|---|---|---|
| `VITE_API_BASE_URL` | `/api/v1` | API root seen by the browser |
| `DEV_API_PROXY_TARGET` | `http://127.0.0.1:8000` | Where the Vite dev server proxies `/api` |

## Moving data from the Laravel database

The importer copies users (passwords keep working), products, bills, orders (including
returned ones), notes and the shop details, keeping ids and dates.

```bash
.venv/bin/python manage.py migrate            # fresh, empty database
export LEGACY_DB_HOST=127.0.0.1 LEGACY_DB_USER=root LEGACY_DB_NAME=shop LEGACY_DB_PASSWORD=...
.venv/bin/python manage.py import_legacy_mysql
```

It runs in one transaction and refuses to run on a database that already has data. What
it repairs and skips is listed in
[docs/workflows/notes-backups-legacy.md](docs/workflows/notes-backups-legacy.md).
Try it on a copy first and compare the statistics page with the old system.

## Docker

Development (Django runserver, Celery worker and beat, Redis, Vite):

```bash
make up        # docker compose up --build
docker compose exec api python manage.py createsuperuser
```

The app is at <http://localhost:5173>. The SQLite database lives in the `sqlite_data` volume.

Production (gunicorn, Celery, Redis, nginx serving the built SPA and proxying `/api` and `/admin`):

```bash
cp .env.example .env   # set SECRET_KEY, ALLOWED_HOSTS, DEBUG=False, CSRF_TRUSTED_ORIGINS, email
make prod-up           # docker compose -f docker-compose.prod.yml up -d --build
docker compose -f docker-compose.prod.yml exec api python manage.py createsuperuser
```

nginx listens on port 80 (override with `HTTP_PORT=8080`). Terminate HTTPS in front of it
(or keep `SECURE_SSL_REDIRECT=True` only when requests arrive over HTTPS).

## Tests, linting, formatting

```bash
make test      # pytest (backend) + vitest (frontend)
make lint      # ruff, black --check, mypy, bandit, vue-tsc, oxlint, eslint
make format    # ruff --fix, black, prettier
make check     # lint + test + Django checks, missing migrations, OpenAPI schema validation
```

Backend tests use an in-memory SQLite database (`config.settings.test`). Pre-commit hooks:
`.venv/bin/pre-commit install`.

## Deployment

1. Build and start the production stack as shown above, or run gunicorn, a Celery worker,
   Celery beat and Redis yourself, with `DJANGO_SETTINGS_MODULE=config.settings.production`.
2. Serve `frontend/dist/` (from `npm run build`) with any web server, falling back to
   `index.html`, and proxy `/api/` and `/admin/` to gunicorn. `frontend/nginx.conf` is a
   working example.
3. Run `python manage.py migrate` and `collectstatic` on each release.
4. Back up the database volume and the files in `BACKUP_DIR` off the server.

SQLite runs in WAL mode with `IMMEDIATE` transactions, which suits one shop with a few
cashiers. Keep the database on a local disk (not a network share).

## Documentation

- [docs/architecture/overview.md](docs/architecture/overview.md): layers and folder layout
- [docs/workflows/](docs/workflows/): business rules, states and APIs per module
- [docs/api/README.md](docs/api/README.md): OpenAPI / Swagger

## Changes from the Laravel version

Behavior intentionally kept: roles and what each role sees, 5-digit product codes, stock
rules for sales and returns (including restoring deleted products), soft-deleted orders and
bills counting as returns, the statistics figures, the receipt layout, product exports for
the current year, and all user-facing text in Arabic.

Fixed or changed:

- **Permissions are enforced by the API.** The old app only hid links, so any signed-in user
  could open admin URLs, and `bills/search` needed no login at all.
- **Saving a bill is one transaction.** A failed bill used to be soft-deleted, which counted
  it as a "return" in the statistics. Two cashiers can no longer oversell the same stock.
- **Editing a bill** rolls back completely on a stock error (it used to stop half way). A
  bill whose lines are all set to 0 is returned. A line on a deleted product restores that
  product instead of creating a duplicate.
- **New validation:** discounts cannot exceed the line or bill total; quantities and prices
  cannot be negative; nobody can create masters through the API, change their own role, or
  delete their own account.
- **Yesterday's net profit** now subtracts bill discounts, like today's figure.
- **Product totals** on the products page follow the active filter (for example "running out").
- **Lists are paginated** instead of loading everything, or cutting off at 2022 / 500 rows.
- **Password reset** uses Django's expiring signed tokens, and the response does not reveal
  whether an email is registered.
- **Time zone** is `Africa/Cairo` (the old app used UTC, so "today" changed at 2 or 3 AM).
- **The receipt header and return policy** are editable on the statistics page instead of
  hard-coded.
- **Backups** use SQLite's online backup API twice a day instead of `mysqldump`.
- **Dropped:** unused leftovers (categories, governorates, cities, clinic/news code), the
  stored `total_price` (now computed), the unused product `discount`, and public registration.
