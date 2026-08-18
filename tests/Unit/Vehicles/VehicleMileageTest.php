<?php

declare(strict_types=1);

use App\Actions\Vehicles\RegisterVehicleAction;
use App\Actions\Vehicles\UpdateVehicleMileageAction;
use App\DTOs\Vehicles\UpsertVehicleDTO;
use App\Enums\Vehicles\FuelTypeEnum;
use App\Enums\Vehicles\ServiceTypeEnum;
use App\Enums\Vehicles\VehicleStatusEnum;
use App\Enums\Vehicles\VehicleTypeEnum;
use App\Exceptions\Vehicles\InvalidMileageException;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('registers vehicle with valid non-negative initial mileage and Colombian types', function () {
    $action = app(RegisterVehicleAction::class);

    $dto = new UpsertVehicleDTO(
        plateNumber: 'ABC-123',
        brand: 'Toyota',
        model: 'Hilux',
        year: 2024,
        currentMileage: 1500,
        vehicleType: VehicleTypeEnum::CAMIONETA,
        serviceType: ServiceTypeEnum::PUBLICO,
        status: VehicleStatusEnum::DISPONIBLE,
        fuelType: FuelTypeEnum::DIESEL,
        notes: 'Vehículo nuevo de flota',
    );

    $vehicle = $action($dto);

    expect($vehicle)->toBeInstanceOf(Vehicle::class)
        ->and($vehicle->plate_number)->toBe('ABC-123')
        ->and($vehicle->brand)->toBe('Toyota')
        ->and($vehicle->model)->toBe('Hilux')
        ->and($vehicle->year)->toBe(2024)
        ->and($vehicle->vehicle_type)->toBe(VehicleTypeEnum::CAMIONETA)
        ->and($vehicle->service_type)->toBe(ServiceTypeEnum::PUBLICO)
        ->and($vehicle->current_mileage)->toBe(1500)
        ->and($vehicle->status)->toBe(VehicleStatusEnum::DISPONIBLE)
        ->and($vehicle->fuel_type)->toBe(FuelTypeEnum::DIESEL)
        ->and($vehicle->isAvailable())->toBeTrue()
        ->and($vehicle->isPublicService())->toBeTrue()
        ->and($vehicle->notes)->toBe('Vehículo nuevo de flota');
});

test('rejects registration with negative initial mileage (R6)', function () {
    $action = app(RegisterVehicleAction::class);

    $dto = new UpsertVehicleDTO(
        plateNumber: 'XYZ-789',
        brand: 'Chevrolet',
        model: 'D-Max',
        year: 2023,
        currentMileage: -50,
    );

    expect(fn () => $action($dto))
        ->toThrow(InvalidMileageException::class, 'El kilometraje inicial no puede ser negativo (-50 km).');
});

test('updates vehicle mileage when new reading is greater than current (R3)', function () {
    $vehicle = Vehicle::factory()->create([
        'current_mileage' => 10000,
    ]);

    $action = app(UpdateVehicleMileageAction::class);
    $updated = $action($vehicle, 12500);

    expect($updated->current_mileage)->toBe(12500);
});

test('allows updating vehicle mileage when new reading is equal to current', function () {
    $vehicle = Vehicle::factory()->create([
        'current_mileage' => 10000,
    ]);

    $action = app(UpdateVehicleMileageAction::class);
    $updated = $action($vehicle, 10000);

    expect($updated->current_mileage)->toBe(10000);
});

test('rejects mileage update when new reading is lower than current (R5)', function () {
    $vehicle = Vehicle::factory()->create([
        'current_mileage' => 15000,
    ]);

    $action = app(UpdateVehicleMileageAction::class);

    expect(fn () => $action($vehicle, 12000))
        ->toThrow(InvalidMileageException::class, 'El nuevo kilometraje (12000 km) no puede ser menor al kilometraje actual registrado (15000 km).');
});

test('rejects mileage update when new reading is negative', function () {
    $vehicle = Vehicle::factory()->create([
        'current_mileage' => 5000,
    ]);

    $action = app(UpdateVehicleMileageAction::class);

    expect(fn () => $action($vehicle, -100))
        ->toThrow(InvalidMileageException::class, 'El kilometraje inicial no puede ser negativo (-100 km).');
});

test('vehicle scopes filter available and in service vehicles', function () {
    $avail = Vehicle::factory()->available()->create();
    $inTrip = Vehicle::factory()->inTrip()->create();
    $maint = Vehicle::factory()->maintenance()->create();
    $decomm = Vehicle::factory()->decommissioned()->create();

    $availablePlucks = Vehicle::query()->available()->pluck('id');
    expect($availablePlucks)->toContain($avail->id)
        ->and($availablePlucks)->not->toContain($inTrip->id, $maint->id, $decomm->id);

    $inServicePlucks = Vehicle::query()->inService()->pluck('id');
    expect($inServicePlucks)->toContain($avail->id, $inTrip->id)
        ->and($inServicePlucks)->not->toContain($maint->id, $decomm->id);
});
