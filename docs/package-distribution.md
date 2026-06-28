# Package Naming and Distribution

This document records the first package naming, namespace, repository structure,
distribution, and licensing assumptions for Strata.

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
default unless licensing or ownership decisions require a change before the
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

Strata is not ready for production or staging installation until licensing,
support, security, installation, and release documentation are finalized.

Before public Packagist distribution, the project should decide:

- whether the package is public, private, commercial, or source-available
- whether Packagist should index the repository
- whether release archives or GitHub releases are required
- which installation docs are stable enough for users
- how security advisories and vulnerability reports will be handled

Until then, prototype install examples may use the intended package name, but
docs should make availability limitations clear.

## Licensing Impact

The repository currently uses a proprietary/all-rights-reserved license notice.
That means public distribution and reuse rights are not finalized.

Licensing must be decided before a stable package release. The decision should
cover:

- source visibility
- package redistribution rights
- commercial use
- contribution terms
- support expectations
- whether documentation and code have the same license

## Revisit Criteria

Revisit package naming and distribution when:

- product licensing is finalized
- package ownership changes
- the package is prepared for Packagist
- installation docs become release-blocking
- Laravel compatibility targets change
