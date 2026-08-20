<?php

declare(strict_types=1);

namespace Tests\Feature\Trips;

use App\Enums\Trips\TripStatusEnum;
use App\Enums\Vehicles\VehicleStatusEnum;
use App\Filament\Driver\Resources\Trips\Pages\ListAssignedTrips;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->driverUser = User::factory()->create();
    $this->driverUser->assignRole('driver');
    $this->driverProfile = Driver::factory()->active()->create([
        'user_id' => $this->driverUser->id,
        'full_name' => 'Carlos Conductor',
    ]);

    $this->otherDriverUser = User::factory()->create();
    $this->otherDriverUser->assignRole('driver');
    $this->otherDriverProfile = Driver::factory()->active()->create([
        'user_id' => $this->otherDriverUser->id,
        'full_name' => 'Pedro Chofer',
    ]);

    $this->adminUser = User::factory()->create();
    $this->adminUser->assignRole('super_admin');
});

test('driver can access driver panel and cannot access admin panel (D4.4)', function () {
    $adminPanel = Filament::getPanel('admin');
    $driverPanel = Filament::getPanel('driver');

    expect($this->driverUser->canAccessPanel($driverPanel))->toBeTrue()
        ->and($this->driverUser->canAccessPanel($adminPanel))->toBeFalse()
        ->and($this->adminUser->canAccessPanel($adminPanel))->toBeTrue()
        ->and($this->adminUser->canAccessPanel($driverPanel))->toBeFalse();
});

test('driver in driver panel only sees their assigned trips (D4.2 & R3)', function () {
    Filament::setCurrentPanel(Filament::getPanel('driver'));
    $this->actingAs($this->driverUser);

    $myTrip = Trip::factory()->assigned()->create([
        'driver_id' => $this->driverProfile->id,
        'origin' => 'Origen de Carlos',
    ]);

    $otherTrip = Trip::factory()->assigned()->create([
        'driver_id' => $this->otherDriverProfile->id,
        'origin' => 'Origen de Pedro',
    ]);

    Livewire::test(ListAssignedTrips::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$myTrip])
        ->assertCanNotSeeTableRecords([$otherTrip]);
});

test('driver can start assigned trip from driver panel (R3)', function () {
    Storage::fake('public');
    Filament::setCurrentPanel(Filament::getPanel('driver'));
    $this->actingAs($this->driverUser);

    $vehicle = Vehicle::factory()->assigned()->create(['current_mileage' => 10000]);
    $myTrip = Trip::factory()->assigned()->create([
        'driver_id' => $this->driverProfile->id,
        'vehicle_id' => $vehicle->id,
    ]);

    $file = UploadedFile::fake()->image('odometer.jpg');

    Livewire::test(ListAssignedTrips::class)
        ->callTableAction('startTrip', $myTrip, data: [
            'initial_mileage' => 10000,
            'photo_evidence' => $file,
        ])
        ->assertHasNoTableActionErrors();

    expect($myTrip->fresh()->status)->toBe(TripStatusEnum::EN_CURSO)
        ->and($myTrip->fresh()->actual_departure_at)->not->toBeNull()
        ->and($vehicle->fresh()->status)->toBe(VehicleStatusEnum::EN_VIAJE);
});
