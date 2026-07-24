<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
    ->prefix('notifications')
    ->name('notifications.')
    ->group(function (): void {
        Route::get(
            '/',
            [
                NotificationController::class,
                'index',
            ]
        )->name('index');

        Route::put(
            '/mark-all-read',
            [
                NotificationController::class,
                'markAllRead',
            ]
        )->name('mark-all-read');

        Route::get(
            '/{notification}/open',
            [
                NotificationController::class,
                'open',
            ]
        )->name('open');

        Route::put(
            '/{notification}/read',
            [
                NotificationController::class,
                'markRead',
            ]
        )->name('read');

        Route::put(
            '/{notification}/unread',
            [
                NotificationController::class,
                'markUnread',
            ]
        )->name('unread');
    });
