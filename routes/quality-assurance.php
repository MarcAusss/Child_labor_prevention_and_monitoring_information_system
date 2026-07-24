<?php

use App\Http\Controllers\QualityAssuranceController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
    ->group(function (): void {
        Route::get(
            '/quality-assurance',
            QualityAssuranceController::class
        )->name(
            'quality-assurance.index'
        );
    });
