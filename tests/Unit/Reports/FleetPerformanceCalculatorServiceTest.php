<?php

declare(strict_types=1);

namespace Tests\Unit\Reports;

use App\DTOs\Reports\PerformanceMetricsDTO;
use App\Models\FuelLog;
use App\Models\Trip;
use App\Models\Vehicle;
use App\Services\Reports\FleetPerformanceCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FleetPerformanceCalculatorServiceTest extends TestCase
{
    use RefreshDatabase;

    private FleetPerformanceCalculatorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FleetPerformanceCalculatorService;
    }

    public function test_it_calculates_km_per_gallon_accurately_for_a_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create();

        // 2 closed trips: 300 km + 200 km = 500 km
        Trip::factory()->closed()->create([
            'vehicle_id' => $vehicle->id,
            'distance_traveled' => 300,
            'actual_departure_at' => '2026-08-01 08:00:00',
            'actual_arrival_at' => '2026-08-01 14:00:00',
        ]);
        Trip::factory()->completed()->create([
            'vehicle_id' => $vehicle->id,
            'distance_traveled' => 200,
            'actual_departure_at' => '2026-08-05 09:00:00',
            'actual_arrival_at' => '2026-08-05 13:00:00',
        ]);

        // 2 fuel logs: 10.5 gal + 9.5 gal = 20.0 gal
        FuelLog::factory()->create([
            'vehicle_id' => $vehicle->id,
            'gallons' => 10.50,
            'total_cost' => 150000,
            'refuel_date' => '2026-08-02 10:00:00',
        ]);
        FuelLog::factory()->create([
            'vehicle_id' => $vehicle->id,
            'gallons' => 9.50,
            'total_cost' => 140000,
            'refuel_date' => '2026-08-06 11:00:00',
        ]);

        // Expected: 500 km / 20 gal = 25.0 km/gal
        $kmPerGallon = $this->service->calculateKmPerGallon($vehicle);

        $this->assertSame(25.0, $kmPerGallon);
    }

    public function test_it_returns_zero_when_no_fuel_is_consumed_to_prevent_division_by_zero(): void
    {
        $vehicle = Vehicle::factory()->create();

        Trip::factory()->closed()->create([
            'vehicle_id' => $vehicle->id,
            'distance_traveled' => 300,
        ]);

        $kmPerGallon = $this->service->calculateKmPerGallon($vehicle);

        $this->assertSame(0.0, $kmPerGallon);
    }

    public function test_it_returns_zero_when_no_trips_completed(): void
    {
        $vehicle = Vehicle::factory()->create();

        FuelLog::factory()->create([
            'vehicle_id' => $vehicle->id,
            'gallons' => 15.0,
            'total_cost' => 200000,
        ]);

        $kmPerGallon = $this->service->calculateKmPerGallon($vehicle);

        $this->assertSame(0.0, $kmPerGallon);
    }

    public function test_it_ignores_trips_that_are_not_completed_or_closed(): void
    {
        $vehicle = Vehicle::factory()->create();

        // Active/cancelled trips should not contribute to audited distance
        Trip::factory()->inProgress()->create([
            'vehicle_id' => $vehicle->id,
            'distance_traveled' => 100,
        ]);
        Trip::factory()->cancelled()->create([
            'vehicle_id' => $vehicle->id,
            'distance_traveled' => 200,
        ]);
        Trip::factory()->scheduled()->create([
            'vehicle_id' => $vehicle->id,
            'distance_traveled' => 300,
        ]);

        // Only closed trip counts
        Trip::factory()->closed()->create([
            'vehicle_id' => $vehicle->id,
            'distance_traveled' => 150,
        ]);

        FuelLog::factory()->create([
            'vehicle_id' => $vehicle->id,
            'gallons' => 10.0,
        ]);

        // 150 km / 10 gal = 15.0
        $kmPerGallon = $this->service->calculateKmPerGallon($vehicle);

        $this->assertSame(15.0, $kmPerGallon);
    }

    public function test_it_filters_metrics_by_date_range(): void
    {
        $vehicle = Vehicle::factory()->create();

        // Trip in July (out of range)
        Trip::factory()->closed()->create([
            'vehicle_id' => $vehicle->id,
            'distance_traveled' => 400,
            'actual_departure_at' => '2026-07-15 08:00:00',
            'actual_arrival_at' => '2026-07-15 14:00:00',
        ]);
        FuelLog::factory()->create([
            'vehicle_id' => $vehicle->id,
            'gallons' => 10.0,
            'refuel_date' => '2026-07-16 09:00:00',
        ]);

        // Trip in August (in range)
        Trip::factory()->closed()->create([
            'vehicle_id' => $vehicle->id,
            'distance_traveled' => 240,
            'actual_departure_at' => '2026-08-10 08:00:00',
            'actual_arrival_at' => '2026-08-10 12:00:00',
        ]);
        FuelLog::factory()->create([
            'vehicle_id' => $vehicle->id,
            'gallons' => 8.0,
            'refuel_date' => '2026-08-11 10:00:00',
        ]);

        $kmPerGallonAugust = $this->service->calculateKmPerGallon(
            $vehicle,
            '2026-08-01',
            '2026-08-31'
        );

        // August only: 240 km / 8 gal = 30.0 km/gal
        $this->assertSame(30.0, $kmPerGallonAugust);
    }

    public function test_it_returns_detailed_performance_metrics_dto_for_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create();

        Trip::factory()->closed()->create([
            'vehicle_id' => $vehicle->id,
            'distance_traveled' => 500,
            'actual_departure_at' => '2026-08-01 08:00:00',
            'actual_arrival_at' => '2026-08-01 18:00:00',
        ]);

        FuelLog::factory()->create([
            'vehicle_id' => $vehicle->id,
            'gallons' => 20.0,
            'total_cost' => 300000,
            'refuel_date' => '2026-08-02 09:00:00',
        ]);

        $metrics = $this->service->calculateVehiclePerformance($vehicle);

        $this->assertInstanceOf(PerformanceMetricsDTO::class, $metrics);
        $this->assertSame(500, $metrics->totalKm);
        $this->assertSame(20.0, $metrics->totalGallons);
        $this->assertSame(25.0, $metrics->kmPerGallon);
        $this->assertSame(300000, $metrics->totalFuelCost);
        $this->assertSame(600.0, $metrics->costPerKm); // 300,000 COP / 500 km = 600 COP/km
        $this->assertSame(1, $metrics->completedTripsCount);
    }

    public function test_it_calculates_fleet_wide_performance_metrics(): void
    {
        $vehicle1 = Vehicle::factory()->create();
        $vehicle2 = Vehicle::factory()->create();

        // Vehicle 1: 300 km, 10 gal, $150.000 COP
        Trip::factory()->closed()->create([
            'vehicle_id' => $vehicle1->id,
            'distance_traveled' => 300,
            'actual_departure_at' => '2026-08-01 08:00:00',
            'actual_arrival_at' => '2026-08-01 14:00:00',
        ]);
        FuelLog::factory()->create([
            'vehicle_id' => $vehicle1->id,
            'gallons' => 10.0,
            'total_cost' => 150000,
            'refuel_date' => '2026-08-02 10:00:00',
        ]);

        // Vehicle 2: 700 km, 30 gal, $450.000 COP
        Trip::factory()->completed()->create([
            'vehicle_id' => $vehicle2->id,
            'distance_traveled' => 700,
            'actual_departure_at' => '2026-08-03 08:00:00',
            'actual_arrival_at' => '2026-08-03 20:00:00',
        ]);
        FuelLog::factory()->create([
            'vehicle_id' => $vehicle2->id,
            'gallons' => 30.0,
            'total_cost' => 450000,
            'refuel_date' => '2026-08-04 10:00:00',
        ]);

        $metrics = $this->service->calculateFleetPerformance();

        // Total Fleet: 1000 km, 40 gal, $600.000 COP
        $this->assertSame(1000, $metrics->totalKm);
        $this->assertSame(40.0, $metrics->totalGallons);
        $this->assertSame(25.0, $metrics->kmPerGallon); // 1000 / 40 = 25.0
        $this->assertSame(600000, $metrics->totalFuelCost);
        $this->assertSame(600.0, $metrics->costPerKm); // 600,000 / 1000 = 600.0
        $this->assertSame(2, $metrics->completedTripsCount);
    }
}
