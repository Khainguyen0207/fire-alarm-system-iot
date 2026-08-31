<?php

namespace App\Console\Commands;

use App\Services\MeasurementAggregationService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('telemetry:aggregate-minutes {--before= : Aggregate readings before this ISO-8601 timestamp}')]
#[Description('Aggregate closed sensor measurement minutes')]
class AggregateSensorMeasurements extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(MeasurementAggregationService $measurementAggregationService): int
    {
        $before = $this->option('before') === null
            ? now()->utc()->startOfMinute()->toImmutable()
            : CarbonImmutable::parse((string) $this->option('before'))->utc()->startOfMinute();

        $aggregatedMinutes = $measurementAggregationService->aggregate($before);

        $this->info("Aggregated {$aggregatedMinutes} sensor minute buckets.");

        return self::SUCCESS;
    }
}
