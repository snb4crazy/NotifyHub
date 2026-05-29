<?php

use App\Services\ProjectBootstrapService;
use App\Services\UserBootstrapService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('notifyhub:setup {--name= : Human-readable project name} {--slug= : Optional project slug} {--ingest-key= : Optional ingest key for repeatable setup} {--owner-name= : Optional owner name} {--owner-email= : Optional owner email for mobile login} {--owner-password= : Optional owner password for mobile login} {--force : Update an existing project with the same slug}', function (ProjectBootstrapService $service, UserBootstrapService $users) {
    try {
        $project = $service->bootstrap([
            'name' => $this->option('name'),
            'slug' => $this->option('slug'),
            'ingest_key' => $this->option('ingest-key'),
            'force' => (bool) $this->option('force'),
        ]);

        $owner = $users->bootstrapOwner($project, [
            'name' => $this->option('owner-name'),
            'email' => $this->option('owner-email'),
            'password' => $this->option('owner-password'),
        ]);
    } catch (\Throwable $throwable) {
        $this->error($throwable->getMessage());

        return 1;
    }

    $this->components->info('NotifyHub project is ready.');
    $this->line('Project: '.$project->name);
    $this->line('Slug: '.$project->slug);
    $this->line('Ingest key: '.$project->ingest_key);

    if ($owner) {
        $this->line('Owner email: '.$owner->email);
        $this->line('Owner role: owner');
    }

    $this->newLine();
    $this->line('Use the ingest key as the value of the X-Project-Key header in your apps.');

    return 0;
})->purpose('Bootstrap a NotifyHub project for MVP or team usage');

