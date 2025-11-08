<?php

use Illuminate\Support\Facades\Route;
use LaravelArtifacts\Http\Controllers\Web\DashboardController;
use LaravelArtifacts\Http\Controllers\Web\ArtifactWebController;

/*
|--------------------------------------------------------------------------
| Laravel Artifacts Web Routes
|--------------------------------------------------------------------------
|
| Routes pour l'interface web du dashboard Laravel Artifacts.
| Ces routes fournissent une interface utilisateur complète pour gérer
| les artifacts via le navigateur.
|
*/

Route::middleware(['web'])->prefix('artifacts')->name('artifacts.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Artifacts CRUD
    Route::get('/', [ArtifactWebController::class, 'index'])->name('index');
    Route::get('/create', [ArtifactWebController::class, 'create'])->name('create');
    Route::post('/', [ArtifactWebController::class, 'store'])->name('store');
    Route::get('/{artifact}', [ArtifactWebController::class, 'show'])->name('show');
    Route::get('/{artifact}/edit', [ArtifactWebController::class, 'edit'])->name('edit');
    Route::put('/{artifact}', [ArtifactWebController::class, 'update'])->name('update');
    Route::delete('/{artifact}', [ArtifactWebController::class, 'destroy'])->name('destroy');

    // Actions sur les artifacts
    Route::post('/{artifact}/publish', [ArtifactWebController::class, 'publish'])->name('publish');
    Route::post('/{artifact}/archive', [ArtifactWebController::class, 'archive'])->name('archive');

    // Versions
    Route::get('/{artifact}/versions', [ArtifactWebController::class, 'versions'])->name('versions');
    Route::get('/{artifact}/versions/{version}', [ArtifactWebController::class, 'showVersion'])->name('version.show');
    Route::post('/{artifact}/versions/{version}/restore', [ArtifactWebController::class, 'restoreVersion'])->name('version.restore');

    // Validation
    Route::get('/{artifact}/validate', [ArtifactWebController::class, 'validate'])->name('validate');
});
