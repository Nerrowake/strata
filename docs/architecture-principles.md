# Architecture Principles

Application architecture has not been implemented yet. These principles should
guide the first technical design.

## Package Boundary

Strata should be easy to install into a Laravel staging environment and easy to
remove.

The package should avoid surprising application behavior and should keep capture
logic isolated from display logic.

## Capture Pipeline

Telemetry capture should be:

- low friction
- configurable
- redacted by default
- resilient to dashboard failures
- safe when queues or storage are unavailable
- measurable enough to understand overhead

## Dashboard

The dashboard should prioritize:

- timelines
- filtering
- clear event detail
- issue context links
- safe redaction indicators
- fast scanning during QA sessions

## Testing

Core capture behavior should be covered by automated tests before it is treated
as usable.

Important test areas:

- request capture
- query capture
- redaction
- dashboard authorization
- event storage
- queue and scheduler telemetry
- failure handling
