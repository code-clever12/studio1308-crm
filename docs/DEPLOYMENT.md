# Deployment Guide

This app is deployed manually (no CI/CD auto-deploy) to a Linux server you manage yourself. `.github/workflows/tests.yml` runs the test suite on every push so you know `master` is safe to pull before you deploy it — it does not deploy anything.

## Server requirements

- PHP 8.2+ with extensions: `mbstring`, `dom`, `fileinfo`, `mysql` (pdo_mysql), `gd`, `zip`, `bcmath`
- MySQL or MariaDB (this app has been built and tested against MySQL/MariaDB — see "Why MySQL, not PostgreSQL" below)
- Nginx or Apache with PHP-FPM
- Node.js 20+ and npm (only needed at deploy time, to build frontend assets)
- Composer 2
- Supervisor (keeps the queue worker running)
- A cron entry for Laravel's scheduler
- `mysqldump` on `PATH` (ships with `mysql-client`/`mariadb-client` on virtually every distro) — needed for `backup:database`

### Why MySQL, not PostgreSQL

The spec suggests PostgreSQL for production. This app has been built and tested against MySQL (dev) and SQLite (automated tests) for its entire build — switching engines now would mean re-validating all 24 migrations for Postgres-specific differences (enum columns, JSON handling, etc.) for no functional benefit on a single-tenant, single-location app. MySQL/MariaDB is the supported and tested production database.

### Why no S3

File storage stays on the local disk (`FILESYSTEM_DISK=local`) rather than S3, by request. Note the app doesn't currently have any actual file-upload features built (staff photos, salon logo, service images are schema columns with no upload UI wired up yet) — so there's nothing to migrate off local storage today. If upload features are added later and the server is ever load-balanced across multiple machines, local storage would need to move to shared storage (NFS, S3, etc.) at that point.

## First-time server setup

1. Clone the repo to e.g. `/var/www/studio1308`.
2. `composer install --no-dev --optimize-autoloader`
3. `cp .env.example .env` and fill in real values — see "Environment checklist" below.
4. `php artisan key:generate`
5. `npm ci && npm run build`
6. `php artisan migrate --force` (creates the schema; `--force` is required since `APP_ENV=production`)
7. `php artisan db:seed --class=SalonSeeder --force` if you want the initial salon config seeded, otherwise fill in Salon settings via `/admin/settings` after your first admin account exists — you'll need to create that account manually (e.g. via `php artisan tinker` or a one-off registration you then promote with `User::where('email', ...)->update(['role' => 'admin'])`), since there's no public admin-signup flow.
8. Set up the Supervisor queue worker (below).
9. Set up the cron scheduler entry (below).
10. Point your web server's document root at `public/`.
11. Set up HTTPS (below).

## Environment checklist (`.env`)

| Variable | Production value |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` — never `true` in production, it leaks stack traces/env values to visitors |
| `APP_URL` | your real domain, `https://...` |
| `DB_*` | real MySQL/MariaDB credentials |
| `TRUSTED_PROXIES` | your reverse proxy/load balancer's IP(s), or blank if PHP-FPM receives requests directly |
| `SESSION_SECURE_COOKIE` | `true` — the session cookie only gets the `Secure` flag if this is set; forcing https on generated URLs (already done) doesn't set it for you |
| `SESSION_DRIVER` / `CACHE_STORE` / `QUEUE_CONNECTION` | `database` works fine at this app's scale with zero extra infrastructure; switch to `redis` later only if you actually need the extra throughput (see `.env.example`'s comments) |
| `MAIL_MAILER` | `smtp`, `mailgun`, `ses`, or `postmark` with real credentials — `log` (the dev default) silently writes emails to a log file instead of sending them |
| `STRIPE_KEY` / `STRIPE_SECRET` / `STRIPE_WEBHOOK_SECRET` | real Stripe keys (test or live). Until these are set, the payment page and ACH verification both show a clear "not connected yet" state rather than erroring |
| `SENTRY_LARAVEL_DSN` | optional — leave blank to skip error tracking entirely |

After changing `.env` on the server, run `php artisan config:cache` so the change takes effect (Laravel caches config in production).

## Deploying a new release

```bash
cd /var/www/studio1308
git pull origin master
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart   # workers finish their current job, then pick up new code
```

`queue:restart` signals the Supervisor-managed workers to exit gracefully after their current job; Supervisor's `autorestart=true` immediately respawns them running the new code. Nothing in this list requires downtime.

## Queue worker (Supervisor)

Notifications (booking confirmations, reminders, tip receipts, etc.), ACH payouts, and no-show fee charges all run through the queue — nothing in this app works correctly without a queue worker running continuously.

```bash
sudo cp deploy/supervisor/studio1308-worker.conf /etc/supervisor/conf.d/
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start studio1308-worker:*
```

Edit the `command`/`directory`/`user`/log paths in that file to match your actual server layout first — it ships with `/var/www/studio1308` and `www-data` as placeholders.

## Scheduler (cron)

Laravel's scheduler drives no-show detection (every 5 minutes) and the nightly database backup. Add one cron entry:

```
* * * * * cd /var/www/studio1308 && php artisan schedule:run >> /dev/null 2>&1
```

Laravel itself decides what actually needs to run each minute — see `routes/console.php` for the schedule.

## Database backups

`php artisan backup:database` dumps the database with `mysqldump`, gzips it, and writes it to `storage/app/backups/`, pruning anything older than 14 days (`--keep-days` to change). It's scheduled nightly at 2am via `routes/console.php` — no extra setup needed beyond the cron entry above.

**This only protects against data-corruption/mistake scenarios, not server loss** — the backups live on the same disk as the database. For real disaster recovery, sync `storage/app/backups/` somewhere off-server on your own schedule (e.g. an `rsync`/`rclone` cron job, or your hosting provider's snapshot feature).

To restore a backup: `gunzip -c storage/app/backups/<file>.sql.gz | mysql -u <user> -p <database>`.

## HTTPS

Terminate SSL at your reverse proxy (nginx with a Let's Encrypt cert via `certbot` is the standard, low-effort choice) or your hosting provider's load balancer. Then:

- Set `TRUSTED_PROXIES` in `.env` to the proxy's IP (or `*` only if you're certain every request is already proxied — see the warning comment in `.env.example`).
- `AppServiceProvider::boot()` already forces `https` on every generated URL whenever `APP_ENV=production`, so password reset links, the Stripe payment page's `return_url`, etc. are correct regardless of how the request reached PHP.

## Error tracking (optional)

Set `SENTRY_LARAVEL_DSN` in `.env` to enable — `bootstrap/app.php` already wires Sentry into Laravel's exception handler (`Integration::handles($exceptions)`), and it's a genuine no-op with no performance cost when the DSN is blank. No code changes needed to turn it on or off.

## Logs

`storage/logs/laravel.log` (application) and `storage/logs/worker.log` (queue worker, per the Supervisor config) are the two files worth tailing when something goes wrong. Rotate them with `logrotate` if you're not using Sentry, since Laravel's `single` log channel (the dev/example default) doesn't rotate on its own — switch `LOG_CHANNEL` to `daily` in production for automatic rotation instead.
