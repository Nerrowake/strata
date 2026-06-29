<?php

namespace Nerrowake\Strata\Tests;

use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\EventMutex;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobQueued;
use Illuminate\Support\Facades\Event as EventFacade;
use Nerrowake\Strata\Capture\ExceptionTelemetryRecorder;
use Nerrowake\Strata\Capture\ReviewSessionRecorder;
use Nerrowake\Strata\Storage\TelemetryEventStore;
use RuntimeException;

class AlphaTelemetryTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('strata.enabled', true);
        $app['config']->set('strata.capture.exceptions', true);
        $app['config']->set('strata.capture.jobs', true);
        $app['config']->set('strata.capture.scheduled_tasks', true);
        $app['config']->set('strata.session.id', 'qa-2026-06-29');
        $app['config']->set('strata.session.label', 'QA checkout pass');
    }

    protected function setUp(): void
    {
        parent::setUp();

        app(TelemetryEventStore::class)->reset();
    }

    public function test_exception_recorder_captures_safe_context_without_raw_message(): void
    {
        app(ExceptionTelemetryRecorder::class)->record(new RuntimeException('secret-token leaked'), 'worker');

        $event = app(TelemetryEventStore::class)->recent()[0];

        $this->assertSame('exception', $event['type']);
        $this->assertSame(RuntimeException::class, $event['exception_class']);
        $this->assertSame('[redacted]', $event['message']);
        $this->assertSame('worker', $event['context']);
        $this->assertStringNotContainsString('secret-token', json_encode($event));
        $this->assertSame('qa-2026-06-29', $event['session_id']);
    }

    public function test_review_session_recorder_marks_start_and_end(): void
    {
        $recorder = app(ReviewSessionRecorder::class);

        $recorder->start('qa-window', 'QA window');
        $recorder->end('qa-window', 'QA window');

        $events = app(TelemetryEventStore::class)->recent();

        $this->assertSame('session.ended', $events[0]['event']);
        $this->assertSame('session.started', $events[1]['event']);
        $this->assertSame('qa-window', $events[0]['session_id']);
    }

    public function test_job_lifecycle_events_exclude_payload_and_capture_failure_class(): void
    {
        EventFacade::dispatch(new JobQueued('database', 'emails', 'job-1', SendAlphaDigest::class, '{"token":"secret-token"}', null));

        $job = $this->fakeJob();
        EventFacade::dispatch(new JobProcessing('database', $job));
        EventFacade::dispatch(new JobProcessed('database', $job));
        EventFacade::dispatch(new JobFailed('database', $job, new RuntimeException('secret-token failed')));

        $events = app(TelemetryEventStore::class)->recent();

        $this->assertSame(['job.failed', 'job.completed', 'job.started', 'job.queued'], array_column($events, 'event'));
        $this->assertSame(RuntimeException::class, $events[0]['exception_class']);
        $this->assertSame('[redacted]', $events[0]['exception_message']);
        $this->assertStringNotContainsString('secret-token', json_encode($events));
    }

    public function test_scheduled_task_events_capture_safe_command_and_failures(): void
    {
        $task = new Event($this->eventMutex(), 'php artisan reports:send');

        EventFacade::dispatch(new ScheduledTaskStarting($task));
        EventFacade::dispatch(new ScheduledTaskFinished($task, 0.25));
        EventFacade::dispatch(new ScheduledTaskFailed($task, new RuntimeException('secret-token failed')));

        $events = app(TelemetryEventStore::class)->recent();

        $this->assertSame(['schedule.failed', 'schedule.completed', 'schedule.started'], array_column($events, 'event'));
        $this->assertStringContainsString('php artisan reports:send', $events[0]['task']);
        $this->assertSame('[redacted]', $events[0]['exception_message']);
        $this->assertStringNotContainsString('secret-token', json_encode($events));
    }

    public function test_retention_command_prunes_only_events_older_than_boundary(): void
    {
        config()->set('strata.retention.hours', 24);

        $store = app(TelemetryEventStore::class);
        $store->record([
            'type' => 'request',
            'event' => 'request.completed',
            'occurred_at' => now()->subHours(25),
            'method' => 'GET',
            'path' => '/old',
            'status' => 200,
            'redactions' => [],
        ]);
        $store->record([
            'type' => 'request',
            'event' => 'request.completed',
            'occurred_at' => now()->subHours(2),
            'method' => 'GET',
            'path' => '/active',
            'status' => 200,
            'redactions' => [],
        ]);

        $this->artisan('strata:prune')
            ->expectsOutput('Pruned 1 Strata event(s).')
            ->assertSuccessful();

        $this->assertSame(['/active'], array_column($store->recent(), 'path'));
    }

    private function eventMutex(): EventMutex
    {
        return new class implements EventMutex
        {
            public function create(Event $event): bool
            {
                return true;
            }

            public function exists(Event $event): bool
            {
                return false;
            }

            public function forget(Event $event): void {}
        };
    }

    private function fakeJob(): Job
    {
        return new class implements Job
        {
            public function uuid(): ?string
            {
                return 'uuid-1';
            }

            public function getJobId(): string
            {
                return 'job-1';
            }

            public function payload(): array
            {
                return ['data' => '[redacted]'];
            }

            public function fire(): void {}

            public function release($delay = 0): void {}

            public function isReleased(): bool
            {
                return false;
            }

            public function delete(): void {}

            public function isDeleted(): bool
            {
                return false;
            }

            public function isDeletedOrReleased(): bool
            {
                return false;
            }

            public function attempts(): int
            {
                return 1;
            }

            public function hasFailed(): bool
            {
                return false;
            }

            public function markAsFailed(): void {}

            public function fail($e = null): void {}

            public function maxTries(): ?int
            {
                return null;
            }

            public function maxExceptions(): ?int
            {
                return null;
            }

            public function timeout(): ?int
            {
                return null;
            }

            public function retryUntil(): ?int
            {
                return null;
            }

            public function getName(): string
            {
                return SendAlphaDigest::class;
            }

            public function resolveName(): string
            {
                return SendAlphaDigest::class;
            }

            public function resolveQueuedJobClass(): string
            {
                return SendAlphaDigest::class;
            }

            public function getConnectionName(): string
            {
                return 'database';
            }

            public function getQueue(): string
            {
                return 'emails';
            }

            public function getRawBody(): string
            {
                return '{"token":"secret-token"}';
            }
        };
    }
}

class SendAlphaDigest {}
