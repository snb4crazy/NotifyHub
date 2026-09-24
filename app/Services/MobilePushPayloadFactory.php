<?php

namespace App\Services;

use App\Models\Event;

class MobilePushPayloadFactory
{
    /**
     * Build a platform-neutral notification payload from an event.
     *
     * @return array<string, array<string, string>|string>
     */
    public function make(Event $event): array
    {
        return [
            'notification' => [
                'title' => sprintf('[%s] %s', strtoupper($event->severity), $event->title),
                'body' => mb_strimwidth($event->message, 0, 120, '…'),
            ],
            'data' => [
                'event_id' => $event->public_id,
                'project_slug' => $event->project->slug,
                'severity' => $event->severity,
                'event_type' => $event->event_type,
                'application' => (string) ($event->application ?? ''),
                'environment' => (string) ($event->environment ?? ''),
            ],
        ];
    }
}
