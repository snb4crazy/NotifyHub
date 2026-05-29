<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class ProjectBootstrapService
{
    /**
     * Create or update the initial project used by a single-user MVP or a new team.
     *
     * @param  array{name?: string|null, slug?: string|null, ingest_key?: string|null, force?: bool}  $options
     */
    public function bootstrap(array $options = []): Project
    {
        if (! Schema::hasTable('projects')) {
            throw new RuntimeException('The database is not migrated yet. Run `php artisan migrate` first.');
        }

        $name = trim((string) ($options['name'] ?? config('notifyhub.defaults.project_name')));
        $slug = $this->resolveSlug($options['slug'] ?? config('notifyhub.defaults.project_slug'), $name);
        $ingestKey = trim((string) ($options['ingest_key'] ?? ''));
        $force = (bool) ($options['force'] ?? false);

        if ($ingestKey === '') {
            $ingestKey = (string) Str::ulid();
        }

        $existing = Project::query()->where('slug', $slug)->first();

        if ($existing && ! $force) {
            throw new RuntimeException(sprintf('Project slug [%s] already exists. Use --force to update it.', $slug));
        }

        if ($existing) {
            $existing->fill([
                'name' => $name,
                'ingest_key' => $ingestKey,
            ])->save();

            return $existing->refresh();
        }

        return Project::query()->create([
            'name' => $name,
            'slug' => $slug,
            'ingest_key' => $ingestKey,
        ]);
    }

    /**
     * Normalize a project slug from either the configured value or the project name.
     */
    protected function resolveSlug(?string $configuredSlug, string $name): string
    {
        $slug = trim((string) $configuredSlug);

        if ($slug !== '') {
            return Str::slug($slug);
        }

        return Str::slug($name);
    }
}


