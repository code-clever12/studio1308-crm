# Launch Checklist

Where this build stands after all 12 steps of `docs/PROJECT_SPEC.md`, and what's genuinely left before real customers can use it.

## Done

- **Core booking flow**: browse → multi-step booking wizard → real-time availability with double-booking prevention → deposit + tip payment → email confirmation with calendar invite. Covered end-to-end by `tests/Feature/BookingFlowTest.php`.
- **USA-specific business logic**: tipping, sales tax, no-show auto-charge + auto-blocking, ACH staff payouts (commission + tips combined) — all real, tested logic, not stubs.
- **Admin tooling**: dashboard, drag-and-drop calendar, walk-in booking, staff/schedule management, service catalog, consent form builder, customer CRM, commission/tips/tax reports, payout management, salon settings.
- **Stripe integration**: deposits, tips, refunds, no-show off-session charges, and ACH Connect payouts — all real API calls (mockable in tests via HTTP-layer interception), webhook signature verification, idempotent event handling.
- **Email**: 9 real Mailable + Blade templates (booking confirmation with .ics attachment, reminders, cancellation notices, waitlist alerts, staff assignment, no-show receipts, tip receipts, admin daily summary, payout failure alerts).
- **Testing**: 170 Pest tests — booking/overlap logic, payments, webhooks, notifications, authorization, and one full end-to-end journey test.
- **Security**: full audit completed and every finding fixed (`docs/SECURITY_AUDIT.md`) — rate limiting on auth and booking/payment endpoints, session cookie hardening, a dead mass-assignment field removed, IDOR/authorization confirmed correct throughout.
- **Deployment readiness**: manual-deploy runbook (`docs/DEPLOYMENT.md`), Supervisor config for the queue worker, nightly database backups, CI (tests + style check on every push), optional Sentry error tracking, HTTPS enforcement.
- **Docs**: this checklist, `README.md`, `docs/ADMIN_GUIDE.md`, `docs/CUSTOMER_FAQ.md`, `docs/PERFORMANCE.md`.

## Still needed before real customers use it (yours to do, not code)

These aren't things I can complete for you — they require real-world accounts, decisions, or people:

1. **A real Stripe account** — test mode first, then live keys once you're ready to accept real payments. Set `STRIPE_KEY`/`STRIPE_SECRET`/`STRIPE_WEBHOOK_SECRET`. Until then, the payment page and staff payout verification both show a clear "not connected" state rather than breaking.
2. **A real mail provider** — SMTP/Mailgun/SES/Postmark credentials. Until then, emails write to a local log file instead of sending.
3. **A server** — you said you'll deploy manually; `docs/DEPLOYMENT.md` has the full runbook once you've picked one.
4. **A domain + SSL certificate** for HTTPS.
5. **Your actual salon's data** — real services, staff, pricing, hours, cancellation policy, and tax rate, entered through Settings/Services/Staff rather than the seeded demo data.
6. **A first admin account** — there's no public admin signup; create it manually on the server (see `docs/DEPLOYMENT.md`'s first-time setup).
7. **Soft launch** — run it with a small group of real customers/staff first and see what breaks in practice before opening it up fully. No amount of automated testing substitutes for real usage.

## Recommended before soft launch, optional before that

- Consider a Sentry account (free tier is enough to start) so you hear about production errors instead of finding out from a customer.
- Re-read `docs/DEPLOYMENT.md`'s backup section — the nightly backup only protects against mistakes, not server loss, until you set up off-server syncing.
- If you add real photo/logo upload features later (the schema has columns for them, but no upload UI was built — see `docs/PERFORMANCE.md`/spec), revisit the local-vs-S3 storage decision then, especially if you ever run more than one app server.
