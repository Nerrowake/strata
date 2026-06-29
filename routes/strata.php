<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Nerrowake\Strata\Storage\TelemetryEventStore;

Route::middleware(config('strata.dashboard.middleware', ['web']))
    ->get(config('strata.dashboard.path', 'strata'), function (Request $request) {
        if (config('strata.dashboard.gate') && Gate::denies(config('strata.dashboard.gate'))) {
            abort(403);
        }

        $timelineError = null;

        try {
            $store = app(TelemetryEventStore::class);
            $storedEvents = $store->recent();
            $sessions = $store->sessions();
        } catch (Throwable) {
            $storedEvents = [];
            $sessions = [];
            $timelineError = 'Timeline events could not be loaded.';
        }

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'method' => strtoupper((string) $request->query('method', '')),
            'status' => strtolower((string) $request->query('status', '')),
            'session' => (string) $request->query('session', ''),
            'types' => array_values(array_filter((array) $request->query('types', []))),
        ];

        $toTimelineEvent = static function (array $event): array {
            $type = (string) ($event['type'] ?? 'event');
            $eventName = (string) ($event['event'] ?? $type.'.captured');
            $time = $event['occurred_at']?->format('H:i:s') ?? '--:--:--';
            $status = (string) ($event['status'] ?? 'ok');
            $severity = ($event['failed'] ?? false) ? 'danger' : 'normal';
            $duration = array_key_exists('duration_ms', $event)
                ? ' | duration: '.number_format((float) $event['duration_ms'], 1).' ms'
                : '';

            $row = [
                'id' => $event['id'] ?? 0,
                'time' => $time,
                'type' => str($type)->headline()->toString(),
                'type_filter' => $type,
                'method' => (string) ($event['method'] ?? ''),
                'path' => (string) ($event['path'] ?? ''),
                'route' => (string) ($event['route'] ?? ''),
                'session_id' => (string) ($event['session_id'] ?? ''),
                'status' => str_replace('_', ' ', str($status)->headline()->toString()),
                'status_filter' => strtolower($status),
                'summary' => $eventName,
                'meta' => trim('session: '.($event['session_id'] ?? 'none').$duration),
                'severity' => $severity,
                'safe_search' => [],
                'detail' => [
                    'Event' => $eventName,
                    'Status' => $status,
                    'Session' => $event['session_id'] ?? 'none',
                ],
            ];

            if ($type === 'query') {
                $queryStatus = match ($status) {
                    'possible_n_plus_one' => 'Possible N+1',
                    'slow' => 'Slow',
                    default => 'OK',
                };

                $row['status'] = $queryStatus;
                $row['summary'] = $event['sql_shape'] ?? 'SQL shape unavailable';
                $row['meta'] = 'connection: '.($event['connection'] ?? 'unknown').' | duration: '.number_format((float) ($event['duration_ms'] ?? 0), 1).' ms | repeated: '.($event['repeated_query_count'] ?? 1);
                $row['severity'] = ($event['possible_n_plus_one'] ?? false) || ($event['slow'] ?? false) ? 'warning' : 'normal';
                $row['safe_search'] = [$row['summary'], $row['meta'], $queryStatus];
                $row['detail'] = [
                    'Event' => 'Query captured',
                    'SQL shape' => $event['sql_shape'] ?? 'unavailable',
                    'Connection' => $event['connection'] ?? 'unknown',
                    'Bindings' => config('strata.redaction.replacement', '[redacted]'),
                    'Possible N+1' => ($event['possible_n_plus_one'] ?? false) ? 'yes' : 'no',
                ];
            } elseif ($type === 'request') {
                $row['status'] = $eventName === 'request.started' ? 'Started' : (string) ($event['status'] ?? 'unknown');
                $row['status_filter'] = strtolower((string) ($event['status'] ?? 'started'));
                $row['summary'] = ($event['method'] ?? 'GET').' '.($event['path'] ?? '/');
                $row['meta'] = 'route: '.($event['route'] ?? 'unmatched').$duration;
                $row['safe_search'] = [$row['summary'], $row['meta'], $row['status']];
                $row['detail'] = [
                    'Event' => $eventName,
                    'Method' => $event['method'] ?? 'GET',
                    'Path' => $event['path'] ?? '/',
                    'Route' => $event['route'] ?? 'unmatched',
                    'Status' => (string) $row['status'],
                    'Duration' => array_key_exists('duration_ms', $event) ? number_format((float) $event['duration_ms'], 1).' ms' : 'not completed',
                    'Request body' => config('strata.redaction.replacement', '[redacted]'),
                    'Headers' => config('strata.redaction.replacement', '[redacted]'),
                    'Cookies' => config('strata.redaction.replacement', '[redacted]'),
                ];
            } elseif ($type === 'exception') {
                $row['summary'] = $event['exception_class'] ?? 'Exception captured';
                $row['meta'] = 'context: '.($event['context'] ?? 'application');
                $row['safe_search'] = [$row['summary'], $row['meta'], $eventName];
                $row['detail'] = [
                    'Event' => $eventName,
                    'Exception' => $event['exception_class'] ?? 'unknown',
                    'Message' => $event['message'] ?? config('strata.redaction.replacement', '[redacted]'),
                    'Context' => $event['context'] ?? 'application',
                    'Stack trace' => array_key_exists('stack_frames', $event) ? 'limited frames captured' : config('strata.redaction.replacement', '[redacted]'),
                ];
            } elseif ($type === 'job') {
                $row['summary'] = $event['job_class'] ?? 'Job event';
                $row['meta'] = 'queue: '.($event['queue'] ?? 'default').' | connection: '.($event['connection'] ?? 'unknown');
                $row['safe_search'] = [$row['summary'], $row['meta'], $eventName];
                $row['detail'] = [
                    'Event' => $eventName,
                    'Job' => $event['job_class'] ?? 'unknown',
                    'Queue' => $event['queue'] ?? 'default',
                    'Connection' => $event['connection'] ?? 'unknown',
                    'Payload' => config('strata.redaction.replacement', '[redacted]'),
                    'Failure' => $event['exception_class'] ?? 'none',
                ];
            } elseif ($type === 'schedule') {
                $row['summary'] = $event['task'] ?? 'Scheduled task';
                $row['meta'] = 'scheduled task'.$duration;
                $row['safe_search'] = [$row['summary'], $row['meta'], $eventName];
                $row['detail'] = [
                    'Event' => $eventName,
                    'Task' => $event['task'] ?? 'unknown',
                    'Status' => $event['status'] ?? 'unknown',
                    'Output' => config('strata.redaction.replacement', '[redacted]'),
                    'Failure' => $event['exception_class'] ?? 'none',
                ];
            } elseif ($type === 'session') {
                $row['summary'] = $event['session_label'] ?? $event['session_id'] ?? 'Review session';
                $row['meta'] = 'session: '.($event['session_id'] ?? 'unknown');
                $row['safe_search'] = [$row['summary'], $row['meta'], $eventName];
                $row['detail'] = [
                    'Event' => $eventName,
                    'Session' => $event['session_id'] ?? 'unknown',
                    'Label' => $event['session_label'] ?? 'unlabeled',
                    'Status' => $event['status'] ?? 'unknown',
                ];
            }

            return $row;
        };

        $events = array_map($toTimelineEvent, $storedEvents);
        $events = array_values(array_filter($events, static function (array $event) use ($filters): bool {
            if ($filters['types'] !== [] && ! in_array($event['type_filter'], $filters['types'], true)) {
                return false;
            }

            if ($filters['session'] !== '' && $event['session_id'] !== $filters['session']) {
                return false;
            }

            if ($filters['method'] !== '' && $event['method'] !== $filters['method']) {
                return false;
            }

            if ($filters['status'] !== '' && $event['status_filter'] !== $filters['status']) {
                return false;
            }

            if ($filters['q'] === '') {
                return true;
            }

            $haystack = strtolower(implode(' ', array_merge([
                $event['type'],
                $event['method'],
                $event['path'],
                $event['route'],
                $event['status'],
                $event['summary'],
                $event['meta'],
            ], $event['safe_search'])));

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
            'sessions' => $sessions,
            'selectedEventId' => $selectedEvent['id'] ?? null,
            'storedEvents' => app(TelemetryEventStore::class)->count(),
            'matchingEvents' => count($events),
            'timelineError' => $timelineError,
        ]);
    })
    ->name('strata.dashboard');
