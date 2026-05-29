<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Event */
class MobileFeedEventResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->public_id,
            'project' => [
                'name' => $this->project->name,
                'slug' => $this->project->slug,
            ],
            'event_type' => $this->event_type,
            'severity' => $this->severity,
            'title' => $this->title,
            'message_preview' => mb_strimwidth($this->message, 0, 160, '…'),
            'application' => $this->application,
            'environment' => $this->environment,
            'has_sensitive_context' => ! empty($this->sensitive_context),
            'occurred_at' => $this->occurred_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

