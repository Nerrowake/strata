<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Nerrowake\Strata\Storage\TelemetryEventStore;

Route::middleware(config('strata.dashboard.middleware', ['web']))
    ->get(config('strata.dashboard.path', 'strata'), function (Request $request) {
        $timelineError = null;

        try {
            $storedEvents = app(TelemetryEventStore::class)->recent();
        } catch (Throwable) {
            $storedEvents = [];
            $timelineError = 'Timeline events could not be loaded.';
        }

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'method' => strtoupper((string) $request->query('method', '')),
            'status' => strtolower((string) $request->query('status', '')),
        ];

        $toTimelineEvent = static function (array $event): array {
            $time = $event['occurred_at']?->format('H:i:s') ?? '--:--:--';

            if (($event['type'] ?? null) === 'query') {
                $duration = number_format((float) ($event['duration_ms'] ?? 0), 1);
                $status = match ($event['status'] ?? 'ok') {
                    'possible_n_plus_one' => 'Possible N+1',
                    'slow' => 'Slow',
                    default => 'OK',
                };

                return [
                    'id' => $event['id'] ?? 0,
                    'time' => $time,
                    'type' => 'Query',
                    'method' => '',
                    'path' => '',
                    'route' => '',
                    'status' => $status,
                    'status_filter' => strtolower((string) ($event['status'] ?? 'ok')),
                    'summary' => $event['sql_shape'] ?? 'SQL shape unavailable',
                    'meta' => 'connection: '.($event['connection'] ?? 'unknown').' | duration: '.$duration.' ms | repeated: '.($event['repeated_query_count'] ?? 1),
                    'severity' => ($event['possible_n_plus_one'] ?? false) || ($event['slow'] ?? false) ? 'warning' : 'normal',
                    'detail' => [
                        'Event' => 'Query captured',
                        'SQL shape' => $event['sql_shape'] ?? 'unavailable',
                        'Connection' => $event['connection'] ?? 'unknown',
                        'Bindings' => config('strata.redaction.replacement', '[redacted]'),
                        'Possible N+1' => ($event['possible_n_plus_one'] ?? false) ? 'yes' : 'no',
                    ],
                ];
            }

            $eventName = $event['event'] ?? 'request.captured';
            $status = $eventName === 'request.started'
                ? 'Started'
                : (string) ($event['status'] ?? 'unknown');
            $duration = array_key_exists('duration_ms', $event)
                ? ' | duration: '.number_format((float) $event['duration_ms'], 1).' ms'
                : '';
            $route = $event['route'] ?? 'unmatched';
            $method = $event['method'] ?? 'GET';
            $path = $event['path'] ?? '/';

            return [
                'id' => $event['id'] ?? 0,
                'time' => $time,
                'type' => 'Request',
                'method' => $method,
                'path' => $path,
                'route' => $route,
                'status' => $status,
                'status_filter' => strtolower((string) $status),
                'summary' => $method.' '.$path,
                'meta' => 'route: '.$route.$duration,
                'severity' => ($event['failed'] ?? false) ? 'danger' : 'normal',
                'detail' => [
                    'Event' => $eventName,
                    'Method' => $method,
                    'Path' => $path,
                    'Route' => $route,
                    'Status' => (string) $status,
                    'Duration' => array_key_exists('duration_ms', $event) ? number_format((float) $event['duration_ms'], 1).' ms' : 'not completed',
                    'Request body' => config('strata.redaction.replacement', '[redacted]'),
                    'Headers' => config('strata.redaction.replacement', '[redacted]'),
                    'Cookies' => config('strata.redaction.replacement', '[redacted]'),
                ],
            ];
        };

        $events = array_map($toTimelineEvent, $storedEvents);
        $events = array_values(array_filter($events, static function (array $event) use ($filters): bool {
            if ($filters['method'] !== '' && $event['method'] !== $filters['method']) {
                return false;
            }

            if ($filters['status'] !== '' && $event['status_filter'] !== $filters['status']) {
                return false;
            }

            if ($filters['q'] === '') {
                return true;
            }

            $haystack = strtolower(implode(' ', [
                $event['type'],
                $event['method'],
                $event['path'],
                $event['route'],
                $event['status'],
                $event['summary'],
                $event['meta'],
            ]));

            return str_contains($haystack, strtolower($filters['q']));
        }));

        $selectedId = (int) $request->query('event', 0);
        $selectedEvent = $selectedId > 0
            ? collect($events)->firstWhere('id', $selectedId)
            : ($events[0] ?? null);
        $detail = $selectedEvent['detail'] ?? [
            'Event' => 'No event selected',
            'Status' => $timelineError ?? 'No matching telemetry event',
            'Sensitive fields' => config('strata.redaction.replacement', '[redacted]'),
        ];

        return view('strata::dashboard', [
            'events' => $events,
            'detail' => $detail,
            'filters' => $filters,
            'selectedEventId' => $selectedEvent['id'] ?? null,
            'storedEvents' => app(TelemetryEventStore::class)->count(),
            'matchingEvents' => count($events),
            'timelineError' => $timelineError,
        ]);
    })
    ->name('strata.dashboard');
