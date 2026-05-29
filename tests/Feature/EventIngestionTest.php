<?php

namespace Tests\Feature;

use App\Jobs\SendEventPushJob;
use App\Models\Event;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EventIngestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_is_ingested_and_acknowledged(): void
    {
        Queue::fake();

        $project = Project::query()->create([
            'name' => 'Billing API',
            'slug' => 'billing-api',
            'ingest_key' => 'proj_ingest_test_key',
        ]);

        $response = $this->withHeaders([
            'X-Project-Key' => $project->ingest_key,
        ])->postJson('/api/v1/events', [
            'title' => '<b>Payment Failed</b>',
            'message' => 'Order #1234 <script>alert(1)</script>',
            'severity' => 'critical',
            'application' => '<i>billing-api</i>',
            'context' => ['order_id' => 1234],
            'sensitive_context' => ['trace' => ['frame1']],
            'fingerprint' => 'billing-api:payment:1234',
        ]);

        $response->assertAccepted();
        $response->assertJsonPath('status', 'accepted');
        $response->assertJsonStructure(['event_id']);

        $eventId = $response->json('event_id');

        /** @var Event $event */
        $event = Event::query()->where('public_id', $eventId)->firstOrFail();

        $this->assertSame('Payment Failed', $event->title);
        $this->assertSame('Order #1234 alert(1)', $event->message);
        $this->assertSame('billing-api', $event->application);
        $this->assertSame($project->id, $event->project_id);
        $this->assertNotNull($event->acknowledged_at);

        Queue::assertPushed(SendEventPushJob::class, function (SendEventPushJob $job) use ($event): bool {
            return $job->event->is($event);
        });
    }

    public function test_event_ingestion_requires_project_key(): void
    {
        $response = $this->postJson('/api/v1/events', [
            'title' => 'Payment Failed',
            'message' => 'Order #1234',
            'severity' => 'critical',
        ]);
        
        $response->assertUnauthorized();
        $response->assertJsonPath('message', 'Invalid or missing project ingest key.');
    }
}

