@extends('portal.layout')

@section('title', 'Notifications | NotifyHub Portal')

@section('content')
    <div class="card">
        <h1 style="margin-top: 0; margin-bottom: 6px;">Notifications</h1>
        <p class="muted" style="margin-top: 0;">Showing only events from projects you are a member of.</p>

        <form method="get" action="{{ route('portal.index') }}" class="grid cols-6" style="align-items: end;">
            <div>
                <label for="project_slug">Project</label>
                <select id="project_slug" name="project_slug">
                    <option value="">All projects</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->slug }}" @selected($filters['project_slug'] === $project->slug)>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="severity">Severity</label>
                <select id="severity" name="severity">
                    <option value="">All severities</option>
                    @foreach (['info', 'warning', 'error', 'critical'] as $severity)
                        <option value="{{ $severity }}" @selected($filters['severity'] === $severity)>{{ strtoupper($severity) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="event_type">Event type</label>
                <input id="event_type" type="text" name="event_type" value="{{ $filters['event_type'] }}" placeholder="laravel.exception">
            </div>

            <div>
                <label for="from">From date</label>
                <input id="from" type="date" name="from" value="{{ $filters['from'] }}">
            </div>

            <div>
                <label for="to">To date</label>
                <input id="to" type="date" name="to" value="{{ $filters['to'] }}">
            </div>

            <div>
                <label for="per_page">Rows</label>
                <select id="per_page" name="per_page">
                    @foreach ([10, 20, 50, 100] as $rows)
                        <option value="{{ $rows }}" @selected((int) $filters['per_page'] === $rows)>{{ $rows }}</option>
                    @endforeach
                </select>
            </div>

            <div style="grid-column: 1 / -1; display:flex; gap:10px;">
                <button type="submit" class="btn">Apply filters</button>
                <a href="{{ route('portal.index') }}" class="btn secondary">Reset</a>
                <span class="muted" style="align-self:center;">{{ $events->total() }} total</span>
            </div>
        </form>
    </div>

    @forelse ($eventsByDate as $date => $dayEvents)
        <div class="card">
            <h2 style="margin-top: 0; margin-bottom: 12px;">
                {{ $date === 'unknown' ? 'Unknown Date' : \Illuminate\Support\Carbon::parse($date)->translatedFormat('F j, Y') }}
            </h2>
            <table class="table">
                <thead>
                <tr>
                    <th>Time</th>
                    <th>Severity</th>
                    <th>Project</th>
                    <th>Type</th>
                    <th>Title</th>
                    <th>Message</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach ($dayEvents as $event)
                    <tr>
                        <td>
                            {{ ($event->occurred_at ?? $event->created_at)?->format('H:i:s') ?? '-' }}
                        </td>
                        <td>
                            <span class="badge severity-{{ $event->severity }}">{{ strtoupper($event->severity) }}</span>
                        </td>
                        <td>{{ $event->project->name }}</td>
                        <td><code>{{ $event->event_type }}</code></td>
                        <td>{{ $event->title }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($event->message, 90) }}</td>
                        <td>
                            <a class="btn" href="{{ route('portal.events.show', $event) }}">Open</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <div class="card">
            <p style="margin: 0;">No events found for this filter set.</p>
        </div>
    @endforelse

    <div class="card pagination">
        {{ $events->links() }}
    </div>
@endsection

