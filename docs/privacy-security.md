# Privacy and Security

Strata will inspect staging behavior, so privacy and security must be designed
into the first implementation.

## Baseline Requirements

- Redact sensitive headers by default.
- Avoid capturing full request bodies by default.
- Avoid capturing raw query bindings by default.
- Make telemetry capture explicit and configurable.
- Make dashboard access private by default.
- Document data retention clearly.
- Provide safe defaults for local and staging environments.

## Dangerous Defaults to Avoid

- public dashboard access
- full payload capture without opt-in
- token or cookie logging
- long retention without visibility
- production enablement without clear warnings
- silent capture of personal data

## Open Decisions

- Default retention window
- Storage driver options
- Authentication model for the dashboard
- Safe user identity representation
- Team-sharing model for issue links
- Whether production usage is unsupported, discouraged, or separately licensed
