<?php

declare(strict_types=1);

use App\Actions\Vehicles\RegisterVehicleAction;
use App\DTOs\Vehicles\UpsertVehicleDTO;
use App\Enums\Vehicles\FuelTypeEnum;
use App\Enums\Vehicles\ServiceTypeEnum;
use App\Enums\Vehicles\VehicleStatusEnum;
use App\Enums\Vehicles\VehicleTypeEnum;
use App\Filament\Resources\Vehicles\Pages\CreateVehicle;
use App\Filament\Resources\Vehicles\Pages\EditVehicle;
use App\Filament\Resources\Vehicles\Pages\ListVehicles;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Database\QueryException;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->adminUser = User::factory()->create();
    $this->adminUser->assignRole('super_admin');
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

test('admin can access vehicles list in filament panel', function () {
    $this->actingAs($this->adminUser);

    Vehicle::factory()->count(3)->create();

    Livewire::test(ListVehicles::class)
        ->assertSuccessful()
        ->assertCountTableRecords(3);
});

test('admin can filter vehicles by availability status (R2)', function () {
    $this->actingAs($this->adminUser);

    $availableVehicle = Vehicle::factory()->available()->create(['plate_number' => 'DSP-101']);
    $inTripVehicle = Vehicle::factory()->inTrip()->create(['plate_number' => 'TRP-101']);
    $maintenanceVehicle = Vehicle::factory()->maintenance()->create(['plate_number' => 'MNT-101']);

    Livewire::test(ListVehicles::class)
        ->assertCanSeeTableRecords([$availableVehicle, $inTripVehicle, $maintenanceVehicle])
        ->filterTable('status', VehicleStatusEnum::DISPONIBLE->value)
        ->assertCanSeeTableRecords([$availableVehicle])
        ->assertCanNotSeeTableRecords([$inTripVehicle, $maintenanceVehicle]);
});

test('admin can filter vehicles by vehicle type and service type', function () {
    $this->actingAs($this->adminUser);

    $van = Vehicle::factory()->publicService()->withType(VehicleTypeEnum::VAN)->create(['plate_number' => 'VAN-101']);
    $truck = Vehicle::factory()->particularService()->withType(VehicleTypeEnum::CAMION)->create(['plate_number' => 'CAM-101']);

    Livewire::test(ListVehicles::class)
        ->assertCanSeeTableRecords([$van, $truck])
        ->filterTable('vehicle_type', VehicleTypeEnum::VAN->value)
        ->assertCanSeeTableRecords([$van])
        ->assertCanNotSeeTableRecords([$truck]);
});

test('admin can create a vehicle via filament form (R1)', function () {
    $this->actingAs($this->adminUser);

    Livewire::test(CreateVehicle::class)
        ->fillForm([
            'plate_number' => 'FLT-100',
            'service_type' => ServiceTypeEnum::PUBLICO->value,
            'vehicle_type' => VehicleTypeEnum::CAMIONETA->value,
            'brand' => 'Nissan',
            'model' => 'Frontier',
            'year' => 2024,
            'current_mileage' => 0,
            'status' => VehicleStatusEnum::DISPONIBLE->value,
            'fuel_type' => FuelTypeEnum::DIESEL->value,
            'notes' => 'Vehículo asignado a operaciones',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('vehicles', [
        'plate_number' => 'FLT-100',
        'service_type' => 'publico',
        'vehicle_type' => 'camioneta',
        'brand' => 'Nissan',
        'model' => 'Frontier',
        'year' => 2024,
        'current_mileage' => 0,
        'status' => 'disponible',
        'fuel_type' => 'diesel',
    ]);
});

test('rejects invalid Colombian plate format via form validation', function () {
    $this->actingAs($this->adminUser);

    Livewire::test(CreateVehicle::class)
        ->fillForm([
            'plate_number' => 'PLACA_INVALIDA_12345',
            'brand' => 'Toyota',
            'model' => 'Hilux',
            'year' => 2024,
        ])
        ->call('create')
        ->assertHasFormErrors(['plate_number' => 'regex']);
});

test('rejects duplicate plate number registration (R4)', function () {
    Vehicle::factory()->create(['plate_number' => 'DUP-999']);

    $action = app(RegisterVehicleAction::class);
    $dto = new UpsertVehicleDTO(
        plateNumber: 'DUP-999',
        brand: 'Ford',
        model: 'Ranger',
        year: 2023,
        currentMileage: 5000,
    );

    expect(fn () => $action($dto))->toThrow(QueryException::class);
});

test('admin can update vehicle information via edit page preserving odometer', function () {
    $this->actingAs($this->adminUser);

    $vehicle = Vehicle::factory()->create([
        'plate_number' => 'OLD-111',
        'current_mileage' => 10000,
        'status' => VehicleStatusEnum::DISPONIBLE,
    ]);

    Livewire::test(EditVehicle::class, ['record' => $vehicle->getRouteKey()])
        ->fillForm([
            'brand' => 'Toyota Updated',
            'model' => 'Hilux 4x4',
            'year' => 2024,
            'status' => VehicleStatusEnum::MANTENIMIENTO->value,
            'fuel_type' => FuelTypeEnum::DIESEL->value,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $vehicle->refresh();
    expect($vehicle->brand)->toBe('Toyota Updated')
        ->and($vehicle->model)->toBe('Hilux 4x4')
        ->and($vehicle->current_mileage)->toBe(10000)
        ->and($vehicle->status)->toBe(VehicleStatusEnum::MANTENIMIENTO);
});

test('rejects duplicate plate number via filament form validation (R4)', function () {
    $this->actingAs($this->adminUser);

    Vehicle::factory()->create(['plate_number' => 'DUP-123']);

    Livewire::test(CreateVehicle::class)
        ->fillForm([
            'plate_number' => 'DUP-123',
            'brand' => 'Chevrolet',
            'model' => 'Tracker',
            'year' => 2024,
            'current_mileage' => 100,
            'status' => VehicleStatusEnum::DISPONIBLE->value,
            'fuel_type' => FuelTypeEnum::GASOLINA->value,
        ])
        ->call('create')
        ->assertHasFormErrors(['plate_number' => 'unique']);
});

test('user without view vehicle permissions cannot access vehicles list', function () {
    $regularUser = User::factory()->create();
    $regularUser->assignRole('panel_user');

    $this->actingAs($regularUser);

    Livewire::test(ListVehicles::class)
        ->assertForbidden();
});
