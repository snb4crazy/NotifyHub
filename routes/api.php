<?php

use App\Http\Controllers\Api\V1\EventIngestionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::middleware('project.ingest')->group(function (): void {
        Route::post('/events', [EventIngestionController::class, 'store']);
    });
});

