# Sender Helper + ACK/Grouping Plan

This document focuses on two MVP-friendly additions for next iteration:

1. a ready-to-copy sender helper for other Laravel apps;
2. simple event acknowledgment/grouping so repeated errors are easier to manage.

Both are designed to stay optional and can be disabled via `.env` values.

## 1) Ready-to-copy sender helper (for other Laravel apps)

Add this service class to any Laravel app that should send alerts into NotifyHub.

```php
<?php

namespace App\Support\NotifyHub;

use Illuminate\Support\Facades\Http;

class NotifyHubSender
{
    public function send(array $payload): void
    {
        if (! config('services.notifyhub.enabled', true)) {
            return;
        }

        Http::baseUrl((string) config('services.notifyhub.url'))
            ->withHeaders([
                'X-Project-Key' => (string) config('services.notifyhub.ingest_key'),
                'Accept' => 'application/json',
            ])
            ->timeout((int) config('services.notifyhub.timeout', 5))
            ->retry(2, 200)
            ->post('/api/v1/events', $payload)
            ->throw();
    }

    public function sendException(\Throwable $exception, array $context = []): void
    {
        $this->send([
            'event_type' => 'laravel.exception',
            'title' => 'Unhandled exception',
            'message' => $exception->getMessage(),
            'severity' => 'critical',
            'application' => config('app.name'),
            'environment' => app()->environment(),
            'fingerprint' => sprintf('%s:%s:%s', config('app.name'), app()->environment(), $exception::class),
            'context' => $context,
            'sensitive_context' => [
                'class' => $exception::class,
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => collect($exception->getTrace())->take(15)->values()->all(),
            ],
        ]);
    }
}
```

Suggested sender-side config in `config/services.php`:

```php
'notifyhub' => [
    'enabled' => env('NOTIFYHUB_ENABLED', true),
    'url' => env('NOTIFYHUB_URL'),
    'ingest_key' => env('NOTIFYHUB_INGEST_KEY'),
    'timeout' => env('NOTIFYHUB_TIMEOUT', 5),
],
```

Sender-side `.env` example:

```env
NOTIFYHUB_ENABLED=true
NOTIFYHUB_URL=https://notifyhub.example.com
NOTIFYHUB_INGEST_KEY=proj_ingest_key_here
NOTIFYHUB_TIMEOUT=5
```

### Why this helper is practical
- One small class to copy into each Laravel app.
- Uses the same normalized payload structure across apps.
- Enables quick webhook testing before deeper integrations.

## 2) Simple ACK/grouping plan (optional, env-driven)

### Problem
When one root issue generates many repeated errors, the feed can become noisy.

### Minimal behavior to add
- Group repeated events by `fingerprint` inside a time window.
- Keep one open group entry and increment a counter for repeats.
- Allow manual acknowledgment of a group from mobile/API.

### Proposed env switches

```env
NOTIFYHUB_ACK_GROUPING_ENABLED=true
NOTIFYHUB_ACK_GROUPING_WINDOW_MINUTES=30
NOTIFYHUB_ACK_GROUPING_KEY=fingerprint
NOTIFYHUB_ACK_GROUPING_AUTO_ACK=false
```

If `NOTIFYHUB_ACK_GROUPING_ENABLED=false`, behavior falls back to current mode:
- every incoming event is stored independently;
- no grouping/aggregation logic is applied.

### Minimal data model proposal
- `event_groups`
  - `id`
  - `project_id`
  - `group_key` (for example fingerprint)
  - `title`
  - `severity`
  - `status` (`open`, `acknowledged`, `resolved`)
  - `first_event_at`
  - `last_event_at`
  - `occurrences_count`
  - `acknowledged_at`
  - `acknowledged_by`
- Add nullable `event_group_id` to `events`.

### Minimal API proposal
- `POST /api/v1/mobile/event-groups/{id}/acknowledge`
- `GET /api/v1/mobile/feed?mode=groups`

ACK response sketch:

```json
{
  "status": "acknowledged",
  "event_group_id": 42,
  "acknowledged_at": "2026-05-28T22:00:00Z"
}
```

### Grouped feed item sketch

```json
{
  "id": 42,
  "project_slug": "billing-api",
  "group_key": "billing-api:production:PDOException",
  "title": "Unhandled exception",
  "severity": "critical",
  "occurrences_count": 17,
  "first_event_at": "2026-05-28T21:40:00Z",
  "last_event_at": "2026-05-28T21:58:00Z",
  "status": "open"
}
```

## 3) Rollout approach
1. Ship sender helper docs first and reuse current `/api/v1/events`.
2. Add grouping feature behind `NOTIFYHUB_ACK_GROUPING_ENABLED`.
3. Start with grouped feed read-only view.
4. Add manual ACK action.
5. Expand to auto-resolve/routing only if needed.

