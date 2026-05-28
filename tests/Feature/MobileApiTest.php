<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_user_can_login_and_view_feed_and_details(): void
    {
        $user = User::factory()->create([
            'password' => 'password',
            'notification_preferences' => [
                'push_enabled' => true,
                'minimum_severity' => 'error',
            ],
        ]);

        $project = Project::query()->create([
            'name' => 'Billing API',
            'slug' => 'billing-api',
            'ingest_key' => 'billing_ingest_key',
        ]);

        $project->users()->attach($user->id, [
            'role' => 'triager',
            'can_view_sensitive' => true,
        ]);

        $event = Event::query()->create([
            'public_id' => 'bdf9692f-f89e-47d0-8161-db6f78224d3c',
            'project_id' => $project->id,
            'event_type' => 'laravel.exception',
            'title' => 'Unhandled exception',
            'message' => 'SQLSTATE[HY000] disk full',
            'severity' => 'critical',
            'application' => 'billing-api',
            'environment' => 'production',
            'context' => ['job' => 'nightly-report'],
            'sensitive_context' => ['trace' => ['frame1']],
            'fingerprint' => 'billing-api:disk-full',
            'occurred_at' => now(),
            'acknowledged_at' => now(),
        ]);

        $login = $this->postJson('/api/v1/mobile/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'iPhone',
        ]);

        $login->assertOk();
        $token = $login->json('token');

        $feed = $this->withToken($token)->getJson('/api/v1/mobile/feed');
        $feed->assertOk();
        $feed->assertJsonPath('data.0.id', $event->public_id);
        $feed->assertJsonPath('data.0.event_type', 'laravel.exception');
        $feed->assertJsonPath('data.0.environment', 'production');

        $details = $this->withToken($token)->getJson('/api/v1/mobile/events/'.$event->public_id);
        $details->assertOk();
        $details->assertJsonPath('data.id', $event->public_id);
        $details->assertJsonPath('data.can_view_sensitive', true);
        $details->assertJsonPath('data.sensitive_context.trace.0', 'frame1');
    }

    public function test_viewer_receives_redacted_sensitive_context(): void
    {
        $user = User::factory()->create([
            'password' => 'password',
        ]);
        
        $project = Project::query()->create([
            'name' => 'Operations',
            'slug' => 'operations',
            'ingest_key' => 'ops_ingest_key',
        ]);
        
        $project->users()->attach($user->id, [
            'role' => 'viewer',
            'can_view_sensitive' => false,
        ]);
        
        $event = Event::query()->create([
            'public_id' => '81994339-601c-4e01-8580-cf5193a38fc6',
            'project_id' => $project->id,
            'event_type' => 'laravel.exception',
            'title' => 'Cron failed',
            'message' => 'Daily sync crashed',
            'severity' => 'error',
            'sensitive_context' => ['trace' => ['frame1']],
            'acknowledged_at' => now(),
        ]);
        
        $token = $user->createToken('viewer')->plainTextToken;
        
        $details = $this->withToken($token)->getJson('/api/v1/mobile/events/'.$event->public_id);
        $details->assertOk();
        $details->assertJsonPath('data.can_view_sensitive', false);
        $details->assertJsonPath('data.sensitive_context', null);
    }
    
    
}

