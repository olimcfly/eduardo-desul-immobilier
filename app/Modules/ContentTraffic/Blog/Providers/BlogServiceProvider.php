<?php

namespace App\Modules\ContentTraffic\Blog\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class BlogServiceProvider extends ServiceProvider
{
    protected string $modulePath = __DIR__ . '/..';

    public function boot(): void
    {
        $this->registerConfig();
        $this->registerMigrations();
        $this->registerRoutes();
        $this->registerViews();
        $this->registerCommands();
        $this->publishAssets();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            "{$this->modulePath}/Config/blog.php",
            'blog'
        );
    }

    private function registerConfig(): void
    {
        $this->publishes([
            "{$this->modulePath}/Config/blog.php" => config_path('blog.php'),
        ], 'blog-config');
    }

    private function registerMigrations(): void
    {
        $this->loadMigrationsFrom("{$this->modulePath}/Database/Migrations");
    }

    private function registerRoutes(): void
    {
        Route::middleware(config('blog.route_middleware', ['web']))
            ->prefix(config('blog.route_prefix', 'api/blog'))
            ->name('blog.')
            ->group("{$this->modulePath}/Routes/api.php");

        Route::middleware(config('blog.web_middleware', ['web']))
            ->prefix(config('blog.web_prefix', 'blog'))
            ->name('blog.')
            ->group("{$this->modulePath}/Routes/web.php");
    }

    private function registerViews(): void
    {
        $this->loadViewsFrom("{$this->modulePath}/Resources/Views", 'blog');

        $this->publishes([
            "{$this->modulePath}/Resources/Views" => resource_path('views/vendor/blog'),
        ], 'blog-views');
    }

    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                // Console\GenerateSitemapCommand::class,
            ]);
        }
    }

    private function publishAssets(): void
    {
        $this->publishes([
            "{$this->modulePath}/Resources/Assets" => public_path('vendor/blog'),
        ], 'blog-assets');
    }
}
