# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Backend Lyrium** — Laravel 12 REST API for a multi-vendor biomarketplace. PHP 8.2+, MySQL (`db-lyrium`), Sanctum auth, Spatie roles/permissions.

Frontend is a separate Next.js 16 repo expected at `http://localhost:3000`.

## Commands

```bash
composer run setup        # Install deps + migrate + seed + build
composer run dev          # Start server, queue, log viewer, and Vite in parallel
composer run test         # Run PHPUnit tests
php artisan migrate --seed  # Run migrations with seeders
php artisan test --filter=TestName  # Run a single test
vendor/bin/pint           # Code style fixer (Laravel Pint)
```

## Architecture

### Auth & Roles
- **Sanctum** bearer tokens for API auth
- **Spatie Permission** with 4 roles: `administrator`, `seller`, `customer`, `logistics_operator`
- Auth endpoints: login, register (seller+store), register-customer, token refresh/logout

### Middleware (registered in `bootstrap/app.php`)
- `ForceJson` — forces `Accept: application/json` on all API requests
- `EnsureRole` — role-based route guard
- `EnsureStoreApproved` — blocks sellers with unapproved stores

### Models & Relationships
- **User** → owns many Stores, member of many Stores (via StoreMember pivot)
- **Store** → has many Products, one latest Subscription; status workflow: `pending → approved/rejected/banned`
- **Product** → belongs to Store, many-to-many Categories, has many ProductAttributes; status workflow: `draft → pending_review → approved/rejected`
- **Category** — self-referencing hierarchy via `parent_id`
- **Plan/Subscription** — store subscription plans with commission rates
- Soft deletes on User, Store, Product

### Request/Response Pattern
- Validation in `app/Http/Requests/` (FormRequest classes)
- JSON transformation in `app/Http/Resources/` (API Resources)
- Controllers in `app/Http/Controllers/Api/`

### Routes
All API routes defined in `routes/api.php`. Three tiers:
- Public (no auth): product/category browsing, auth endpoints
- Authenticated: user profile, auth management
- Role-gated: admin (user/store/product management), seller+admin (store/product CRUD)

## Database

### Seeders (run order via DatabaseSeeder)
- `RoleSeeder` → 4 roles
- `PlanSeeder` → 3 plans (Emprende 5%, Crece 10%, Especial 15% commission)
- `AdminUserSeeder` → admin@lyrium.com / password, vendedor@lyrium.com / password
- `CategorySeeder` → 8 bio/organic product categories

### Testing
PHPUnit configured with in-memory SQLite (`phpunit.xml`). Test suites: `tests/Feature/`, `tests/Unit/`.

## Environment
Copy `.env.example` to `.env`. Key vars:
- `DB_DATABASE=db-lyrium` (MySQL via XAMPP)
- `FRONTEND_URL=http://localhost:3000` (used by CORS config)