<?php

namespace App\Http\Controllers\Api\V1\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserDeviceRequest;
use App\Models\UserDevice;
use Illuminate\Http\JsonResponse;

class DeviceController extends Controller
{
    /**
     * Register or update an FCM device token for the current user.
     */
    public function store(StoreUserDeviceRequest $request): JsonResponse
    {
        $user = $request->user();
        $payload = $request->validated();

        $device = UserDevice::query()->updateOrCreate(
            ['fcm_token' => $payload['fcm_token']],
            [
                'user_id' => $user->id,
                'name' => $payload['name'] ?? null,
                'platform' => $payload['platform'] ?? 'unknown',
                'notifications_enabled' => $payload['notifications_enabled'] ?? true,
                'last_seen_at' => now(),
            ],
        );

        return response()->json([
            'status' => 'registered',
            'device_id' => $device->id,
        ]);
    }
}


