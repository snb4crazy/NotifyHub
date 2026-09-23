<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_factory_persists_to_the_database(): void
    {
        $user = User::factory()->create([
            'name' => 'CI Test User',
            'email' => 'ci-test-user@example.com',
            'password' => 'secret-password',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'CI Test User',
            'email' => 'ci-test-user@example.com',
        ]);

        $this->assertTrue(Hash::check('secret-password', $user->password));
    }
}
