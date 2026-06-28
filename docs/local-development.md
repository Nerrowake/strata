# Local Development and Example App Strategy

This document defines how contributors should run Strata against realistic
Laravel behavior during development.

## Development Modes

Strata should support two development modes.

### Package Test Harness

The package test harness is the default development path for fast feedback. It
uses Orchestra Testbench to exercise package bootstrapping, configuration,
routes, listeners, storage contracts, and feature behavior without maintaining a
full application in the repository.

Required services:

- PHP 8.5
- Composer dependencies
- SQLite for fast package tests

Primary commands:

```bash
composer install
composer test
```

### Example Laravel Application

An example app should be added when package behavior needs realistic manual
verification beyond Testbench. The example app should use a local path
repository or workbench-style setup so Strata can be tested as an installed
package without publishing it first.

The example app should be documented before it becomes release-blocking. It
should not become the only way to run automated tests.

## Example App Requirements

The example app should cover these services and workflows when introduced:

- web requests with named routes and common HTTP statuses
- authentication with at least one authorized dashboard user and one
  unauthorized user
- database queries, slow query examples, and repeated query examples
- queued jobs, completed jobs, and failed jobs
- scheduled tasks, completed tasks, and failed tasks
- exceptions and warnings
- configurable environment and deployment identifiers

## Seed Scenarios

Seeded scenarios should be small, deterministic, and privacy-safe:

- a successful checkout-like request with multiple related queries
- a request that triggers a handled exception
- a request that produces repeated query shapes for possible N+1 evidence
- a queued job that succeeds
- a queued job that fails without storing serialized payloads
- a scheduled task that succeeds
- a scheduled task that fails without storing command output
- an unauthorized dashboard access attempt

No seed scenario should require real customer, payment, token, or credential
data.

## Database Strategy

SQLite remains the default for package tests and lightweight local development.
Before the first release, MySQL 8.0 or newer and PostgreSQL 14 or newer should
be validated through CI, release checklist, or documented manual verification.

MariaDB and SQL Server should remain possible through Laravel database APIs but
should not block the first release unless a real staging workflow depends on
them.

## Queue Strategy

Automated tests should start with Laravel's sync or fake queue behavior where it
proves package behavior clearly. The example app should add a realistic queue
worker path once job telemetry is implemented.

Queue tests must prove lifecycle capture without storing serialized job payloads
or model attributes.

## Scheduler Strategy

Scheduler behavior should be covered through package tests where possible and
manual example-app checks where visual or operator behavior matters.

Scheduled task telemetry must not store command output or sensitive arguments by
default.

## Auth Strategy

Dashboard auth tests should prove:

- dashboard routes are absent or inaccessible when disabled
- unauthorized users cannot access the dashboard
- authorized users can access the dashboard
- shared issue context preserves authorization

The example app should demonstrate the recommended middleware and authorization
extension point, not invent a Strata-specific user system.

## Guardrails

- Keep the package test harness fast and authoritative.
- Add the example app only when it improves realistic verification.
- Keep seed data synthetic and safe.
- Do not require external infrastructure for the first implementation.
- Document any manual checks that cannot be proven automatically.
