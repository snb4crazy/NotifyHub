<?php

namespace App\Http\Resources;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Event */
class EventDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $canViewSensitive = (bool) $request->attributes->get('can_view_sensitive', false);

        return [
            'id' => $this->public_id,
            'project' => [
                'name' => $this->project->name,
                'slug' => $this->project->slug,
            ],
            'event_type' => $this->event_type,
            'severity' => $this->severity,
            'title' => $this->title,
            'message' => $this->message,
            'application' => $this->application,
            'environment' => $this->environment,
            'fingerprint' => $this->fingerprint,
            'source_ip' => $this->source_ip,
            'context' => $this->context ?? [],
            'can_view_sensitive' => $canViewSensitive,
            'sensitive_context' => $canViewSensitive ? ($this->sensitive_context ?? []) : null,
            'occurred_at' => $this->occurred_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
