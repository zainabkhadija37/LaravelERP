<div align="center">

# Laravel ERP & Inventory Management System

**A production-style backend that proves I can own a real domain — not just ship CRUD.**

[![CI](https://github.com/YOUR_USERNAME/laravel-erp/actions/workflows/ci.yml/badge.svg)](https://github.com/YOUR_USERNAME/laravel-erp/actions)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-4169E1?logo=postgresql&logoColor=white)
![Tests](https://img.shields.io/badge/tests-Pest%203-8B5CF6)
![License](https://img.shields.io/badge/license-MIT-green)

[Quick Start](#quick-start) · [What This Demonstrates](#what-this-demonstrates) · [Architecture](#architecture) · [API Docs](#api-overview) · [Tests](#tests)

</div>

---

## The problem this solves

Inventory systems fail in one specific way: **stock counts drift from reality** under concurrent writes — two sales completing at once, a purchase order received while a sale is in flight. Most tutorial projects ignore this. This one doesn't.

Every stock change in this system — a purchase received, a sale completed, a manual correction — flows through **one locked, transactional code path** (`StockService`), and every change is written to an **immutable ledger** (`stock_movements`) so you can always answer *"why does this product have 43 units right now?"* by replaying history instead of trusting a number.

That single design decision is the difference between a CRUD demo and something that could actually run a warehouse.

---

## What this demonstrates

If you're scanning this for a hire/no-hire signal, here's the mapping:

| What you need | Where it is |
|---|---|
| Can design a service layer, not just fat controllers | [`app/Services/`](app/Services) — `StockService`, `SaleService`, `PurchaseOrderService` |
| Understands transactions & concurrency | `StockService::increase/decrease` — `DB::transaction` + `lockForUpdate` |
| Can model a real domain with correct relationships | 16 migrations, polymorphic stock movements, pivot-based multi-warehouse stock |
| Knows Laravel's event system, not just routes | [`app/Events/`](app/Events) + [`app/Listeners/`](app/Listeners) — sale completion decoupled from email/notification side effects |
| Can ship background processing | [`app/Jobs/`](app/Jobs) — queued exports, scheduled digest emails |
| Writes tests that verify business logic, not just "200 OK" | [`tests/`](tests) — race-condition-relevant stock math, full PO/sale lifecycle, RBAC enforcement |
| Cares about auth & authorization | Sanctum tokens + Spatie roles/permissions, enforced per-endpoint |
| Ships things that run | Docker Compose, GitHub Actions CI (lint + test on every push) |

---

## Quick start

```bash
git clone https://github.com/YOUR_USERNAME/laravel-erp.git
cd laravel-erp
cp .env.example .env
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

App: `http://localhost:8000` · Mailhog (catches outgoing mail): `http://localhost:8025`

Seeded login (all roles, password `password`): `admin@erp.test` · `manager@erp.test` · `employee@erp.test`

---

## Architecture

```
app/
├── Models/        Thin — relationships, scopes, casts. No business logic.
├── Services/       Owns transactions & business rules. Controllers never touch the DB directly.
├── Http/
│   ├── Controllers/Api/   Validate → delegate to Service → return API Resource. That's it.
│   ├── Requests/           Validation rules live here, not in controllers.
│   └── Resources/          Consistent, versioned JSON shape.
├── Events/ + Listeners/    Side effects (email, notifications) decoupled from the action that caused them.
├── Jobs/                   Anything slow or bulk (report generation) runs off the request cycle.
└── Notifications/          Mail + in-app, queued.
```

**The one diagram worth looking at** — how stock never goes wrong under concurrent requests:

```
PurchaseOrderService::receive()        SaleService::complete()
            │                                     │
            └──────────────┬──────────────────────┘
                            ▼
              StockService::increase() / decrease()
                            │
        DB transaction + SELECT ... FOR UPDATE on the stock row
                            │
          update quantity  +  insert stock_movements row (audit trail)
                            │
              fires StockLevelLow if balance ≤ reorder level
                            │
                queued listener → email/db notification to managers
```

---

## Feature checklist

**Core** — Auth (Sanctum) · Roles & Permissions (Admin/Manager/Employee) · Products, Categories, Warehouses, Suppliers, Customers · Purchase Orders (draft→pending→approved→received) · Sales (pending→completed, payment tracking) · Stock Adjustments · Dashboard · Reports · Activity Log

**Advanced** — Queued Jobs · Notifications (mail + database) · Events & Listeners · Soft Deletes · Search + Pagination everywhere · CSV/XLSX Export · Versioned REST API · Pest test suite

---

## API overview

Base URL `/api/v1`. Everything except `auth/register`/`auth/login` requires a Sanctum bearer token.

```
POST /auth/register · /auth/login · /auth/logout · GET /auth/me
GET  /dashboard

GET|POST /products (+ /products/export)   GET|PUT|DELETE /products/{id}
GET|POST /categories · /warehouses · /suppliers · /customers   (full CRUD)

GET|POST /purchase-orders   POST .../approve · .../receive · .../cancel
GET|POST /sales             POST .../complete · .../payments · .../cancel

GET|POST /stock-adjustments

GET  /reports/sales · /reports/inventory-valuation · /reports/stock-movements · /reports/low-stock
POST /reports/sales/export   (queued — notifies on completion)

GET  /activity-log
```

Every list endpoint supports `?search=`, `?per_page=`, and contextual filters (`?status=`, `?low_stock=1`, etc.).

---

## Tests

```bash
docker compose exec app ./vendor/bin/pest
```

What's actually covered — not "does the endpoint return 200," but the business rules:

- Stock increase/decrease math, including the row-lock behavior and the `RuntimeException` thrown when a sale would oversell
- Full purchase-order lifecycle: create → approve → receive → stock actually increases
- Full sale lifecycle: create (stock untouched) → complete (stock decreases, `SaleCompleted` fires)
- RBAC: an Employee gets a 403 trying to cancel a purchase order
- Soft deletes: a deleted product is `assertSoftDeleted`, not gone
- Validation: selling price can't be set below cost price

---

## Decisions worth asking me about in an interview

- Why sales are `pending` → `completed` as two separate steps instead of one (mirrors how real POS/checkout systems separate "order placed" from "fulfilled," and keeps stock deduction reversible)
- Why there's a `stock_movements` ledger *in addition to* the `product_warehouse.quantity` column, instead of just computing quantity from the ledger
- Why `StockService` takes a polymorphic `$reference` argument instead of a `$type` string alone

---

## What I'd build next

- Interactive API docs (Scramble/Swagger)
- Multi-currency support on POs and sales
- Barcode scanning endpoint for stock counts
- Webhooks for external accounting integrations

---

<div align="center">

**Khadija** — Laravel Developer, ~2.5 years experience, currently open to opportunities.

[GitHub](#) · [LinkedIn](#) · [Email](#)

</div>
