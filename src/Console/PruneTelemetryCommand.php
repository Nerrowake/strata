<?php

namespace Nerrowake\Strata\Console;

use Illuminate\Console\Command;
use Nerrowake\Strata\Storage\TelemetryEventStore;

class PruneTelemetryCommand extends Command
{
    protected $signature = 'strata:prune {--dry-run : Report how many events would be pruned without deleting them}';

    protected $description = 'Prune prototype Strata telemetry events outside the configured retention window.';

    public function handle(TelemetryEventStore $events): int
    {
        if (! config('strata.retention.enabled', true)) {
            $this->info('Strata retention cleanup is disabled.');

            return self::SUCCESS;
        }

        $threshold = now()->subHours((int) config('strata.retention.hours', 24));
        $before = $events->count();

        if ($this->option('dry-run')) {
            $thresholdTimestamp = $threshold->getTimestamp();
            $wouldPrune = collect($events->recent($before))->filter(
                static fn (array $event): bool => isset($event['occurred_at'])
                    && $event['occurred_at'] instanceof \DateTimeInterface
                    && $event['occurred_at']->getTimestamp() < $thresholdTimestamp
            )->count();

            $this->info("Would prune {$wouldPrune} Strata event(s).");

            return self::SUCCESS;
        }

        $pruned = $events->pruneOlderThan($threshold);

        $this->info("Pruned {$pruned} Strata event(s).");

        return self::SUCCESS;
    }
}
