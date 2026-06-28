# Telemetry Scope

Strata should capture staging telemetry that helps teams understand what happened
during a QA or review session.

## Planned Event Types

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

## Review Rule

Every telemetry category must document what it captures, why it is needed, how it
is redacted, and how teams can disable or narrow it.
