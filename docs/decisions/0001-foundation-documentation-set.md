# 0001 - Foundation Documentation Set

## Status

Accepted

## Context

Strata has several foundation decisions that must be visible before deeper
package implementation continues. Some decisions are living product or
architecture policies, while others need a durable log so future changes can be
reviewed against the original tradeoffs.

## Decision

Keep current foundation decisions in focused living docs under `docs/`, and use
`docs/decisions/` for durable decision records when a decision needs historical
context.

The first foundation documentation set includes:

- personas and staging workflows
- product requirements
- information architecture
- local development and example app strategy
- package naming and distribution
- dashboard authentication
- telemetry event schema
- configuration surface and defaults
- decision log convention

## Consequences

Contributors get direct links for current policy and a place to record future
tradeoffs. This keeps README compact while preserving the reasoning needed for
security, privacy, package, and dashboard decisions.

The cost is that documentation has to stay in sync with implementation as
prototype and alpha code lands.

## Security and Privacy

Security and privacy implications must remain visible in both living docs and
decision records. Dashboard access, redaction, retention, and telemetry scope
should not be treated as implementation details only.

## Related

- [Product Brief](../product-brief.md)
- [Privacy and Security](../privacy-security.md)
- [Architecture Principles](../architecture-principles.md)
- [Configuration Surface and Defaults](../configuration.md)
