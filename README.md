# TokoJoko

Laravel e-commerce app: a Livewire + Filament storefront/admin, plus a token-based REST
API (`/api/v1/*`) meant to be consumed by an external client — e.g. a mobile app (PPB) or
a client-side/SPA project (PSC).

## Stack

- Laravel 10, PHP 8.1+ (developed/tested on PHP 8.4)
- Livewire 2 (storefront: home, product details, cart, checkout) + Filament 2 (admin panel)
- Sanctum (API token auth)
- MySQL (app), SQLite in-memory (automated tests)

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
# set DB_DATABASE / DB_USERNAME / DB_PASSWORD in .env, then:
php artisan migrate
php artisan serve
```

## REST API

Full endpoint reference: [docs/API.md](docs/API.md).
Postman collection: [docs/postman_collection.json](docs/postman_collection.json)
(import into Postman, set `base_url`, run "Login"/"Register" first to auto-fill the token).

**Interactive Swagger UI**: visit `/docs` (e.g. `http://localhost:8000/docs`) once the app
is running — it renders the OpenAPI 3.0 spec at `public/openapi.json` and lets you try
every endpoint (including `Authorize` with a bearer token) straight from the browser.

Scope covered:

- **Auth** — register/login/logout/me via Sanctum bearer tokens (`app/Http/Controllers/Api/AuthController.php`)
- **Products & Categories** — public read endpoints with pagination, search, category/price
  filtering, and sorting (`app/Http/Controllers/Api/ProductController.php`, `CategoryController.php`)
- **Orders** — authenticated checkout endpoint with stock validation, DB-transaction-safe
  stock decrement, and ownership-based authorization (`app/Http/Controllers/Api/OrderController.php`,
  `app/Policies/OrderPolicy.php`)
- **Throttling** — 60 req/min per user/IP on all `/api/*` routes, plus a stricter 6 req/min
  limit on `/auth/register` and `/auth/login`
- **Consistent JSON errors** — every `/api/*` error (validation, auth, 404, 403, 500) returns
  JSON, not an HTML error page (`app/Exceptions/Handler.php`)

## Testing

```bash
php artisan test              # full suite
php artisan test --filter=Api # API-only (24 tests: auth, products, categories, orders)
```

Tests run against an in-memory SQLite database (configured in `phpunit.xml`), so they never
touch your local/dev MySQL data.

## Hosting notes

- Set `APP_ENV=production`, `APP_DEBUG=false`, and a real `APP_URL` before deploying.
- `config/cors.php` currently allows all origins (`allowed_origins => ['*']`) so any client
  (mobile app, Postman, SPA) can call the API during development/grading — narrow this to
  your actual client origin(s) for a production deployment.
- The API is stateless (bearer tokens via Sanctum, no session/cookie auth), so it can be
  deployed independently of the Livewire storefront if needed.
