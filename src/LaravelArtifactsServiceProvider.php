<?php

namespace LaravelArtifacts;

use Illuminate\Support\ServiceProvider;

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
        // $this->app->singleton(MergeService::class); // Sprint 3
        // $this->app->singleton(ValidationService::class); // Sprint 3
        // $this->app->singleton(GenerationService::class); // Sprint 3
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

        // Publish views (will be added in Sprint 4)
        // $this->publishes([
        //     __DIR__.'/../resources/views' => resource_path('views/vendor/artifacts'),
        // ], 'artifacts-views');

        // Load migrations from package
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        // Load views (will be added in Sprint 4)
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'artifacts');

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
        ];
    }
}
