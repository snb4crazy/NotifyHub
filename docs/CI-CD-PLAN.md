# CI/CD Learning Plan for NotifyHub

## Goals
Build a production-safe CI pipeline for this Laravel app that:
- runs on pull requests
- runs on the main branch
- validates tests in a clean environment
- caches dependencies for speed
- publishes useful summaries and artifacts
- avoids deployment, since this is an open-source project

## Current state
- Laravel app with PHPUnit configured
- SQLite test settings already present in `phpunit.xml`
- No GitHub Actions workflows yet
- Minimal test coverage so far

## Recommended CI scope

### Phase 1: baseline CI
Add a GitHub Actions workflow that runs:
- `composer install`
- `php artisan test`
- optional coverage collection
- artifact upload for reports/logs

Trigger on:
- `pull_request`
- push to `main` or `master`

Add:
- Composer cache
- concurrency cancellation
- job summary output
- test artifact upload

### Phase 2: test infrastructure hardening
Make tests independent of `.env`:
- rely on `phpunit.xml` env overrides
- keep DB set to SQLite in testing
- ensure no test reads production `.env` values directly

Use SQLite in CI:
- file-based SQLite is often easier to debug than `:memory:` for multi-step CI jobs
- in-memory SQLite is fine for simple unit/feature tests
- if migrations or multiple processes become involved, use a file DB like `database/database.sqlite`

Run migrations in CI:
- `php artisan migrate --force`
- only after the test database is configured
- confirm tests are isolated from production config

### Phase 3: learning extras
Add a deliberately failing test in a separate branch or temporary PR to learn:
- job failure behavior
- annotations
- artifact retention
- failure summaries

Add an external API mock only if the app uses a real external dependency:
- mock HTTP calls
- avoid real network calls in CI
- assert timeout and error handling

Add a service-unavailable test only when there is a real service to protect:
- simulate API failure
- verify graceful fallback
- verify retry or error reporting behavior

## Suggested workflow design

### Workflow 1: CI
Trigger:
- pull requests
- pushes to main branch

Jobs:
1. `test`
  - checkout
  - setup PHP
  - install Composer deps with cache
  - configure testing env
  - run migrations
  - run tests
  - upload artifacts
  - publish summary

2. Optional `lint`
  - run Pint or static analysis if added later

### Workflow 2: learning-failure-demo
Optional and temporary:
- a separate workflow or branch that contains a deliberately failing test
- used only for learning
- not merged into production branch

## Test environment plan
- Keep `phpunit.xml` as the source of truth for test env vars
- Ensure tests do not require `.env`
- Prefer SQLite for CI
- If the app grows, use `RefreshDatabase` in feature tests
- Add database setup before test execution

## What to avoid
- deployment steps
- production environment variables in CI
- real external API calls in tests
- adding too many test cases before CI is stable

## Definition of done
- PRs automatically run CI
- main branch pushes automatically run CI
- tests pass without `.env`
- CI uses SQLite
- migrations run in CI
- Composer dependencies are cached
- job summary is visible in GitHub Actions
- artifacts are uploaded for debugging
- concurrency cancellation prevents redundant runs

## Next implementation order
1. Add GitHub Actions workflow
2. Add cache and concurrency
3. Add SQLite/migration setup
4. Add summary and artifacts
5. Add more tests
6. Add mocked external API tests if needed
7. Add a temporary failing-test learning branch