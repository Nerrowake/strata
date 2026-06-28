# Privacy and Security

Strata will inspect staging behavior, so privacy and security must be designed
into the first implementation.

## Privacy Position

Strata should be redaction-first. The product should make staging behavior
visible without turning staging into a warehouse of user-submitted content,
secrets, or personal data.

Default behavior should assume:

- staging data can still contain real or sensitive information
- clients and QA testers may enter private information during review
- logs, query bindings, headers, cookies, and job payloads are high risk
- teams need useful context, not unrestricted capture

## Baseline Requirements

- Redact sensitive headers by default.
- Avoid capturing full request bodies by default.
- Avoid capturing raw query bindings by default.
- Make telemetry capture explicit and configurable.
- Make dashboard access private by default.
- Document data retention clearly.
- Provide safe defaults for local and staging environments.
- Apply redaction before telemetry is stored or streamed to the dashboard.
- Make opt-in capture visible in configuration and documentation.
- Ensure every telemetry category can be disabled independently.

## Dangerous Defaults to Avoid

- public dashboard access
- full payload capture without opt-in
- token or cookie logging
- long retention without visibility
- production enablement without clear warnings
- silent capture of personal data
- raw query binding capture
- serialized job payload capture
- unbounded telemetry retention
- broad "capture everything" modes

## Redacted by Default

The first implementation must redact or exclude these values by default:

- `Authorization`, `Cookie`, `Set-Cookie`, `X-CSRF-TOKEN`, `X-XSRF-TOKEN`, and
  similar sensitive headers
- bearer tokens, API keys, session IDs, CSRF tokens, and password reset tokens
- password, password confirmation, secret, token, key, and credential fields
- full request bodies
- uploaded files
- raw database query bindings
- serialized job payloads
- command output from scheduled tasks
- environment variables
- payment data
- personal data unless a team has explicitly configured a safe identifier

Redaction should replace values with a stable marker such as `[redacted]` rather
than dropping fields silently when the field name itself is useful for triage.

## Allowed by Default

Default telemetry may include:

- timestamps
- event type
- request method, path, and route name
- response status and duration
- exception class and redacted message
- SQL shape without raw bindings
- query duration and connection name
- query count and repeated-query evidence
- job class, queue, lifecycle state, and duration
- scheduled task name, lifecycle state, exit status, and duration
- configured environment or deployment identifier

Default telemetry should be enough to understand system behavior without storing
the submitted contents of the workflow.

## Opt-In Only

These fields require explicit opt-in configuration:

- request query strings
- selected request input keys
- sanitized query bindings
- selected authenticated user identifiers
- expanded stack traces
- deployment metadata beyond a configured version, commit, or release name
- retention beyond the default staging window

Opt-in settings should be narrow and named. For example, allowlisting specific
request input keys is safer than enabling every request body field.

## Telemetry Controls

Teams must be able to disable or narrow capture without changing package code.

Required controls:

- global telemetry enable or disable flag
- dashboard enable or disable flag
- per-category capture flags for requests, exceptions, queries, N+1 signals,
  jobs, scheduled tasks, and deployment context
- ignored paths and route names
- ignored job classes and queue names
- ignored scheduled task names
- configurable slow-query and repeated-query thresholds
- configurable redaction keys, header names, and value patterns
- configurable retention window and cleanup behavior

Safe defaults should be documented beside the configuration file once
implementation begins.

## Storage and Retention Expectations

Telemetry should be treated as temporary staging review data.

- Retention should be bounded by default.
- Cleanup behavior should be documented and test-covered.
- Sensitive values should be redacted before storage, not only before display.
- Exported or shared issue context should preserve redaction.
- Teams should be able to clear captured telemetry from a staging environment.

The initial storage and retention decision is documented in
[Storage and Retention](storage-retention.md).

## Foundation Decisions and Follow-Ups

Documented foundation decisions:

- Authentication model for the dashboard is documented in
  [Dashboard Authentication Model](dashboard-authentication.md)

Remaining follow-up decisions:

- Safe user identity representation
- Team-sharing model for issue links
- Whether production usage is unsupported, discouraged, or separately licensed
