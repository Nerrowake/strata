# Product Brief

Strata is a real-time staging telemetry dashboard for Laravel teams.

## Problem

Staging environments are where QA testers, clients, and developers often find
the issues that did not appear during local development. The problem is that the
technical context is scattered across logs, database tools, queue workers,
browser consoles, and chat messages.

Strata should make staging behavior visible as it happens.

## Audience

- Laravel developers
- QA testers
- technical leads
- agencies and client-service teams
- product teams reviewing staging builds

## Core Use Cases

- Watch requests and errors during QA sessions.
- Identify slow queries and N+1 patterns.
- Confirm jobs and scheduled tasks are running.
- Capture enough context to reproduce a staging issue.
- Share issue context without exposing sensitive data.

## Non-Goals

- Production observability replacement
- Full APM platform
- Log aggregation platform
- Error tracker replacement
- Business analytics dashboard

Strata can integrate with those tools later, but it should stay focused on
staging feedback first.
