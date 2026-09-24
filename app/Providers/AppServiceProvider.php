<?php

namespace App\Providers;

use App\Contracts\PushGateway;
use App\Models\Event;
use App\Policies\EventPolicy;
use App\Services\FcmPushGateway;
use App\Services\LoggingPushGateway;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PushGateway::class, function ($app) {
            return match (config('notifyhub.push.driver', 'log')) {
                'fcm' => $app->make(FcmPushGateway::class),
                default => $app->make(LoggingPushGateway::class),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Event::class, EventPolicy::class);
    }
}
