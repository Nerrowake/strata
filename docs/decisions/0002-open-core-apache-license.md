# 0002 - Open-Core Apache License

## Status

Accepted

## Context

Strata now contains package code, prototype dashboard code, tests, and
foundation documentation. Earlier repository language still described the
project as planning-only and said licensing should be finalized before
application code was added.

The intended product direction is open core: make the early Strata package
open-source, then add paid or commercial features later.

## Decision

License the `nerrowake/strata` core package under the Apache License 2.0.

Future paid features should be delivered as separate packages, services, hosted
offerings, or commercial add-ons rather than changing the core package away from
Apache-2.0.

## Consequences

The current core package can be inspected, used, modified, and redistributed
under a standard permissive open-source license with an explicit patent grant.
This aligns early adoption and contribution with the intended open-core model.

The project still needs future decisions for commercial feature packaging,
hosted services, support terms, and any separate paid-feature licenses.

## Security and Privacy

Open-source licensing does not reduce Strata's security and privacy
requirements. Dashboard access, redaction, retention, safe defaults, and
production-use guardrails remain release-blocking product behavior.

## Related

- [License](../../LICENSE.md)
- [Package Naming and Distribution](../package-distribution.md)
- [Product Requirements](../product-requirements.md)
