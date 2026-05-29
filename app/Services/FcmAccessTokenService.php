<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FcmAccessTokenService
{
    /**
     * Obtain an OAuth access token for the Firebase Cloud Messaging HTTP v1 API.
     */
    public function getAccessToken(): string
    {
        return Cache::remember('notifyhub:fcm-access-token', now()->addMinutes(50), function (): string {
            $credentials = $this->credentials();
            $jwt = $this->buildJwt($credentials['client_email'], $credentials['private_key']);

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ])->throw();

            return (string) $response->json('access_token');
        });
    }

    /**
     * @return array{client_email: string, private_key: string, project_id: string}
     */
    public function credentials(): array
    {
        $path = trim((string) config('notifyhub.push.fcm.credentials_path', ''));

        if ($path !== '') {
            if (! is_file($path)) {
                throw new RuntimeException(sprintf('FCM credentials file [%s] was not found.', $path));
            }

            /** @var array<string, mixed>|null $json */
            $json = json_decode((string) file_get_contents($path), true);

            if (! is_array($json)) {
                throw new RuntimeException('FCM credentials file is not valid JSON.');
            }

            return [
                'client_email' => (string) Arr::get($json, 'client_email', ''),
                'private_key' => (string) Arr::get($json, 'private_key', ''),
                'project_id' => (string) Arr::get($json, 'project_id', ''),
            ];
        }

        return [
            'client_email' => trim((string) config('notifyhub.push.fcm.client_email', '')),
            'private_key' => str_replace('\\n', "\n", trim((string) config('notifyhub.push.fcm.private_key', ''))),
            'project_id' => trim((string) config('notifyhub.push.fcm.project_id', '')),
        ];
    }

    /**
     * Determine whether the service has enough information to contact FCM.
     */
    public function isConfigured(): bool
    {
        try {
            $credentials = $this->credentials();
        } catch (RuntimeException) {
            return false;
        }

        return $credentials['client_email'] !== ''
            && $credentials['private_key'] !== ''
            && $credentials['project_id'] !== '';
    }

    protected function buildJwt(string $clientEmail, string $privateKey): string
    {
        $issuedAt = time();
        $header = $this->base64UrlEncode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT',
        ], JSON_THROW_ON_ERROR));

        $payload = $this->base64UrlEncode(json_encode([
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $issuedAt,
            'exp' => $issuedAt + 3600,
        ], JSON_THROW_ON_ERROR));

        $signatureInput = $header.'.'.$payload;

        if (! openssl_sign($signatureInput, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Failed to sign FCM JWT with the configured private key.');
        }

        return $signatureInput.'.'.$this->base64UrlEncode($signature);
    }

    protected function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}

