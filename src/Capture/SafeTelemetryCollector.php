<?php

namespace Nerrowake\Strata\Capture;

use Nerrowake\Strata\Contracts\TelemetryCollector;
use Nerrowake\Strata\Storage\TelemetryEventStore;
use Throwable;

class SafeTelemetryCollector implements TelemetryCollector
{
    public function __construct(
        private readonly TelemetryEventStore $events,
    ) {}

    public function record(array $event): ?array
    {
        try {
            $event = $this->enrich($event);

            return $this->events->record($event);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<string, mixed>
     */
    private function enrich(array $event): array
    {
        $event['environment'] ??= config('strata.environment.name');
        $event['deployment'] ??= config('strata.environment.deployment');

        if (! array_key_exists('session_id', $event) && config('strata.session.id')) {
            $event['session_id'] = config('strata.session.id');
        }

        if (! array_key_exists('session_label', $event) && config('strata.session.label')) {
            $event['session_label'] = config('strata.session.label');
        }

        return $event;
    }
}
