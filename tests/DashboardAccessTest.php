<?php

namespace Nerrowake\Strata\Tests;

use Illuminate\Support\Facades\Gate;

class DashboardAccessTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');
        $app['config']->set('strata.dashboard.enabled', true);
        $app['config']->set('strata.dashboard.middleware', ['web']);
        $app['config']->set('strata.dashboard.gate', 'viewStrata');
    }

    public function test_dashboard_gate_can_deny_access(): void
    {
        Gate::define('viewStrata', fn ($user = null) => false);

        $this->get('/strata')->assertForbidden();
    }

    public function test_dashboard_gate_can_allow_access(): void
    {
        Gate::define('viewStrata', fn ($user = null) => true);

        $this->get('/strata')->assertOk();
    }
}
