<?php

declare(strict_types=1);

use App\Actions\Vehicles\AdjustVehicleMileageAction;
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

test('registers vehicle with valid non-negative initial mileage and enum types', function () {
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

test('adjusts vehicle mileage with audit trail entry and justification', function () {
    $vehicle = Vehicle::factory()->create([
        'current_mileage' => 20000,
        'notes' => 'Notas previas de entrega.',
    ]);

    $action = app(AdjustVehicleMileageAction::class);
    $adjusted = $action($vehicle, 21500, 'Calibración técnica y reemplazo de odómetro');

    expect($adjusted->current_mileage)->toBe(21500)
        ->and($adjusted->notes)->toContain('Notas previas de entrega.')
        ->and($adjusted->notes)->toContain('Ajuste manual de odómetro: de 20000 km a 21500 km')
        ->and($adjusted->notes)->toContain('Calibración técnica y reemplazo de odómetro');
});

test('allows adjusting vehicle mileage to a lower value with audit trail entry (e.g. typo correction)', function () {
    $vehicle = Vehicle::factory()->create([
        'current_mileage' => 30000,
        'notes' => 'Notas previas.',
    ]);

    $action = app(AdjustVehicleMileageAction::class);
    $adjusted = $action($vehicle, 25000, 'Corrección por error de digitación previo');

    expect($adjusted->current_mileage)->toBe(25000)
        ->and($adjusted->notes)->toContain('Notas previas.')
        ->and($adjusted->notes)->toContain('Ajuste manual de odómetro: de 30000 km a 25000 km')
        ->and($adjusted->notes)->toContain('Corrección por error de digitación previo');
});

test('rejects adjusting vehicle mileage to a negative value', function () {
    $vehicle = Vehicle::factory()->create([
        'current_mileage' => 10000,
    ]);

    $action = app(AdjustVehicleMileageAction::class);

    expect(fn () => $action($vehicle, -500, 'Ajuste inválido'))
        ->toThrow(InvalidMileageException::class, 'El kilometraje inicial no puede ser negativo (-500 km).');
});

test('rejects adjusting vehicle mileage with empty justification reason', function () {
    $vehicle = Vehicle::factory()->create([
        'current_mileage' => 10000,
    ]);

    $action = app(AdjustVehicleMileageAction::class);

    expect(fn () => $action($vehicle, 8000, '   '))
        ->toThrow(InvalidArgumentException::class, 'El motivo del ajuste de odómetro es obligatorio.');
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
