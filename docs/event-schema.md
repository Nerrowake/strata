# Telemetry Event Schema v0

This document defines the initial normalized telemetry event schema shared by
request, query, job, scheduler, and exception telemetry.

## Schema Versioning

Every stored event should include a schema version.

Initial value:

```text
v0
```

Schema changes should be additive during early development when possible. A
breaking schema change should update the version and document migration or
compatibility expectations before release.

## Common Event Fields

| Field | Required | Sensitive | Description |
| --- | --- | --- | --- |
| `schema_version` | yes | no | Event schema version, initially `v0`. |
| `id` | yes | no | Strata event identifier. |
| `type` | yes | no | Normalized event type. |
| `occurred_at` | yes | no | Timestamp when the framework signal occurred. |
| `recorded_at` | yes | no | Timestamp when Strata recorded the event. |
| `environment` | no | no | Configured environment label. |
| `deployment` | no | no | Configured release, build, or commit identifier. |
| `request_id` | no | no | Safe request correlation identifier. |
| `session_id` | no | no | Safe review-session or activity-window identifier. |
| `severity` | yes | no | `debug`, `info`, `warning`, `error`, or `critical`. |
| `status` | no | no | Event-specific status such as `ok`, `slow`, or `failed`. |
| `duration_ms` | no | no | Event duration when known. |
| `summary` | yes | no | Short redacted dashboard summary. |
| `payload` | yes | mixed | Normalized event-specific metadata. |
| `redactions` | yes | no | Names of fields redacted or excluded. |

The `payload` field must contain redacted normalized metadata, never raw
framework payloads for later cleanup.

The prototype collector accepts associative arrays that expose these fields
directly while the package is still validating the capture pipeline. Capture
sources must include `type`, `occurred_at`, safe event-specific fields, and a
`redactions` list that names omitted sensitive fields.

## Event Types

Initial event types:

- `request.started`
- `request.completed`
- `exception.captured`
- `query.executed`
- `query.slow`
- `query.possible_n_plus_one`
- `job.queued`
- `job.started`
- `job.completed`
- `job.failed`
- `schedule.started`
- `schedule.completed`
- `schedule.failed`

## Per-Event Required Fields

### Requests

Required payload fields:

- method
- path
- route name when available
- response status for completed requests
- duration for completed requests

Sensitive or excluded by default:

- request body
- uploaded files
- cookies
- authorization headers
- session values

### Exceptions

Required payload fields:

- exception class
- redacted message or message marker
- safe frame summary when configured
- related request identifier when available

Sensitive or excluded by default:

- request body
- cookies
- tokens
- environment secrets
- local file contents beyond safe stack metadata

### Queries

Required payload fields:

- SQL shape
- connection name
- duration
- slow-query threshold outcome

Sensitive or excluded by default:

- raw bindings
- credentials
- personally identifying values

### Possible N+1 Signals

Required payload fields:

- repeated SQL shape
- repeated count
- threshold used
- related request identifier when available

Sensitive or excluded by default:

- raw bindings
- model attributes
- relationship data

### Jobs

Required payload fields:

- job class
- queue name
- lifecycle state
- duration when known
- failure status when failed

Sensitive or excluded by default:

- serialized job payload
- model attributes
- secrets embedded in payloads

### Scheduled Tasks

Required payload fields:

- command or task name
- lifecycle state
- duration when known
- exit status or failure state

Sensitive or excluded by default:

- command output
- environment variables
- arguments marked sensitive

## Redaction Requirements

Redaction must happen before storage or streaming to the dashboard. Display-time
redaction is not sufficient.

Event payloads should include redaction metadata so dashboard users can tell
when values were masked or intentionally excluded.

## Storage Notes

The first storage model should support:

- append-heavy writes
- ordering by occurred time
- filtering by type, status, severity, path, job, task, and text search over
  safe metadata
- retention cleanup by recorded or occurred timestamp
- bounded dashboard reads

The schema should stay database-agnostic and avoid first-release dependence on
database-specific JSON indexing.
