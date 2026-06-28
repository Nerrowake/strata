<?php

namespace Nerrowake\Strata;

use Illuminate\Support\ServiceProvider;

class StrataServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/strata.php', 'strata');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/strata.php' => config_path('strata.php'),
        ], 'strata-config');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'strata');

        if (config('strata.dashboard.enabled', false)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/strata.php');
        }
    }
}
