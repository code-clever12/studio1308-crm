# Performance Notes — 2026-07-21

## What real load testing would need (and why it isn't in this repo)

Genuine load testing needs a target that resembles production: PHP-FPM (not `php artisan serve`, which is single-threaded and explicitly documented by Laravel as dev-only), dedicated hardware, and cached config/routes/views. None of that exists yet — there's no live server. The numbers below are a local sanity check on this Windows/XAMPP dev machine, not a capacity benchmark; treat them as "nothing is obviously broken," not "here's what production will handle."

## What was actually checked

**Concurrency smoke test** (`ab -n 100 -c 10`, `php artisan serve`, unauthenticated routes):

| Route | Complete | Failed | Mean time/request |
|---|---|---|---|
| `/` | 100 | 0 | ~1.66s |
| `/login` | 100 | 0 | ~1.63s |
| `/up` (health check) | 100 | 0 | ~1.44s |

Zero failed requests under concurrency — no crashes, deadlocks, or unhandled errors under load. The ~1.5s/request figures are an artifact of `artisan serve`'s single-threaded dev server serializing all 10 concurrent connections, not a reflection of real latency; ignore them for capacity planning.

**Query counts on the heaviest list pages** (via `DB::enableQueryLog()`, real seeded data):

| Page | Queries |
|---|---|
| Admin dashboard (KPIs, charts, recent activity) | 6 |
| Customer "my appointments" (with service/staff/review eager-loaded) | 7 |
| Admin appointments calendar | 1 |

No N+1 blowups — these numbers stay flat regardless of how many appointments/customers exist, because every list controller eager-loads its relations (`->with([...])`) rather than lazy-loading inside a loop. A regression that removed an eager-load and reintroduced N+1 would show up here as query count scaling with row count — worth re-checking this way if these pages ever feel slow.

## Existing optimizations already built in (Steps 1–9)

- **Indexes** (`database/migrations/`): `appointments` has `(staff_id, appointment_date)` and `(appointment_date, status)` composite indexes plus a `customer_id` index — matching exactly the WHERE clauses `SlotService` and the overlap-prevention query use. 18 of the app's migrations define indexes or unique constraints on their most-queried columns.
- **Availability caching** (`app/Services/SlotService.php`): busy intervals per staff/date are cached for 5 minutes (`CACHE_STORE`-backed), invalidated explicitly on booking/cancellation/schedule changes — repeated slot lookups for the same staff/date don't re-hit the database.
- **Eager loading**: every list/index controller method loads its relations up front (`Service::with(['category', 'consentForm', 'staff'])`, `Appointment::with(['service', 'staff.user', 'review'])`, etc.) — confirmed via the query-count check above.
- **Queued background work**: notifications, ACH payouts, and no-show fee charges all run off the request/response cycle via the queue, so a slow email send or Stripe API call never blocks a page load.

## For production

`docs/DEPLOYMENT.md`'s deploy steps already include `php artisan config:cache`, `route:cache`, and `view:cache` — these matter far more for real-world response time than anything measurable on this dev box, since they remove filesystem/parsing overhead on every request. PHP's OPcache should also be enabled on the production PHP-FPM pool (standard for any Laravel production deploy, not specific to this app).
