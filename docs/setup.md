# NotifyHub Setup Guide

This project is designed to work in two modes:

- **Single-user MVP**: one owner, one mobile app, one or a few Laravel apps.
- **Team mode**: multiple users, project membership, sensitive context redaction, and role-based access.

## 1) Fresh install

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

If you use SQLite, make sure `database/database.sqlite` exists and `DB_CONNECTION=sqlite` is set.

## 2) Create your first project

For the quickest MVP, bootstrap one project and reuse its ingest key in your Laravel apps.

```bash
php artisan notifyhub:setup \
  --name="Personal Alerts" \
  --slug=personal-alerts \
  --owner-name="Owner" \
  --owner-email="owner@example.com" \
  --owner-password="secret-pass"
```

The command prints the ingest key. Put that key into your sending apps as `X-Project-Key`.
If owner credentials are provided, you can immediately log into the mobile API.

### Optional explicit key for testing

```bash
php artisan notifyhub:setup \
  --name="Personal Alerts" \
  --slug=personal-alerts \
  --ingest-key=test_ingest_key_123
```

## 3) Configure single-user mode

Use these `.env` values for a simple MVP:

```env
NOTIFYHUB_MODE=single
NOTIFYHUB_DEFAULT_PROJECT_NAME=Personal Alerts
NOTIFYHUB_DEFAULT_PROJECT_SLUG=personal-alerts
NOTIFYHUB_PUSH_DRIVER=log
NOTIFYHUB_PUSH_ENABLED=true
NOTIFYHUB_PUSH_MIN_SEVERITY=error
NOTIFYHUB_SENSITIVE_ROLES=owner,admin,triager
```

### What this means
- **single** mode keeps the operational model simple.
- Push notifications are only sent for `error` and `critical` by default.
- `viewer` users do not see sensitive context.

## 4) Team mode

When you add teammates, move to project membership:

- `owner` - full access.
- `admin` - manage members and channels.
- `triager` - inspect errors and stack traces.
- `viewer` - feed and basic details only.

Recommended team changes:

1. Create one project per product or environment.
2. Assign users to projects through membership rows.
3. Give only trusted roles access to `sensitive_context`.
4. Keep one ingest key per project, not per user.

## 5) Send events from a Laravel app

```bash
curl -X POST "https://your-notifyhub.example/api/v1/events" \
  -H "Content-Type: application/json" \
  -H "X-Project-Key: YOUR_PROJECT_INGEST_KEY" \
  -d '{
    "title": "Payment Failed",
    "message": "Order #1234 failed",
    "severity": "critical",
    "application": "billing-api",
    "context": {"order_id": 1234}
  }'
```

## 6) What is implemented now

- HTTP event intake at `POST /api/v1/events`
- Validation + sanitization
- Event storage
- Async push dispatch scaffold
- Sanctum token auth for the mobile API
- Mobile feed, details, settings, and device registration endpoints
- Initial project bootstrap command
- Documentation for easy setup and team growth

## 7) Mobile API quick try

```bash
curl -X POST "http://127.0.0.1:8000/api/v1/mobile/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email":"owner@example.com",
    "password":"secret-pass",
    "device_name":"iPhone"
  }'
```

Then call the mobile feed with the returned bearer token.

Reference contract for mobile implementation:
- `docs/api-contract.md` (authoritative request/response shapes)
- `docs/laravel-error-flow.md` (end-to-end examples from sender to mobile)

## 8) Optional real FCM configuration

Keep `NOTIFYHUB_PUSH_DRIVER=log` while testing locally.

When you are ready to send real pushes, switch to:

```env
NOTIFYHUB_PUSH_DRIVER=fcm
NOTIFYHUB_FCM_PROJECT_ID=your-firebase-project-id
NOTIFYHUB_FCM_CLIENT_EMAIL=firebase-adminsdk-xxxxx@your-project.iam.gserviceaccount.com
NOTIFYHUB_FCM_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----\n"
```

You can also provide `NOTIFYHUB_FCM_CREDENTIALS_PATH=/absolute/path/to/service-account.json` instead of the inline email/private key pair.

## 9) Next recommended steps

- Implement mobile client UI flows using `docs/api-contract.md`.
- Add refresh/polling strategy in mobile feed.
- Replace the logging push adapter with real FCM delivery in non-local environments.
- Add grouped incident/ACK flow if noise becomes high (`docs/sender-helper-and-ack-grouping.md`).

