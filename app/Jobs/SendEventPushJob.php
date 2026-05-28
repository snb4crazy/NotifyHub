<?php

namespace App\Jobs;

use App\Contracts\PushGateway;
use App\Models\Event;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendEventPushJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new queue job for a stored event.
     */
    public function __construct(public Event $event)
    {
    }

    /**
     * Deliver the stored event through the configured push gateway.
     */
    public function handle(PushGateway $pushGateway): void
    {
        $pushGateway->sendToProjectUsers($this->event->project, $this->event);
    }
}

