<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_for_portal_pages(): void
    {
        $this->get('/portal')
            ->assertRedirect('/login');

        $this->get('/portal/settings')
            ->assertRedirect('/login');
    }

    public function test_user_only_sees_events_from_their_projects(): void
    {
        $user = User::factory()->create();
        $ownProject = Project::query()->create([
            'name' => 'Own Project',
            'slug' => 'own-project',
            'ingest_key' => 'ingest_own_project_key',
        ]);
        $otherProject = Project::query()->create([
            'name' => 'Other Project',
            'slug' => 'other-project',
            'ingest_key' => 'ingest_other_project_key',
        ]);
        
        $ownProject->users()->attach($user->id, [
            'role' => 'viewer',
            'can_view_sensitive' => false,
        ]);
        
        Event::query()->create([
            'public_id' => 'bfb52f8f-7f3c-4299-9e42-88506c9271a7',
            'project_id' => $ownProject->id,
            'event_type' => 'laravel.exception',
            'title' => 'Own exception',
            'message' => 'This must be visible',
            'severity' => 'error',
            'occurred_at' => now(),
        ]);
        
        Event::query()->create([
            'public_id' => '06a8e620-f728-4d2f-b8d7-4d83bcfbd42c',
            'project_id' => $otherProject->id,
            'event_type' => 'laravel.exception',
            'title' => 'Other exception',
            'message' => 'This must stay hidden',
            'severity' => 'critical',
            'occurred_at' => now(),
        ]);
        
        $this->actingAs($user)
            ->get('/portal')
            ->assertOk()
            ->assertSee('Own exception')
            ->assertDontSee('Other exception');
    }

    public function test_user_cannot_open_event_details_outside_their_projects(): void
    {
        $user = User::factory()->create();
        $owner = User::factory()->create();
        $privateProject = Project::query()->create([
            'name' => 'Private',
            'slug' => 'private',
            'ingest_key' => 'ingest_private_key',
        ]);
        
        $privateProject->users()->attach($owner->id, [
            'role' => 'owner',
            'can_view_sensitive' => true,
        ]);
        
        $event = Event::query()->create([
            'public_id' => '1867ed18-4f52-4ad6-a96f-45f52169e174',
            'project_id' => $privateProject->id,
            'event_type' => 'laravel.exception',
            'title' => 'Restricted',
            'message' => 'No access expected',
            'severity' => 'error',
            'occurred_at' => now(),
        ]);
        
        $this->actingAs($user)
            ->get('/portal/events/'.$event->public_id)
            ->assertForbidden();
    }

    public function test_user_can_update_portal_settings(): void
    {
        $user = User::factory()->create([
            'notification_preferences' => [
                'push_enabled' => true,
                'minimum_severity' => 'error',
            ],
        ]);
        
        $this->actingAs($user)
            ->put('/portal/settings', [
                'name' => 'Portal User',
                'timezone' => 'Europe/Kyiv',
                'notification_preferences' => [
                    'push_enabled' => false,
                    'minimum_severity' => 'warning',
                ],
            ])
            ->assertRedirect();
        
        $user->refresh();
        
        $this->assertSame('Portal User', $user->name);
        $this->assertSame('Europe/Kyiv', $user->timezone);
        $this->assertSame('warning', $user->notification_preferences['minimum_severity']);
        $this->assertFalse($user->notification_preferences['push_enabled']);
    }
}

