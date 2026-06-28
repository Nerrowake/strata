# Dashboard Authentication Model

This document defines how staging users should access the Strata dashboard
safely.

## Default Access Model

The Strata dashboard must be private by default.

Initial defaults:

- dashboard routes are disabled unless explicitly enabled
- dashboard routes use host application middleware
- production access is disabled or strongly guarded by configuration
- access decisions are delegated to the host application
- shared issue context must preserve the same authorization checks

Strata should not create its own user model, password system, or role system in
the first release.

## Authorization Integration Points

The first implementation should support host-owned authorization through
configuration. Acceptable extension points include:

- configured dashboard middleware
- a Laravel gate name
- an authorization callback
- a documented policy hook if the package later needs one

The host application should own which users may view Strata. Strata should own
safe defaults, route isolation, and tests that prove unauthorized users cannot
access dashboard behavior.

## Public Exposure Risks

Dashboard exposure is security-sensitive because staging telemetry can reveal:

- request paths and route names
- exception classes and redacted messages
- SQL shapes and timing behavior
- job and scheduled task names
- environment or deployment identifiers
- operational clues useful to attackers

Even when sensitive values are redacted, the dashboard should not be public.

Dangerous defaults to avoid:

- enabling dashboard routes automatically
- allowing production access without explicit configuration
- using only obscured URLs as access control
- sharing issue links that bypass normal middleware
- exposing raw payloads in dashboard details

## Prototype Requirements

The prototype dashboard should:

- remain disabled by default
- use a configurable path
- use configured middleware
- document that stronger authorization is required before usable release
- include tests for route absence when disabled and route access when enabled

Before the first usable release, dashboard authorization should also test:

- unauthorized users cannot access the dashboard
- authorized users can access the dashboard
- shared issue context preserves authorization
- production behavior is blocked or explicitly opted in

## Recommended First Config Shape

```php
'dashboard' => [
    'enabled' => env('STRATA_DASHBOARD_ENABLED', false),
    'path' => env('STRATA_DASHBOARD_PATH', 'strata'),
    'middleware' => ['web', 'auth'],
    'gate' => null,
],
```

The exact implementation can evolve, but the default must stay private and
host-controlled.

## Revisit Criteria

Revisit this model when:

- team sharing requires named review sessions or issue links
- production support becomes an explicit goal
- package users need role-based dashboard access examples
- dashboard API endpoints are added
