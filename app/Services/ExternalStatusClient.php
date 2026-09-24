<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class ExternalStatusClient
{
    /**
     * Fetch the status payload from the demo external API.
     *
     * @return array<string, mixed>
     */
    public function fetchStatus(): array
    {
        $response = Http::baseUrl((string) config('services.demo_api.base_url', 'https://api.example.test'))
            ->acceptJson()
            ->timeout(5)
            ->get('/status');

        if ($response->successful()) {
$payload = $response->json();

            return is_array($payload) ? $payload : [];
        }

        throw new RuntimeException(sprintf(
            'Demo API is unavailable (HTTP %d).',
            $response->status()
        ));
    }
}
