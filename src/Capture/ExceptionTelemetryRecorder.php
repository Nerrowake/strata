<?php

namespace Nerrowake\Strata\Capture;

use Nerrowake\Strata\Contracts\TelemetryCollector;
use Throwable;

class ExceptionTelemetryRecorder
{
    public function __construct(
        private readonly TelemetryCollector $events,
    ) {}

    public function record(Throwable $exception, ?string $context = null): void
    {
        if (! config('strata.enabled', false) || ! config('strata.capture.exceptions', true)) {
            return;
        }

        $event = [
            'type' => 'exception',
            'event' => 'exception.captured',
            'occurred_at' => now(),
            'exception_class' => $exception::class,
            'message' => config('strata.redaction.replacement', '[redacted]'),
            'message_redacted' => true,
            'context' => $context ?? 'application',
            'status' => 'captured',
            'failed' => true,
            'redactions' => ['exception_message', 'stack_trace', 'request_body', 'headers', 'cookies'],
        ];

        if (config('strata.exceptions.stack_trace', false)) {
            $event['stack_frames'] = array_slice($exception->getTrace(), 0, (int) config('strata.exceptions.stack_trace_limit', 3));
            $event['redactions'] = ['exception_message', 'request_body', 'headers', 'cookies'];
        }

        $this->events->record($event);
    }
}
