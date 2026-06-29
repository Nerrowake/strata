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
            return $this->events->record($event);
        } catch (Throwable) {
            return null;
        }
    }
}
