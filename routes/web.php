<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocationLookupController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfilingOfficer\DashboardController as ProfilingOfficerDashboardController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\Viewer\DashboardController as ViewerDashboardController;
use App\Http\Controllers\Admin\LocationTestController;
use App\Models\Role;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)
        ->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::prefix('lookup/locations')
        ->name('lookup.locations.')
        ->group(function (): void {
            Route::get(
                '/regions',
                [LocationLookupController::class, 'regions']
            )->name('regions');

            Route::get(
                '/regions/{region}/provinces',
                [LocationLookupController::class, 'provinces']
            )->name('provinces');

            Route::get(
                '/regions/{region}/localities',
                [LocationLookupController::class, 'regionalLocalities']
            )->name('regional-localities');

            Route::get(
                '/provinces/{province}/localities',
                [LocationLookupController::class, 'provincialLocalities']
            )->name('provincial-localities');

            Route::get(
                '/localities/{locality}/children',
                [LocationLookupController::class, 'childLocalities']
            )->name('child-localities');

            Route::get(
                '/localities/{locality}/barangays',
                [LocationLookupController::class, 'barangays']
            )->name('barangays');
        });
    /*
    |--------------------------------------------------------------------------
    | Super Admin
    |--------------------------------------------------------------------------
    */

    Route::prefix('super-admin')
        ->name('super-admin.')
        ->middleware('role:' . Role::SUPER_ADMIN)
        ->group(function (): void {
            Route::get(
                '/dashboard',
                SuperAdminDashboardController::class
            )->name('dashboard');
        });

    /*
    |--------------------------------------------------------------------------
    | Admin and Super Admin
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin')
        ->name('admin.')
        ->middleware(
            'role:' . Role::SUPER_ADMIN . ',' . Role::ADMIN
        )
        ->group(function (): void {
            Route::get(
                '/dashboard',
                AdminDashboardController::class
            )->name('dashboard');

            Route::get(
                '/users',
                [UserController::class, 'index']
            )->name('users.index');

            Route::get(
                '/users/create',
                [UserController::class, 'create']
            )->name('users.create');

            Route::post(
                '/users',
                [UserController::class, 'store']
            )->name('users.store');

            Route::get(
                '/users/{user}/edit',
                [UserController::class, 'edit']
            )->name('users.edit');

            Route::patch(
                '/users/{user}',
                [UserController::class, 'update']
            )->name('users.update');

            Route::patch(
                '/users/{user}/toggle-status',
                [UserController::class, 'toggleStatus']
            )->name('users.toggle-status');

            Route::patch(
                '/users/{user}/reset-password',
                [UserController::class, 'resetPassword']
            )->name('users.reset-password');
            Route::get(
                '/location-test',
                [LocationTestController::class, 'create']
            )->name('location-test.create');

            Route::post(
                '/location-test',
                [LocationTestController::class, 'store']
            )->name('location-test.store');
        });

    /*
    |--------------------------------------------------------------------------
    | Profiling Officer
    |--------------------------------------------------------------------------
    */

    Route::prefix('profiling-officer')
        ->name('profiling-officer.')
        ->middleware('role:' . Role::PROFILING_OFFICER)
        ->group(function (): void {
            Route::get(
                '/dashboard',
                ProfilingOfficerDashboardController::class
            )->name('dashboard');
        });

    /*
    |--------------------------------------------------------------------------
    | Viewer
    |--------------------------------------------------------------------------
    */

    Route::prefix('viewer')
        ->name('viewer.')
        ->middleware('role:' . Role::VIEWER)
        ->group(function (): void {
            Route::get(
                '/dashboard',
                ViewerDashboardController::class
            )->name('dashboard');
        });
});

require __DIR__ . '/auth.php';