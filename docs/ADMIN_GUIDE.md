# Admin Guide

Everyday reference for running the salon from the admin dashboard. Log in at `/login` with an admin account (see `README.md` for the demo login), which takes you to `/admin/dashboard`.

## Dashboard

Your landing page: today's revenue, bookings, active customers, top services, and staff performance at a glance. This is the fastest way to see how the day/week/month is going without digging into individual reports.

## Appointments

`Appointments` in the sidebar shows the calendar (day/week/month views). Click any appointment for its status and details; **drag an appointment to a new time slot to reschedule it** — this respects the same overlap-prevention rules as customer bookings, so you can't accidentally double-book a staff member.

**Booking a walk-in:** use the "New Appointment" button. Unlike online bookings (which stay *pending* until the customer's deposit clears), walk-ins are marked **confirmed and paid immediately**, since you're collecting payment in person at the front desk.

**Statuses:** `pending` → `confirmed` (deposit paid) → `in_progress`/`completed`, or `cancelled`/`no_show`. No-shows are detected automatically — a background job marks any appointment still pending/confirmed 30 minutes past its start time, and (if the customer has a card on file) charges the no-show fee automatically.

## Staff

`Staff` → add/edit team members: bio, commission rate (% of service price, not the deposit), which services they perform, and their weekly schedule (working days, shift hours, lunch breaks) plus one-off days off/vacation.

**Setting up payouts for a staff member:**
1. On their edit page, fill in the ACH bank account section (account holder name, routing number, account number). These are encrypted at rest.
2. Click **"Verify with Stripe"** — this creates their Stripe Connect account and attaches the bank details. Until this step completes successfully, they can't receive a payout.
3. If Stripe isn't configured yet (no API keys in `.env`), you'll see a clear message rather than an error — payouts just aren't available until Stripe is connected.

**Deactivating a staff member** disables their account and removes them from new bookings without deleting their history — past appointments, payout records, and reviews stay intact.

## Services

`Services` — name, price, duration, buffer time (cleanup/prep time added after the service before the next booking), which category it's in, whether it's taxable, and whether it requires a consent form. Deposit amount can be a flat override per service, or left blank to use the salon's default deposit percentage (set in Settings).

Deleting a service deactivates it rather than deleting it — existing appointments that reference it are unaffected.

## Consent / intake forms

`Forms` — build custom forms (text, email, checkbox, radio, date fields) and attach one to any service that needs it (e.g. a chemical treatment consent form). Customers fill it out during booking if the service requires one. View submitted responses per form under "Responses."

## Customers (CRM)

`Customers` — every customer's booking history, no-show count, and block status in one place. A customer is auto-blocked after 3 no-shows; you can also manually block/unblock anyone from here (e.g. for repeated late cancellations or a support decision), independent of the automatic threshold.

## Reports

`Reports` has three views:
- **Commission** — per-staff commission owed for a date range, based on completed appointments.
- **Tips** — tips earned by staff for a date range.
- **Tax** — sales tax collected, for your monthly/yearly state filing.

## Payouts

`Payouts` — trigger an ACH payout to one or all eligible staff (commission + tips combined for the period). A staff member must have completed bank verification (see Staff above) before they're eligible. Track each payout's status: `pending` → `in_transit` → `completed` (or `failed`, with a reason, which also emails you).

## Settings

`Settings` — salon info (name, address, phone, timezone), operating hours, cancellation policy text, deposit percentage, no-show fee amount, tipping (on/off), and sales tax rate for your state. This is also where you'd update the salon's address/state if you ever move locations, since sales tax and the confirmation email's location both read from here.
