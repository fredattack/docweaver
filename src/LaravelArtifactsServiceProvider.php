<?php

namespace LaravelArtifacts;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class LaravelArtifactsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/artifacts.php',
            'artifacts'
        );

        // Register services as singletons
        $this->app->singleton(\LaravelArtifacts\Services\VersioningService\VersioningService::class);
        $this->app->singleton(\LaravelArtifacts\Services\StorageService\StorageService::class);
        $this->app->singleton(\LaravelArtifacts\Services\MergeService\MergeService::class);
        $this->app->singleton(\LaravelArtifacts\Services\ValidationService\ValidationService::class);
        $this->app->singleton(\LaravelArtifacts\Services\GenerationService\GenerationService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../config/artifacts.php' => config_path('artifacts.php'),
        ], 'artifacts-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'artifacts-migrations');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/artifacts'),
        ], 'artifacts-views');

        // Load migrations from package
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'artifacts');

        // Register Livewire components
        Livewire::component('markdown-editor', \LaravelArtifacts\Livewire\MarkdownEditor::class);
        Livewire::component('validation-results', \LaravelArtifacts\Livewire\ValidationResults::class);
        Livewire::component('version-comparison', \LaravelArtifacts\Livewire\VersionComparison::class);
        Livewire::component('merge-conflict-resolution', \LaravelArtifacts\Livewire\MergeConflictResolution::class);

        // Register commands (will be added in Sprint 2+)
        // if ($this->app->runningInConsole()) {
        //     $this->commands([
        //         // Artisan commands here
        //     ]);
        // }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            \LaravelArtifacts\Services\VersioningService\VersioningService::class,
            \LaravelArtifacts\Services\StorageService\StorageService::class,
            \LaravelArtifacts\Services\MergeService\MergeService::class,
            \LaravelArtifacts\Services\ValidationService\ValidationService::class,
            \LaravelArtifacts\Services\GenerationService\GenerationService::class,
        ];
    }
}
