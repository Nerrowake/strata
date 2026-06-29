<p align="center">
  <img src="assets/brand/strata-logo-16x9.png" alt="Strata - real-time staging telemetry for Laravel teams" width="100%">
</p>

<h1 align="center">Strata</h1>

<p align="center">
  <strong>Real-time staging telemetry for Laravel teams.</strong>
</p>

<p align="center">
  See requests, queries, jobs, scheduled tasks, and staging issues while QA
  testers, clients, and developers use the environment.
</p>

---

## Status

Strata is in early package development. The repository includes foundation
documentation, a Laravel package skeleton, a prototype dashboard shell, and
early telemetry capture experiments.

## What Strata Will Do

Strata is planned as a real-time staging telemetry dashboard for Laravel teams.
It will help teams observe what happens inside a staging environment while real
people interact with it.

Planned telemetry areas:

- HTTP requests
- database queries
- slow queries
- N+1 query patterns
- queued jobs
- failed jobs
- scheduled tasks
- exceptions and warnings
- deployment and environment context

## Who It Is For

Strata is for Laravel teams that need staging feedback during QA, client review,
internal demos, and pre-release validation.

It should help:

- developers find issues quickly
- QA testers report behavior with better context
- technical leads understand staging health
- clients review work without needing local dev tools
- teams reduce "works on my machine" ambiguity

## Product Principles

- Make staging behavior visible.
- Keep sensitive data out by default.
- Prefer clear timelines over noisy dashboards.
- Make issue context easy to share.
- Treat documentation as part of the product.
- Optimize for correctness and trust before performance polish.

## Repository Contents

- [Brand Assets](assets/brand)
- [Roadmap](ROADMAP.md)
- [Changelog](CHANGELOG.md)
- [Contributing](CONTRIBUTING.md)
- [Security](SECURITY.md)
- [Support](SUPPORT.md)
- [Product Brief](docs/product-brief.md)
- [Personas and Staging Workflows](docs/personas-workflows.md)
- [Product Requirements](docs/product-requirements.md)
- [Telemetry Scope](docs/telemetry-scope.md)
- [Telemetry Event Schema](docs/event-schema.md)
- [Privacy and Security](docs/privacy-security.md)
- [Architecture Principles](docs/architecture-principles.md)
- [Information Architecture](docs/information-architecture.md)
- [Dashboard Authentication](docs/dashboard-authentication.md)
- [Configuration Surface and Defaults](docs/configuration.md)
- [Compatibility](docs/compatibility.md)
- [Storage and Retention](docs/storage-retention.md)
- [Package Naming and Distribution](docs/package-distribution.md)
- [Local Development](docs/local-development.md)
- [Testing Strategy](docs/testing-strategy.md)
- [Definition of Done](docs/definition-of-done.md)
- [Decision Log](docs/decisions)

## Development

Strata is now in the package skeleton and prototype telemetry stage.

Local development uses the package test harness:

```bash
composer install
composer format -- --test
composer test
```

The CI workflow runs those same formatting and test commands on pull requests.

## Prototype Installation

Prototype installation path once the package is published:

```bash
composer require nerrowake/strata
php artisan vendor:publish --tag=strata-config
```

For local package development before publication, use a Composer path
repository from a Laravel application:

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

Then require the package from the application:

```bash
composer require nerrowake/strata:@dev
php artisan vendor:publish --tag=strata-config
```

Enable the prototype explicitly in `config/strata.php` or environment values:

```env
STRATA_ENABLED=true
STRATA_DASHBOARD_ENABLED=true
STRATA_MAX_EVENTS=500
```

The dashboard route defaults to `/strata` and uses the configured middleware.
The published config defaults to `['web', 'auth']`; keep the dashboard private
in staging and use host application auth rules for access.

The package currently provides:

- Laravel package metadata
- auto-discovered service provider
- publishable `config/strata.php`
- isolated dashboard route and prototype timeline/detail shell
- Testbench-based package smoke tests
- internal telemetry collector contract with safe failure behavior
- bounded in-memory prototype event storage
- prototype request lifecycle capture for method, path, route, status, and
  duration
- prototype query capture with SQL shape redaction, slow-query flags, and
  possible repeated-query evidence

Known prototype limitations:

- Storage is in-memory and process-local; events reset when the PHP process
  restarts.
- Dashboard filtering is server-rendered and intended for early validation.
- Query and request capture are implemented first; jobs, scheduled tasks, and
  exception capture are still future work.
- Production usage is out of scope until a separate production policy exists.

## License

Copyright (c) 2026 Nerrowake.

Strata core is open-source software licensed under the
[Apache License 2.0](LICENSE.md). Future commercial or hosted features may be
licensed separately.
