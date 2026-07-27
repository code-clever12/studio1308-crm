# Mobile App API

Authenticated JSON API for the companion mobile app — lead management only
(view/edit/update/delete/export form submissions). Fully separate from the
public, unauthenticated `POST /api/v1/submit-form` endpoint used by external
landing pages; nothing below is CORS-exposed or reachable by them.

- **Base URL (local):** `http://127.0.0.1:8000/api/v1` (adjust to whatever
  port `php artisan serve` prints)
- **Base URL (production):** `https://studio1308.code-clever.com/api/v1`
- **Auth:** Bearer token (Laravel Sanctum). Get one from `POST /auth/login`.
- **Access:** every protected endpoint requires `role = admin` on the
  authenticated account — a customer/staff login is rejected.
- **Format:** all requests/responses are JSON. Send `Content-Type:
  application/json` and `Accept: application/json` on every request.

---

## Authentication

### `POST /auth/login`

Public (no token required). Rate-limited to 10 requests/minute per IP.

**Request**
```json
{
  "email": "admin@ritualsalon.test",
  "password": "password",
  "device_name": "Alex's Pixel 6"
}
```
`device_name` is just a label — it shows up if you later list/revoke tokens,
so use something that identifies the physical device.

**Response `200`**
```json
{
  "token": "3|xK9pQ7z...(long string)",
  "user": {
    "id": 1,
    "name": "Alex Rivera",
    "email": "admin@ritualsalon.test",
    "role": "admin"
  }
}
```
Store `token` and send it on every subsequent request:
```
Authorization: Bearer 3|xK9pQ7z...
```

**Response `422`** (wrong password, unknown email, or a non-admin account)
```json
{
  "message": "The given data was invalid.",
  "errors": { "email": ["These credentials do not match our records."] }
}
```

### `POST /auth/logout`
Requires auth. Revokes the token used to make this request (i.e. logs out
*this device* only, not every device).

**Response `200`**
```json
{ "message": "Logged out." }
```

### `GET /auth/me`
Requires auth. Returns the current user — useful on app launch to confirm a
stored token is still valid.

**Response `200`**
```json
{ "user": { "id": 1, "name": "Alex Rivera", "email": "admin@ritualsalon.test", "role": "admin" } }
```

---

## Push notification device tokens

Register the device's FCM token so it receives a push the moment a new lead
completes a form (see "Push Notifications" below).

### `POST /device-tokens`
```json
{ "token": "<FCM registration token>", "platform": "android" }
```
`platform` is `android` or `ios`. Re-registering the same token (e.g. after
a token refresh) just updates it — safe to call every app launch.

**Response `201`**
```json
{ "success": true, "id": 7 }
```

### `DELETE /device-tokens`
Call this on logout so a signed-out device stops receiving pushes.
```json
{ "token": "<FCM registration token>" }
```
**Response `200`**
```json
{ "success": true }
```

---

## Forms

### `GET /forms`
List every form that has ever received a submission (for a filter dropdown).

**Response `200`**
```json
{
  "data": [
    { "id": 2, "name": "Hair Salon Contact Us", "slug": "hair-salon-contact-us" },
    { "id": 3, "name": "Hair Color Salon Contact Us", "slug": "hair-color-salon-contact-us" }
  ]
}
```

---

## Submissions (leads)

### `GET /submissions`
Paginated list, 20 per page, newest first.

**Query parameters** (all optional, combine freely):
| Param | Example | Filters by |
|---|---|---|
| `form_id` | `3` | a specific form |
| `service` | `lashes` | the `service` column (see Filters note below) |
| `capture_status` | `completed` or `abandoned` | whether the visitor finished the form |
| `page` | `2` | pagination |

**Response `200`**
```json
{
  "data": [
    {
      "id": 42,
      "form": { "id": 3, "name": "Hair Color Salon Contact Us", "slug": "hair-color-salon-contact-us" },
      "payload": { "name": "Jane Doe", "phone": "5551234567", "service": "lashes" },
      "url": "https://example.com/landing",
      "utm_parameters": { "utm_source": "facebook" },
      "value": 49.99,
      "service": "lashes",
      "status": "cold_lead",
      "capture_status": "completed",
      "submission_time": "2026-07-27T10:15:00+00:00",
      "created_at": "2026-07-27T10:15:00+00:00"
    }
  ],
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." },
  "meta": { "current_page": 1, "last_page": 2, "per_page": 20, "total": 31 }
}
```
`status` is one of `cold_lead` / `warm_lead` / `hot_lead` (admin-managed lead
quality). `capture_status` is `abandoned` / `completed` (whether the visitor
finished the form — see the partial-capture feature).

### `GET /submissions/{id}`
Single submission, same shape as one item in the list above, under `"data"`.

### `PUT /submissions/{id}`
Edit a lead's fields.
```json
{
  "payload": { "name": "Jane Smith", "phone": "5551234567", "service": "lashes" },
  "value": 75.00,
  "status": "hot_lead",
  "url": "https://example.com/landing"
}
```
`status` is required; the others are optional. Returns the updated
submission under `"data"`.

### `PATCH /submissions/{id}/status`
Quick status-only update (e.g. a swipe action in a list view).
```json
{ "status": "warm_lead" }
```

### `DELETE /submissions/{id}`
```json
{ "success": true }
```

### `GET /submissions/export`
Streams a CSV of every submission matching the current filters (same query
parameters as `GET /submissions`, but **not paginated** — the whole filtered
set downloads at once). Columns: ID, Form, Payload (JSON), URL, Value,
Service, Capture Status, Lead Status, Submitted At.

### `GET /submissions/services`
Every distinct value seen in any submission's `service` field, for a filter
dropdown — adapts automatically as your forms' service options change.
```json
{ "data": ["hair", "headspa", "lashes"] }
```

---

## Errors

| Status | Meaning |
|---|---|
| `401` | Missing/invalid/expired token |
| `403` | Valid token, but the account isn't `role = admin` |
| `422` | Validation failed — see `errors` in the response body |
| `429` | Rate limited — back off and retry |

---

## Push notifications (server setup, not an app concern)

New-lead pushes are sent automatically to every registered device the
moment a submission's `is_partial` is `false` (see `POST
/api/v1/submit-form`'s docs) — no app-side trigger needed, just register the
device token once via `POST /device-tokens` and the server does the rest.

Until a real Firebase project is connected, this silently no-ops — set
`FCM_PROJECT_ID` and `FCM_CREDENTIALS_PATH` in `.env` (a Firebase
service-account JSON key file path) to turn it on. See the comments in
`config/services.php` for exactly where to get that file.
