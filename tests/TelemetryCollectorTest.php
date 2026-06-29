<?php

namespace Nerrowake\Strata\Tests;

use Nerrowake\Strata\Capture\SafeTelemetryCollector;
use Nerrowake\Strata\Contracts\TelemetryCollector;
use Nerrowake\Strata\Storage\TelemetryEventStore;
use RuntimeException;

class TelemetryCollectorTest extends TestCase
{
    public function test_collector_contract_records_basic_event_payload(): void
    {
        $collector = app(TelemetryCollector::class);

        $event = $collector->record([
            'type' => 'prototype',
            'event' => 'prototype.recorded',
            'occurred_at' => now(),
            'summary' => 'collector smoke path',
            'redactions' => [],
        ]);

        $this->assertNotNull($event);
        $this->assertSame('prototype.recorded', $event['event']);
        $this->assertSame($event, app(TelemetryEventStore::class)->recent()[0]);
    }

    public function test_safe_collector_drops_storage_failure_without_throwing(): void
    {
        $collector = new SafeTelemetryCollector(new class extends TelemetryEventStore
        {
            public function record(array $event): array
            {
                throw new RuntimeException('storage unavailable');
            }
        });

        $this->assertNull($collector->record([
            'type' => 'prototype',
            'event' => 'prototype.failed',
            'occurred_at' => now(),
            'redactions' => [],
        ]));
    }
}
