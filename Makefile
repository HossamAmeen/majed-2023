SHELL := /bin/bash
.DEFAULT_GOAL := help

PYTHON ?= .venv/bin/python
MANAGE = $(PYTHON) manage.py
BIN = .venv/bin
FRONTEND = frontend
COMPOSE = docker compose

BACKEND_HOST ?= 127.0.0.1
BACKEND_PORT ?= 8000
FRONTEND_HOST ?= 127.0.0.1
FRONTEND_PORT ?= 5173

# npm 9 (shipped by some Linux distros) crashes while installing the frontend
# ("reading 'edgesOut'"), so use npm 11 through npx when the system npm is older than 10.
NPM_MAJOR := $(shell npm --version 2>/dev/null | cut -d. -f1)
NPM ?= $(if $(filter 1 2 3 4 5 6 7 8 9,$(NPM_MAJOR)),npx -y npm@11,npm)

.PHONY: help install migrate makemigrations shell superuser run run-backend run-frontend run-all \
	test lint format check worker beat backup import-legacy frontend-install frontend-build \
	frontend-test frontend-lint up down prod-up prod-down _require-install

help: ## Show available commands
	@grep -E '^[a-zA-Z_-]+:.*?## ' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-18s\033[0m %s\n", $$1, $$2}'

install: ## Create .venv and install backend + frontend dependencies
	test -d .venv || python3 -m venv .venv
	$(BIN)/pip install -r requirements/development.txt
	-$(BIN)/pre-commit install
	cd $(FRONTEND) && $(NPM) ci

migrate: ## Apply database migrations
	$(MANAGE) migrate

makemigrations: ## Create migrations after model changes
	$(MANAGE) makemigrations

shell: ## Django shell
	$(MANAGE) shell

superuser: ## Create a master (role 1) account
	$(MANAGE) createsuperuser

run: _require-install ## Run the app: API on :8000 and Vue on :5173 (Ctrl+C stops both)
	@$(MANAGE) migrate --noinput
	@echo "API:      http://$(BACKEND_HOST):$(BACKEND_PORT)/api/docs/"
	@echo "Frontend: http://$(FRONTEND_HOST):$(FRONTEND_PORT)/"
	@trap 'trap - EXIT; pkill -P $$back 2>/dev/null; kill $$back $$front 2>/dev/null; wait' INT TERM EXIT; \
	$(MANAGE) runserver $(BACKEND_HOST):$(BACKEND_PORT) & back=$$!; \
	(cd $(FRONTEND) && DEV_API_PROXY_TARGET=http://$(BACKEND_HOST):$(BACKEND_PORT) exec ./node_modules/.bin/vite --host $(FRONTEND_HOST) --port $(FRONTEND_PORT) --strictPort) & front=$$!; \
	wait -n $$back $$front

run-all: _require-install ## Like run, plus the Celery worker and beat (needs Redis)
	@$(MANAGE) migrate --noinput
	@trap 'trap - EXIT; pkill -P $$back 2>/dev/null; kill $$back $$front $$worker $$beat 2>/dev/null; wait' INT TERM EXIT; \
	$(MANAGE) runserver $(BACKEND_HOST):$(BACKEND_PORT) & back=$$!; \
	(cd $(FRONTEND) && DEV_API_PROXY_TARGET=http://$(BACKEND_HOST):$(BACKEND_PORT) exec ./node_modules/.bin/vite --host $(FRONTEND_HOST) --port $(FRONTEND_PORT) --strictPort) & front=$$!; \
	$(BIN)/celery -A config worker -l info & worker=$$!; \
	$(BIN)/celery -A config beat -l info & beat=$$!; \
	wait -n $$back $$front $$worker $$beat

run-backend: ## Run only the Django API
	$(MANAGE) runserver $(BACKEND_HOST):$(BACKEND_PORT)

run-frontend: ## Run only the Vue dev server
	cd $(FRONTEND) && DEV_API_PROXY_TARGET=http://$(BACKEND_HOST):$(BACKEND_PORT) $(NPM) run dev -- --host $(FRONTEND_HOST) --port $(FRONTEND_PORT)

_require-install:
	@test -x $(PYTHON) || { echo "Backend is not installed. Run: make install"; exit 1; }
	@test -d $(FRONTEND)/node_modules || { echo "Installing frontend dependencies..."; cd $(FRONTEND) && $(NPM) ci; }

test: ## Backend (pytest) and frontend (vitest) tests
	$(BIN)/pytest --cov --cov-report=term-missing
	cd $(FRONTEND) && $(NPM) run test:unit -- --run

lint: ## ruff, black, mypy, bandit, vue-tsc, oxlint, eslint
	$(BIN)/ruff check .
	$(BIN)/black --check .
	$(BIN)/mypy .
	$(BIN)/bandit -q -c pyproject.toml -r .
	cd $(FRONTEND) && $(NPM) run type-check && npx oxlint . && npx eslint .

format: ## Auto-format backend and frontend code
	$(BIN)/ruff check --fix .
	$(BIN)/black .
	cd $(FRONTEND) && $(NPM) run format

check: lint test ## lint + test + Django checks, missing migrations, OpenAPI schema
	$(MANAGE) check
	$(MANAGE) makemigrations --check --dry-run
	$(MANAGE) spectacular --validate --fail-on-warn --file /dev/null

worker: ## Celery worker (scheduled backups)
	$(BIN)/celery -A config worker -l info

beat: ## Celery beat scheduler
	$(BIN)/celery -A config beat -l info

backup: ## Back up the SQLite database now
	$(MANAGE) backup_database

import-legacy: ## Copy the old Laravel MySQL data into an empty database (see README)
	$(MANAGE) import_legacy_mysql

frontend-install: ## Install frontend dependencies
	cd $(FRONTEND) && $(NPM) ci

frontend-build: ## Production build of the frontend into frontend/dist
	cd $(FRONTEND) && $(NPM) run build

frontend-test: ## Frontend unit tests
	cd $(FRONTEND) && $(NPM) run test:unit -- --run

frontend-lint: ## Frontend type-check and lint
	cd $(FRONTEND) && $(NPM) run type-check && npx oxlint . && npx eslint .

up: ## Docker development stack
	$(COMPOSE) up --build

down: ## Stop the Docker development stack
	$(COMPOSE) down

prod-up: ## Docker production stack (nginx on port 80, or HTTP_PORT)
	$(COMPOSE) -f docker-compose.prod.yml up -d --build

prod-down: ## Stop the Docker production stack
	$(COMPOSE) -f docker-compose.prod.yml down
