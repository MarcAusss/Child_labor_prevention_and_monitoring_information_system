<?php

use App\Http\Controllers\SystemSecurityController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
    ->prefix('security')
    ->name('security.')
    ->group(function (): void {
        Route::get(
            '/status',
            SystemSecurityController::class
        )->name('status');
    });
