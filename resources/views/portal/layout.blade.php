<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'NotifyHub Portal')</title>
    <style>
        :root {
            color-scheme: light;
            font-family: Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        body {
            margin: 0;
            background: #f5f7fb;
            color: #1f2937;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            padding: 16px;
            margin-bottom: 16px;
        }

        .topbar {
            background: #0f172a;
            color: #f8fafc;
            padding: 12px 0;
            margin-bottom: 20px;
        }

        .topbar .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding-top: 0;
            padding-bottom: 0;
        }

        .topbar a {
            color: #cbd5e1;
            text-decoration: none;
            margin-right: 14px;
            font-size: 14px;
        }

        .topbar a:hover,
        .topbar a.active {
            color: #fff;
        }

        .btn {
            border: 0;
            border-radius: 8px;
            background: #2563eb;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            padding: 8px 12px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn.secondary {
            background: #1f2937;
        }

        .btn.link {
            background: transparent;
            color: #cbd5e1;
            padding: 0;
            font-weight: 500;
        }

        .btn.link:hover {
            color: #fff;
        }

        input, select, textarea {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 8px 10px;
            font-size: 14px;
            box-sizing: border-box;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            color: #4b5563;
        }

        .grid {
            display: grid;
            gap: 12px;
        }

        .grid.cols-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .grid.cols-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .grid.cols-6 {
            grid-template-columns: repeat(6, minmax(0, 1fr));
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            padding: 10px 8px;
            vertical-align: top;
            font-size: 14px;
        }

        .table th {
            font-size: 12px;
            text-transform: uppercase;
            color: #6b7280;
            letter-spacing: 0.04em;
        }

        .badge {
            display: inline-block;
            border-radius: 999px;
            padding: 2px 8px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .severity-info { background: #e0f2fe; color: #075985; }
        .severity-warning { background: #fef3c7; color: #92400e; }
        .severity-error { background: #fee2e2; color: #991b1b; }
        .severity-critical { background: #fce7f3; color: #9d174d; }

        .muted {
            color: #6b7280;
            font-size: 13px;
        }

        .status {
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 14px;
            margin-bottom: 12px;
        }

        .status.ok {
            background: #dcfce7;
            color: #166534;
        }

        .status.error {
            background: #fee2e2;
            color: #991b1b;
        }

        pre {
            background: #0f172a;
            color: #e2e8f0;
            border-radius: 10px;
            padding: 12px;
            overflow: auto;
            font-size: 13px;
        }

        .pagination {
            margin-top: 14px;
        }

        @media (max-width: 900px) {
            .grid.cols-6,
            .grid.cols-3,
            .grid.cols-2 {
                grid-template-columns: 1fr;
            }

            .table th:nth-child(5),
            .table td:nth-child(5),
            .table th:nth-child(6),
            .table td:nth-child(6) {
                display: none;
            }
        }
    </style>
</head>
<body>
@auth
    <div class="topbar">
        <div class="container">
            <div>
                <strong>NotifyHub Portal</strong>
                <span class="muted" style="color: #94a3b8; margin-left: 8px;">{{ auth()->user()->email }}</span>
            </div>
            <div>
                <a href="{{ route('portal.index') }}" class="{{ request()->routeIs('portal.index') ? 'active' : '' }}">Notifications</a>
                <a href="{{ route('portal.settings') }}" class="{{ request()->routeIs('portal.settings') ? 'active' : '' }}">Settings</a>
                <form method="post" action="{{ route('logout') }}" style="display:inline">
                    @csrf
                    <button type="submit" class="btn link">Logout</button>
                </form>
            </div>
        </div>
    </div>
@endauth

<div class="container">
    @yield('content')
</div>
</body>
</html>

