# Changelog

All notable changes to Strata will be documented in this file.

## Unreleased

### Added

- Initial repository foundation, project documentation, GitHub templates, and
  planning structure.
- Initial Laravel package skeleton with Composer metadata, service provider,
  publishable configuration, dashboard route/view, and package
  smoke tests.
- Prototype dashboard shell with timeline scanning, filters, empty state, and
  redacted event detail.
- Prototype request lifecycle capture with safe method, path, route, status,
  duration, and redaction metadata.
- Bounded in-memory prototype event store and safe telemetry collector
  contract.
- Prototype query telemetry capture with SQL shape redaction, slow query
  indicators, and cautious repeated-query N+1 signals.
- Prototype CI workflow for formatting and package tests.
- Foundation documentation for personas, product requirements, information
  architecture, local development, package distribution, dashboard
  authentication, event schema, configuration defaults, and decision logs.
- Apache-2.0 licensing for the open-source Strata core and documentation for the
  open-core commercial feature path.

### Changed

- Roadmap now marks Foundation and Prototype as complete, with Alpha as the
  next active milestone.
