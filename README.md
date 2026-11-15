# SureProcure

SureProcure is a procurement workspace for companies and their suppliers. Customers submit purchase requisitions, a dedicated procurement officer sources accredited vendors and evaluates their quotations, and the customer approves the recommendation before the officer issues a purchase order.

## Who uses SureProcure

| Role | What they do |
| --- | --- |
| Customer | Create and track requisitions, respond to clarification requests, and review bid evaluations and purchase orders. |
| Vendor | Submit a public inquiry, join by invitation, complete accreditation, view requests they were invited to quote on, and acknowledge awarded orders. |
| Procurement officer | Manage assigned requisitions, invite accredited vendors to quote, record quotations, evaluate bids, and issue purchase orders. |
| Admin | Review vendor inquiries, issue account invitations, review accreditation documents, and assign requisitions to officers. |

## Procurement workflow

1. The customer submits a requisition. An admin assigns a procurement officer, who confirms the requirements with the customer.
2. The officer invites accredited vendors to quote and records the quotations received.
3. The officer compares technical and commercial criteria and submits a recommendation to the customer.
4. The customer approves the evaluation or requests changes. After approval, the officer issues a purchase order for the awarded quotation.
5. The vendor acknowledges the purchase order. The officer closes the requisition once the order is fulfilled.

## Stack

Laravel, Blade, Tailwind CSS, Alpine.js, and Vite. The application uses PostgreSQL (currently hosted on Supabase) and a private S3-compatible Supabase Storage bucket for documents.

## Local development

You need PHP 8.3 or later with the PostgreSQL PDO extension, Composer 2, Node.js 24 with npm, and a development PostgreSQL database with the SureProcure schema already installed.

The repo contains [SQL updates](database/sql) and [Laravel migrations](database/migrations). A fresh database also needs the base SureProcure schema, which is maintained separately. Obtain that schema before setting up a new environment.

From PowerShell in the repository root:

```powershell
composer install
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
npm ci
```

Set `APP_NAME=SureProcure` and `DB_CONNECTION=pgsql` in your local `.env`, then add the connection details for your development database. For a local setup that uses file-backed sessions and cache, set `SESSION_DRIVER=file`, `CACHE_STORE=file`, and `QUEUE_CONNECTION=sync`. To test document uploads and downloads, configure the private bucket with the `SUPABASE_S3_*` settings in [config/filesystems.php](config/filesystems.php). Set `SUREPROCURE_CONTACT_EMAIL` if you want a public email link in the landing-page footer.

Run `php artisan key:generate` if `APP_KEY` is blank. With the base schema in place and the portal SQL updates applied, run:

```powershell
php artisan config:clear
php artisan migrate --path=database/migrations/2026_11_15_000000_create_vendor_inquiries_table.php
npm run build
php artisan serve
```

Open [http://localhost:8000](http://localhost:8000). To create the first admin, set `ADMIN_EMAIL` and optionally `ADMIN_NAME` and `ADMIN_PASSWORD`, then run `php artisan db:seed --class=AdminUserSeeder`. Keep credentials in your local `.env`.

## Tests

Run `php artisan test` from the repository root after building the frontend. The default suite uses in-memory SQLite. The starter migrations currently omit parts of the PostgreSQL schema, so some authentication and profile tests fail with the default setup.

## Repository layout

| Path | Contents |
| --- | --- |
| [routes/](routes) | Public routes and role-specific entry points |
| [app/Http/Controllers/](app/Http/Controllers) | Application workflows |
| [app/Models/](app/Models) and [app/Policies/](app/Policies) | Domain models and access rules |
| [resources/views/](resources/views) | Landing page, dashboards, and role workspaces |
| [database/](database) | Migrations, SQL updates, and seeders |
| [tests/](tests) | Automated tests |
