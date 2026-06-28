# Decision Log

The decision log records durable product, architecture, security, privacy, and
package decisions that affect how Strata is built.

Use the decision log when a choice is hard to infer from code alone, likely to
be revisited, or important for future contributors to understand.

## Location

Decision records live in:

```text
docs/decisions/
```

Use sequential filenames:

```text
0001-short-title.md
0002-short-title.md
```

## Template

```markdown
# 0000 - Decision Title

## Status

Proposed | Accepted | Superseded

## Context

What problem, constraint, or tradeoff forced this decision?

## Decision

What choice was made?

## Consequences

What becomes easier, harder, safer, or riskier because of this choice?

## Security and Privacy

What security or privacy implications should future work preserve?

## Related

- Issue or PR links
- Related docs
```

## Candidate Foundation Decisions

The first candidates for decision records are:

- product scope and non-goals
- supported Laravel, PHP, and database versions
- telemetry capture and redaction policy
- storage and retention strategy
- dashboard authentication model
- package naming and Composer distribution plan
- configuration surface and defaults
- event schema versioning approach
- local development and example app strategy

Not every candidate needs a separate record immediately. Add a decision record
when a decision needs history beyond the living documentation page.

## Guardrails

- Keep records short and evidence-based.
- Link to the living docs that explain the current policy.
- Do not use decision records to hide unresolved product questions.
- Supersede old records instead of rewriting history once accepted.
