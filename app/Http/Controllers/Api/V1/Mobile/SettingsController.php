<?php

namespace App\Http\Controllers\Api\V1\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserSettingsRequest;
use App\Http\Resources\UserSettingsResource;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Return the current user's mobile settings, memberships, and device state.
     */
    public function show(Request $request): UserSettingsResource
    {
        return new UserSettingsResource($request->user()->load(['projects', 'devices']));
    }

    /**
     * Update lightweight user settings for the mobile client.
     */
    public function update(UpdateUserSettingsRequest $request): UserSettingsResource
    {
        $user = $request->user();
        $payload = $request->validated();

        if (array_key_exists('timezone', $payload)) {
            $user->timezone = $payload['timezone'];
        }

        if (array_key_exists('notification_preferences', $payload)) {
            $user->notification_preferences = array_merge(
                $user->notification_preferences ?? [],
                $payload['notification_preferences'],
            );
        }

        $user->save();

        return new UserSettingsResource($user->load(['projects', 'devices']));
    }
}

