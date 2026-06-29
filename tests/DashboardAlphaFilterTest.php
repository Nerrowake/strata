<?php

namespace Nerrowake\Strata\Tests;

use Nerrowake\Strata\Storage\TelemetryEventStore;

class DashboardAlphaFilterTest extends TestCase
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

    public function test_dashboard_filters_mixed_timelines_by_type_session_and_safe_search(): void
    {
        $store = app(TelemetryEventStore::class);
        $store->record([
            'type' => 'exception',
            'event' => 'exception.captured',
            'occurred_at' => now(),
            'exception_class' => 'App\\Exceptions\\CheckoutFailed',
            'message' => '[redacted]',
            'context' => 'request',
            'status' => 'captured',
            'failed' => true,
            'session_id' => 'qa-1',
            'redactions' => ['exception_message'],
        ]);
        $store->record([
            'type' => 'job',
            'event' => 'job.completed',
            'occurred_at' => now(),
            'job_class' => 'App\\Jobs\\SendReceipt',
            'queue' => 'emails',
            'connection' => 'database',
            'status' => 'completed',
            'session_id' => 'qa-2',
            'redactions' => ['job_payload'],
        ]);

        $this->get('/strata?types[]=exception&session=qa-1&q=CheckoutFailed')
            ->assertOk()
            ->assertSee('CheckoutFailed')
            ->assertDontSee('SendReceipt')
            ->assertSee('with active filters');
    }

    public function test_dashboard_no_results_state_is_clear_for_search(): void
    {
        app(TelemetryEventStore::class)->record([
            'type' => 'job',
            'event' => 'job.completed',
            'occurred_at' => now(),
            'job_class' => 'App\\Jobs\\SendReceipt',
            'queue' => 'emails',
            'connection' => 'database',
            'status' => 'completed',
            'redactions' => ['job_payload'],
        ]);

        $this->get('/strata?q=missing-event')
            ->assertOk()
            ->assertSee('No matching telemetry events')
            ->assertSee('Showing 0 of 1 stored events');
    }
}
