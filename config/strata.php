<?php

return [
    'enabled' => env('STRATA_ENABLED', false),

    'dashboard' => [
        'enabled' => env('STRATA_DASHBOARD_ENABLED', false),
        'path' => env('STRATA_DASHBOARD_PATH', 'strata'),
        'middleware' => ['web', 'auth'],
        'gate' => null,
    ],

    'storage' => [
        'driver' => 'memory',
        'connection' => env('STRATA_DB_CONNECTION', null),
        'max_events' => env('STRATA_MAX_EVENTS', 500),
    ],

    'retention' => [
        'enabled' => true,
        'hours' => env('STRATA_RETENTION_HOURS', 24),
    ],

    'session' => [
        'id' => env('STRATA_SESSION_ID', null),
        'label' => env('STRATA_SESSION_LABEL', null),
    ],

    'capture' => [
        'requests' => true,
        'exceptions' => true,
        'queries' => true,
        'slow_queries' => true,
        'n_plus_one' => true,
        'jobs' => true,
        'scheduled_tasks' => true,
        'environment' => true,
    ],

    'ignore' => [
        'paths' => [],
        'routes' => [],
        'jobs' => [],
        'queues' => [],
        'scheduled_tasks' => [],
    ],

    'thresholds' => [
        'slow_query_ms' => env('STRATA_SLOW_QUERY_MS', 250),
        'repeated_query_count' => env('STRATA_REPEATED_QUERY_COUNT', 5),
    ],

    'redaction' => [
        'replacement' => '[redacted]',
        'headers' => [
            'authorization',
            'cookie',
            'set-cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ],
        'keys' => [
            'password',
            'password_confirmation',
            'token',
            'secret',
            'key',
            'credential',
        ],
        'patterns' => [],
    ],

    'exceptions' => [
        'message' => 'redacted',
        'stack_trace' => false,
        'stack_trace_limit' => 3,
    ],

    'environment' => [
        'name' => env('STRATA_ENVIRONMENT_NAME', env('APP_ENV', 'staging')),
        'deployment' => env('STRATA_DEPLOYMENT_ID', null),
    ],
];
