<?php

declare(strict_types=1);

namespace App\Services\Reports;

use App\DTOs\Reports\PerformanceMetricsDTO;
use App\Enums\Trips\TripStatusEnum;
use App\Models\FuelLog;
use App\Models\Trip;
use App\Models\Vehicle;

class FleetPerformanceCalculatorService
{
    /**
     * Calculate fuel efficiency (km per gallon) for a specific vehicle.
     */
    public function calculateKmPerGallon(Vehicle $vehicle, ?string $startDate = null, ?string $endDate = null): float
    {
        $metrics = $this->calculateVehiclePerformance($vehicle, $startDate, $endDate);

        return $metrics->kmPerGallon;
    }

    /**
     * Calculate full performance metrics for a specific vehicle.
     */
    public function calculateVehiclePerformance(Vehicle $vehicle, ?string $startDate = null, ?string $endDate = null): PerformanceMetricsDTO
    {
        $tripsQuery = $vehicle->trips()
            ->whereIn('status', [TripStatusEnum::FINALIZADO, TripStatusEnum::CERRADO])
            ->when($startDate, fn ($q) => $q->whereDate('actual_departure_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where(function ($sub) use ($endDate): void {
                $sub->whereDate('actual_arrival_at', '<=', $endDate)
                    ->orWhere(fn ($sq) => $sq->whereNull('actual_arrival_at')->whereDate('actual_departure_at', '<=', $endDate));
            }));

        $totalKm = (int) $tripsQuery->sum('distance_traveled');
        $completedTripsCount = (int) $tripsQuery->count();

        $fuelQuery = $vehicle->fuelLogs()
            ->when($startDate, fn ($q) => $q->whereDate('refuel_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('refuel_date', '<=', $endDate));

        $totalGallons = (float) $fuelQuery->sum('gallons');
        $totalFuelCost = (int) $fuelQuery->sum('total_cost');

        $kmPerGallon = $totalGallons > 0 ? round($totalKm / $totalGallons, 2) : 0.0;
        $costPerKm = $totalKm > 0 ? round($totalFuelCost / $totalKm, 2) : 0.0;

        return new PerformanceMetricsDTO(
            totalKm: $totalKm,
            totalGallons: $totalGallons,
            kmPerGallon: $kmPerGallon,
            totalFuelCost: $totalFuelCost,
            costPerKm: $costPerKm,
            completedTripsCount: $completedTripsCount,
        );
    }

    /**
     * Calculate fleet-wide performance metrics across all vehicles.
     */
    public function calculateFleetPerformance(?string $startDate = null, ?string $endDate = null): PerformanceMetricsDTO
    {
        $tripsQuery = Trip::query()
            ->whereIn('status', [TripStatusEnum::FINALIZADO, TripStatusEnum::CERRADO])
            ->when($startDate, fn ($q) => $q->whereDate('actual_departure_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where(function ($sub) use ($endDate): void {
                $sub->whereDate('actual_arrival_at', '<=', $endDate)
                    ->orWhere(fn ($sq) => $sq->whereNull('actual_arrival_at')->whereDate('actual_departure_at', '<=', $endDate));
            }));

        $totalKm = (int) $tripsQuery->sum('distance_traveled');
        $completedTripsCount = (int) $tripsQuery->count();

        $fuelQuery = FuelLog::query()
            ->when($startDate, fn ($q) => $q->whereDate('refuel_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('refuel_date', '<=', $endDate));

        $totalGallons = (float) $fuelQuery->sum('gallons');
        $totalFuelCost = (int) $fuelQuery->sum('total_cost');

        $kmPerGallon = $totalGallons > 0 ? round($totalKm / $totalGallons, 2) : 0.0;
        $costPerKm = $totalKm > 0 ? round($totalFuelCost / $totalKm, 2) : 0.0;

        return new PerformanceMetricsDTO(
            totalKm: $totalKm,
            totalGallons: $totalGallons,
            kmPerGallon: $kmPerGallon,
            totalFuelCost: $totalFuelCost,
            costPerKm: $costPerKm,
            completedTripsCount: $completedTripsCount,
        );
    }
}
