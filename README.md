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

Strata is in foundation planning. Application code has not been written yet.

This repository is being prepared first so the project starts with clear
standards, documentation, security expectations, and product direction.

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
- [Telemetry Scope](docs/telemetry-scope.md)
- [Privacy and Security](docs/privacy-security.md)
- [Architecture Principles](docs/architecture-principles.md)
- [Compatibility](docs/compatibility.md)
- [Storage and Retention](docs/storage-retention.md)
- [Testing Strategy](docs/testing-strategy.md)
- [Definition of Done](docs/definition-of-done.md)

## Development

Strata is now in the package skeleton stage. Telemetry capture has not been
implemented yet.

Local development will use the package test harness:

```bash
composer install
composer test
```

Prototype installation path once the package is published:

```bash
composer require nerrowake/strata
php artisan vendor:publish --tag=strata-config
```

The package currently provides:

- Laravel package metadata
- auto-discovered service provider
- publishable `config/strata.php`
- isolated dashboard route and view placeholders
- Testbench-based package smoke tests

Telemetry capture, storage migrations, dashboard functionality, and release
automation are still future work.

## License

Copyright (c) 2026 Nerrowake.

All rights reserved. Product licensing should be finalized before application
code is published.
