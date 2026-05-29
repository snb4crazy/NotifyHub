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
    public function store(StoreEventRequest $request): JsonResponse
    {
        /** @var Project $project */
        $project = $request->attributes->get('ingestProject');

        $payload = $request->validated();

        $event = Event::query()->create([
            'public_id' => (string) Str::uuid(),
            'project_id' => $project->id,
            'title' => $this->sanitizeText($payload['title']),
            'message' => $this->sanitizeText($payload['message']),
            'severity' => $payload['severity'],
            'application' => isset($payload['application']) ? $this->sanitizeText($payload['application']) : null,
            'context' => $payload['context'] ?? null,
            'sensitive_context' => $payload['sensitive_context'] ?? null,
            'occurred_at' => $payload['occurred_at'] ?? null,
            // TODO: Phase 0 stores fingerprint for correlation/indexed lookup only; repeated
            // requests with the same fingerprint are not deduplicated or treated idempotently here.
            'fingerprint' => $payload['fingerprint'] ?? null,
            'source_ip' => $request->ip(),
            'acknowledged_at' => now(),
        ]);

        SendEventPushJob::dispatch($event);

        return response()->json([
            'status' => 'accepted',
            'event_id' => $event->public_id,
        ], Response::HTTP_ACCEPTED);
    }

    protected function sanitizeText(string $value): string
    {
        return trim(strip_tags($value));
    }
}

