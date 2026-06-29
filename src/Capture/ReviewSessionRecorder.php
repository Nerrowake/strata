<?php

namespace Nerrowake\Strata\Capture;

use Nerrowake\Strata\Contracts\TelemetryCollector;

class ReviewSessionRecorder
{
    public function __construct(
        private readonly TelemetryCollector $events,
    ) {}

    public function start(string $sessionId, ?string $label = null): void
    {
        $this->record('session.started', $sessionId, $label, 'started');
    }

    public function end(string $sessionId, ?string $label = null): void
    {
        $this->record('session.ended', $sessionId, $label, 'ended');
    }

    private function record(string $event, string $sessionId, ?string $label, string $status): void
    {
        if (! config('strata.enabled', false)) {
            return;
        }

        $this->events->record([
            'type' => 'session',
            'event' => $event,
            'occurred_at' => now(),
            'session_id' => $sessionId,
            'session_label' => $label,
            'status' => $status,
            'redactions' => [],
        ]);
    }
}
