# NotifyHub API Contract (Phase 0)

Base path: `/api/v1`

## Authentication

### Intake endpoint
- Header: `X-Project-Key: <project_ingest_key>`
- If missing/invalid, server returns `401` with JSON error.

## POST `/events`
Ingests an event, stores it, and enqueues push delivery.

### Request body
```json
{
  "title": "Payment Failed",
  "message": "Order #1234 failed authorization",
  "severity": "critical",
  "application": "billing-api",
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

### Validation rules
- `title`: required, string, max 140
- `message`: required, string, max 5000
- `severity`: required, one of `info|warning|error|critical`
- `application`: nullable, string, max 120
- `context`: nullable, object
- `sensitive_context`: nullable, object
- `occurred_at`: nullable, ISO datetime
- `fingerprint`: nullable, string, max 255

### Response (`202 Accepted`)
```json
{
  "status": "accepted",
  "event_id": "5e834698-16f1-4e6d-98d4-0bf715493f7d"
}
```

### Error response examples
- `401 Unauthorized` when key is missing/invalid.
- `422 Unprocessable Entity` when payload validation fails.

## Notes
- Phase 0 stores then queues push delivery.
- Current push gateway logs payloads (FCM implementation in Phase 2).
- Mobile feed/details/settings routes are planned for Phase 1.

### Quick try commands:
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