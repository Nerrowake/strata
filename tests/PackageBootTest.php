<?php

namespace Nerrowake\Strata\Tests;

use Illuminate\Support\Facades\Route;

class PackageBootTest extends TestCase
{
    public function test_package_merges_default_configuration(): void
    {
        $this->assertFalse(config('strata.enabled'));
        $this->assertSame('database', config('strata.storage.driver'));
        $this->assertNull(config('strata.storage.connection'));
        $this->assertSame(24, config('strata.retention.hours'));
        $this->assertSame('[redacted]', config('strata.redaction.replacement'));
    }

    public function test_dashboard_route_is_disabled_by_default(): void
    {
        $this->assertFalse(Route::has('strata.dashboard'));

        $this->get('/strata')->assertNotFound();
    }
}
