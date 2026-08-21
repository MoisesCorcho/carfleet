<?php

declare(strict_types=1);

namespace Tests\Feature\Fuel;

use App\Enums\Evidences\EvidenceTypeEnum;
use App\Filament\Driver\Resources\Trips\Pages\ListAssignedTrips;
use App\Filament\Resources\FuelLogs\Pages\CreateFuelLog;
use App\Filament\Resources\FuelLogs\Pages\ListFuelLogs;
use App\Filament\Resources\FuelLogs\Pages\ViewFuelLog;
use App\Filament\Resources\Trips\Pages\ViewTrip;
use App\Filament\Resources\Vehicles\Pages\ViewVehicle;
use App\Models\Driver;
use App\Models\FuelLog;
use App\Models\Trip;
use App\Models\TripEvidence;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    Storage::fake('public');

    $this->adminUser = User::factory()->create();
    $this->adminUser->assignRole('super_admin');

    $this->driverUser = User::factory()->create();
    $this->driverUser->assignRole('driver');
    $this->driverProfile = Driver::factory()->active()->create([
        'user_id' => $this->driverUser->id,
        'full_name' => 'Roberto Conductor',
    ]);
});

test('admin can access fuel logs list and view records (US6.3)', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs($this->adminUser);

    $vehicle = Vehicle::factory()->create(['plate_number' => 'ABC-123']);
    $fuelLog = FuelLog::factory()->create([
        'vehicle_id' => $vehicle->id,
        'voucher_number' => 'V-889900',
        'gallons' => 14.50,
        'total_cost' => 210000,
    ]);

    Livewire::test(ListFuelLogs::class)
        ->assertSuccessful()
        ->assertSee('ABC-123')
        ->assertSee('V-889900');
});

test('admin can create fuel log via CreateFuelLog page with voucher (US6.1, US6.3)', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs($this->adminUser);

    $vehicle = Vehicle::factory()->create(['current_mileage' => 30000]);
    $voucherFile = UploadedFile::fake()->image('voucher_admin.jpg');

    Livewire::test(CreateFuelLog::class)
        ->fillForm([
            'vehicle_id' => $vehicle->id,
            'refuel_date' => now()->toDateTimeString(),
            'mileage_at_refuel' => 30100,
            'gallons' => 11.25,
            'total_cost' => 165000,
            'voucher_number' => 'V-ADMIN-01',
            'voucher_photo_path' => $voucherFile,
            'notes' => 'Tanqueo administrativo',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $fuelLog = FuelLog::query()->where('voucher_number', 'V-ADMIN-01')->first();
    expect($fuelLog)->not->toBeNull()
        ->and($fuelLog->vehicle_id)->toBe($vehicle->id)
        ->and($fuelLog->mileage_at_refuel)->toBe(30100)
        ->and((float) $fuelLog->gallons)->toBe(11.25)
        ->and($fuelLog->total_cost)->toBe(165000)
        ->and($vehicle->fresh()->current_mileage)->toBe(30100);

    Storage::disk('public')->assertExists($fuelLog->voucher_photo_path);
});

test('admin can view fuel log in ViewFuelLog page', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs($this->adminUser);

    $fuelLog = FuelLog::factory()->create([
        'voucher_number' => 'V-VIEW-99',
        'mileage_at_refuel' => 45000,
    ]);

    Livewire::test(ViewFuelLog::class, ['record' => $fuelLog->id])
        ->assertSuccessful()
        ->assertSchemaStateSet([
            'voucher_number' => 'V-VIEW-99',
            'mileage_at_refuel' => 45000,
        ]);
});

test('admin can view vehicle and trip details with fuel logs relation manager (US6.3)', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs($this->adminUser);

    $vehicle = Vehicle::factory()->create();
    $fuelLog = FuelLog::factory()->create([
        'vehicle_id' => $vehicle->id,
        'voucher_number' => 'V-REL-VEH',
    ]);

    Livewire::test(ViewVehicle::class, ['record' => $vehicle->id])
        ->assertSuccessful();

    $trip = Trip::factory()->inProgress()->create(['vehicle_id' => $vehicle->id]);
    FuelLog::factory()->create([
        'trip_id' => $trip->id,
        'vehicle_id' => $vehicle->id,
        'voucher_number' => 'V-REL-TRIP',
    ]);

    Livewire::test(ViewTrip::class, ['record' => $trip->id])
        ->assertSuccessful();
});

test('driver can register fuel log for in-progress trip from driver panel (US6.1, US6.2)', function () {
    Filament::setCurrentPanel(Filament::getPanel('driver'));
    $this->actingAs($this->driverUser);

    $vehicle = Vehicle::factory()->inTrip()->create(['current_mileage' => 50000]);
    $trip = Trip::factory()->inProgress()->create([
        'driver_id' => $this->driverProfile->id,
        'vehicle_id' => $vehicle->id,
        'initial_mileage' => 50000,
    ]);

    $file = UploadedFile::fake()->image('voucher_driver_modal.jpg');

    Livewire::test(ListAssignedTrips::class)
        ->callTableAction('registerFuel', $trip, data: [
            'refuel_date' => now()->toDateTimeString(),
            'mileage_at_refuel' => 50150,
            'gallons' => 9.50,
            'total_cost' => 140000,
            'voucher_number' => 'V-DRIVER-777',
            'voucher_photo' => $file,
            'notes' => 'Tanqueo en carretera',
        ])
        ->assertHasNoTableActionErrors();

    $fuelLog = FuelLog::query()
        ->where('trip_id', $trip->id)
        ->where('voucher_number', 'V-DRIVER-777')
        ->first();

    expect($fuelLog)->not->toBeNull()
        ->and($fuelLog->vehicle_id)->toBe($vehicle->id)
        ->and($fuelLog->driver_id)->toBe($this->driverProfile->id)
        ->and((float) $fuelLog->gallons)->toBe(9.50)
        ->and($fuelLog->total_cost)->toBe(140000)
        ->and($fuelLog->mileage_at_refuel)->toBe(50150)
        ->and($vehicle->fresh()->current_mileage)->toBe(50150);

    $evidence = TripEvidence::query()
        ->where('trip_id', $trip->id)
        ->where('type', EvidenceTypeEnum::VOUCHER_COMBUSTIBLE)
        ->first();

    expect($evidence)->not->toBeNull()
        ->and($evidence->file_path)->toBe($fuelLog->voucher_photo_path);

    Storage::disk('public')->assertExists($fuelLog->voucher_photo_path);
});
