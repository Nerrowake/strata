<?php

namespace Nerrowake\Strata\Capture;

use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Console\Scheduling\Event;
use Nerrowake\Strata\Contracts\TelemetryCollector;

class ScheduledTaskTelemetryRecorder
{
    public function __construct(
        private readonly TelemetryCollector $events,
    ) {}

    public function starting(ScheduledTaskStarting $event): void
    {
        $this->record('schedule.started', $event->task, ['status' => 'started']);
    }

    public function finished(ScheduledTaskFinished $event): void
    {
        $this->record('schedule.completed', $event->task, [
            'status' => 'completed',
            'duration_ms' => round($event->runtime * 1000, 2),
            'exit_code' => $event->task->exitCode,
        ]);
    }

    public function failed(ScheduledTaskFailed $event): void
    {
        $this->record('schedule.failed', $event->task, [
            'status' => 'failed',
            'failed' => true,
            'exception_class' => $event->exception::class,
            'exception_message' => config('strata.redaction.replacement', '[redacted]'),
        ]);
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function record(string $eventName, Event $task, array $extra): void
    {
        $command = $task->getSummaryForDisplay();

        if (! config('strata.enabled', false) || ! config('strata.capture.scheduled_tasks', true)) {
            return;
        }

        if (in_array($command, config('strata.ignore.scheduled_tasks', []), true)) {
            return;
        }

        $this->events->record(array_merge([
            'type' => 'schedule',
            'event' => $eventName,
            'occurred_at' => now(),
            'task' => $command,
            'redactions' => ['command_output', 'environment_variables'],
        ], $extra));
    }
}
