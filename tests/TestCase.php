<?php

namespace Nerrowake\Strata\Tests;

use Nerrowake\Strata\StrataServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            StrataServiceProvider::class,
        ];
    }
}
