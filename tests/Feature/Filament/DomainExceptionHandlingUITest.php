<?php

declare(strict_types=1);

use App\Filament\Resources\Drivers\Pages\EditDriver;
use App\Filament\Resources\Drivers\Pages\ListDrivers;
use App\Filament\Resources\Requesters\Pages\EditRequester;
use App\Filament\Resources\Requesters\Pages\ListRequesters;
use App\Filament\Resources\Trips\Pages\EditTrip;
use App\Filament\Resources\Vehicles\Pages\EditVehicle;
use App\Filament\Resources\Vehicles\Pages\ListVehicles;
use App\Models\Driver;
use App\Models\Requester;
use App\Models\Trip;
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

test('cannot delete vehicle in service from table action and shows notification', function () {
    $this->actingAs($this->adminUser);

    $vehicle = Vehicle::factory()->inTrip()->create();
    Trip::factory()->inProgress()->create(['vehicle_id' => $vehicle->id]);

    Livewire::test(ListVehicles::class)
        ->callTableAction('delete', $vehicle)
        ->assertNotified();

    $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'deleted_at' => null]);
});

test('cannot delete vehicle in service from edit page header action', function () {
    $this->actingAs($this->adminUser);

    $vehicle = Vehicle::factory()->inTrip()->create();
    Trip::factory()->inProgress()->create(['vehicle_id' => $vehicle->id]);

    Livewire::test(EditVehicle::class, ['record' => $vehicle->getRouteKey()])
        ->callAction('delete')
        ->assertNotified();

    $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'deleted_at' => null]);
});

test('cannot delete driver with active trip from table action', function () {
    $this->actingAs($this->adminUser);

    $driver = Driver::factory()->active()->create();
    Trip::factory()->inProgress()->create(['driver_id' => $driver->id]);

    Livewire::test(ListDrivers::class)
        ->callTableAction('delete', $driver)
        ->assertNotified();

    $this->assertDatabaseHas('drivers', ['id' => $driver->id, 'deleted_at' => null]);
});

test('cannot delete driver with active trip from edit page', function () {
    $this->actingAs($this->adminUser);

    $driver = Driver::factory()->active()->create();
    Trip::factory()->inProgress()->create(['driver_id' => $driver->id]);

    Livewire::test(EditDriver::class, ['record' => $driver->getRouteKey()])
        ->callAction('delete')
        ->assertNotified();

    $this->assertDatabaseHas('drivers', ['id' => $driver->id, 'deleted_at' => null]);
});

test('cannot delete requester with active trip from table action', function () {
    $this->actingAs($this->adminUser);

    $requester = Requester::factory()->create();
    Trip::factory()->inProgress()->create(['requester_id' => $requester->id]);

    Livewire::test(ListRequesters::class)
        ->callTableAction('delete', $requester)
        ->assertNotified();

    $this->assertDatabaseHas('requesters', ['id' => $requester->id, 'deleted_at' => null]);
});

test('cannot delete requester with active trip from edit page', function () {
    $this->actingAs($this->adminUser);

    $requester = Requester::factory()->create();
    Trip::factory()->inProgress()->create(['requester_id' => $requester->id]);

    Livewire::test(EditRequester::class, ['record' => $requester->getRouteKey()])
        ->callAction('delete')
        ->assertNotified();

    $this->assertDatabaseHas('requesters', ['id' => $requester->id, 'deleted_at' => null]);
});

test('edit trip form disables core fields when trip is in progress', function () {
    $this->actingAs($this->adminUser);

    $trip = Trip::factory()->inProgress()->create();

    Livewire::test(EditTrip::class, ['record' => $trip->getRouteKey()])
        ->assertFormFieldIsDisabled('requester_id')
        ->assertFormFieldIsDisabled('origin')
        ->assertFormFieldIsDisabled('destination')
        ->assertFormFieldIsDisabled('scheduled_departure_at')
        ->assertFormFieldIsDisabled('scheduled_arrival_at')
        ->assertFormFieldIsDisabled('vehicle_id')
        ->assertFormFieldIsDisabled('driver_id')
        ->assertFormFieldIsEnabled('notes');
});

test('edit trip form disables core fields when trip is completed', function () {
    $this->actingAs($this->adminUser);

    $trip = Trip::factory()->completed()->create();

    Livewire::test(EditTrip::class, ['record' => $trip->getRouteKey()])
        ->assertFormFieldIsDisabled('requester_id')
        ->assertFormFieldIsDisabled('origin')
        ->assertFormFieldIsDisabled('destination')
        ->assertFormFieldIsDisabled('scheduled_departure_at')
        ->assertFormFieldIsDisabled('scheduled_arrival_at')
        ->assertFormFieldIsDisabled('vehicle_id')
        ->assertFormFieldIsDisabled('driver_id')
        ->assertFormFieldIsEnabled('notes');
});
