# Configuration Surface and Defaults

This document specifies the first `config/strata.php` surface, safe defaults,
environment-specific behavior, and telemetry controls.

## Configuration Principles

- Keep configuration in one readable published file.
- Make telemetry explicit and configurable.
- Keep dashboard access private by default.
- Redact high-risk values before storage or display.
- Allow every telemetry category to be disabled independently.
- Avoid broad "capture everything" switches.
- Treat longer retention and higher-risk fields as opt-in choices.

## Sections

### Global Enablement

```php
'enabled' => env('STRATA_ENABLED', false),
```

Strata should be disabled by default. Enabling it should be an explicit staging
decision.

### Dashboard

```php
'dashboard' => [
    'enabled' => env('STRATA_DASHBOARD_ENABLED', false),
    'path' => env('STRATA_DASHBOARD_PATH', 'strata'),
    'middleware' => ['web', 'auth'],
    'gate' => null,
],
```

Dashboard routes should be disabled by default and private when enabled. The
host application should own middleware and authorization decisions.

### Storage

```php
'storage' => [
    'driver' => 'memory',
    'connection' => env('STRATA_DB_CONNECTION', null),
    'max_events' => env('STRATA_MAX_EVENTS', 500),
],
```

The prototype storage driver is `memory`. It is process-local and intended only
for early package and dashboard validation. `max_events` bounds the number of
stored prototype events so dashboard reads and memory use stay predictable.

The `connection` key is reserved for the first database-backed storage driver.

### Retention

```php
'retention' => [
    'enabled' => true,
    'hours' => env('STRATA_RETENTION_HOURS', 24),
],
```

Telemetry should be temporary staging review data. The first default retention
window is 24 hours.

### Capture Categories

```php
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
```

Every category must be independently disableable without editing package code.

### Ignore Rules

```php
'ignore' => [
    'paths' => [],
    'routes' => [],
    'jobs' => [],
    'queues' => [],
    'scheduled_tasks' => [],
],
```

Ignore rules let teams narrow capture for noisy or inappropriate telemetry
sources.

### Thresholds

```php
'thresholds' => [
    'slow_query_ms' => env('STRATA_SLOW_QUERY_MS', 250),
    'repeated_query_count' => env('STRATA_REPEATED_QUERY_COUNT', 5),
],
```

Thresholds should be conservative defaults that teams can tune for their
staging environment.

### Redaction

```php
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
```

Redaction should replace sensitive values with a stable marker instead of
silently dropping useful field names. Custom keys and patterns must be
test-covered when implemented.

### Environment Context

```php
'environment' => [
    'name' => env('STRATA_ENVIRONMENT_NAME', env('APP_ENV', 'staging')),
    'deployment' => env('STRATA_DEPLOYMENT_ID', null),
],
```

Environment and deployment identifiers should be configured, safe metadata.
Strata must not read or store full environment variables.

## Environment-Specific Behavior

### Local

Local development can enable Strata for package and example-app testing, but
defaults should still avoid raw payload capture.

### Staging

Staging is the first supported target. Teams should explicitly enable Strata,
choose dashboard middleware, confirm retention, and review redaction settings.

### Production

Production usage is out of scope until a separate production policy exists.
Production enablement should be disabled or strongly guarded.

## Opt-In Fields

These fields require explicit, narrow opt-in configuration before capture:

- request query strings
- selected request input keys
- sanitized query bindings
- selected authenticated user identifiers
- expanded stack traces
- deployment metadata beyond configured version, commit, or release name
- retention beyond the default staging window

## Documentation Requirements

When implementation changes configuration behavior, the change must update:

- this document
- `config/strata.php` comments or defaults
- README installation or configuration notes if user-facing
- tests that prove safe defaults and category disablement
