<?php

namespace App\Jobs;

use App\Contracts\PushGateway;
use App\Models\Event;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendEventPushJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public Event $event)
    {
    }

    public function handle(PushGateway $pushGateway): void
    {
        $pushGateway->sendToProjectUsers($this->event->project, $this->event);
    }
}

