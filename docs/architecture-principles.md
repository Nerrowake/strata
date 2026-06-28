# Architecture Principles

This document defines the first architecture plan for Strata before application
code begins. It should guide the package skeleton, early tests, and dashboard
prototype.

## Architecture Goal

Strata should be a Laravel package that can be installed into a staging
application, capture useful technical metadata, store it safely, and display it
in a private dashboard without changing the host application's behavior.

The first architecture should optimize for:

- clear package boundaries
- redaction before storage or display
- database-agnostic persistence through Laravel APIs
- isolated dashboard routes and assets
- graceful failure when capture, storage, or dashboard rendering fails
- simple testability before broader extensibility

## Package Boundary

Strata should be easy to install into a Laravel staging environment and easy to
remove.

The package should own:

- service provider registration
- configuration publishing
- route registration for the dashboard
- telemetry capture listeners
- event normalization and redaction
- storage contracts and database storage implementation
- dashboard controllers and views or frontend assets
- cleanup commands
- tests for package behavior

The host application should own:

- whether Strata is enabled
- which database connection Strata uses
- dashboard authorization rules
- capture category configuration
- redaction keys and patterns
- retention window
- deployment or environment identifiers

Strata should avoid modifying host application models, routes, middleware,
database settings, queues, or exception handling beyond documented listeners and
service provider registration.

## Proposed Package Structure

The first package skeleton should use a conventional Laravel package layout:

```text
config/
  strata.php
database/
  migrations/
resources/
  views/
routes/
  strata.php
src/
  Capture/
  Dashboard/
  Events/
  Redaction/
  Storage/
  Support/
  StrataServiceProvider.php
tests/
```

The exact structure can evolve, but the core separation should remain:

- `Capture` observes Laravel behavior.
- `Events` defines normalized telemetry payloads.
- `Redaction` removes or masks sensitive data.
- `Storage` persists and retrieves telemetry.
- `Dashboard` reads telemetry and presents it.
- `Support` holds shared configuration and utility code.

Capture logic should not render dashboard UI. Dashboard code should not know how
Laravel listeners collect raw data.

## Configuration Model

Strata should publish a single readable configuration file.

The first configuration surface and safe defaults are documented in
[Configuration Surface and Defaults](configuration.md).

Initial configuration areas:

- global enable or disable flag
- dashboard enable or disable flag
- dashboard path and middleware
- dashboard authorization callback or gate name
- storage driver and connection
- retention window
- capture category flags
- ignored paths, routes, jobs, queues, and scheduled tasks
- slow query and repeated-query thresholds
- redaction headers, keys, and value patterns
- optional environment or deployment identifiers

The default configuration should be safe for staging:

- dashboard private by default
- production disabled or strongly guarded
- high-risk fields redacted by default
- full payload capture unavailable by default
- bounded retention expected from the beginning

## Capture Pipeline

Telemetry capture should follow one path regardless of event source:

```text
Laravel signal -> capture listener -> normalized event -> redaction -> storage -> dashboard read model
```

The capture pipeline should:

- listen to documented Laravel events where possible
- normalize raw framework data into Strata event payloads
- redact before storage
- avoid throwing exceptions into the host application
- add enough metadata to relate events to a request or review session
- allow each telemetry category to be disabled independently

Early capture categories:

- request lifecycle
- exception metadata
- query timing and SQL shape
- slow query signals
- possible N+1 signals
- queued job lifecycle
- scheduled task lifecycle
- configured environment or deployment context

## Transport Model

The first implementation should keep transport simple.

Initial transport should be in-process:

- capture listeners normalize and redact events during the host application's
  normal execution flow
- storage writes happen through the configured Strata storage implementation
- the dashboard reads stored telemetry through application routes

This avoids introducing queues, WebSockets, or external services before the
core product is proven.

Later transport options can be evaluated when needed:

- queued event persistence for lower request overhead
- polling endpoints for dashboard refresh
- server-sent events or WebSockets for live dashboards
- export endpoints for issue context

The first architecture should not require GraphQL. GraphQL may be considered
later as an API layer if Strata needs a richer external query surface, but it is
not a storage mechanism and should not drive the first package design.

## Storage Plan

The first storage driver should be `database`.

Storage should use Laravel database APIs so the host application can choose the
database connection:

```php
'storage' => [
    'driver' => 'database',
    'connection' => env('STRATA_DB_CONNECTION', null),
],
```

If `connection` is `null`, Strata should use the application's default database
connection. Teams that want isolation can point Strata at a dedicated
connection.

The storage design should:

- avoid database-specific features in the first implementation
- work with SQLite for package tests
- validate against MySQL and PostgreSQL before first release
- keep MariaDB and SQL Server possible through Laravel-supported database APIs
- store redacted normalized telemetry, not raw framework payloads
- support retention cleanup
- support bounded dashboard reads

Database tables should be designed around append-heavy telemetry and dashboard
reads. The storage and retention decision is documented in
[Storage and Retention](storage-retention.md). The first model should likely
include:

- telemetry event identifier
- event type
- occurred timestamp
- request or session correlation identifier
- severity or status fields
- duration fields where relevant
- normalized redacted payload
- environment or deployment identifier when configured

## Dashboard Architecture

The dashboard should be private, focused, and separate from capture behavior.

The first dashboard information architecture is documented in
[Information Architecture](information-architecture.md).

The dashboard should provide:

- timeline-first event browsing
- filters for event type, status, path, job, task, and text search
- event detail panels
- visible redaction indicators
- empty, loading, and error states
- issue context links or copied summaries when safe
- clear retention and environment context

Dashboard routes should:

- be disabled when Strata is disabled
- use a configurable path
- require private middleware by default
- use an authorization gate or callback
- never bypass the host application's security expectations

The first dashboard can be server-rendered or use a lightweight frontend, but it
should not require a separate service. The package should keep assets isolated
and documented.

## Dashboard Access

Dashboard access is security-sensitive and should be explicit.

The first dashboard authentication model is documented in
[Dashboard Authentication Model](dashboard-authentication.md).

Initial rules:

- no public dashboard access by default
- no production dashboard access without explicit configuration
- dashboard authorization must be test-covered
- access failures should return normal Laravel authorization responses
- shared issue links must preserve authorization checks

The first implementation should provide a simple authorization extension point
instead of inventing its own user or role system.

## Failure Handling

Strata must not break the host application when telemetry fails.

Failure rules:

- capture failures should be swallowed or reported through a controlled internal
  channel, not thrown into user requests
- storage failures should not fail the host request
- dashboard failures should show actionable error states
- redaction failures should fail closed by dropping or masking risky fields
- missing optional services should disable the affected telemetry category
- cleanup failures should be visible to operators but not corrupt telemetry

The package should prefer losing telemetry over exposing sensitive data or
breaking staging workflows.

## Testing Strategy

Core capture behavior should be covered by automated tests before it is treated
as usable.

Important test areas:

- package boot and configuration loading
- dashboard route registration and authorization
- request capture
- query capture
- exception capture
- redaction before storage
- event storage and retrieval
- retention cleanup
- queue and scheduler telemetry
- failure handling
- SQLite package tests
- MySQL and PostgreSQL compatibility checks before first release

Testing should prove the safety contract, not only the happy path.

## First Implementation Order

The first code should be deliberately plain:

1. package skeleton and service provider
2. published configuration file
3. test harness
4. storage contract and in-memory or database-backed prototype store
5. request lifecycle capture
6. redaction service
7. dashboard route and empty shell
8. dashboard timeline reading from stored events

This keeps Strata installable and testable before adding the full telemetry
surface.

## Architecture Guardrails

- Prefer Laravel public APIs, events, middleware, gates, service providers, and
  database abstractions.
- Do not introduce external infrastructure for the first implementation.
- Do not require a specific database engine.
- Do not store raw sensitive values for later redaction.
- Do not let dashboard failures affect capture.
- Do not let capture failures affect host application behavior.
- Keep capture, redaction, storage, and dashboard code separable.
- Add abstractions only where they protect a real boundary.
