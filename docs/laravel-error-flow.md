# Laravel Error Flow Example

This document shows how a Laravel app can send an exception-like event into NotifyHub today, how it is stored, what push notification is generated, and what the future mobile app reads back from the API.

## 1) Example input request from a Laravel app

A sending Laravel app can POST an event whenever an exception, failed job, cron failure, or infrastructure issue happens.

### Minimal sender example in another Laravel app

```php
use Illuminate\Support\Facades\Http;

Http::baseUrl(config('services.notifyhub.url'))
    ->withHeaders([
        'X-Project-Key' => config('services.notifyhub.ingest_key'),
    ])
    ->post('/api/v1/events', [
        'event_type' => 'laravel.exception',
        'title' => 'Unhandled exception',
        'message' => $exception->getMessage(),
        'severity' => 'critical',
        'application' => config('app.name'),
        'environment' => app()->environment(),
        'context' => [
            'exception_class' => $exception::class,
            'url' => request()?->fullUrl(),
        ],
        'sensitive_context' => [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => collect($exception->getTrace())->take(10)->all(),
        ],
    ]);
```

```json
{
  "event_type": "laravel.exception",
  "title": "Unhandled exception",
  "message": "SQLSTATE[HY000]: General error: 8 attempt to write a readonly database",
  "severity": "critical",
  "application": "billing-api",
  "environment": "production",
  "fingerprint": "billing-api:production:readonly-database",
  "occurred_at": "2026-05-28T20:30:14Z",
  "context": {
    "exception_class": "PDOException",
    "request_id": "01HVKJ2M8S5QW1A7VZ0A",
    "url": "https://billing.example.com/api/checkout",
    "method": "POST",
    "user_id": 481,
    "tags": ["database", "checkout", "incident"]
  },
  "sensitive_context": {
    "file": "/var/www/app/Services/CheckoutService.php",
    "line": 182,
    "trace": [
      "App\\Services\\CheckoutService->charge()",
      "App\\Http\\Controllers\\CheckoutController->store()"
    ]
  }
}
```

## 2) How NotifyHub stores it

Current MVP storage model in `events`:

```json
{
  "public_id": "5e834698-16f1-4e6d-98d4-0bf715493f7d",
  "project_id": 1,
  "event_type": "laravel.exception",
  "title": "Unhandled exception",
  "message": "SQLSTATE[HY000]: General error: 8 attempt to write a readonly database",
  "severity": "critical",
  "application": "billing-api",
  "environment": "production",
  "context": {
    "exception_class": "PDOException",
    "request_id": "01HVKJ2M8S5QW1A7VZ0A",
    "url": "https://billing.example.com/api/checkout",
    "method": "POST",
    "user_id": 481,
    "tags": ["database", "checkout", "incident"]
  },
  "sensitive_context": {
    "file": "/var/www/app/Services/CheckoutService.php",
    "line": 182,
    "trace": [
      "App\\Services\\CheckoutService->charge()",
      "App\\Http\\Controllers\\CheckoutController->store()"
    ]
  },
  "fingerprint": "billing-api:production:readonly-database",
  "source_ip": "203.0.113.10",
  "acknowledged_at": "2026-05-28T20:30:14Z"
}
```

## 3) What push notification looks like

The push payload builder currently produces a compact notification:

```json
{
  "notification": {
    "title": "[CRITICAL] Unhandled exception",
    "body": "SQLSTATE[HY000]: General error: 8 attempt to write a readonly database"
  },
  "data": {
    "event_id": "5e834698-16f1-4e6d-98d4-0bf715493f7d",
    "project_slug": "billing-api",
    "severity": "critical",
    "event_type": "laravel.exception",
    "application": "billing-api",
    "environment": "production"
  }
}
```

That gives the mobile app enough information to open the event details page.

## 4) Mobile feed route and structure

### Route
`GET /api/v1/mobile/feed`

### Example response
```json
{
  "data": [
    {
      "id": "5e834698-16f1-4e6d-98d4-0bf715493f7d",
      "project": {
        "name": "Billing API",
        "slug": "billing-api"
      },
      "event_type": "laravel.exception",
      "severity": "critical",
      "title": "Unhandled exception",
      "message_preview": "SQLSTATE[HY000]: General error: 8 attempt to write a readonly database",
      "application": "billing-api",
      "environment": "production",
      "has_sensitive_context": true,
      "occurred_at": "2026-05-28T20:30:14Z",
      "created_at": "2026-05-28T20:30:15Z"
    }
  ]
}
```

### Intended mobile use
- Show a feed of latest alerts across projects.
- Use `severity` for color/icon.
- Use `event_type` to distinguish exceptions vs cron failures vs payment events later.
- Use `has_sensitive_context` to show an indicator without exposing protected data.

## 5) Mobile details route and structure

### Route
`GET /api/v1/mobile/events/{public_id}`

### Example response for a triager/admin/owner
```json
{
  "data": {
    "id": "5e834698-16f1-4e6d-98d4-0bf715493f7d",
    "project": {
      "name": "Billing API",
      "slug": "billing-api"
    },
    "event_type": "laravel.exception",
    "severity": "critical",
    "title": "Unhandled exception",
    "message": "SQLSTATE[HY000]: General error: 8 attempt to write a readonly database",
    "application": "billing-api",
    "environment": "production",
    "fingerprint": "billing-api:production:readonly-database",
    "source_ip": "203.0.113.10",
    "context": {
      "exception_class": "PDOException",
      "request_id": "01HVKJ2M8S5QW1A7VZ0A"
    },
    "can_view_sensitive": true,
    "sensitive_context": {
      "file": "/var/www/app/Services/CheckoutService.php",
      "line": 182,
      "trace": [
        "App\\Services\\CheckoutService->charge()",
        "App\\Http\\Controllers\\CheckoutController->store()"
      ]
    },
    "occurred_at": "2026-05-28T20:30:14Z",
    "created_at": "2026-05-28T20:30:15Z"
  }
}
```

### Example response for a viewer
`can_view_sensitive` becomes `false` and `sensitive_context` becomes `null`.

## 6) Mobile settings route and structure

### Route
`GET /api/v1/mobile/settings`

### Example response
```json
{
  "data": {
    "user": {
      "name": "Serhii",
      "email": "owner@example.com",
      "timezone": "Europe/Kyiv"
    },
    "notification_preferences": {
      "push_enabled": true,
      "minimum_severity": "error"
    },
    "projects": [
      {
        "name": "Billing API",
        "slug": "billing-api",
        "role": "owner",
        "can_view_sensitive": true
      }
    ],
    "devices": [
      {
        "id": 1,
        "name": "My iPhone",
        "platform": "ios",
        "notifications_enabled": true,
        "last_seen_at": "2026-05-28T20:35:00Z"
      }
    ]
  }
}
```

## 7) Can the current code handle webhook-based Laravel error events?

Yes, for an MVP it can handle them well if you map the payload consistently:

- put a stable category into `event_type` such as `laravel.exception`, `queue.failed`, `cron.failed`, `payment.failed`;
- put the human summary into `title`;
- put the short operator-facing message into `message`;
- put searchable metadata into `context`;
- put stack traces, file paths, and internals into `sensitive_context`;
- use `fingerprint` when you want future deduplication/grouping.

That means the schema is already broad enough for more than PHP/DB/server exceptions without changing the intake contract every time.


