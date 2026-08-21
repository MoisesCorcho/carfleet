<?php

declare(strict_types=1);

namespace App\DTOs\Reports;

readonly class PerformanceMetricsDTO
{
    public function __construct(
        public int $totalKm,
        public float $totalGallons,
        public float $kmPerGallon,
        public int $totalFuelCost,
        public float $costPerKm,
        public int $completedTripsCount,
    ) {}

    public static function empty(): self
    {
        return new self(
            totalKm: 0,
            totalGallons: 0.0,
            kmPerGallon: 0.0,
            totalFuelCost: 0,
            costPerKm: 0.0,
            completedTripsCount: 0,
        );
    }
}
