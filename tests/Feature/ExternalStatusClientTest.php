<?php

namespace Tests\Feature;

use App\Services\ExternalStatusClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class ExternalStatusClientTest extends TestCase
{
    public function test_it_fetches_status_from_the_demo_api(): void
    {
        config()->set('services.demo_api.base_url', 'https://api.example.test');

        Http::fake([
            'https://api.example.test/*' => Http::response([
                'status' => 'ok',
                'uptime' => 123,
            ], 200),
        ]);

        $client = new ExternalStatusClient;

        $this->assertSame([
            'status' => 'ok',
            'uptime' => 123,
        ], $client->fetchStatus());

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'GET'
                && $request->url() === 'https://api.example.test/status'
                && $request->hasHeader('Accept', 'application/json');
        });
    }

    public function test_it_raises_a_clear_error_when_the_demo_api_is_unavailable(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Demo API is unavailable (HTTP 503).');

        config()->set('services.demo_api.base_url', 'https://api.example.test');

        Http::fake([
            'https://api.example.test/*' => Http::response([
                'message' => 'Service Unavailable',
            ], 503),
        ]);

        $client = new ExternalStatusClient;

        $client->fetchStatus();
    }
}
