<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserSettingsResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user' => [
                'name' => $this->name,
                'email' => $this->email,
                'timezone' => $this->timezone,
            ],
            'notification_preferences' => $this->notification_preferences ?? [
                'push_enabled' => true,
                'minimum_severity' => config('notifyhub.push.minimum_severity', 'error'),
            ],
            'projects' => $this->projects->map(fn ($project) => [
                'name' => $project->name,
                'slug' => $project->slug,
                'role' => $project->pivot->role,
                'can_view_sensitive' => (bool) $project->pivot->can_view_sensitive,
            ])->values(),
            'devices' => $this->devices->map(fn ($device) => [
                'id' => $device->id,
                'name' => $device->name,
                'platform' => $device->platform,
                'notifications_enabled' => $device->notifications_enabled,
                'last_seen_at' => $device->last_seen_at?->toIso8601String(),
            ])->values(),
        ];
    }
}
