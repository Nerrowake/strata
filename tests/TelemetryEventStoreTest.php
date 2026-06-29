<?php

namespace Nerrowake\Strata\Tests;

use Nerrowake\Strata\Storage\TelemetryEventStore;

class TelemetryEventStoreTest extends TestCase
{
    public function test_events_can_be_appended_and_read_newest_first(): void
    {
        $store = app(TelemetryEventStore::class);
        $store->reset();

        $first = $store->record([
            'type' => 'request',
            'event' => 'request.started',
            'occurred_at' => now(),
            'method' => 'GET',
            'path' => '/first',
            'status' => 'started',
            'redactions' => [],
        ]);
        $second = $store->record([
            'type' => 'request',
            'event' => 'request.completed',
            'occurred_at' => now(),
            'method' => 'GET',
            'path' => '/second',
            'status' => 200,
            'redactions' => [],
        ]);

        $this->assertSame(1, $first['id']);
        $this->assertSame(2, $second['id']);
        $this->assertSame(['/second', '/first'], array_column($store->recent(), 'path'));
    }

    public function test_store_keeps_only_configured_maximum_events(): void
    {
        config()->set('strata.storage.max_events', 2);

        $store = app(TelemetryEventStore::class);
        $store->reset();

        foreach (['/one', '/two', '/three'] as $path) {
            $store->record([
                'type' => 'request',
                'event' => 'request.completed',
                'occurred_at' => now(),
                'method' => 'GET',
                'path' => $path,
                'status' => 200,
                'redactions' => [],
            ]);
        }

        $this->assertSame(2, $store->count());
        $this->assertSame(['/three', '/two'], array_column($store->recent(), 'path'));
        $this->assertSame([3, 2], array_column($store->recent(), 'id'));
    }
}
