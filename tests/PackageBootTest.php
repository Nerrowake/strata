<?php

namespace Nerrowake\Strata\Tests;

use Illuminate\Support\Facades\Route;
use Nerrowake\Strata\StrataServiceProvider;

class PackageBootTest extends TestCase
{
    public function test_package_merges_default_configuration(): void
    {
        $this->assertFalse(config('strata.enabled'));
        $this->assertSame('memory', config('strata.storage.driver'));
        $this->assertNull(config('strata.storage.connection'));
        $this->assertSame(500, config('strata.storage.max_events'));
        $this->assertSame(['web', 'auth'], config('strata.dashboard.middleware'));
        $this->assertSame(24, config('strata.retention.hours'));
        $this->assertSame('[redacted]', config('strata.redaction.replacement'));
    }

    public function test_package_registers_publishable_configuration(): void
    {
        $paths = StrataServiceProvider::pathsToPublish(StrataServiceProvider::class, 'strata-config');

        $this->assertSame(
            realpath(__DIR__.'/../config/strata.php'),
            realpath(array_key_first($paths))
        );
        $this->assertSame('strata.php', basename($paths[array_key_first($paths)]));
    }

    public function test_dashboard_route_is_disabled_by_default(): void
    {
        $this->assertFalse(Route::has('strata.dashboard'));

        $this->get('/strata')->assertNotFound();
    }
}
