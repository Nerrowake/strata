# Package Naming and Distribution

This document records the first package naming, namespace, repository structure,
distribution, and open-core licensing assumptions for Strata.

## Current Decision

The first Composer package name is:

```text
nerrowake/strata
```

The PHP namespace is:

```text
Nerrowake\Strata
```

These names are already reflected in `composer.json` and should remain the
default unless ownership or distribution decisions require a change before the
first public release.

## Repository Structure

Strata should continue as a conventional Laravel package repository:

```text
config/
database/
docs/
resources/
routes/
src/
tests/
```

Package code should live under `src/` and package tests under `tests/`.
Documentation should remain in `docs/` with README links for important product,
architecture, security, and workflow decisions.

## Composer Metadata

Initial Composer assumptions:

- type: `library`
- package: `nerrowake/strata`
- namespace: `Nerrowake\Strata\`
- Laravel provider: `Nerrowake\Strata\StrataServiceProvider`
- PHP constraint: `^8.5`
- Laravel framework constraint: `^13.0`

The package should keep auto-discovery enabled unless a concrete integration
reason requires changing it.

## Distribution Assumptions

Strata core is intended to be open-source. The current package is licensed under
Apache-2.0 so early users and contributors can inspect, run, modify, and
redistribute the core package under a standard permissive license.

Strata is not ready for production or staging installation until support,
security, installation, and release documentation are finalized.

Before public Packagist distribution, the project should decide:

- whether Packagist should index the repository
- whether release archives or GitHub releases are required
- which installation docs are stable enough for users
- how security advisories and vulnerability reports will be handled

Until then, prototype install examples may use the intended package name, and
docs should make availability and support limitations clear.

## Open-Core Licensing Model

The current `nerrowake/strata` package is the open-source core and is licensed
under Apache-2.0.

Future paid or commercial features should be added through separate packages,
services, or hosted offerings rather than changing the core package license.
Possible commercial surfaces include:

- team review workflows
- advanced issue sharing
- hosted dashboards
- longer retention or managed storage
- enterprise authentication integrations
- external tracker integrations
- priority support

Commercial licensing should be finalized before those paid features are
released.

## Revisit Criteria

Revisit package naming and distribution when:

- commercial feature licensing is finalized
- package ownership changes
- the package is prepared for Packagist
- installation docs become release-blocking
- Laravel compatibility targets change
