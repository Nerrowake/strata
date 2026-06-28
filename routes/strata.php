<?php

use Illuminate\Support\Facades\Route;

Route::middleware(config('strata.dashboard.middleware', ['web']))
    ->get(config('strata.dashboard.path', 'strata'), function () {
        return view('strata::dashboard', [
            'events' => [
                [
                    'time' => '14:21:08',
                    'type' => 'Request',
                    'status' => '200 OK',
                    'summary' => 'GET /checkout reviewed in 128 ms',
                    'meta' => 'route: checkout.review',
                    'severity' => 'normal',
                ],
                [
                    'time' => '14:21:10',
                    'type' => 'Query',
                    'status' => 'Slow',
                    'summary' => 'SQL shape exceeded the 250 ms threshold',
                    'meta' => 'connection: mysql | duration: 384 ms',
                    'severity' => 'warning',
                ],
                [
                    'time' => '14:21:12',
                    'type' => 'Exception',
                    'status' => 'Handled',
                    'summary' => 'RuntimeException captured with redacted context',
                    'meta' => 'request: req_01hzy9',
                    'severity' => 'danger',
                ],
            ],
            'detail' => [
                'Event' => 'Exception captured',
                'Request ID' => 'req_01hzy9',
                'Path' => '/checkout',
                'Message' => '[redacted]',
                'Authorization' => '[redacted]',
                'Cookie' => '[redacted]',
            ],
        ]);
    })
    ->name('strata.dashboard');
