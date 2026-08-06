# Package Health Checker starter notes

Starter package path:

- `packages/package-health-checker-laravel`

What it gives you now:

- pluggable check architecture
- artisan command `health:check`
- JSON output for log processors
- optional email alerting
- optional skip schedule (for SSL/mail)

Suggested next tuning steps for your environment:

1. Add all your DB replicas in `health-checker.database.remotes`.
2. Add Redis and frontend/API targets.
3. Add custom checks for external services not covered by default.
4. Wire command to scheduler with hourly and daily profiles.
5. Tune alerting channel, recipients, and severity.

