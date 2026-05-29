# NotifyHub Roadmap

## Vision
NotifyHub is the central alerting API for all your Laravel apps. Apps send normalized event payloads over HTTP, NotifyHub stores them, then pushes high-signal notifications to your mobile app via FCM.

## Product goals
- Reliable event intake with clear ACK/NACK semantics.
- Role-based access so only allowed members can view sensitive diagnostics.
- Fast mobile experience: feed, details, per-user settings.
- Decoupled delivery pipeline (event storage first, push dispatch async).

## MVP operating model

### Single-user mode
- One operator owns the NotifyHub instance.
- One project can collect events from one or many Laravel apps.
- Use the bootstrap command to create a project and share its ingest key.
- Keep push dispatch on by default for `error` and `critical` severities.

### Team mode
- Each product/environment gets its own project.
- Users are added to projects with roles.
- Sensitive details are available only to trusted members.
- Mobile apps can show project feed, event details, and user settings.

## Scope phases

### Phase 0 - Foundation (current sprint)
- Data model for `projects`, `project_user`, `events`, `user_devices`.
- Event intake endpoint with request validation + sanitization.
- API key style project auth for intake (`X-Project-Key`).
- Async push dispatch job (initial adapter logs payloads; FCM adapter next).
- Initial automated tests for intake flow.

### Phase 1 - Mobile API baseline
- User auth strategy for mobile API (Laravel Sanctum recommended).
- Project membership APIs.
- Event feed and event details endpoints.
- Redaction policy: hide `sensitive_context` unless member has permission.
- Device registration endpoint (`fcm_token`, platform, enabled flag).

### Phase 2 - FCM production delivery
- Real FCM gateway implementation.
- Retry and dead-letter handling for push jobs.
- Severity-based notification templates.
- Delivery telemetry (attempted/sent/failed) and dashboard metrics.

### Phase 3 - Operations hardening
- Rate limiting + request signature support for intake.
- Idempotency/fingerprint handling to reduce duplicate noise.
- Alert routing rules (per project and severity).
- Retention, archival, and data pruning jobs.

## RBAC proposal
Project-level membership in `project_user`:
- `owner`: full access, membership/admin controls.
- `admin`: manage members + channels, view sensitive diagnostics.
- `triager`: can view stack traces and context, cannot manage members.
- `viewer`: feed/details only, sensitive fields redacted.

Use `can_view_sensitive` as a policy override so role and exception-based access are both possible.

## Deliverables checklist
- [x] Initial architecture + roadmap docs.
- [x] Domain tables + Eloquent models.
- [x] POST `/api/v1/events` endpoint.
- [x] Validation/sanitization + ACK response contract.
- [x] Async push dispatch scaffold.
- [x] Feature tests for intake success and auth failure.
- [x] Single-user bootstrap command and configuration.
- [x] Setup guide for team expansion.
- [x] Mobile auth + feed/details/settings endpoints.
- [x] Device registration endpoint.
- [x] RBAC-based sensitive context redaction for event details.
- [x] FCM gateway implementation (credentials wiring still environment-specific).

## Risks and decisions to confirm
- Should event intake auth be static API key only, or also HMAC signed payloads?
- Which severities should trigger push by default (`error+critical` vs all)?
- Do you want per-project retention (for example 30/90/365 days)?
- For sensitive redaction, is role-based enough or should fields be masked by key names too?

