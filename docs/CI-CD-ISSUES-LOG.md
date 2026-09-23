# CI/CD Issues and Fixes Log

Use this document to track problems discovered while building CI and test infrastructure.

## Template
- Date:
- Area:
- Problem:
- Root cause:
- Fix:
- Verification:
- Notes:

## Example entries

### 1. Tests depend on local `.env`
- Date: 2026-09-23
- Area: test environment
- Problem: Tests fail on fresh CI runners because `.env` is missing or differs from local settings.
- Root cause: Test behavior depends on runtime environment instead of test overrides.
- Fix:
  - move required settings into `phpunit.xml`
  - use test-specific env vars
  - ensure tests do not read production `.env`
- Verification:
  - run `php artisan test` in a clean environment
  - confirm no `.env` is needed
- Notes: This is one of the first things to solve.

### 2. Database connection mismatch
- Date: 2026-09-23
- Area: database
- Problem: CI tries to use MySQL or another external database.
- Root cause: default app config still points to production-style settings.
- Fix:
  - force SQLite in testing
  - run migrations against the test database
- Verification:
  - run migrations in CI
  - confirm tests pass without external services
- Notes: Use a file-based SQLite DB if needed for debugging.

### 3. Composer install is slow
- Date: 2026-09-23
- Area: CI performance
- Problem: CI takes too long to install dependencies.
- Root cause: Composer dependencies are not cached.
- Fix:
  - enable Composer cache in the workflow
- Verification:
  - compare run times before and after caching
- Notes: Keep cache keys tied to `composer.lock`.

### 4. Redundant CI runs
- Date: 2026-09-23
- Area: workflow behavior
- Problem: multiple pushes to the same PR branch cause outdated runs to keep executing.
- Root cause: concurrency not configured.
- Fix:
  - add workflow concurrency cancellation
- Verification:
  - push two commits quickly and confirm old run is canceled
- Notes: Helps save minutes and reduces noise.

### 5. Coverage or test output hard to inspect
- Date: 2026-09-23
- Area: observability
- Problem: Failure details are hard to inspect after CI completes.
- Root cause: logs are ephemeral and no artifact is uploaded.
- Fix:
  - upload test and coverage artifacts
  - add job summary links
- Verification:
  - inspect artifacts from a completed workflow
- Notes: Useful for learning and debugging.

### 6. External dependency instability
- Date: 2026-09-23
- Area: integration tests
- Problem: Tests are flaky because they call a real external API.
- Root cause: no mock or stub layer.
- Fix:
  - mock the API client or fake HTTP responses
  - keep tests offline
- Verification:
  - run tests with network disabled or mocked responses only
- Notes: Add only if the app truly depends on an external service.

### 7. Deliberately failing test
- Date: 2026-09-23
- Area: learning/demo
- Problem: Need to learn failure reporting and CI behavior.
- Root cause: educational exercise.
- Fix:
  - create a temporary branch or demo test that fails on purpose
- Verification:
  - confirm CI marks the job as failed and uploads logs/artifacts
- Notes: Do not merge this into main.

### 8. Manual failure demo workflow
- Date: 2026-09-23
- Area: workflow learning
- Problem: Need a safe way to demonstrate a failing test without breaking normal CI.
- Root cause: production CI must remain green, so the failure must be gated.
- Fix:
  - add a manual-only `workflow_dispatch` demo workflow
  - gate the failure behind `DEMO_FAILING_TEST=1`
  - upload the failure log as an artifact
- Verification:
  - run the demo workflow manually in GitHub Actions
  - confirm the workflow fails as expected and preserves the log artifact
- Notes: Keep this workflow isolated from the main CI trigger path.

## Questions to decide later
- Do we want file-based SQLite or in-memory SQLite in CI?
- Do we want coverage generation now or only basic test execution?
- Is there a real external API to mock?
- Should migrations run in every CI job or only feature/integration jobs?