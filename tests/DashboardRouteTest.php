<?php

namespace Nerrowake\Strata\Tests;

use Illuminate\Support\Facades\Route;

class DashboardRouteTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');
        $app['config']->set('strata.dashboard.enabled', true);
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
            ->assertSee('[redacted]');
    }
}
