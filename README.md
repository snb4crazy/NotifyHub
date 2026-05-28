# NotifyHub

NotifyHub is a central notification server for multiple Laravel applications.
Apps POST normalized events to a single API, NotifyHub stores them, and the system
fans out mobile push notifications via FCM.

The MVP is intentionally simple so one person can run it quickly, but the codebase
is structured to grow into a multi-user team platform with project membership,
redaction rules, and mobile feed APIs.

## What is implemented now

- `POST /api/v1/events` for event intake.
- Project-scoped ingest key authentication via `X-Project-Key`.
- Validation, sanitization, and event persistence.
- Queued push-dispatch scaffold.
- Bootstrap command for creating the first project.
- Docs for MVP setup and team expansion.

## Quick start

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan notifyhub:setup --name="Personal Alerts" --slug=personal-alerts
php artisan serve
```

## MVP setup for one user

Use the defaults in `config/notifyhub.php` and `.env`:

```env
NOTIFYHUB_MODE=single
NOTIFYHUB_DEFAULT_PROJECT_NAME=Personal Alerts
NOTIFYHUB_DEFAULT_PROJECT_SLUG=personal-alerts
NOTIFYHUB_PUSH_ENABLED=true
NOTIFYHUB_PUSH_MIN_SEVERITY=error
NOTIFYHUB_SENSITIVE_ROLES=owner,admin,triager
```

Then send events using the ingest key created by `php artisan notifyhub:setup`.

## Team setup

For multiple users and projects, follow `docs/setup.md` and `docs/roadmap.md`.
Recommended team pattern:

- one project per product or environment;
- one ingest key per project;
- role-based access (`owner`, `admin`, `triager`, `viewer`);
- redact `sensitive_context` for users without permission.

## API overview

- `POST /api/v1/events` - intake events from Laravel apps.
- Future mobile API - feed, details, settings, devices, and membership views.

See `docs/api-contract.md` for the current request/response format.

## Testing

Run the full suite:

```bash
php artisan test
```

## Documentation

- `docs/roadmap.md` - product phases, RBAC proposal, deliverables.
- `docs/api-contract.md` - intake payload contract and response examples.
- `docs/setup.md` - single-user MVP setup and team onboarding.

## Extending the platform

The code is built to swap infrastructure without rewriting the application layer:

- replace `App\Services\LoggingPushGateway` with a real FCM adapter;
- expand `App\Models\Project` membership and policies;
- add Sanctum for mobile authentication;
- introduce feed/details endpoints under `/api/v1`.

## Laravel and agent-friendly notes

Key classes and functions include detailed docblocks so humans and AI helpers can
trace the flow quickly:

- `app/Http/Controllers/Api/V1/EventIngestionController.php`
- `app/Http/Middleware/EnsureProjectIngestKey.php`
- `app/Http/Requests/StoreEventRequest.php`
- `app/Services/ProjectBootstrapService.php`
- `app/Jobs/SendEventPushJob.php`

## License

MIT
