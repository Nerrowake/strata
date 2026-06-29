# Telemetry Scope

Strata should capture staging telemetry that helps teams understand what happened
during a QA or review session.

## Capture Policy

Strata may capture technical metadata that helps a Laravel team understand a
staging workflow. It should avoid content capture unless a team explicitly opts
in after understanding the risk.

The default capture model is:

- capture event timing, names, identifiers, counts, and status fields
- redact secrets and sensitive values before events are stored or displayed
- avoid full payloads, raw query bindings, cookies, tokens, and authorization
  values by default
- allow every telemetry category to be disabled independently
- allow high-risk fields only through explicit opt-in configuration
- treat production usage as out of scope until a separate production policy
  exists

## Planned Event Types

The initial normalized event schema is documented in
[Telemetry Event Schema v0](event-schema.md).

- request started
- request completed
- exception captured
- query executed
- slow query detected
- possible N+1 pattern detected
- job queued
- job started
- job completed
- job failed
- scheduled task started
- scheduled task completed
- scheduled task failed

## Event Context

Useful context may include:

- timestamp
- request method and path
- route name
- response status
- duration
- authenticated user identifier, if safely configured
- query count
- job class
- queue name
- scheduler command
- environment name
- deployment identifier

## Event Category Details

Each telemetry category should document what it captures, what it redacts, and
how it can be disabled or narrowed.

| Category | Captured by default | Redacted or excluded by default | Controls |
| --- | --- | --- | --- |
| Requests | method, path, route name, status, duration, timestamp, safe request identifier | request body, cookies, authorization headers, session values, uploaded files | enable or disable request capture; ignore paths; choose whether query strings are captured |
| Exceptions | exception class, message, safe frame summary, request identifier, timestamp | request body, cookies, tokens, environment secrets, local file contents beyond stack metadata | enable or disable exception capture; configure stack depth; redact messages by pattern |
| Queries | SQL shape, connection name, duration, query count, request identifier | raw bindings, credentials, personally identifying values | enable or disable query capture; set slow-query threshold; opt in to sanitized bindings later if needed |
| N+1 signals | repeated query shape, count, related request, threshold evidence | raw bindings and model data | enable or disable N+1 detection; configure thresholds |
| Jobs | job class, queue, lifecycle state, duration, failure status, timestamp | serialized job payloads, model attributes, secrets in payloads | enable or disable job telemetry; ignore job classes or queues |
| Scheduled tasks | command or task name, lifecycle state, duration, exit status, timestamp | command arguments marked sensitive, environment variables, command output | enable or disable scheduler telemetry; ignore task names |
| Environment context | configured environment name, app version or deployment identifier when provided | server secrets, full environment variables, infrastructure credentials | choose which identifiers are provided; disable deployment context |

## Request Telemetry Plan

The first request telemetry implementation records safe lifecycle metadata for
web requests when Strata and request capture are enabled.

Initial request fields:

- lifecycle event name: `request.started` or `request.completed`
- HTTP method
- path without query string
- route name when Laravel resolved one
- response status for completed requests
- duration in milliseconds for completed requests
- failure marker for exceptions or server-error responses
- redaction markers for excluded bodies, headers, cookies, and uploaded files

Redaction safeguards:

- request bodies are excluded from stored events
- headers and cookies are excluded from stored events
- query strings are excluded unless a future explicit opt-in adds them
- request capture can be disabled with `capture.requests`
- paths and route names can be excluded with `ignore.paths` and
  `ignore.routes`
- telemetry failures are swallowed so host requests continue normally

## Query Telemetry Plan

The first query telemetry implementation records Laravel database query events
as normalized SQL shapes. It stores timing metadata and connection name, but it
does not store raw bindings.

Initial query fields:

- SQL shape with inline string and numeric literals masked
- connection name
- duration in milliseconds
- slow-query indicator based on `thresholds.slow_query_ms`
- repeated-query count for the current in-process telemetry window
- possible N+1 indicator when a SQL shape reaches
  `thresholds.repeated_query_count`
- binding redaction marker

The first N+1 approach is intentionally cautious. It does not claim model or
relationship intent. It only marks repeated SQL shapes as possible N+1 evidence
once the configured threshold is reached, so the dashboard can point developers
toward something worth inspecting without overstating certainty.

Redaction safeguards:

- raw bindings are excluded from stored events
- inline quoted strings and numeric literals are masked in SQL shapes
- query capture can be disabled with `capture.queries`
- possible N+1 detection can be disabled with `capture.n_plus_one`
- slow and repeated-query thresholds are configurable

## Default Capture

The first implementation should default to the smallest useful staging signal:

- request lifecycle metadata
- response status and duration
- exception metadata
- database query timing and SQL shape
- slow query flags
- possible N+1 pattern evidence
- job lifecycle state
- scheduled task lifecycle state
- configurable environment or deployment identifier

Default capture should be enough to reconstruct what happened without storing
the contents of what users submitted.

## Opt-In Capture

The following must require explicit opt-in configuration:

- request query strings
- sanitized request input keys
- sanitized exception messages if message redaction is enabled by default
- sanitized query bindings
- selected authenticated user identifiers
- selected deployment metadata beyond a configured version or commit
- longer retention windows

Opt-in capture should be narrow, named, documented, and test-covered. A broad
"capture everything" switch should not exist in the first release.

## Sensitive Data

Strata should not collect sensitive data by default.

High-risk data includes:

- passwords
- tokens
- cookies
- API keys
- authorization headers
- personal data
- payment data
- full request bodies
- full query bindings
- uploaded files
- session values
- environment variables
- secrets stored in job payloads
- private infrastructure identifiers

## Review Rule

Every telemetry category must document what it captures, why it is needed, how it
is redacted, and how teams can disable or narrow it.

## Disable and Narrowing Requirements

Teams should be able to reduce telemetry without editing package code.

Required controls:

- disable all Strata telemetry
- disable dashboard routes
- disable each telemetry category independently
- ignore request paths and route names
- ignore job classes and queue names
- ignore scheduled task names
- configure slow query and N+1 thresholds
- configure retention and cleanup behavior
- configure redaction keys, header names, and value patterns

Configuration should be readable in a single published config file once
implementation begins.
