<?php

namespace App\Contracts;

use App\Models\Event;
use App\Models\Project;

interface PushGateway
{
    public function sendToProjectUsers(Project $project, Event $event): void;
}
