# Testing Strategy

This document defines the required test layers for Strata before application
code begins. It should guide the package test harness, CI workflow, and release
readiness checks.

## Testing Goal

Strata should earn trust before it earns features.

The local development and example app strategy is documented in
[Local Development and Example App Strategy](local-development.md).

The test suite should prove that Strata:

- captures useful Laravel staging telemetry
- redacts sensitive data before storage or display
- protects dashboard access
- stores and retrieves normalized events correctly
- respects capture configuration
- works with the first supported database targets
- fails without breaking the host application

## Test Layers

Strata should use several focused test layers instead of one broad end-to-end
suite.

### Unit Tests

Use unit tests for isolated behavior that does not need a Laravel application.

Required coverage:

- telemetry event normalization
- redaction keys, headers, and value patterns
- opt-in field allowlists
- duration and threshold calculations
- storage query objects or payload builders
- configuration value objects or helpers
- failure-path helpers

Unit tests should be fast and should run on every pull request.

### Package Integration Tests

Use Laravel package integration tests for behavior that depends on Laravel
bootstrapping, events, routes, database access, or configuration.

Required coverage:

- service provider registration
- config publishing and default values
- route registration when dashboard is enabled
- route absence when dashboard is disabled
- dashboard authorization
- database connection selection
- migration loading
- command registration
- event listener registration

The first implementation should use a package test harness compatible with
Laravel 13 and PHP 8.5.

### Feature Tests

Use feature tests for user-observable Strata behavior inside a test Laravel
application.

Required coverage:

- request telemetry appears after a request
- exceptions are captured safely
- query telemetry is captured without raw bindings
- slow query thresholds are respected
- possible N+1 evidence is grouped cautiously
- jobs emit lifecycle telemetry
- scheduled tasks emit lifecycle telemetry
- dashboard timeline can read stored events
- filters narrow event results
- retention cleanup removes only expired telemetry

Feature tests should prefer realistic workflows over testing implementation
details.

### Compatibility Tests

Compatibility tests should prove that Strata works with the first-release
runtime and database targets.

Required targets:

- PHP 8.5
- Laravel 13.x
- SQLite for fast package tests and local CI
- MySQL 8.0 or newer before first release
- PostgreSQL 14 or newer before first release

MariaDB and SQL Server should remain possible through Laravel-supported database
APIs, but they are not first-release blocking test targets.

### Manual Verification

Manual verification should be documented when behavior is hard to prove fully
through automated tests.

Manual checks may be appropriate for:

- dashboard visual hierarchy
- loading, empty, and error states
- copied issue context
- staging review workflow notes
- browser-specific UI behavior

Manual checks should include screenshots or visual notes when the UI changes.

## Required Coverage Areas

### Capture Behavior

Tests should prove that each capture category can be enabled, disabled, and
narrowed without changing package code.

Required categories:

- request lifecycle
- exception metadata
- query timing and SQL shape
- slow query signals
- possible N+1 signals
- queued job lifecycle
- scheduled task lifecycle
- configured environment or deployment context

Capture tests must verify that disabled categories do not record events.

### Redaction

Redaction tests are release-blocking.

Tests must prove that Strata redacts or excludes:

- authorization headers
- cookies
- CSRF tokens
- API keys and bearer tokens
- password-like fields
- full request bodies
- uploaded files
- raw query bindings
- serialized job payloads
- environment variables
- configured custom redaction keys and patterns

Tests should verify redaction before storage, not only before display.

### Dashboard Authorization

Dashboard authorization tests are release-blocking.

Tests must prove:

- dashboard routes are unavailable when disabled
- unauthenticated or unauthorized users cannot access the dashboard
- authorized users can access the dashboard
- production access requires explicit configuration if production support is
  ever allowed
- shared issue links preserve authorization checks

### Event Storage

Storage tests should prove that normalized events can be stored, queried, and
cleaned up safely.

Tests must cover:

- configured database connection selection
- event insertion
- event ordering by occurrence time
- bounded reads for dashboard timelines
- event type filtering
- retention cleanup boundaries
- storage failure behavior
- SQLite behavior in the default package test suite

MySQL and PostgreSQL checks should be included before the first release.

### Queues

Queue tests should prove lifecycle capture without storing sensitive payloads.

Tests must cover:

- job queued
- job started
- job completed
- job failed
- ignored job classes
- ignored queues
- payload redaction or exclusion
- behavior when queue telemetry is disabled

### Scheduler Telemetry

Scheduler tests should prove scheduled task lifecycle capture without exposing
command output or sensitive arguments.

Tests must cover:

- task started
- task completed
- task failed
- ignored task names
- exit status or failure metadata
- behavior when scheduler telemetry is disabled

### Failure Handling

Failure tests should prove that Strata does not break the host application.

Tests must cover:

- storage unavailable during capture
- redaction failure behavior
- dashboard storage read failures
- cleanup command failures
- missing optional services
- invalid configuration

The expected behavior is to preserve the host application and fail closed for
privacy.

## CI Expectations

The first CI workflow should run:

- dependency installation
- code style checks when tooling is selected
- static analysis when tooling is selected
- unit tests
- package integration tests
- SQLite feature tests

Before the first release, CI or a release checklist should also validate:

- MySQL 8.0 or newer
- PostgreSQL 14 or newer
- clean install in a Laravel 13 application

## Test Documentation

Every new telemetry feature should document:

- what behavior is tested automatically
- what behavior requires manual verification
- what data is captured
- what data is redacted
- what configuration disables or narrows the feature
- how failure behavior is tested

`tests/README.md` should list the actual commands once the package skeleton and
test harness exist.

## Definition of Done Link

A Strata feature is not complete until the relevant tests exist or the absence
of a test is explicitly justified. Security, privacy, dashboard authorization,
redaction, and storage failure paths should not be treated as optional polish.
