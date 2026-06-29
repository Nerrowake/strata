<?php

namespace Nerrowake\Strata\Tests;

use Illuminate\Support\Facades\DB;
use Nerrowake\Strata\Storage\TelemetryEventStore;

class RedactionTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('strata.enabled', true);
        $app['config']->set('strata.dashboard.enabled', true);
        $app['config']->set('strata.dashboard.middleware', ['web']);
        $app['config']->set('strata.capture.requests', true);
        $app['config']->set('strata.capture.queries', true);
    }

    protected function defineRoutes($router): void
    {
        $router->middleware('web')->post('/redaction-check', fn () => 'ok')->name('redaction.check');
    }

    protected function setUp(): void
    {
        parent::setUp();

        app(TelemetryEventStore::class)->reset();
    }

    public function test_request_headers_cookies_tokens_and_body_are_not_stored(): void
    {
        $this->withHeader('Authorization', 'Bearer secret-token')
            ->withCookie('session', 'secret-cookie')
            ->post('/redaction-check', [
                'password' => 'secret-password',
                'token' => 'secret-token',
            ])
            ->assertOk();

        $encoded = json_encode(app(TelemetryEventStore::class)->recent());

        $this->assertStringNotContainsString('secret-token', $encoded);
        $this->assertStringNotContainsString('secret-cookie', $encoded);
        $this->assertStringNotContainsString('secret-password', $encoded);
    }

    public function test_query_bindings_are_not_stored_or_rendered(): void
    {
        DB::select('select ? as token', ['secret-token']);

        $encoded = json_encode(app(TelemetryEventStore::class)->recent());

        $this->assertStringNotContainsString('secret-token', $encoded);

        $this->get('/strata?types[]=query')
            ->assertOk()
            ->assertSee('Bindings')
            ->assertSee('[redacted]')
            ->assertDontSee('secret-token');
    }
}
