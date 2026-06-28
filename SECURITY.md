# Security Policy

Strata is planned as a staging telemetry product. Security and privacy are core
product requirements, not later cleanup.

## Reporting

Do not open public issues for vulnerabilities or sensitive security concerns.

Use GitHub private vulnerability reporting when available, or contact the
maintainer privately.

## Areas of Concern

Relevant security concerns include:

- exposure of request payloads, headers, cookies, or tokens
- accidental capture of secrets or personal data
- unsafe query logging
- dashboard authentication or authorization bypasses
- insecure event transport
- unsafe local storage or retention behavior
- cross-site scripting in telemetry display
- package supply-chain concerns

## Privacy Defaults

Strata should be designed to avoid collecting sensitive data by default.

Before telemetry capture ships, the project must define:

- redaction defaults
- opt-in and opt-out behavior
- retention policy
- safe local development behavior
- production-use warnings

## Scope

This repository currently contains planning documentation, a Laravel package
skeleton, prototype dashboard code, and early telemetry capture experiments.
Security reports should be tied to documented plans or published code.
