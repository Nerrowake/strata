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
