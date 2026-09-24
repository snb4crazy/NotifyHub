<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'NotifyHub') }}</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                body { font-family: ui-sans-serif, system-ui, -apple-system, sans-serif; margin: 0; background: #f3f4f6; color: #111827; }
                .wrap { max-width: 64rem; margin: 0 auto; padding: 2rem; }
                .card { background: #fff; border: 1px solid #e5e7eb; border-radius: .5rem; padding: 2rem; }
                .grid { display: grid; gap: 1rem; }
                @media (min-width: 640px) { .grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
                .item { border: 1px solid #d1d5db; border-radius: .375rem; padding: .75rem 1rem; }
                .muted { color: #4b5563; }
                .kbd { background: #111827; color: #fff; font-size: .75rem; border-radius: .25rem; padding: .1rem .35rem; }
                .pill { display: inline-block; font-size: .75rem; padding: .15rem .5rem; border-radius: 9999px; background: #eef2ff; color: #3730a3; }
                code { background: #f9fafb; padding: 0 .25rem; border-radius: .25rem; }
            </style>
        @endif
    </head>
    <body class="bg-gray-100 text-gray-900 min-h-screen">
        <main class="wrap mx-auto p-8">
            <section class="card bg-white border border-gray-200 rounded-lg p-8 shadow-sm">
                <div class="flex items-center justify-between gap-3" style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                    <h1 class="text-3xl font-semibold" style="margin:0;">NotifyHub</h1>
                    <span class="pill">Laravel notification control plane</span>
                </div>

                <p class="mt-3 text-gray-700">
                    A central intake and delivery service for Laravel alerts: ingest events from many apps,
                    inspect them in one timeline, and fan out push notifications to registered devices.
                </p>

                <div class="grid mt-6 gap-4 sm:grid-cols-2">
                    <a href="/login" class="item block rounded-md border border-gray-300 px-4 py-3 hover:bg-gray-50">
                        <div class="font-medium">Portal login</div>
                        <div class="text-sm text-gray-600 muted">Web UI for feed, event details, and settings</div>
                    </a>
                    <a href="/up" class="item block rounded-md border border-gray-300 px-4 py-3 hover:bg-gray-50">
                        <div class="font-medium">Service health</div>
                        <div class="text-sm text-gray-600 muted">GET /up readiness endpoint</div>
                    </a>
                </div>

                <h2 class="mt-8 text-xl font-semibold">How it works</h2>
                <ol class="mt-3 space-y-2 text-sm" style="padding-left: 1.25rem;">
                    <li>Your app sends event JSON to <code>POST /api/v1/events</code> with <code>X-Project-Key</code>.</li>
                    <li>NotifyHub validates and stores the event with project scoping.</li>
                    <li>Queued push jobs dispatch notifications based on severity and preferences.</li>
                    <li>Portal and mobile APIs expose role-scoped event data (with sensitive redaction).</li>
                </ol>

                <h2 class="mt-8 text-xl font-semibold">API quick reference</h2>
                <div class="grid mt-3 gap-4 sm:grid-cols-2">
                    <div class="item">
                        <div class="font-medium">Ingestion</div>
                        <div class="text-sm muted"><code>POST /api/v1/events</code></div>
                    </div>
                    <div class="item">
                        <div class="font-medium">Mobile auth</div>
                        <div class="text-sm muted"><code>POST /api/v1/mobile/login</code>, <code>DELETE /api/v1/mobile/logout</code></div>
                    </div>
                    <div class="item">
                        <div class="font-medium">Mobile feed</div>
                        <div class="text-sm muted"><code>GET /api/v1/mobile/feed</code>, <code>GET /api/v1/mobile/events/{public_id}</code></div>
                    </div>
                    <div class="item">
                        <div class="font-medium">User settings and devices</div>
                        <div class="text-sm muted"><code>GET|PUT /api/v1/mobile/settings</code>, <code>POST /api/v1/mobile/devices</code></div>
                    </div>
                </div>

                <h2 class="mt-8 text-xl font-semibold">Get started</h2>
                <p class="mt-3 text-sm text-gray-700">
                    Bootstrap the first project and owner account, then start sending events from your apps.
                    Full setup and contract details are in <code>docs/setup.md</code> and <code>docs/api-contract.md</code>.
                </p>
                <p class="mt-3 text-sm muted">
                    Tip: use <span class="kbd">php artisan notifyhub:setup</span> to create a project and ingest key in one step.
                </p>

                <p class="mt-8 text-xs text-gray-500">Laravel v{{ app()->version() }}</p>
            </section>
        </main>
    </body>
</html>
