<?php

namespace Nerrowake\Strata\Capture;

use Closure;
use Illuminate\Http\Request;
use Nerrowake\Strata\Contracts\TelemetryCollector;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RequestTelemetryMiddleware
{
    public function __construct(
        private readonly TelemetryCollector $events,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->shouldCapture($request)) {
            return $next($request);
        }

        $startedAt = microtime(true);
        $this->recordStarted($request);

        try {
            $response = $next($request);
        } catch (Throwable $exception) {
            $this->recordCompleted($request, 500, $startedAt, true);

            throw $exception;
        }

        $this->recordCompleted($request, $response->getStatusCode(), $startedAt, false);

        return $response;
    }

    private function shouldCapture(Request $request): bool
    {
        if (! config('strata.enabled', false) || ! config('strata.capture.requests', true)) {
            return false;
        }

        $path = '/'.ltrim($request->path(), '/');
        $ignoredPaths = array_map(
            static fn (string $ignored): string => '/'.ltrim($ignored, '/'),
            config('strata.ignore.paths', [])
        );

        if (in_array($path, $ignoredPaths, true)) {
            return false;
        }

        $routeName = $request->route()?->getName();

        return $routeName === null || ! in_array($routeName, config('strata.ignore.routes', []), true);
    }

    private function recordStarted(Request $request): void
    {
        $this->record([
            'type' => 'request',
            'event' => 'request.started',
            'occurred_at' => now(),
            'method' => $request->method(),
            'path' => '/'.ltrim($request->path(), '/'),
            'route' => $request->route()?->getName(),
            'status' => 'started',
            'redactions' => ['request_body', 'headers', 'cookies', 'uploaded_files'],
        ]);
    }

    private function recordCompleted(Request $request, int $statusCode, float $startedAt, bool $failed): void
    {
        $this->record([
            'type' => 'request',
            'event' => 'request.completed',
            'occurred_at' => now(),
            'method' => $request->method(),
            'path' => '/'.ltrim($request->path(), '/'),
            'route' => $request->route()?->getName(),
            'status' => $statusCode,
            'duration_ms' => round((microtime(true) - $startedAt) * 1000, 2),
            'failed' => $failed || $statusCode >= 500,
            'redactions' => ['request_body', 'headers', 'cookies', 'uploaded_files'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $event
     */
    private function record(array $event): void
    {
        $this->events->record($event);
    }
}
