<?php

namespace App\Http\Controllers\Api\V1\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventDetailResource;
use App\Http\Resources\MobileFeedEventResource;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class FeedController extends Controller
{
    /**
     * Return a paginated mobile feed for the current user's projects.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $projectIds = $user->projects()->pluck('projects.id');
        $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);

        $query = Event::query()
            ->with('project')
            ->whereIn('project_id', $projectIds)
            ->latest('occurred_at')
            ->latest();

        if ($request->filled('project_slug')) {
            $query->whereHas('project', fn ($builder) => $builder->where('slug', (string) $request->string('project_slug')));
        }

        if ($request->filled('severity')) {
            $query->where('severity', (string) $request->string('severity'));
        }

        if ($request->filled('event_type')) {
            $query->where('event_type', (string) $request->string('event_type'));
        }

        return MobileFeedEventResource::collection($query->paginate($perPage));
    }

    /**
     * Return full event details with sensitive diagnostics redacted when needed.
     */
    public function show(Request $request, Event $event): EventDetailResource
    {
        Gate::authorize('view', $event);

        $request->attributes->set('can_view_sensitive', $request->user()->can('viewSensitive', $event));

        return new EventDetailResource($event->load('project'));
    }
}
