# Information Architecture

This document defines how Strata should organize environments, review sessions,
timelines, events, filters, and detail views in the first release.

## Navigation Model

The first dashboard should use a focused workbench model:

1. Environment context
2. Review timeline
3. Event filters
4. Event detail
5. Safe issue context

Strata should avoid deep navigation until the core staging workflow is proven.
The first dashboard can live at a single configurable route with internal
sections for scanning, filtering, and inspecting events.

## Primary Dashboard Sections

### Environment Header

Shows the configured environment name, deployment identifier when provided,
retention window, and dashboard access state. This makes it clear which staging
system the team is inspecting.

### Timeline

The timeline is the primary view. It lists normalized telemetry events newest
first by default, with enough summary data to scan quickly:

- occurred time
- event type
- severity or status
- request, path, job, task, or query shape summary
- duration or count when useful
- redaction indicators when detail fields are masked

### Filters

Filters should narrow the timeline without changing package code:

- event type
- status or severity
- request path
- route name
- job class
- queue name
- scheduled task name
- time window
- text search over safe metadata

### Event Detail

The detail view explains a selected event using normalized, redacted fields. It
should make the privacy boundary visible so teams can trust copied context.

Prototype detail behavior:

- timeline rows link to a selected event detail view
- missing route names display as `unmatched`
- omitted request bodies, headers, cookies, and query bindings display the
  configured redaction marker
- no matching event shows an empty detail state instead of failing the page
- the server-rendered prototype treats the full page request as the loading
  boundary; live polling can add a dedicated loading indicator later

### Issue Context

Issue context should be a redacted summary of the selected event and nearby
related events. It should preserve dashboard authorization and avoid becoming a
public data export.

## Event Detail Hierarchy

Event detail should be organized from broad context to specific evidence:

```text
Event identity
  type
  occurred time
  event id
  schema version

Environment context
  environment name
  deployment identifier

Correlation context
  request id
  session id or review window
  route or path

Event-specific evidence
  status
  duration
  count
  class, command, queue, or SQL shape

Privacy context
  redacted fields
  excluded high-risk payloads
  opt-in fields, if configured
```

## Terminology

- **Environment** means the configured Laravel environment or staging label.
- **Deployment** means an optional release, commit, or build identifier.
- **Review session** means a short staging activity window, whether manually
  named later or inferred from event timing.
- **Timeline** means ordered telemetry events for the current environment and
  filters.
- **Event** means a normalized Strata telemetry record.
- **Issue context** means a redacted, shareable summary for triage.

## Guardrails

- Keep the first dashboard timeline-first.
- Do not add executive reporting or analytics navigation in the first release.
- Do not expose raw payloads in detail views.
- Keep environment and retention context visible.
- Design shared context as redacted evidence, not as unrestricted export.
