<?php

declare(strict_types=1);

use App\Enums\Drivers\DocumentTypeEnum;
use App\Enums\Drivers\DriverStatusEnum;
use App\Enums\Drivers\LicenseCategoryEnum;
use App\Enums\Requesters\RequesterDocumentTypeEnum;
use App\Enums\Vehicles\FuelTypeEnum;
use App\Enums\Vehicles\ServiceTypeEnum;
use App\Enums\Vehicles\VehicleStatusEnum;
use App\Enums\Vehicles\VehicleTypeEnum;
use App\Filament\Resources\Drivers\Pages\CreateDriver;
use App\Filament\Resources\Requesters\Pages\CreateRequester;
use App\Filament\Resources\Vehicles\Pages\CreateVehicle;
use App\Models\Driver;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->adminUser = User::factory()->create();
    $this->adminUser->assignRole('super_admin');
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

test('plate without dash is automatically formatted with dash on create (e.g. wxy123 -> WXY-123)', function () {
    $this->actingAs($this->adminUser);

    Livewire::test(CreateVehicle::class)
        ->fillForm([
            'plate_number' => 'wxy123',
            'service_type' => ServiceTypeEnum::PUBLICO->value,
            'vehicle_type' => VehicleTypeEnum::CAMIONETA->value,
            'brand' => 'Toyota',
            'model' => 'Prado',
            'year' => 2024,
            'current_mileage' => 0,
            'status' => VehicleStatusEnum::DISPONIBLE->value,
            'fuel_type' => FuelTypeEnum::DIESEL->value,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('vehicles', [
        'plate_number' => 'WXY-123',
    ]);
});

test('driver form rejects document number containing special characters like @', function () {
    $this->actingAs($this->adminUser);

    $driverUser = User::factory()->create();

    Livewire::test(CreateDriver::class)
        ->fillForm([
            'user_id' => $driverUser->id,
            'full_name' => 'Conductor de Prueba',
            'document_type' => DocumentTypeEnum::CC->value,
            'document_number' => '123123123-@',
            'phone' => '+57 300 123 4567',
            'license_number' => 'LIC-123456',
            'license_category' => LicenseCategoryEnum::C1->value,
            'status' => DriverStatusEnum::ACTIVO->value,
        ])
        ->call('create')
        ->assertHasFormErrors(['document_number' => 'regex']);
});

test('driver form rejects license number containing special characters like @', function () {
    $this->actingAs($this->adminUser);

    $driverUser = User::factory()->create();

    Livewire::test(CreateDriver::class)
        ->fillForm([
            'user_id' => $driverUser->id,
            'full_name' => 'Conductor de Prueba',
            'document_type' => DocumentTypeEnum::CC->value,
            'document_number' => '1020304050',
            'phone' => '+57 300 123 4567',
            'license_number' => 'LIC-998877@',
            'license_category' => LicenseCategoryEnum::C1->value,
            'status' => DriverStatusEnum::ACTIVO->value,
        ])
        ->call('create')
        ->assertHasFormErrors(['license_number' => 'regex']);
});

test('requester form rejects document number or NIT containing special characters like @', function () {
    $this->actingAs($this->adminUser);

    Livewire::test(CreateRequester::class)
        ->fillForm([
            'company_name' => 'Minas y Energía S.A.S.',
            'name' => 'Ing. Laura Restrepo',
            'document_type' => RequesterDocumentTypeEnum::NIT->value,
            'document_number' => '900999888-@',
            'phone' => '+57 318 000 1122',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasFormErrors(['document_number' => 'regex']);
});

test('requester form accepts valid NIT format with verification digit (e.g. 900999888-1)', function () {
    $this->actingAs($this->adminUser);

    Livewire::test(CreateRequester::class)
        ->fillForm([
            'company_name' => 'Minas y Energía S.A.S.',
            'name' => 'Ing. Laura Restrepo',
            'document_type' => RequesterDocumentTypeEnum::NIT->value,
            'document_number' => '900999888-1',
            'phone' => '+57 318 000 1122',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('requesters', [
        'document_number' => '900999888-1',
    ]);
});

test('displays custom spanish validation messages on unique conflicts in vehicle and driver forms', function () {
    $this->actingAs($this->adminUser);

    $existingVehicle = Vehicle::factory()->create(['plate_number' => 'ABC-123']);
    $existingDriver = Driver::factory()->create(['document_number' => '1020304050']);

    Livewire::test(CreateVehicle::class)
        ->fillForm([
            'plate_number' => 'ABC-123',
            'brand' => 'Toyota',
            'model' => 'Hilux',
            'year' => 2024,
            'current_mileage' => 0,
        ])
        ->call('create')
        ->assertHasFormErrors(['plate_number' => 'unique']);

    Livewire::test(CreateDriver::class)
        ->fillForm([
            'user_id' => $existingDriver->user_id,
            'full_name' => 'Otro Conductor',
            'document_type' => DocumentTypeEnum::CC->value,
            'document_number' => '1020304050',
            'phone' => '+57 300 000 0000',
            'license_number' => 'LIC-000000',
        ])
        ->call('create')
        ->assertHasFormErrors(['user_id' => 'unique', 'document_number' => 'unique']);
});

test('rejects duplicate plate even if user types without dash (e.g. dup123 when DUP-123 exists)', function () {
    $this->actingAs($this->adminUser);

    Vehicle::factory()->create(['plate_number' => 'DUP-123']);

    Livewire::test(CreateVehicle::class)
        ->fillForm([
            'plate_number' => 'dup123',
            'brand' => 'Chevrolet',
            'model' => 'Tracker',
            'year' => 2024,
            'current_mileage' => 100,
            'status' => VehicleStatusEnum::DISPONIBLE->value,
            'fuel_type' => FuelTypeEnum::GASOLINA->value,
        ])
        ->call('create')
        ->assertHasFormErrors(['plate_number']);
});

test('driver form accepts standard numeric license (RUNT format) and alphanumeric with hyphen', function () {
    $this->actingAs($this->adminUser);

    $driverUser1 = User::factory()->create();
    $driverUser2 = User::factory()->create();

    // RUNT numeric format
    Livewire::test(CreateDriver::class)
        ->fillForm([
            'user_id' => $driverUser1->id,
            'full_name' => 'Conductor RUNT',
            'document_type' => DocumentTypeEnum::CC->value,
            'document_number' => '1020304051',
            'phone' => '+57 300 111 2233',
            'license_number' => '1020304051',
            'license_category' => LicenseCategoryEnum::C1->value,
            'status' => DriverStatusEnum::ACTIVO->value,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    // Alphanumeric with hyphen format
    Livewire::test(CreateDriver::class)
        ->fillForm([
            'user_id' => $driverUser2->id,
            'full_name' => 'Conductor Legado',
            'document_type' => DocumentTypeEnum::CC->value,
            'document_number' => '1020304052',
            'phone' => '+57 300 444 5566',
            'license_number' => 'LIC-778899',
            'license_category' => LicenseCategoryEnum::C2->value,
            'status' => DriverStatusEnum::ACTIVO->value,
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});
