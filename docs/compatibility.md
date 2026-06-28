# Compatibility

This document defines the initial framework, runtime, and database support
targets for Strata before package code begins.

## Current Decision

Strata will start as a latest-first Laravel package.

Initial targets:

- Laravel: 13.x
- PHP: 8.5
- Composer package constraint: `laravel/framework:^13.0`
- PHP package constraint: `php:^8.5`

This keeps the first implementation focused on the current Laravel generation
and avoids carrying compatibility branches before the product shape is proven.

## Source Baseline

This decision was checked against official documentation on June 28, 2026:

- Laravel 13.x is the current Laravel documentation track.
- Laravel 13 supports PHP 8.3 through PHP 8.5.
- PHP 8.5 is an actively supported PHP branch.
- Laravel 13 lists first-party database support for MariaDB, MySQL,
  PostgreSQL, SQLite, and SQL Server.

References:

- [Laravel release notes](https://laravel.com/docs/13.x/releases)
- [Laravel database documentation](https://laravel.com/docs/13.x/database)
- [PHP supported versions](https://www.php.net/supported-versions.php)

Strata intentionally chooses PHP 8.5 as the first supported runtime even though
Laravel 13 can run on older PHP versions. Older PHP support can be added later
only if it does not make the package harder to maintain.

## Database Support

Strata should observe Laravel database activity without requiring teams to move
away from their existing Laravel-supported database driver.

### First-Release Validation Targets

The first release should validate Strata against:

- SQLite for fast package tests and local CI
- MySQL 8.0 or newer for common Laravel staging environments
- PostgreSQL 14 or newer for teams using PostgreSQL in staging

These are the first databases that should block release quality. They cover the
most likely local, CI, and staging workflows while keeping the test matrix
small enough to maintain.

### Supported but Not First-Release Blocking

These drivers are part of Laravel's first-party database support, but they
should not block the first Strata release unless a real user workflow depends on
them:

- MariaDB
- SQL Server

Strata should avoid design choices that make these drivers impossible, but full
validation can wait until there is a practical reason to support them.

## Compatibility Guardrails

- Prefer Laravel public APIs, documented events, and service provider patterns.
- Avoid relying on internal framework behavior unless there is no stable public
  alternative.
- Keep PHP 8.5 features acceptable in Strata code.
- Do not add compatibility shims for older Laravel or PHP versions during the
  initial package skeleton.
- Keep the database test matrix small until core telemetry behavior is stable.
- Document any driver-specific telemetry behavior before release.

## Revisit Criteria

Revisit this decision when:

- Laravel 14 becomes the current major version.
- PHP 8.6 becomes stable and supported by Laravel.
- a real staging user needs Laravel 12 or PHP 8.4 support.
- database telemetry behaves differently across supported drivers.
- package adoption is blocked by the narrow initial support window.
