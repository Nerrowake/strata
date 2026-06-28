<?php

namespace Nerrowake\Strata\Capture;

use Illuminate\Database\Events\QueryExecuted;
use Nerrowake\Strata\Redaction\SqlShapeSanitizer;
use Nerrowake\Strata\Storage\QueryEventStore;
use Throwable;

class QueryTelemetryRecorder
{
    public function __construct(
        private readonly QueryEventStore $events,
        private readonly SqlShapeSanitizer $sanitizer,
    ) {}

    public function record(QueryExecuted $query): void
    {
        if (! config('strata.enabled', false) || ! config('strata.capture.queries', true)) {
            return;
        }

        try {
            $duration = (float) $query->time;
            $shape = $this->sanitizer->sanitize($query->sql);
            $slowThreshold = (float) config('strata.thresholds.slow_query_ms', 250);

            $this->events->record([
                'type' => 'query',
                'occurred_at' => now(),
                'connection' => $this->connectionName($query),
                'duration_ms' => $duration,
                'sql_shape' => $shape,
                'status' => $duration >= $slowThreshold ? 'slow' : 'ok',
                'slow' => $duration >= $slowThreshold,
                'bindings_redacted' => true,
            ]);
        } catch (Throwable) {
            // Strata should never break the host request when telemetry fails.
        }
    }

    private function connectionName(QueryExecuted $query): string
    {
        if (property_exists($query, 'connectionName') && is_string($query->connectionName)) {
            return $query->connectionName;
        }

        return $query->connection->getName();
    }
}
