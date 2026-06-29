# Tests

Strata has a package smoke test suite for the first Laravel package skeleton.

The required test layers and coverage areas are defined in
[Testing Strategy](../docs/testing-strategy.md).

Current commands:

```bash
composer install
composer format -- --test
composer test
```

Current coverage:

- package service provider registration through Testbench
- default config merging
- dashboard route disabled by default
- dashboard route registration when enabled
- prototype dashboard shell rendering for filters, empty state, timeline, and
  redacted event detail
- dashboard timeline filtering by method, status, route/path, and search text
- selected request event detail rendering
- collector contract binding and safe failure behavior
- bounded in-memory event storage with newest-first reads
- request lifecycle capture for successful and failing web requests
- request metadata storage without query strings, headers, cookies, bodies, or
  uploaded files
- query telemetry listener capture through Laravel database events
- SQL shape storage without raw bindings
- slow query indicators
- first repeated-query threshold signal for possible N+1 patterns
- exception telemetry with redacted messages
- job lifecycle telemetry without payload capture
- scheduled task telemetry without command output capture
- review session start/end grouping
- dashboard access gates
- event type, session, and safe metadata search filters
- retention pruning boundaries
- redaction coverage for headers, cookies, tokens, request bodies, query
  bindings, and dashboard rendering

Future tests should add durable storage, broader compatibility, and release
smoke coverage described in the testing strategy.
