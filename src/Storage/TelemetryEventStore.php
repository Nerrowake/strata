<?php

namespace Nerrowake\Strata\Storage;

class TelemetryEventStore
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $events = [];

    /**
     * @var array<string, int>
     */
    private array $shapeCounts = [];

    private int $nextId = 1;

    /**
     * @param  array<string, mixed>  $event
     * @return array<string, mixed>
     */
    public function record(array $event): array
    {
        $event['id'] = $this->nextId++;

        if (($event['type'] ?? null) === 'query') {
            $event = $this->applyQueryEvidence($event);
        }

        array_unshift($this->events, $event);
        $this->events = array_slice($this->events, 0, $this->maxEvents());

        return $event;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function recent(int $limit = 50): array
    {
        return array_slice($this->events, 0, $limit);
    }

    public function count(): int
    {
        return count($this->events);
    }

    /**
     * @return array<int, string>
     */
    public function sessions(): array
    {
        return array_values(array_unique(array_filter(array_map(
            static fn (array $event): ?string => isset($event['session_id']) ? (string) $event['session_id'] : null,
            $this->events
        ))));
    }

    public function pruneOlderThan(\DateTimeInterface $threshold): int
    {
        $before = count($this->events);
        $thresholdTimestamp = $threshold->getTimestamp();

        $this->events = array_values(array_filter(
            $this->events,
            static fn (array $event): bool => ! isset($event['occurred_at'])
                || ! $event['occurred_at'] instanceof \DateTimeInterface
                || $event['occurred_at']->getTimestamp() >= $thresholdTimestamp
        ));

        return $before - count($this->events);
    }

    public function reset(): void
    {
        $this->events = [];
        $this->shapeCounts = [];
        $this->nextId = 1;
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<string, mixed>
     */
    private function applyQueryEvidence(array $event): array
    {
        $shape = (string) ($event['sql_shape'] ?? '');
        $count = $this->shapeCounts[$shape] = ($this->shapeCounts[$shape] ?? 0) + 1;
        $threshold = (int) config('strata.thresholds.repeated_query_count', 5);
        $detectNPlusOne = config('strata.capture.n_plus_one', true);

        $event['repeated_query_count'] = $count;
        $event['possible_n_plus_one'] = $detectNPlusOne && $threshold > 1 && $count >= $threshold;

        if ($event['possible_n_plus_one']) {
            $event['status'] = 'possible_n_plus_one';
        }

        return $event;
    }

    private function maxEvents(): int
    {
        return max(1, (int) config('strata.storage.max_events', 500));
    }
}
