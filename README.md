# NotifyHub

[![Tests](https://github.com/snb4crazy/NotifyHub/actions/workflows/tests.yml/badge.svg)](https://github.com/snb4crazy/NotifyHub/actions/workflows/tests.yml)
[![Code Style](https://github.com/snb4crazy/NotifyHub/actions/workflows/pint.yml/badge.svg)](https://github.com/snb4crazy/NotifyHub/actions/workflows/pint.yml)
[![PHP Version](https://img.shields.io/badge/php-%5E8.3-blue)](https://www.php.net/)
[![License](https://img.shields.io/github/license/snb4crazy/NotifyHub)](./LICENSE)

NotifyHub is a central error and alert intake server for Laravel applications.
Your apps send normalized events to one API endpoint, NotifyHub stores them,
exposes a secure feed for web/mobile clients, and dispatches push notifications.

## Current capabilities

- Ingestion API: `POST /api/v1/events` with project-level key auth (`X-Project-Key`).
- Mobile API (Sanctum): login, feed, event details, settings, and device registration.
- Web portal (session auth): event timeline, filters, event details, and user settings.
- Queue-backed push dispatch with pluggable gateway (`log` or Firebase Cloud Messaging).
- Role-aware sensitive context access via project membership policy.
- One-command bootstrap for first project + owner account (`notifyhub:setup`).

## Architecture at a glance

1. Laravel app sends event payload to `POST /api/v1/events`.
2. NotifyHub validates and persists the event.
3. `SendEventPushJob` is queued when push is enabled.
4. Mobile/web clients read scoped events by project membership.
5. Sensitive fields are redacted for users without permission.

## Quick start (local)

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan notifyhub:setup --name="Personal Alerts" --slug=personal-alerts --owner-name="Owner" --owner-email="owner@example.com" --owner-password="secret-pass"
php artisan queue:work
php artisan serve
```

After setup, keep the printed ingest key and use it in sender apps as `X-Project-Key`.

## Configuration essentials

Use these baseline values in `.env` for a simple single-project install:

```env
NOTIFYHUB_MODE=single
NOTIFYHUB_DEFAULT_PROJECT_NAME=Personal Alerts
NOTIFYHUB_DEFAULT_PROJECT_SLUG=personal-alerts
NOTIFYHUB_PUSH_DRIVER=log
NOTIFYHUB_PUSH_ENABLED=true
NOTIFYHUB_PUSH_MIN_SEVERITY=error
NOTIFYHUB_SENSITIVE_ROLES=owner,admin,triager
```

To send real push notifications, switch to `NOTIFYHUB_PUSH_DRIVER=fcm` and provide Firebase credentials via `NOTIFYHUB_FCM_*` variables (or `NOTIFYHUB_FCM_CREDENTIALS_PATH`).

## API surface

- `POST /api/v1/events`
- `POST /api/v1/mobile/login`
- `DELETE /api/v1/mobile/logout`
- `GET /api/v1/mobile/feed`
- `GET /api/v1/mobile/events/{public_id}`
- `GET|PUT /api/v1/mobile/settings`
- `POST /api/v1/mobile/devices`

Authoritative request/response contract: `docs/api-contract.md`.

## Web portal

- `GET /login` for session login.
- `GET /portal` for the event feed with filters.
- `GET /portal/events/{event}` for event details.
- `GET|PUT /portal/settings` for user profile and notification preferences.

## Companion package

The in-repo package `packages/notifyhub-laravel/` helps sender applications report events with less boilerplate.

```bash
composer require snb4crazy/notifyhub-laravel
```

See `packages/notifyhub-laravel/README.md` for integration examples.

## Documentation map

- `docs/api-contract.md` - API contract and response shapes.
- `docs/setup.md` - install and bootstrap walkthrough.
- `docs/laravel-error-flow.md` - end-to-end sender to client flow.
- `docs/roadmap.md` - planned phases and expansion direction.
- `docs/sender-helper-and-ack-grouping.md` - optional sender helper and ACK/grouping strategy.

## Testing

```bash
php artisan test
```


## License

MIT
