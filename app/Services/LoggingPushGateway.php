<?php

namespace App\Services;

use App\Contracts\PushGateway;
use App\Models\Event;
use App\Models\Project;
use Illuminate\Support\Facades\Log;

class LoggingPushGateway implements PushGateway
{
    public function __construct(protected MobilePushPayloadFactory $payloadFactory)
    {
    }

    public function sendToProjectUsers(Project $project, Event $event): void
    {
        $payload = $this->payloadFactory->make($event);

        Log::info('Push dispatch queued (logging adapter).', [
            'project_id' => $project->id,
            'project_slug' => $project->slug,
            'event_id' => $event->public_id,
            'severity' => $event->severity,
            'title' => $event->title,
            'payload' => $payload,
        ]);
    }
}

