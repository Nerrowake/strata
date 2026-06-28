# Changelog

All notable changes to Strata will be documented in this file.

## Unreleased

### Added

- Initial repository foundation, project documentation, GitHub templates, and
  planning structure.
- Initial Laravel package skeleton with Composer metadata, service provider,
  publishable configuration, dashboard route/view placeholders, and package
  smoke tests.
- Prototype dashboard shell with timeline scanning, filters, empty state, and
  redacted event detail placeholders.
- Prototype query telemetry capture with SQL shape redaction, slow query
  indicators, and cautious repeated-query N+1 signals.
- Foundation documentation for personas, product requirements, information
  architecture, local development, package distribution, dashboard
  authentication, event schema, configuration defaults, and decision logs.

### Changed

- Strata has package bootstrapping and a dashboard prototype in place, but
  telemetry capture and storage are not implemented yet.
