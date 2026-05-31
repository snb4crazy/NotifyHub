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

    
}

