<?php

use App\Http\Controllers\Api\V1\EventIngestionController;
use App\Http\Controllers\Api\V1\Mobile\AuthController;
use App\Http\Controllers\Api\V1\Mobile\DeviceController;
use App\Http\Controllers\Api\V1\Mobile\FeedController;
use App\Http\Controllers\Api\V1\Mobile\SettingsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::middleware('project.ingest')->group(function (): void {
        Route::post('/events', [EventIngestionController::class, 'store']);
    });

    Route::prefix('mobile')->group(function (): void {
        Route::post('/login', [AuthController::class, 'store']);

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::delete('/logout', [AuthController::class, 'destroy']);
            Route::get('/feed', [FeedController::class, 'index']);
            Route::get('/events/{event}', [FeedController::class, 'show']);
            Route::get('/settings', [SettingsController::class, 'show']);
            Route::put('/settings', [SettingsController::class, 'update']);
            Route::post('/devices', [DeviceController::class, 'store']);
        });
    });
});
