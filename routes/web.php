<?php

use App\Http\Controllers\Web\PortalAuthController;
use App\Http\Controllers\Web\PortalController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [PortalAuthController::class, 'create'])->name('login');
    Route::post('/login', [PortalAuthController::class, 'store'])->name('portal.login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::redirect('/', '/portal');

    Route::get('/portal', [PortalController::class, 'index'])->name('portal.index');
    Route::get('/portal/events/{event}', [PortalController::class, 'show'])->name('portal.events.show');
    Route::get('/portal/settings', [PortalController::class, 'settings'])->name('portal.settings');
    Route::put('/portal/settings', [PortalController::class, 'updateSettings'])->name('portal.settings.update');

    Route::post('/logout', [PortalAuthController::class, 'destroy'])->name('logout');
});
