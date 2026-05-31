<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePortalSettingsRequest;
use App\Models\Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->loadMissing('projects');
        $projectIds = $user->projects->pluck('id');
        $perPage = min(max((int) $request->integer('per_page', 20), 10), 100);

        $query = Event::query()
            ->with('project')
            ->whereIn('project_id', $projectIds)
            ->orderByDesc('occurred_at')
            ->latest();

        if ($request->filled('project_slug')) {
            $query->whereHas('project', fn (Builder $builder) => $builder->where('slug', (string) $request->string('project_slug')));
        }

        if ($request->filled('severity')) {
            $query->where('severity', (string) $request->string('severity'));
        }

        if ($request->filled('event_type')) {
            $query->where('event_type', (string) $request->string('event_type'));
        }

        if ($request->filled('from')) {
            try {
                $from = Carbon::createFromFormat('Y-m-d', (string) $request->string('from'))->startOfDay();
                $query->where('occurred_at', '>=', $from);
            } catch (\Throwable) {
                // Ignore invalid date filters and keep the feed usable.
            }
        }

        if ($request->filled('to')) {
            try {
                $to = Carbon::createFromFormat('Y-m-d', (string) $request->string('to'))->endOfDay();
                $query->where('occurred_at', '<=', $to);
            } catch (\Throwable) {
                // Ignore invalid date filters and keep the feed usable.
            }
        }

        $events = $query->paginate($perPage)->withQueryString();

        return view('portal.index', [
            'projects' => $user->projects->sortBy('name')->values(),
            'events' => $events,
            'eventsByDate' => $this->groupEventsByDate($events),
            'filters' => [
                'project_slug' => (string) $request->string('project_slug'),
                'severity' => (string) $request->string('severity'),
                'event_type' => (string) $request->string('event_type'),
                'from' => (string) $request->string('from'),
                'to' => (string) $request->string('to'),
                'per_page' => $perPage,
            ],
        ]);
    }

    public function show(Request $request, Event $event): View
    {
        Gate::authorize('view', $event);

        return view('portal.event', [
            'event' => $event->load('project'),
            'canViewSensitive' => $request->user()->can('viewSensitive', $event),
        ]);
    }

    public function settings(Request $request): View
    {
        return view('portal.settings', [
            'user' => $request->user(),
            'notificationPreferences' => $request->user()->notification_preferences ?? [
                'push_enabled' => true,
                'minimum_severity' => config('notifyhub.push.minimum_severity', 'error'),
            ],
        ]);
    }

    public function updateSettings(UpdatePortalSettingsRequest $request): RedirectResponse
    {
        $user = $request->user();
        $payload = $request->validated();

        $user->name = $payload['name'];
        $user->timezone = $payload['timezone'] ?? null;

        if (array_key_exists('notification_preferences', $payload)) {
            $user->notification_preferences = array_merge(
                $user->notification_preferences ?? [],
                $payload['notification_preferences'] ?? [],
            );
        }

        $user->save();

        return back()->with('status', 'Settings updated.');
    }

    /**
     * @return array<string, \Illuminate\Support\Collection<int, Event>>
     */
    protected function groupEventsByDate(LengthAwarePaginator $events): array
    {
        return $events->getCollection()
            ->groupBy(function (Event $event): string {
                $date = $event->occurred_at ?? $event->created_at;

                return $date ? $date->toDateString() : 'unknown';
            })
            ->all();
    }
}
