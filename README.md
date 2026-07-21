# Ritual Barber Studio

A complete Salon & Appointment Booking Management Platform for a single, independent USA salon — built with Laravel 12, Blade, Tailwind CSS v4, Alpine.js, and Stripe.

Customers browse services and staff, book multi-step appointments with real-time availability, pay a deposit (plus an optional tip) by card, and get a confirmation email with a calendar invite. Admins run the whole business from a dashboard: staff schedules, services, a drag-and-drop appointment calendar, consent forms, sales tax and commission reports, and ACH payouts to staff.

## USA-specific features

- **Tipping** at checkout (15% / 18% / 20% / custom), tracked per staff member
- **Sales tax** calculated per the salon's configured state rate
- **No-show fees** auto-charged to the card on file 30 minutes after a missed appointment, with auto-blocking after 3 no-shows
- **ACH payouts** to staff (commission + tips combined) via Stripe Connect
- **Stripe** for deposits, tips, refunds, and payouts — PCI DSS scope stays minimal since card data never touches this app's server (Stripe Elements handles it directly)

## Tech stack

- Laravel 12, Blade, Laravel Breeze (auth)
- Tailwind CSS v4 (separate `app.css`/`admin.css` builds for customer vs. admin) + Alpine.js
- MySQL/MariaDB, Stripe (`stripe/stripe-php`), Pest for testing
- Sentry for error tracking (optional, off unless configured)

## Local setup

Requires PHP 8.2+, Composer, Node 20+, and MySQL/MariaDB.

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Create a database matching `.env`'s `DB_DATABASE` (`studio1308` by default), then:

```bash
php artisan migrate --seed
npm install
composer run dev
```

`composer run dev` runs the PHP dev server, a queue listener, and the Vite dev server together. Visit `http://localhost:8000`.

### Demo accounts

All seeded with the password `password`:

| Role | Email |
|---|---|
| Admin | `admin@ritualsalon.test` |
| Staff | `jordan@ritualsalon.test` (and `morgan`/`casey`/`riley`/`sam@ritualsalon.test`) |
| Customer | `customer@ritualsalon.test` |

### Connecting real services (optional for local dev)

The app runs fully without these — payment and email features degrade to a clear "not connected" state or write to a local log file instead of failing:

- **Stripe**: set `STRIPE_KEY`/`STRIPE_SECRET`/`STRIPE_WEBHOOK_SECRET` in `.env` (test keys are fine). Without them, the payment page shows a "not connected yet" message and the admin "Verify with Stripe" action for staff payouts does the same.
- **Email**: `MAIL_MAILER=log` (the default) writes emails to `storage/logs/laravel.log` instead of sending them. Switch to `smtp`/`mailgun`/`ses`/`postmark` with real credentials to actually send.

## Testing

```bash
php artisan test
./vendor/bin/pint --test   # code style check
```

170+ Pest tests cover booking/overlap prevention, payments (mocked Stripe HTTP calls + offline webhook signature verification), notifications/email, authorization, and a full end-to-end booking journey.

## Project structure

- `app/Services/` — business logic (slot generation, booking, payments, tipping, sales tax, no-shows, ACH payouts, commissions, cancellations, notifications)
- `app/Http/Controllers/Admin/` and `Customer/` — role-scoped controllers
- `app/Mail/` + `resources/views/emails/` — transactional email templates
- `resources/views/customer/` and `admin/` — Blade views, with separate `app.css`/`admin.css` Tailwind builds
- `database/migrations/`, `factories/`, `seeders/` — schema and demo data
- `tests/Feature/` — the full Pest suite

## Documentation

- [`docs/PROJECT_SPEC.md`](docs/PROJECT_SPEC.md) — the original full specification this app was built from
- [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md) — manual deployment runbook (server setup, environment checklist, Supervisor, cron, HTTPS, backups)
- [`docs/ADMIN_GUIDE.md`](docs/ADMIN_GUIDE.md) — how to run the salon day-to-day from the admin dashboard
- [`docs/CUSTOMER_FAQ.md`](docs/CUSTOMER_FAQ.md) — customer-facing booking/payment/cancellation questions
- [`docs/SECURITY_AUDIT.md`](docs/SECURITY_AUDIT.md) — pre-launch security review and fixes
- [`docs/PERFORMANCE.md`](docs/PERFORMANCE.md) — query counts, existing optimizations, and what real load testing would need
- [`docs/LAUNCH_CHECKLIST.md`](docs/LAUNCH_CHECKLIST.md) — what's done vs. what's left before going live
