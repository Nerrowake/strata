<?php

namespace Nerrowake\Strata\Tests;

use Nerrowake\Strata\Storage\TelemetryEventStore;
use RuntimeException;

class RequestTelemetryTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');
        $app['config']->set('strata.enabled', true);
        $app['config']->set('strata.capture.requests', true);
        $app['config']->set('strata.capture.queries', false);
    }

    protected function defineRoutes($router): void
    {
        $router->middleware('web')->get('/prototype-ok', fn () => 'done')->name('prototype.ok');
        $router->middleware('web')->get('/prototype-fail', fn () => throw new RuntimeException('Prototype failure'))->name('prototype.fail');
    }

    protected function setUp(): void
    {
        parent::setUp();

        app(TelemetryEventStore::class)->reset();
    }

    public function test_request_lifecycle_records_safe_started_and_completed_events(): void
    {
        $this->withHeader('Authorization', 'Bearer secret-token')
            ->get('/prototype-ok?token=secret-token')
            ->assertOk();

        $events = app(TelemetryEventStore::class)->recent();

        $this->assertCount(2, $events);
        $this->assertSame('request.completed', $events[0]['event']);
        $this->assertSame('GET', $events[0]['method']);
        $this->assertSame('/prototype-ok', $events[0]['path']);
        $this->assertSame('prototype.ok', $events[0]['route']);
        $this->assertSame(200, $events[0]['status']);
        $this->assertIsFloat($events[0]['duration_ms']);
        $this->assertFalse($events[0]['failed']);
        $this->assertSame('request.started', $events[1]['event']);
        $this->assertSame('started', $events[1]['status']);
        $this->assertStringNotContainsString('secret-token', json_encode($events));
        $this->assertStringNotContainsString('?token=', $events[0]['path']);
        $this->assertContains('headers', $events[0]['redactions']);
    }

    public function test_request_lifecycle_records_failed_completion_without_swallowing_exception(): void
    {
        $this->withoutExceptionHandling();

        try {
            $this->get('/prototype-fail');
        } catch (RuntimeException $exception) {
            $this->assertSame('Prototype failure', $exception->getMessage());
        }

        $events = app(TelemetryEventStore::class)->recent();

        $this->assertCount(2, $events);
        $this->assertSame('request.completed', $events[0]['event']);
        $this->assertSame(500, $events[0]['status']);
        $this->assertTrue($events[0]['failed']);
        $this->assertSame('request.started', $events[1]['event']);
    }
}
