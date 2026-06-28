# Storage and Retention

This document defines Strata's initial storage assumptions, retention defaults,
cleanup behavior, and privacy controls for staging telemetry.

## Storage Decision

The first Strata storage driver should be `database`.

The related configuration shape is documented in
[Configuration Surface and Defaults](configuration.md).

Strata is a Laravel package, so it should store telemetry through Laravel's
database abstractions instead of forcing a specific database engine.

Initial configuration shape:

```php
'storage' => [
    'driver' => 'database',
    'connection' => env('STRATA_DB_CONNECTION', null),
],
```

If `connection` is `null`, Strata should use the host application's default
database connection. If a team wants isolation, it can configure a dedicated
Strata database connection.

## Database Compatibility

The storage layer should avoid database-specific behavior in the first release.

First-release validation targets:

- SQLite for package tests and lightweight local development
- MySQL 8.0 or newer for common Laravel staging environments
- PostgreSQL 14 or newer for teams using PostgreSQL in staging

MariaDB and SQL Server should remain possible through Laravel-supported database
APIs, but they are not first-release blocking validation targets.

## Stored Data

Strata should store normalized, redacted telemetry events. It should not store
raw framework payloads for later processing.

Each stored event should be shaped around dashboard reads and cleanup:

- event identifier
- event type
- occurred timestamp
- request or session correlation identifier
- severity or status fields
- duration fields where relevant
- environment or deployment identifier when configured
- normalized redacted payload
- creation timestamp

Sensitive values must be redacted before storage. Redaction at display time is
not enough.

## Data Not Stored by Default

Strata should not store these values by default:

- full request bodies
- uploaded files
- cookies
- authorization headers
- raw query bindings
- serialized job payloads
- scheduler command output
- environment variables
- secrets, tokens, passwords, keys, or credentials
- payment data
- personal data unless a team explicitly configures a safe identifier

## Retention Defaults

Telemetry should be treated as temporary staging review data.

Initial retention default:

- retain telemetry for 24 hours

Rationale:

- staging review sessions are usually short lived
- teams need enough time to inspect issues after a QA or client pass
- long retention increases privacy risk and storage noise

Teams should be able to configure the retention window. Longer retention should
be explicit and documented as an opt-in privacy decision.

Suggested configuration shape:

```php
'retention' => [
    'enabled' => true,
    'hours' => 24,
],
```

## Cleanup Behavior

Strata should provide a cleanup command in the first database-backed
implementation.

Expected command behavior:

- delete telemetry older than the configured retention window
- support a dry-run mode before deletion
- report how many events would be or were removed
- fail safely if storage is unavailable
- avoid deleting telemetry newer than the retention boundary
- preserve redaction guarantees in all summaries and logs

Suggested command name:

```text
php artisan strata:prune
```

The package should document how teams can schedule cleanup in Laravel's
scheduler, but it should not silently register destructive cleanup behavior
without making it visible.

## Manual Clearing

Teams should be able to clear Strata telemetry from staging when needed.

Expected behavior:

- clear all stored Strata telemetry after confirmation
- support targeted clearing by environment or session later if needed
- avoid touching host application tables outside Strata-owned storage

Suggested command name:

```text
php artisan strata:clear
```

The clear command should require an explicit confirmation or force flag.

## Privacy Controls

Storage must follow the telemetry and privacy policies:

- redact before storage
- store only normalized event data
- allow telemetry to be disabled globally
- allow every telemetry category to be disabled independently
- allow ignored paths, routes, jobs, queues, and scheduled tasks
- allow teams to configure redaction headers, keys, and value patterns
- preserve redaction in shared issue context
- prefer losing telemetry over storing sensitive data

## Storage Failure Behavior

Strata should not break the host application when storage fails.

Failure rules:

- request handling should continue if telemetry cannot be stored
- storage failures should be surfaced in controlled diagnostics
- dashboard reads should show actionable empty or error states
- cleanup failures should report the failure without corrupting telemetry
- redaction failures should fail closed by dropping or masking risky fields

## Revisit Criteria

Revisit storage and retention when:

- event volume makes database writes too expensive for staging workflows
- dashboard reads need indexing or pagination changes
- users need a dedicated external telemetry store
- queue-backed persistence becomes necessary for lower request overhead
- production support becomes an explicit product goal
- privacy requirements require shorter default retention
