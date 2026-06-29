<?php

namespace Nerrowake\Strata;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Nerrowake\Strata\Capture\QueryTelemetryRecorder;
use Nerrowake\Strata\Capture\RequestTelemetryMiddleware;
use Nerrowake\Strata\Capture\SafeTelemetryCollector;
use Nerrowake\Strata\Contracts\TelemetryCollector;
use Nerrowake\Strata\Redaction\SqlShapeSanitizer;
use Nerrowake\Strata\Storage\QueryEventStore;
use Nerrowake\Strata\Storage\TelemetryEventStore;

class StrataServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/strata.php', 'strata');

        $this->app->singleton(QueryEventStore::class);
        $this->app->alias(QueryEventStore::class, TelemetryEventStore::class);
        $this->app->singleton(TelemetryCollector::class, SafeTelemetryCollector::class);
        $this->app->singleton(SqlShapeSanitizer::class);
        $this->app->singleton(QueryTelemetryRecorder::class);
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

        $this->app->make(Kernel::class)->pushMiddleware(RequestTelemetryMiddleware::class);

        if (config('strata.enabled', false) && config('strata.capture.queries', true)) {
            DB::listen(function (QueryExecuted $query): void {
                $this->app->make(QueryTelemetryRecorder::class)->record($query);
            });
        }
    }
}
