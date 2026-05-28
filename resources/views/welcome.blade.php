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
                .wrap { max-width: 56rem; margin: 0 auto; padding: 2rem; }
                .card { background: #fff; border: 1px solid #e5e7eb; border-radius: .5rem; padding: 2rem; }
                .grid { display: grid; gap: 1rem; }
                @media (min-width: 640px) { .grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
                .item { border: 1px solid #d1d5db; border-radius: .375rem; padding: .75rem 1rem; }
                .muted { color: #4b5563; }
                code { background: #f9fafb; padding: 0 .25rem; border-radius: .25rem; }
            </style>
        @endif
    </head>
    <body class="bg-gray-100 text-gray-900 min-h-screen">
        <main class="wrap max-w-4xl mx-auto p-8">
            <section class="card bg-white border border-gray-200 rounded-lg p-8 shadow-sm">
                <h1 class="text-3xl font-semibold">NotifyHub</h1>
                <p class="mt-3 text-gray-700">
                    Central notification server for your Laravel apps. Send events to one endpoint,
                    store them, and route alerts to mobile push clients.
                </p>

                <div class="grid mt-6 gap-4 sm:grid-cols-2">
                    <a href="/up" class="item block rounded-md border border-gray-300 px-4 py-3 hover:bg-gray-50">
                        <div class="font-medium">Health Check</div>
                        <div class="text-sm text-gray-600 muted">GET /up</div>
                    </a>
                    <div class="item rounded-md border border-gray-300 px-4 py-3">
                        <div class="font-medium">Ingestion Endpoint</div>
                        <div class="text-sm text-gray-600 muted">POST /api/v1/events</div>
                    </div>
                </div>

                <h2 class="mt-8 text-xl font-semibold">Key docs</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    <li><a class="underline" href="https://laravel.com/docs">Laravel docs</a></li>
                    <li><span class="font-medium">Project docs:</span> <code>docs/setup.md</code>, <code>docs/api-contract.md</code>, <code>docs/roadmap.md</code></li>
                    <li><span class="font-medium">Integration planning:</span> <code>docs/sender-helper-and-ack-grouping.md</code></li>
                </ul>

                <p class="mt-8 text-xs text-gray-500">Laravel v{{ app()->version() }}</p>
            </section>
        </main>
    </body>
</html>
