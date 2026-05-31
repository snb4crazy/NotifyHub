@extends('portal.layout')

@section('title', 'Event Details | NotifyHub Portal')

@section('content')
    <div class="card">
        <a href="{{ route('portal.index', request()->query()) }}" class="muted">&larr; Back to notifications</a>
        <h1 style="margin-bottom: 8px;">{{ $event->title }}</h1>
        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom: 12px;">
            <span class="badge severity-{{ $event->severity }}">{{ strtoupper($event->severity) }}</span>
            <span class="muted">{{ $event->project->name }} ({{ $event->project->slug }})</span>
            <span class="muted">{{ $event->event_type }}</span>
            <span class="muted">{{ $event->occurred_at?->toDayDateTimeString() ?? 'Unknown time' }}</span>
        </div>

        <div class="grid cols-2">
            <div>
                <label>Message</label>
                <pre>{{ $event->message }}</pre>
            </div>
            <div>
                <label>Metadata</label>
                <pre>{
  "application": {{ json_encode($event->application) }},
  "environment": {{ json_encode($event->environment) }},
  "fingerprint": {{ json_encode($event->fingerprint) }},
  "source_ip": {{ json_encode($event->source_ip) }}
}</pre>
            </div>
        </div>
    </div>

    <div class="card">
        <h2 style="margin-top:0;">Context</h2>
        <pre>{{ json_encode($event->context ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
    </div>

    <div class="card">
        <h2 style="margin-top:0;">Sensitive Context</h2>
        @if ($canViewSensitive)
            <pre>{{ json_encode($event->sensitive_context ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        @else
            <p class="muted" style="margin:0;">You do not have permission to view sensitive diagnostics for this project.</p>
        @endif
    </div>
@endsection

