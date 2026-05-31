<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    /**
     * Determine whether the user can see the event at all.
     */
    public function view(User $user, Event $event): bool
    {
        return $user->belongsToProject($event->project_id);
    }

    /**
     * Determine whether the user can inspect sensitive event diagnostics.
     */
    public function viewSensitive(User $user, Event $event): bool
    {
        return $user->canViewSensitiveProject($event->project_id);
    }
}
