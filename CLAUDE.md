# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

HealthKeeper is a fullstack hospital management system:

- **Backend:** `/var/www/html/healthkeeper_backend` — Laravel 10, nwidart/laravel-modules, Doctrine ORM
- **Frontend:** `/var/www/html/healthkeeper_frontend` — React, Vite, Mantine UI, Redux Toolkit

Provides REST APIs and UI for hospital operations including OPD, IPD, pharmacy, billing, lab investigations, inventory, accounting, and medicine management.

## Common Commands

```bash
# Start dev server
php artisan serve

# Update database schema (Doctrine ORM, not standard Laravel migrations)
php artisan doctrine:schema:update --force

# Optimize autoloader & caches
php artisan optimize

# Run tests
./vendor/bin/phpunit
./vendor/bin/phpunit tests/Unit
./vendor/bin/phpunit tests/Feature
./vendor/bin/phpunit --filter=TestClassName

# Code formatting
./vendor/bin/pint

# Asset bundling
npm run dev    # development
npm run build  # production

# Create a new module
php artisan module:make ModuleName
```

## Architecture

### Modular Structure

Code is organized into domain modules under `Modules/`, each following this structure:
```
Modules/<Name>/
  App/Http/Controllers/   # API controllers
  App/Http/Requests/       # Form request validation
  App/Models/              # Eloquent models
  App/Services/            # Business logic
  App/Repositories/        # Data access layer
  App/Providers/           # Module service providers
  Database/                # Module-specific migrations/seeders
  routes/api.php           # Module API routes
  module.json              # Module metadata
```

Module activation is controlled via `modules_statuses.json` in the project root.

### Key Modules

- **Core** — Users, locations, warehouses, customers, vendors, settings, file uploads
- **Hospital** — Largest module: OPD, IPD, prescriptions, billing, pharmacy, lab investigations, patient management, particulars (charge categories)
- **Inventory** — Stock tracking, item history, transfers
- **Medicine** — Medicine catalog, generic/brand management
- **Accounting** — Chart of accounts, financial heads
- **Domain** — Multi-tenant hospital/domain configuration
- **Utility** — Currencies, units, banks, shared settings

### ORM & Database

Uses **Doctrine ORM** (via `laravel-doctrine/orm`) alongside Eloquent. Schema updates use `php artisan doctrine:schema:update --force` rather than standard Laravel migrations. Database is MySQL.

### Authentication

Two-layer API security on most routes:
1. `HeaderAuthenticationMiddleware` — validates `X-Api-Key` header
2. `auth:api` — JWT token validation via `tymon/jwt-auth`

Login endpoints return JWT tokens; the primary login is `POST /api/login-tb` which also returns warehouse/hospital config data.

### Routing

- `routes/api.php` — Auth endpoints (login, logout, me)
- Each module defines its own routes in `Modules/<Name>/routes/api.php`
- Routes are grouped under module prefixes (e.g., `/hospital/*`, `/core/*`, `/inventory/*`)
- Dropdown/select endpoints follow pattern: `/<module>/select/<resource>`

### File Storage

Uses Cloudflare R2 (S3-compatible) via `league/flysystem-aws-s3-v3` and `spatie/laravel-medialibrary`.

### Request Logging

`LogActivity` middleware logs API requests when `IS_REQUEST_LOG` env var is enabled. Additional activity tracking via `spatie/laravel-activitylog`.

## Key Conventions

- Controllers return JSON responses for API consumption
- Services contain business logic; controllers delegate to services
- Form request classes handle input validation
- Models use `Model` suffix (e.g., `PatientModel`, `OpdModel`, `StockItemModel`)
- The `App\Models\User` model implements `JWTSubject` for token generation
- Git commit format: `feat(module): short description`

## Workflow Rules

### Plan-First (Mandatory for non-trivial tasks)

1. **Analyze** — Identify module, API impact, UI impact
2. **Generate plan** — Save to `.claude/plan.md` with task, scope, modules, backend/frontend plans, risks
3. **Wait for approval** — Do not write code before user approves
4. **Implement** — Backend first (Controller → Service → Validation), then frontend
5. **Code review** — Check performance, security, reusability, naming
6. **Git** — Ask user about commit message before committing

For trivial/small fixes, planning can be skipped.

### Session Management

- `start session` → Create `.claude/sessions/session-YYYY-MM-DD-HHMM.md` (task summary, scope, modules)
- `end session` → Summarize completed tasks, files changed, features/bugs into session file
- `update memory` → Update `.claude/memory.md` with decisions, API structures, patterns
- `track bug` → Create `.claude/bugs/bug-YYYYMMDD-HHMM.md`
- `add feature` → Create `.claude/features/feature-YYYYMMDD-HHMM.md`

### Fullstack Sync

- Always maintain frontend-backend sync; never break API contracts
- Backend changes go in `Modules/<Module>/App/` (Controller → Service → Request)
- Frontend changes go in `src/modules/<module>/` (API service → Redux state → UI)
