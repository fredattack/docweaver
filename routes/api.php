<?php

use Illuminate\Support\Facades\Route;
use LaravelArtifacts\Http\Controllers\ArtifactController;
use LaravelArtifacts\Http\Controllers\ArtifactVersionController;

/*
|--------------------------------------------------------------------------
| API Routes - Laravel Artifacts
|--------------------------------------------------------------------------
|
| Routes API pour la gestion des artefacts et versions.
|
*/

Route::prefix('api/artifacts')->middleware('api')->group(function () {
    // Routes CRUD pour les artefacts
    Route::get('/', [ArtifactController::class, 'index'])->name('artifacts.index');
    Route::post('/', [ArtifactController::class, 'store'])->name('artifacts.store');
    Route::get('/{artifact}', [ArtifactController::class, 'show'])->name('artifacts.show');
    Route::put('/{artifact}', [ArtifactController::class, 'update'])->name('artifacts.update');
    Route::delete('/{artifact}', [ArtifactController::class, 'destroy'])->name('artifacts.destroy');

    // Actions spécifiques sur les artefacts
    Route::post('/{artifact}/publish', [ArtifactController::class, 'publish'])->name('artifacts.publish');
    Route::post('/{artifact}/archive', [ArtifactController::class, 'archive'])->name('artifacts.archive');
    Route::post('/{artifact}/duplicate', [ArtifactController::class, 'duplicate'])->name('artifacts.duplicate');

    // Routes pour les versions
    Route::get('/{artifact}/versions', [ArtifactVersionController::class, 'index'])->name('artifacts.versions.index');
    Route::post('/{artifact}/versions', [ArtifactVersionController::class, 'store'])->name('artifacts.versions.store');
    Route::get('/{artifact}/versions/{version}', [ArtifactVersionController::class, 'show'])->name('artifacts.versions.show');

    // Actions sur les versions
    Route::post('/{artifact}/versions/{version}/restore', [ArtifactVersionController::class, 'restore'])->name('artifacts.versions.restore');
    Route::post('/{artifact}/versions/compare', [ArtifactVersionController::class, 'compare'])->name('artifacts.versions.compare');
});
