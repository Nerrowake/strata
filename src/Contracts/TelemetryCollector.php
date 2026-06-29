<?php

namespace Nerrowake\Strata\Contracts;

interface TelemetryCollector
{
    /**
     * Record one normalized telemetry event.
     *
     * Event payloads are associative arrays with a `type`, lifecycle `event`
     * name when applicable, `occurred_at` timestamp, safe summary fields, and
     * explicit redaction markers for omitted sensitive data.
     *
     * @param  array<string, mixed>  $event
     * @return array<string, mixed>|null
     */
    public function record(array $event): ?array;
}
