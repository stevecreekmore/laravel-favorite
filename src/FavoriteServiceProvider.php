<?php

namespace Stevecreekmore\LaravelFavorite;

use Illuminate\Support\ServiceProvider;

class FavoriteServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/favorite.php' => config_path('favorite.php'),
            ], 'favorite-config');

            $this->publishes([
                __DIR__ . '/../database/migrations/create_favorites_table.php' => database_path(
                    'migrations/' . date('Y_m_d_His', time()) . '_create_favorites_table.php'
                ),
            ], 'favorite-migrations');
        }
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/favorite.php',
            'favorite'
        );
    }
}
