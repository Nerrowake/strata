# Tests

Strata has a package smoke test suite for the first Laravel package skeleton.

The required test layers and coverage areas are defined in
[Testing Strategy](../docs/testing-strategy.md).

Current commands:

```bash
composer install
composer test
```

Current coverage:

- package service provider registration through Testbench
- default config merging
- dashboard route disabled by default
- dashboard route registration when enabled
- prototype dashboard shell rendering for filters, empty state, timeline, and
  redacted event detail
- query telemetry listener capture through Laravel database events
- SQL shape storage without raw bindings
- slow query indicators
- first repeated-query threshold signal for possible N+1 patterns

Future tests should add the capture, redaction, storage, queue, scheduler,
failure handling, compatibility, and release smoke coverage described in the
testing strategy.
