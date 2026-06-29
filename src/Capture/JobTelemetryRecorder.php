<?php

namespace Nerrowake\Strata\Capture;

use Illuminate\Contracts\Queue\Job as QueueJob;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobQueued;
use Nerrowake\Strata\Contracts\TelemetryCollector;

class JobTelemetryRecorder
{
    public function __construct(
        private readonly TelemetryCollector $events,
    ) {}

    public function queued(JobQueued $event): void
    {
        if (! $this->shouldCapture($event->queue, $this->jobName($event->job))) {
            return;
        }

        $this->record('job.queued', $event->connectionName, $event->queue, $this->jobName($event->job), [
            'status' => 'queued',
            'job_id' => $event->id,
            'delay' => $event->delay,
        ]);
    }

    public function processing(JobProcessing $event): void
    {
        if (! $this->shouldCapture($event->job->getQueue(), $event->job->resolveName())) {
            return;
        }

        $this->record('job.started', $event->connectionName, $event->job->getQueue(), $event->job->resolveName(), [
            'status' => 'started',
            'job_id' => $event->job->getJobId(),
        ]);
    }

    public function processed(JobProcessed $event): void
    {
        if (! $this->shouldCapture($event->job->getQueue(), $event->job->resolveName())) {
            return;
        }

        $this->record('job.completed', $event->connectionName, $event->job->getQueue(), $event->job->resolveName(), [
            'status' => 'completed',
            'job_id' => $event->job->getJobId(),
        ]);
    }

    public function failed(JobFailed $event): void
    {
        if (! $this->shouldCapture($event->job->getQueue(), $event->job->resolveName())) {
            return;
        }

        $this->record('job.failed', $event->connectionName, $event->job->getQueue(), $event->job->resolveName(), [
            'status' => 'failed',
            'job_id' => $event->job->getJobId(),
            'failed' => true,
            'exception_class' => $event->exception::class,
            'exception_message' => config('strata.redaction.replacement', '[redacted]'),
        ]);
    }

    private function shouldCapture(?string $queue, string $jobClass): bool
    {
        if (! config('strata.enabled', false) || ! config('strata.capture.jobs', true)) {
            return false;
        }

        return ! in_array($queue, config('strata.ignore.queues', []), true)
            && ! in_array($jobClass, config('strata.ignore.jobs', []), true);
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function record(string $event, string $connection, ?string $queue, string $jobClass, array $extra): void
    {
        $this->events->record(array_merge([
            'type' => 'job',
            'event' => $event,
            'occurred_at' => now(),
            'connection' => $connection,
            'queue' => $queue ?? 'default',
            'job_class' => $jobClass,
            'redactions' => ['job_payload', 'model_attributes'],
        ], $extra));
    }

    private function jobName(mixed $job): string
    {
        if ($job instanceof QueueJob) {
            return $job->resolveName();
        }

        return is_object($job) ? $job::class : (string) $job;
    }
}
