<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Jobs\SendEventPushJob;
use App\Models\Event;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EventIngestionController extends Controller
{
    /**
     * Accept an event from a sending application, store it, and acknowledge the request.
     *
     * The endpoint intentionally keeps the write path small and defers delivery to a queued job
     * so the API stays fast even when push providers are slow or unavailable.
     */
    public function store(StoreEventRequest $request): JsonResponse
    {
        /** @var Project $project */
        $project = $request->attributes->get('ingestProject');

        $payload = $request->validated();

        $event = Event::query()->create([
            'public_id' => (string) Str::uuid(),
            'project_id' => $project->id,
            'event_type' => $payload['event_type'] ?? 'generic',
            'title' => $this->sanitizeText($payload['title']),
            'message' => $this->sanitizeText($payload['message']),
            'severity' => $payload['severity'],
            'application' => isset($payload['application']) ? $this->sanitizeText($payload['application']) : null,
            'environment' => isset($payload['environment']) ? $this->sanitizeText($payload['environment']) : null,
            'context' => $payload['context'] ?? null,
            'sensitive_context' => $payload['sensitive_context'] ?? null,
            'occurred_at' => $payload['occurred_at'] ?? null,
            // TODO: Phase 0 stores fingerprint for correlation/indexed lookup only; repeated
            // requests with the same fingerprint are not deduplicated or treated idempotently here.
            'fingerprint' => $payload['fingerprint'] ?? null,
            'source_ip' => $request->ip(),
            'acknowledged_at' => now(),
        ]);

        if ($this->shouldDispatchPush($event)) {
            SendEventPushJob::dispatch($event);
        }

        return response()->json([
            'status' => 'accepted',
            'event_id' => $event->public_id,
        ], Response::HTTP_ACCEPTED);
    }

    /**
     * Normalize user-facing text before storing it.
     */
    protected function sanitizeText(string $value): string
    {
        return trim(strip_tags($value));
    }

    /**
     * Decide whether this event should fan out to mobile push notifications.
     *
     * The current MVP uses a simple severity threshold, while keeping the storage pipeline
     * independent from delivery. This makes it easy to run the platform as a personal inbox
     * or grow into a team workflow with stricter routing rules later.
     */
    protected function shouldDispatchPush(Event $event): bool
    {
        if (! config('notifyhub.push.enabled', true)) {
            return false;
        }

        $severityWeights = [
            'info' => 1,
            'warning' => 2,
            'error' => 3,
            'critical' => 4,
        ];

        $minimumSeverity = (string) config('notifyhub.push.minimum_severity', 'error');

        return ($severityWeights[$event->severity] ?? 0) >= ($severityWeights[$minimumSeverity] ?? 3);
    }
}

