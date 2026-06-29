<?php

namespace Nerrowake\Strata\Tests;

use Illuminate\Support\Facades\Route;
use Nerrowake\Strata\Storage\TelemetryEventStore;

class DashboardRouteTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');
        $app['config']->set('strata.dashboard.enabled', true);
        $app['config']->set('strata.dashboard.middleware', ['web']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        app(TelemetryEventStore::class)->reset();
    }

    public function test_dashboard_route_can_be_enabled(): void
    {
        $this->assertTrue(Route::has('strata.dashboard'));
    }

    public function test_dashboard_shell_renders_timeline_filters_and_safe_details(): void
    {
        $this->get('/strata')
            ->assertOk()
            ->assertSee('Timeline preview')
            ->assertSee('Timeline filters')
            ->assertSee('No telemetry has been captured yet')
            ->assertSee('Event detail')
            ->assertSee('Alpha shell | safe staging telemetry')
            ->assertSee('[redacted]');
    }

    public function test_dashboard_renders_request_timeline_from_store(): void
    {
        app(TelemetryEventStore::class)->record([
            'type' => 'request',
            'event' => 'request.completed',
            'occurred_at' => now(),
            'method' => 'POST',
            'path' => '/checkout',
            'route' => 'checkout.store',
            'status' => 200,
            'duration_ms' => 42.25,
            'failed' => false,
            'redactions' => ['request_body', 'headers', 'cookies'],
        ]);

        $this->get('/strata')
            ->assertOk()
            ->assertSee('POST /checkout')
            ->assertSee('route: checkout.store | duration: 42.3 ms')
            ->assertSee('Showing 1 of 1 stored events')
            ->assertSee('Details');
    }

    public function test_dashboard_filters_by_method_status_route_path_and_text(): void
    {
        $store = app(TelemetryEventStore::class);
        $store->record([
            'type' => 'request',
            'event' => 'request.completed',
            'occurred_at' => now(),
            'method' => 'POST',
            'path' => '/checkout',
            'route' => 'checkout.store',
            'status' => 200,
            'duration_ms' => 42.25,
            'failed' => false,
            'redactions' => ['request_body', 'headers', 'cookies'],
        ]);
        $store->record([
            'type' => 'request',
            'event' => 'request.completed',
            'occurred_at' => now(),
            'method' => 'GET',
            'path' => '/account',
            'route' => 'account.show',
            'status' => 404,
            'duration_ms' => 10.0,
            'failed' => false,
            'redactions' => ['request_body', 'headers', 'cookies'],
        ]);

        $this->get('/strata?method=POST&status=200&q=checkout')
            ->assertOk()
            ->assertSee('POST /checkout')
            ->assertDontSee('GET /account')
            ->assertSee('with active filters')
            ->assertSee('Clear filters');
    }

    public function test_dashboard_selects_request_event_detail(): void
    {
        app(TelemetryEventStore::class)->record([
            'type' => 'request',
            'event' => 'request.completed',
            'occurred_at' => now(),
            'method' => 'GET',
            'path' => '/review',
            'route' => null,
            'status' => 500,
            'duration_ms' => 15.0,
            'failed' => true,
            'redactions' => ['request_body', 'headers', 'cookies'],
        ]);

        $this->get('/strata?event=1')
            ->assertOk()
            ->assertSee('request.completed')
            ->assertSee('/review')
            ->assertSee('unmatched')
            ->assertSee('Request body')
            ->assertSee('[redacted]');
    }
}
