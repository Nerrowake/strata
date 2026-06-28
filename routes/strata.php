<?php

use Illuminate\Support\Facades\Route;
use Nerrowake\Strata\Storage\QueryEventStore;

Route::middleware(config('strata.dashboard.middleware', ['web']))
    ->get(config('strata.dashboard.path', 'strata'), function () {
        $queryEvents = app(QueryEventStore::class)->recent();
        $events = array_map(static function (array $event): array {
            $duration = number_format((float) $event['duration_ms'], 1);
            $status = match ($event['status']) {
                'possible_n_plus_one' => 'Possible N+1',
                'slow' => 'Slow',
                default => 'OK',
            };

            return [
                'time' => $event['occurred_at']->format('H:i:s'),
                'type' => 'Query',
                'status' => $status,
                'summary' => $event['sql_shape'],
                'meta' => 'connection: '.$event['connection'].' | duration: '.$duration.' ms | repeated: '.$event['repeated_query_count'],
                'severity' => $event['possible_n_plus_one'] || $event['slow'] ? 'warning' : 'normal',
            ];
        }, $queryEvents);

        $events = $events ?: [
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
        ];

        $detail = $queryEvents !== []
            ? [
                'Event' => 'Query captured',
                'SQL shape' => $queryEvents[0]['sql_shape'],
                'Connection' => $queryEvents[0]['connection'],
                'Bindings' => config('strata.redaction.replacement', '[redacted]'),
                'Possible N+1' => $queryEvents[0]['possible_n_plus_one'] ? 'yes' : 'no',
            ]
            : [
                'Event' => 'Exception captured',
                'Request ID' => 'req_01hzy9',
                'Path' => '/checkout',
                'Message' => '[redacted]',
                'Authorization' => '[redacted]',
                'Cookie' => '[redacted]',
            ];

        return view('strata::dashboard', [
            'events' => $events,
            'detail' => $detail,
            'storedEvents' => app(QueryEventStore::class)->count(),
        ]);
    })
    ->name('strata.dashboard');
