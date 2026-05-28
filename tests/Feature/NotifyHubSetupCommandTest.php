<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotifyHubSetupCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifyhub_setup_creates_a_project(): void
    {
        $this->artisan('notifyhub:setup', [
            '--name' => 'Personal Alerts',
            '--slug' => 'personal-alerts',
            '--ingest-key' => 'test_ingest_key_123',
        ])->assertExitCode(0);

        $this->assertDatabaseHas('projects', [
            'name' => 'Personal Alerts',
            'slug' => 'personal-alerts',
            'ingest_key' => 'test_ingest_key_123',
        ]);

        $project = Project::query()->where('slug', 'personal-alerts')->firstOrFail();
        $this->assertSame('Personal Alerts', $project->name);
    }
}

