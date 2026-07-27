<?php

use App\Http\Controllers\Admin\ChildLaborImportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('admin/child-labor-imports')->name('admin.child-labor-imports.')->group(function () {
    Route::get('/', [ChildLaborImportController::class, 'index'])->name('index');
    Route::post('/', [ChildLaborImportController::class, 'upload'])->name('upload');
    Route::get('/{childLaborImport}', [ChildLaborImportController::class, 'show'])->name('show');
    Route::post('/{childLaborImport}/commit', [ChildLaborImportController::class, 'commit'])->name('commit');
    Route::get('/{childLaborImport}/errors', [ChildLaborImportController::class, 'errors'])->name('errors');
});
