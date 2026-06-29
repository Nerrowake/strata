<?php

namespace Nerrowake\Strata\Tests;

use Illuminate\Support\Facades\DB;
use Nerrowake\Strata\Redaction\SqlShapeSanitizer;
use Nerrowake\Strata\Storage\TelemetryEventStore;

class QueryTelemetryTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('strata.enabled', true);
        $app['config']->set('strata.capture.queries', true);
        $app['config']->set('strata.capture.n_plus_one', true);
        $app['config']->set('strata.thresholds.slow_query_ms', 0);
        $app['config']->set('strata.thresholds.repeated_query_count', 3);
    }

    protected function setUp(): void
    {
        parent::setUp();

        app(TelemetryEventStore::class)->reset();
    }

    public function test_query_listener_records_redacted_sql_shape_without_bindings(): void
    {
        DB::select('select ? as email, ? as token', [
            'client@example.com',
            'secret-token',
        ]);

        $events = app(TelemetryEventStore::class)->recent();

        $this->assertCount(1, $events);
        $this->assertSame('select ? as email, ? as token', $events[0]['sql_shape']);
        $this->assertTrue($events[0]['bindings_redacted']);
        $this->assertSame('slow', $events[0]['status']);
        $this->assertStringNotContainsString('client@example.com', $events[0]['sql_shape']);
        $this->assertStringNotContainsString('secret-token', $events[0]['sql_shape']);
    }

    public function test_repeated_query_shape_is_marked_as_possible_n_plus_one_at_threshold(): void
    {
        DB::select('select ? as id', [1]);
        DB::select('select ? as id', [2]);
        DB::select('select ? as id', [3]);

        $events = app(TelemetryEventStore::class)->recent();

        $this->assertTrue($events[0]['possible_n_plus_one']);
        $this->assertSame('possible_n_plus_one', $events[0]['status']);
        $this->assertSame(3, $events[0]['repeated_query_count']);
        $this->assertFalse($events[1]['possible_n_plus_one']);
    }

    public function test_sql_shape_sanitizer_masks_inline_literals(): void
    {
        $shape = app(SqlShapeSanitizer::class)->sanitize(
            "select * from users where email = 'client@example.com' and id = 42"
        );

        $this->assertSame('select * from users where email = ? and id = ?', $shape);
    }
}
