<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotifyHubSetupOwnerCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifyhub_setup_can_create_owner_user_and_membership(): void
    {
        $this->artisan('notifyhub:setup', [
            '--name' => 'Personal Alerts',
            '--slug' => 'personal-alerts',
            '--ingest-key' => 'test_ingest_key_123',
            '--owner-name' => 'Serhii',
            '--owner-email' => 'owner@example.com',
            '--owner-password' => 'secret-pass',
        ])->assertExitCode(0);

        $project = Project::query()->where('slug', 'personal-alerts')->firstOrFail();
        $user = User::query()->where('email', 'owner@example.com')->firstOrFail();

        $this->assertTrue($project->users()->where('users.id', $user->id)->exists());
        $this->assertSame('owner', $project->users()->firstWhere('users.id', $user->id)?->pivot->role);
    }
}
