<?php

use App\Http\Controllers\WorkspaceDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
    ->group(function (): void {
        Route::get(
            '/workspace',
            WorkspaceDashboardController::class
        )->name('workspace.dashboard');
    });
