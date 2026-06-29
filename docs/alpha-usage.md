# Alpha Usage and Limitations

This document explains how to use the Strata alpha safely in local and staging
Laravel applications.

## Install

Until Strata is published, install it from a local Composer path repository in a
Laravel application:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../strata"
    }
  ]
}
```

Then require the package and publish the configuration:

```bash
composer require nerrowake/strata:@dev
php artisan vendor:publish --tag=strata-config
```

Enable Strata explicitly:

```env
STRATA_ENABLED=true
STRATA_DASHBOARD_ENABLED=true
STRATA_MAX_EVENTS=500
STRATA_RETENTION_HOURS=24
```

The dashboard route defaults to `/strata`. The default dashboard middleware is
`['web', 'auth']`, and a host-owned gate can be configured with
`strata.dashboard.gate`.

## Supported Alpha Workflows

The alpha supports a private, server-rendered dashboard for staging telemetry:

- request start and completion events
- exception capture with redacted messages by default
- database query capture with SQL shapes and no raw bindings
- slow query indicators
- cautious possible N+1 signals based on repeated SQL shapes
- queued, started, completed, and failed job lifecycle events
- scheduled task start, completion, duration, and failure events
- configurable review session identifiers
- timeline filtering by event type, method, status, session, and safe search
- prototype retention pruning with `php artisan strata:prune`

## Privacy Warnings

Treat staging data as sensitive. The alpha is designed to avoid high-risk fields
by default, but teams should still keep the dashboard private and review
configuration before sharing access.

Excluded or redacted by default:

- request bodies
- cookies and authorization headers
- uploaded files
- raw query bindings
- serialized job payloads
- scheduled task output
- exception messages
- environment variables

Do not expose the dashboard publicly. Use host application authentication,
middleware, and gates to limit access to trusted staging users.

## Unsupported Workflows

Do not rely on the alpha for:

- production telemetry
- durable database-backed storage
- public issue links or unauthenticated sharing
- sanitized request body capture
- raw query binding capture
- full stack trace capture without explicit configuration
- queue payload inspection
- scheduled task output capture
- long-term analytics or reporting

## Known Limitations

- The default `memory` storage driver is process-local and resets when the PHP
  process restarts.
- Retention cleanup only prunes the current in-memory store.
- Dashboard filters are server-rendered.
- Session grouping is configured through safe session identifiers; automatic
  review-window creation is intentionally deferred.
- N+1 detection is heuristic only. It marks repeated SQL shapes as possible
  evidence and does not infer model relationships.
