<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class UserBootstrapService
{
    /**
     * Create or update the initial owner account and attach it to the project.
     *
     * @param  array{name?: string|null, email?: string|null, password?: string|null}  $options
     */
    public function bootstrapOwner(Project $project, array $options = []): ?User
    {
        if (! Schema::hasTable('users')) {
            throw new RuntimeException('The users table is not migrated yet. Run `php artisan migrate` first.');
        }

        $email = trim((string) ($options['email'] ?? ''));
        $password = trim((string) ($options['password'] ?? ''));

        if ($email === '' || $password === '') {
            return null;
        }

        $name = trim((string) ($options['name'] ?? ''));

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name !== '' ? $name : 'NotifyHub Owner',
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'notification_preferences' => [
                    'push_enabled' => true,
                    'minimum_severity' => config('notifyhub.push.minimum_severity', 'error'),
                ],
            ],
        );

        $project->users()->syncWithoutDetaching([
            $user->id => [
                'role' => 'owner',
                'can_view_sensitive' => true,
            ],
        ]);

        return $user->refresh();
    }
}

