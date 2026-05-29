<?php

namespace App\Services;

use App\Contracts\PushGateway;
use App\Models\Event;
use App\Models\Project;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmPushGateway implements PushGateway
{
    public function __construct(
        protected FcmAccessTokenService $accessTokenService,
        protected MobilePushPayloadFactory $payloadFactory,
    ) {}

    /**
     * Send a push notification to all enabled devices in the project.
     */
    public function sendToProjectUsers(Project $project, Event $event): void
    {
        if (! $this->accessTokenService->isConfigured()) {
            Log::warning('FCM push adapter is selected, but credentials are incomplete.', [
                'project_id' => $project->id,
                'event_id' => $event->public_id,
            ]);

            return;
        }

        $credentials = $this->accessTokenService->credentials();
        $accessToken = $this->accessTokenService->getAccessToken();
        $payload = $this->payloadFactory->make($event);

        $devices = $project->users()
            ->with(['devices' => fn ($query) => $query->where('notifications_enabled', true)])
            ->get()
            ->flatMap->devices
            ->unique('fcm_token')
            ->values();

        foreach ($devices as $device) {
            Http::withToken($accessToken)
                ->post(sprintf('https://fcm.googleapis.com/v1/projects/%s/messages:send', $credentials['project_id']), [
                    'message' => [
                        'token' => $device->fcm_token,
                        'notification' => $payload['notification'],
                        'data' => $payload['data'],
                    ],
                ])
                ->throw();
        }
    }
}
