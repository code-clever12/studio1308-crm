# Security Audit — 2026-07-21

Full read-through of authentication, authorization, mass assignment, sensitive data handling, CSRF/webhook security, SQL injection, rate limiting, secrets hygiene, and XSS, ahead of launch. Scope: `app/`, `routes/`, `config/`, `resources/views/`, `.env*`, `.gitignore`.

Every issue found below has been fixed. Nothing here is an open item.

## Findings & fixes

| # | Area | Severity | Finding | Fix |
|---|---|---|---|---|
| 1 | Session | Medium | `SESSION_SECURE_COOKIE` was never set — the session cookie wouldn't carry the `Secure` flag in production even though generated URLs are forced to https | Added `SESSION_SECURE_COOKIE` to `.env.example` (default `false` for local http dev) with a note to set `true` in production; added to `docs/DEPLOYMENT.md`'s environment checklist |
| 2 | Auth | Medium | `POST /register` had no rate limiting (login and password-reset do) | Added `throttle:5,1` to the register route in `routes/auth.php` |
| 3 | Auth | Low | `POST /forgot-password` relied only on the password broker's per-email 60s throttle, with no per-IP cap — an attacker could loop through many different emails instantly | Added `throttle:6,1` (matching the existing email-verification throttle pattern) |
| 4 | Rate limiting | Medium | No throttling anywhere on the customer/admin route groups, including `booking.payment`, which calls the live Stripe API (creates/updates a PaymentIntent) on every `GET` — a real cost/DoS vector against the Stripe account, not just this server | Added `throttle:60,1` at the group level to both the `customer` and `admin` route groups in `routes/web.php`, plus a tighter `throttle:10,1` specifically on `booking.payment` |
| 5 | Rate limiting | Low | The two Stripe webhook routes were unthrottled (low risk since signature verification fails fast on bad requests, but still an unauthenticated public endpoint doing crypto work on every hit) | Added `throttle:120,1` to both webhook routes |
| 6 | Models | Low | `Staff` model still had `bank_account_routing_number`/`bank_account_number`/`bank_account_holder_name` in `$fillable` — dead/duplicate columns left over from an early schema iteration (the real source of truth is the separate `ACHBankAccount` model/table, used by every actual read/write path). Confirmed via grep that no controller, service, or seeder ever wrote to these columns on `Staff`. Being fillable made them a mass-assignment landmine: any future `Staff::create($request->validated())`-style code would have silently accepted bank details through an unintended field | Removed the three fields from `Staff::$fillable`/`$hidden`/`casts()`; removed the corresponding (already-always-null) entries from `StaffFactory`. The database columns themselves are left in place (harmless, unused, nullable) rather than editing an already-applied Step 1 migration — a future cleanup migration can drop them if desired |

## Confirmed already correct (no changes needed)

- **Authorization / IDOR** — every Policy (`AppointmentPolicy`, `StaffPolicy`, `ServicePolicy`) correctly scopes by ownership, and every controller action that accepts a route-bound model calls `$this->authorize(...)` or sits behind role-gated middleware plus an explicit ownership check (e.g. `Admin\ScheduleController::destroyDayOff` scopes through `$staff->daysOff()->findOrFail(...)` rather than a global lookup, preventing cross-staff record tampering by ID). This was the strongest area of the codebase.
- **Mass assignment (everywhere else)** — grepped every `::create()`/`->update()`/`->fill()` call site in `app/`; none use `$request->all()`, and every Form Request's validation rules whitelist exactly the intended fields. Registration explicitly cannot set `role`/`is_active` even though both are fillable on `User`.
- **Sensitive data exposure** — Stripe customer/payment-method IDs are `$hidden` on `User`; bank account numbers/routing numbers on `ACHBankAccount` (the real payout-data table) are both `$hidden` and `encrypted`-cast, confirmed still intact since Step 1.
- **CSRF / webhook security** — the CSRF exemption in `bootstrap/app.php` covers exactly the two Stripe webhook routes, nothing broader. Both webhook handlers verify Stripe's cryptographic signature (`Stripe\Webhook::constructEvent`) before processing anything, and both are idempotent (check current status before applying updates).
- **SQL injection** — only 3 raw-SQL call sites in the whole app (`SalesTaxService`'s `selectRaw` aggregates), all static string literals with no interpolated input.
- **Secrets hygiene** — `.env` has never been committed (checked full git history, not just current status); every `config/*.php` value is `env()`-backed; the only "hardcoded"-looking strings are clearly-fake test/fallback placeholders (`sk_test_not_configured`, `sk_test_fake`).
- **XSS** — zero uses of Blade's raw `{!! !!}` output anywhere in 77 view files; zero uses of `innerHTML`/`v-html`/`dangerouslySetInnerHTML` in any JS.
- **PCI DSS scope** — no raw card data ever touches this app's server; Stripe Elements (Step 8) collects card details directly into Stripe's iframe, and only a `payment_intent`/`payment_method` token ever reaches the backend.

## Verification

Full test suite (170 tests) re-run after every fix above — all passing, no regressions. `./vendor/bin/pint --test` clean.
