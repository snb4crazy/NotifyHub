<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'timezone', 'notification_preferences'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'notification_preferences' => 'array',
            'password' => 'hashed',
        ];
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)
            ->withPivot(['role', 'can_view_sensitive'])
            ->withTimestamps();
    }

    public function devices(): HasMany
    {
        return $this->hasMany(UserDevice::class);
    }

    /**
     * Determine whether the user belongs to the given project.
     */
    public function belongsToProject(Project|int $project): bool
    {
        return $this->membershipForProject($project) !== null;
    }

    /**
     * Determine whether the user can inspect sensitive diagnostics for the project.
     */
    public function canViewSensitiveProject(Project|int $project): bool
    {
        $membership = $this->membershipForProject($project);

        if ($membership === null) {
            return false;
        }

        return (bool) $membership->can_view_sensitive
            || in_array((string) $membership->role, config('notifyhub.security.sensitive_roles', []), true);
    }

    /**
     * Fetch the pivot membership record for a project if it exists.
     */
    protected function membershipForProject(Project|int $project): ?object
    {
        $projectId = $project instanceof Project ? $project->id : $project;

        return $this->projects()
            ->where('projects.id', $projectId)
            ->first()?->pivot;
    }
}
