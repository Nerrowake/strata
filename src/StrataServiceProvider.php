<?php

namespace Nerrowake\Strata;

use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobQueued;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Nerrowake\Strata\Capture\ExceptionTelemetryRecorder;
use Nerrowake\Strata\Capture\JobTelemetryRecorder;
use Nerrowake\Strata\Capture\QueryTelemetryRecorder;
use Nerrowake\Strata\Capture\RequestTelemetryMiddleware;
use Nerrowake\Strata\Capture\ReviewSessionRecorder;
use Nerrowake\Strata\Capture\SafeTelemetryCollector;
use Nerrowake\Strata\Capture\ScheduledTaskTelemetryRecorder;
use Nerrowake\Strata\Console\PruneTelemetryCommand;
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
        $this->app->singleton(ExceptionTelemetryRecorder::class);
        $this->app->singleton(JobTelemetryRecorder::class);
        $this->app->singleton(QueryTelemetryRecorder::class);
        $this->app->singleton(ReviewSessionRecorder::class);
        $this->app->singleton(ScheduledTaskTelemetryRecorder::class);
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

        if ($this->app->runningInConsole()) {
            $this->commands([
                PruneTelemetryCommand::class,
            ]);
        }

        $this->app->make(Kernel::class)->pushMiddleware(RequestTelemetryMiddleware::class);

        if (config('strata.enabled', false) && config('strata.capture.queries', true)) {
            DB::listen(function (QueryExecuted $query): void {
                $this->app->make(QueryTelemetryRecorder::class)->record($query);
            });
        }

        $this->listenForJobTelemetry();
        $this->listenForScheduledTaskTelemetry();
    }

    private function listenForJobTelemetry(): void
    {
        Event::listen(JobQueued::class, fn (JobQueued $event) => $this->app->make(JobTelemetryRecorder::class)->queued($event));
        Event::listen(JobProcessing::class, fn (JobProcessing $event) => $this->app->make(JobTelemetryRecorder::class)->processing($event));
        Event::listen(JobProcessed::class, fn (JobProcessed $event) => $this->app->make(JobTelemetryRecorder::class)->processed($event));
        Event::listen(JobFailed::class, fn (JobFailed $event) => $this->app->make(JobTelemetryRecorder::class)->failed($event));
    }

    private function listenForScheduledTaskTelemetry(): void
    {
        Event::listen(ScheduledTaskStarting::class, fn (ScheduledTaskStarting $event) => $this->app->make(ScheduledTaskTelemetryRecorder::class)->starting($event));
        Event::listen(ScheduledTaskFinished::class, fn (ScheduledTaskFinished $event) => $this->app->make(ScheduledTaskTelemetryRecorder::class)->finished($event));
        Event::listen(ScheduledTaskFailed::class, fn (ScheduledTaskFailed $event) => $this->app->make(ScheduledTaskTelemetryRecorder::class)->failed($event));
    }
}
