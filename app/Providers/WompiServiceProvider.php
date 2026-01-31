<?php

namespace App\Providers;

use App\Services\WompiService;
use Illuminate\Support\ServiceProvider;

class WompiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WompiService::class, function ($app) {
            return new WompiService();
        });

        $this->mergeConfigFrom(__DIR__ . '/../../config/wompi.php', 'wompi');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/wompi.php' => config_path('wompi.php'),
        ], 'wompi-config');
    }
}