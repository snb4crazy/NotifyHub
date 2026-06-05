@extends('portal.layout')

@section('title', 'Settings | NotifyHub Portal')

@section('content')
    <div class="card" style="max-width: 720px;">
        <h1 style="margin-top:0;">User Settings</h1>
        <p class="muted">These settings affect how your account receives and displays notifications.</p>

        @if (session('status'))
            <div class="status ok">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="status error">{{ $errors->first() }}</div>
        @endif

        <form method="post" action="{{ route('portal.settings.update') }}" class="grid cols-2">
            @csrf
            @method('PUT')

            <div>
                <label for="name">Name</label>
                <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required>
            </div>

            <div>
                <label for="email">Email</label>
                <input id="email" type="text" value="{{ $user->email }}" disabled>
            </div>

            <div>
                <label for="timezone">Timezone</label>
                <input id="timezone" type="text" name="timezone" value="{{ old('timezone', $user->timezone) }}" placeholder="Europe/Kyiv">
            </div>

            <div>
                <label for="minimum_severity">Minimum severity for push</label>
                <select id="minimum_severity" name="notification_preferences[minimum_severity]">
                    @php($selectedMinimumSeverity = old('notification_preferences.minimum_severity', $notificationPreferences['minimum_severity'] ?? 'error'))
                    @foreach (['info', 'warning', 'error', 'critical'] as $severity)
                        <option value="{{ $severity }}" @selected($selectedMinimumSeverity === $severity)>{{ strtoupper($severity) }}</option>
                    @endforeach
                </select>
            </div>

            <div style="grid-column: 1 / -1;">
                @php($pushEnabled = (bool) old('notification_preferences.push_enabled', $notificationPreferences['push_enabled'] ?? true))
                <label style="display:flex; gap:8px; align-items:center; margin: 0;">
                    <input type="hidden" name="notification_preferences[push_enabled]" value="0">
                    <input type="checkbox" name="notification_preferences[push_enabled]" value="1" @checked($pushEnabled) style="width:auto;">
                    Enable push notifications
                </label>
            </div>

            <div style="grid-column: 1 / -1; display:flex; gap:10px; margin-top:4px;">
                <button type="submit" class="btn">Save settings</button>
                <a href="{{ route('portal.index') }}" class="btn secondary">Back to notifications</a>
            </div>
        </form>
    </div>
@endsection

