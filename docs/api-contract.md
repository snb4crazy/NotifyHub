# NotifyHub API Contract

Base path: `/api/v1`

This document is the implementation-aligned contract for current endpoints.

## 1) Authentication

### 1.1 Event intake auth
- Header: `X-Project-Key: <project_ingest_key>`
- Used only for `POST /events`
- Missing/invalid key returns `401 Unauthorized`

### 1.2 Mobile API auth
- `POST /mobile/login` is public
- All other `/mobile/*` routes require Sanctum bearer token:
  - Header: `Authorization: Bearer <token>`
- Missing/invalid token returns `401 Unauthorized`

## 2) Error response shape

Laravel default JSON error format is used.

Validation error (`422`) example:
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "severity": ["The selected severity is invalid."]
  }
}
```

Unauthorized (`401`) example:
```json
{
  "message": "Unauthenticated."
}
```

For mobile login credential failure, response is:
```json
{
  "message": "Invalid credentials."
}
```

## 3) Event intake

### POST `/events`
Ingests one event, stores it, and asynchronously schedules push delivery when enabled.

#### Headers
- `Content-Type: application/json`
- `X-Project-Key: <project_ingest_key>`

#### Request body
```json
{
  "event_type": "laravel.exception",
  "title": "Payment Failed",
  "message": "Order #1234 failed authorization",
  "severity": "critical",
  "application": "billing-api",
  "environment": "production",
  "context": {
    "order_id": 1234,
    "provider": "stripe"
  },
  "sensitive_context": {
    "exception": "...",
    "trace": ["..."]
  },
  "occurred_at": "2026-05-28T13:00:00Z",
  "fingerprint": "billing-api:payment:1234"
}
```

#### Validation rules
- `event_type`: nullable, string, max 80
- `title`: required, string, max 140
- `message`: required, string, max 5000
- `severity`: required, enum `info|warning|error|critical`
- `application`: nullable, string, max 120
- `environment`: nullable, string, max 50
- `context`: nullable, object
- `sensitive_context`: nullable, object
- `occurred_at`: nullable, datetime
- `fingerprint`: nullable, string, max 255

#### Success response (`202 Accepted`)
```json
{
  "status": "accepted",
  "event_id": "5e834698-16f1-4e6d-98d4-0bf715493f7d"
}
```

#### Error statuses
- `401 Unauthorized` (missing/invalid ingest key)
- `422 Unprocessable Entity` (validation failed)

## 4) Mobile auth

### POST `/mobile/login`
Issues a Sanctum token.

#### Request body
```json
{
  "email": "owner@example.com",
  "password": "secret-pass",
  "device_name": "iPhone"
}
```

#### Validation rules
- `email`: required, valid email
- `password`: required, string
- `device_name`: nullable, string, max 120

#### Success response (`200 OK`)
```json
{
  "token": "1|long_plain_text_token",
  "token_type": "Bearer"
}
```

#### Error statuses
- `401 Unauthorized` (invalid credentials)
- `422 Unprocessable Entity` (validation failed)

### DELETE `/mobile/logout`
Revokes current bearer token.

#### Auth
- Bearer token required

#### Success response (`200 OK`)
```json
{
  "status": "logged_out"
}
```

## 5) Mobile feed

### GET `/mobile/feed`
Returns paginated events from projects the authenticated user belongs to.

#### Auth
- Bearer token required

#### Query parameters
- `project_slug` (optional, string)
- `severity` (optional, string)
- `event_type` (optional, string)
- `per_page` (optional, int, clamped to `1..100`, default `20`)

#### Success response (`200 OK`)
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
  ],
  "links": {
    "first": "http://127.0.0.1:8000/api/v1/mobile/feed?page=1",
    "last": "http://127.0.0.1:8000/api/v1/mobile/feed?page=3",
    "prev": null,
    "next": "http://127.0.0.1:8000/api/v1/mobile/feed?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 3,
    "path": "http://127.0.0.1:8000/api/v1/mobile/feed",
    "per_page": 20,
    "to": 20,
    "total": 57
  }
}
```

#### Error statuses
- `401 Unauthorized`

## 6) Mobile event details

### GET `/mobile/events/{public_id}`
Returns event details if user has project access.

#### Auth
- Bearer token required

#### Path param
- `public_id` (UUID event ID)

#### Access control
- If user is not a member of the event project: `403 Forbidden`
- If user is member but lacks sensitive permission:
  - `can_view_sensitive: false`
  - `sensitive_context: null`

#### Success response (`200 OK`)
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
    "message": "SQLSTATE[HY000]: General error",
    "application": "billing-api",
    "environment": "production",
    "fingerprint": "billing-api:production:readonly-database",
    "source_ip": "203.0.113.10",
    "context": {
      "request_id": "01HVKJ2M8S5QW1A7VZ0A"
    },
    "can_view_sensitive": true,
    "sensitive_context": {
      "trace": ["..."]
    },
    "occurred_at": "2026-05-28T20:30:14Z",
    "created_at": "2026-05-28T20:30:15Z"
  }
}
```

#### Error statuses
- `401 Unauthorized`
- `403 Forbidden`
- `404 Not Found`

## 7) Mobile settings

### GET `/mobile/settings`
Returns current user profile, preferences, project memberships, and registered devices.

#### Auth
- Bearer token required

#### Success response (`200 OK`)
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

### PUT `/mobile/settings`
Updates lightweight user settings.

#### Auth
- Bearer token required

#### Request body
```json
{
  "timezone": "Europe/Kyiv",
  "notification_preferences": {
    "push_enabled": true,
    "minimum_severity": "warning"
  }
}
```

#### Validation rules
- `timezone`: nullable, string, max 64
- `notification_preferences`: nullable, object
- `notification_preferences.push_enabled`: nullable, boolean
- `notification_preferences.minimum_severity`: nullable, enum `info|warning|error|critical`

#### Partial update semantics
- Omitted top-level fields are not modified.
- If `notification_preferences` is omitted entirely, existing preferences remain unchanged.
- If `notification_preferences` is provided with only one nested key, only that key is updated and the other stored preference keys remain unchanged.
- Example: sending only `{"timezone":"Europe/Kyiv"}` updates timezone only.
- Example: sending only `{"notification_preferences":{"minimum_severity":"warning"}}` updates minimum severity and keeps the current `push_enabled` value.

#### Success response (`200 OK`)
- Same shape as `GET /mobile/settings`

#### Error statuses
- `401 Unauthorized`
- `422 Unprocessable Entity`

## 8) Mobile device registration

### POST `/mobile/devices`
Registers or updates a device by `fcm_token`.

#### Auth
- Bearer token required

#### Request body
```json
{
  "name": "My iPhone",
  "platform": "ios",
  "fcm_token": "fcm_test_token_123",
  "notifications_enabled": true
}
```

#### Validation rules
- `name`: nullable, string, max 120
- `platform`: nullable, string, max 20
- `fcm_token`: required, string, max 2048
- `notifications_enabled`: nullable, boolean

#### Success response (`200 OK`)
```json
{
  "status": "registered",
  "device_id": 1
}
```

#### Error statuses
- `401 Unauthorized`
- `422 Unprocessable Entity`

## 9) Quick try commands

```bash
curl -X POST "http://127.0.0.1:8000/api/v1/events" \
  -H "Content-Type: application/json" \
  -H "X-Project-Key: YOUR_PROJECT_INGEST_KEY" \
  -d '{
    "title":"Payment Failed",
    "message":"Order #1234 failed",
    "severity":"critical",
    "application":"billing-api",
    "context":{"order_id":1234}
  }'
```

```bash
curl -X POST "http://127.0.0.1:8000/api/v1/mobile/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email":"owner@example.com",
    "password":"secret-pass",
    "device_name":"iPhone"
  }'
```
