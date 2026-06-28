# Personas and Staging Workflows

This document defines the first users Strata is being built for and the staging
workflows the product must support.

## Primary Personas

### Laravel Developer

Laravel developers use Strata while reproducing or observing staging behavior.
They need fast technical context without jumping between logs, database tools,
queue workers, and chat messages.

Goals:

- identify the request, query, job, task, or exception behind a staging issue
- compare related events in one review timeline
- share safe context with teammates without exposing sensitive data
- confirm whether a fix changed staging behavior

### QA Tester

QA testers use staging applications and report issues to the development team.
They should not need local development tools or raw logs to provide useful
evidence.

Goals:

- report what action produced an issue
- attach enough technical context for a developer to investigate
- avoid copying sensitive request or account data into bug reports
- keep review sessions moving without waiting for log digging

### Technical Lead

Technical leads use Strata to decide whether a staging build is healthy enough
for QA, client review, or release preparation.

Goals:

- scan staging health during a review window
- notice failures, slow behavior, repeated query patterns, and missing jobs
- understand whether an issue is isolated or part of a broader pattern
- make handoff decisions with evidence

### Client-Service Team Member

Agency and client-service team members support client reviews and need shared
context when a client reports a staging issue.

Goals:

- capture a narrow window of staging activity around a client action
- communicate issue context to developers without exposing client data
- keep review conversations grounded in observable behavior
- reduce ambiguity in "it broke when I clicked this" reports

## Secondary Personas

Product managers, client stakeholders, and support teammates may view or share
Strata context, but they are not the primary design target for the first
release. The first release should remain a technical staging workbench, not a
business analytics or executive reporting surface.

## Core Staging Workflows

### QA Review With Live Developer Support

1. A QA tester opens a staging workflow.
2. A developer opens the Strata dashboard for the same staging environment.
3. The tester performs a focused set of actions.
4. Strata records request, query, job, scheduled task, and exception metadata.
5. The developer filters the timeline by path, status, event type, or time.
6. The team uses the event detail view to decide what to inspect next.

Success means the team can connect the tester's action to safe technical
context without searching multiple tools.

### Client Review Issue Capture

1. A client reviews a staging feature.
2. A team member notes the time and user-facing action that produced an issue.
3. The team filters Strata to the relevant review window.
4. Strata shows related failures, slow queries, repeated query patterns, jobs,
   tasks, and exceptions.
5. The team copies or links a redacted issue context summary.

Success means developers receive enough evidence to reproduce or triage the
issue without receiving cookies, tokens, request bodies, or personal data.

### Developer Reproduction Pass

1. A developer reproduces a staging-only bug.
2. Strata records the request lifecycle and related telemetry.
3. The developer compares successful and failing attempts.
4. The developer inspects slow, repeated, failed, or missing events.
5. The developer uses the normalized event payloads to choose the next code or
   data check.

Success means Strata narrows the investigation while preserving the host
application's normal behavior.

### Staging Readiness Check

1. A technical lead opens Strata before handing staging to QA or a client.
2. The lead scans recent requests, failures, slow queries, jobs, tasks, and
   environment context.
3. The lead confirms telemetry retention and redaction settings are appropriate.
4. The lead decides whether the environment is ready for review.

Success means the lead can make a review-readiness decision using current
staging behavior, not stale assumptions.

## Non-Goal Workflows

Strata is not designed for these workflows in the first release:

- production incident response
- anonymous public traffic monitoring
- long-term trend analytics
- uptime monitoring
- business conversion analytics
- session replay
- infrastructure monitoring
- distributed tracing across services
- replacing a full APM, log aggregator, or error tracker

## Open Follow-Up Questions

- What exact safe user identifier should be recommended by default?
- How should copied issue context be formatted for external trackers?
- Should staging review sessions be manually named in the first release or
  inferred from activity windows?
- How much client-service language should appear in the dashboard versus docs?
