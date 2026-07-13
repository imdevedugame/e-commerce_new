# TokoJoko REST API

Base URL (local): `http://localhost:8000/api/v1`

All requests/responses use JSON. Send `Accept: application/json` (recommended) — error
responses under `/api/*` are always JSON regardless of the `Accept` header.

## Auth

Token-based auth via [Laravel Sanctum](https://laravel.com/docs/sanctum) (personal access
tokens). Register or log in to get a `token`, then send it on every protected request:

```
Authorization: Bearer <token>
```

### `POST /auth/register`
Rate limited: 6 requests/minute per IP.

Body:
```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

201 response:
```json
{
  "message": "Registration successful.",
  "user": { "id": 1, "name": "Jane Doe", "email": "jane@example.com", "is_admin": false, "created_at": "..." },
  "token": "1|xxxxxxxxxxxxxxxxxxxx"
}
```

### `POST /auth/login`
Rate limited: 6 requests/minute per IP. Body: `{ "email", "password" }`.
200 response: same shape as register (`message`, `user`, `token`).
Wrong credentials → `422` with `errors.email`.

### `POST /auth/logout` (auth required)
Revokes the token used for the request. 200 → `{ "message": "Logged out successfully." }`.

### `GET /auth/me` (auth required)
Returns the current authenticated user. Without a valid token → `401 Unauthenticated.`

## Categories

### `GET /categories`
Query params: `per_page` (default 15, max 50).
```json
{ "data": [ { "id": 1, "name": "Bouquets", "slug": "bouquets" } ], "meta": { "current_page": 1, "last_page": 1, "per_page": 15, "total": 4 } }
```

### `GET /categories/{id}`
`404 { "message": "Resource not found." }` if missing.

## Products

### `GET /products`
Public, no auth required. Supports pagination + filtering + sorting:

| Param | Example | Effect |
|---|---|---|
| `per_page` | `12` | Page size (max 50) |
| `category` | `bouquets` | Filter by category slug |
| `search` | `rose` | Search name/description |
| `min_price` / `max_price` | `10` / `100` | Price range filter |
| `sort` | `latest` \| `price_asc` \| `price_desc` | Sort order |

Response:
```json
{
  "data": [ { "id": 1, "name": "...", "price": 49.99, "old_price": 59.99, "sku": "SKU-0001",
              "stock_status": "instock", "quantity": 12, "image": "...", "images": [], "categories": [...] } ],
  "meta": { "current_page": 1, "last_page": 3, "per_page": 12, "total": 30 }
}
```

### `GET /products/{id}`
`404 { "message": "Resource not found." }` if missing.

### `POST /products/import` (admin only)
Bulk-imports products from a CSV file (`multipart/form-data`, field `file`). Sample template:
`docs/products_import_sample.csv`.

Required header columns: `name, sku, price, quantity`. Optional: `brief_description,
description, old_price, stock_status, image, categories` (comma-separated category slugs,
attached without detaching existing ones).

Behavior: upserts by `sku` (existing SKU → updated, new SKU → created). Invalid rows are
skipped and reported individually — the rest of the file still imports.

```json
{
  "message": "Import completed.",
  "summary": { "created": 2, "updated": 0, "failed": 0 },
  "errors": []
}
```
Non-admin → `403`. Missing required header column → `422`.

## Orders (auth required)

### `GET /orders`
Returns the authenticated user's own orders (admins see all). Query params: `per_page`, `status`.

### `POST /orders`
Body:
```json
{
  "items": [ { "product_id": 1, "quantity": 2 } ],
  "billing": {
    "country": "Indonesia", "billing_address": "Jl. Mawar No. 1", "city": "Bandung",
    "state": "Jawa Barat", "zipcode": "40123", "phone": "081234567890", "order_notes": "Please gift-wrap"
  }
}
```
- Validates each product exists and has enough stock (`422` with `errors.items` if not).
- Decrements product stock and creates/updates the user's billing details, all in one DB transaction.
- 201 → `{ "message": "Order created successfully.", "data": { "id", "status": "pending", "total", "items": [...] } }`.

### `GET /orders/{id}`
Owner or admin only — `403` if the order belongs to someone else, `404` if it doesn't exist.

## Rate limiting

- All `/api/*` routes: 60 requests/minute per authenticated user (or per IP for guests).
- `/auth/register` and `/auth/login`: additionally capped at 6 requests/minute per IP.
- Exceeding a limit returns `429 Too Many Requests`.

## Testing this API

- **Automated**: `php artisan test --filter=Api` (24 feature tests covering auth, products,
  categories, orders — validation, pagination, filtering, stock rules, ownership).
- **Manual**: import `docs/postman_collection.json` into Postman (see its `README` section
  in this repo for setup), or point any PPB/PSC mobile/client app at this API.
