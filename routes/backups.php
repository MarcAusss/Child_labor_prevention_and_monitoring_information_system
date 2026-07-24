<?php

use App\Http\Controllers\BackupController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
    ->prefix('backups')
    ->name('backups.')
    ->group(function (): void {
        Route::get(
            '/',
            [
                BackupController::class,
                'index',
            ]
        )->name('index');

        Route::post(
            '/',
            [
                BackupController::class,
                'store',
            ]
        )->name('store');

        Route::put(
            '/{backup}/verify',
            [
                BackupController::class,
                'verify',
            ]
        )->name('verify');

        Route::get(
            '/{backup}/download',
            [
                BackupController::class,
                'download',
            ]
        )->name('download');

        Route::delete(
            '/{backup}',
            [
                BackupController::class,
                'destroy',
            ]
        )->name('destroy');
    });
