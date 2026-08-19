<?php

declare(strict_types=1);

namespace Tests\Feature\Trips;

use App\Actions\Trips\AssignTripResourcesAction;
use App\Actions\Trips\CancelTripAction;
use App\Actions\Trips\CreateTripAction;
use App\Actions\Trips\StartTripAction;
use App\DTOs\Trips\CreateTripDTO;
use App\Enums\Trips\TripStatusEnum;
use App\Enums\Vehicles\VehicleStatusEnum;
use App\Filament\Resources\Trips\Pages\CreateTrip;
use App\Filament\Resources\Trips\Pages\EditTrip;
use App\Filament\Resources\Trips\Pages\ListTrips;
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

test('admin can access trips list in filament admin panel', function () {
    $this->actingAs($this->adminUser);

    Trip::factory()->count(3)->create();

    Livewire::test(ListTrips::class)
        ->assertSuccessful()
        ->assertCountTableRecords(3);
});

test('admin can create a scheduled trip without initial resources via Action (R1)', function () {
    $requester = Requester::factory()->create();

    $dto = new CreateTripDTO(
        requesterId: $requester->id,
        origin: 'Bogotá - Calle 26 #50',
        destination: 'Medellín - El Poblado',
        scheduledDepartureAt: now()->addDay()->format('Y-m-d H:i:s'),
        scheduledArrivalAt: now()->addDay()->addHours(8)->format('Y-m-d H:i:s'),
        notes: 'Carga prioritaria'
    );

    $trip = app(CreateTripAction::class)($dto);

    expect($trip)->toBeInstanceOf(Trip::class)
        ->and($trip->status)->toBe(TripStatusEnum::PROGRAMADO)
        ->and($trip->code)->toMatch('/^TRIP-\d{4}-\d{4}$/')
        ->and($trip->vehicle_id)->toBeNull()
        ->and($trip->driver_id)->toBeNull();
});

test('consecutive trip codes are generated sequentially per year (D4.3)', function () {
    $requester = Requester::factory()->create();
    $year = (int) date('Y');

    $dto1 = new CreateTripDTO(
        requesterId: $requester->id,
        origin: 'Punto A',
        destination: 'Punto B',
        scheduledDepartureAt: now()->addDays(1)->format('Y-m-d H:i:s')
    );

    $dto2 = new CreateTripDTO(
        requesterId: $requester->id,
        origin: 'Punto C',
        destination: 'Punto D',
        scheduledDepartureAt: now()->addDays(2)->format('Y-m-d H:i:s')
    );

    $trip1 = app(CreateTripAction::class)($dto1);
    $trip2 = app(CreateTripAction::class)($dto2);

    expect($trip1->code)->toBe("TRIP-{$year}-0001")
        ->and($trip2->code)->toBe("TRIP-{$year}-0002");
});

test('admin can create a trip with immediate resource assignment (D4.7)', function () {
    $requester = Requester::factory()->create();
    $vehicle = Vehicle::factory()->available()->create();
    $driver = Driver::factory()->active()->create();

    $dto = new CreateTripDTO(
        requesterId: $requester->id,
        origin: 'Sede Central',
        destination: 'Planta Sur',
        scheduledDepartureAt: now()->addDay()->format('Y-m-d H:i:s'),
        vehicleId: $vehicle->id,
        driverId: $driver->id
    );

    $trip = app(CreateTripAction::class)($dto);

    expect($trip->status)->toBe(TripStatusEnum::ASIGNADO)
        ->and($trip->vehicle_id)->toBe($vehicle->id)
        ->and($trip->driver_id)->toBe($driver->id)
        ->and($vehicle->fresh()->status)->toBe(VehicleStatusEnum::ASIGNADO);
});

test('assign resources action sets trip to assigned and vehicle to assigned (R2)', function () {
    $trip = Trip::factory()->scheduled()->create();
    $vehicle = Vehicle::factory()->available()->create();
    $driver = Driver::factory()->active()->create();

    $updatedTrip = app(AssignTripResourcesAction::class)($trip, $vehicle->id, $driver->id);

    expect($updatedTrip->status)->toBe(TripStatusEnum::ASIGNADO)
        ->and($updatedTrip->vehicle_id)->toBe($vehicle->id)
        ->and($updatedTrip->driver_id)->toBe($driver->id)
        ->and($vehicle->fresh()->status)->toBe(VehicleStatusEnum::ASIGNADO);
});

test('reassigning vehicle releases previous vehicle back to available (R7)', function () {
    $vehicle1 = Vehicle::factory()->available()->create(['plate_number' => 'ABC-123']);
    $vehicle2 = Vehicle::factory()->available()->create(['plate_number' => 'XYZ-789']);
    $driver = Driver::factory()->active()->create();

    $trip = Trip::factory()->scheduled()->create();
    app(AssignTripResourcesAction::class)($trip, $vehicle1->id, $driver->id);

    expect($vehicle1->fresh()->status)->toBe(VehicleStatusEnum::ASIGNADO);

    // Reasignar al vehículo 2
    app(AssignTripResourcesAction::class)($trip->fresh(), $vehicle2->id, $driver->id);

    expect($vehicle1->fresh()->status)->toBe(VehicleStatusEnum::DISPONIBLE)
        ->and($vehicle2->fresh()->status)->toBe(VehicleStatusEnum::ASIGNADO)
        ->and($trip->fresh()->vehicle_id)->toBe($vehicle2->id);
});

test('start trip action sets trip to in_progress and vehicle to en_viaje (R3)', function () {
    $vehicle = Vehicle::factory()->assigned()->create();
    $driver = Driver::factory()->active()->create();
    $trip = Trip::factory()->assigned()->create([
        'vehicle_id' => $vehicle->id,
        'driver_id' => $driver->id,
    ]);

    $startedTrip = app(StartTripAction::class)($trip);

    expect($startedTrip->status)->toBe(TripStatusEnum::EN_CURSO)
        ->and($startedTrip->actual_departure_at)->not->toBeNull()
        ->and($vehicle->fresh()->status)->toBe(VehicleStatusEnum::EN_VIAJE);
});

test('cancel trip action transitions to cancelled and releases vehicle (R6)', function () {
    $vehicle = Vehicle::factory()->assigned()->create();
    $driver = Driver::factory()->active()->create();
    $trip = Trip::factory()->assigned()->create([
        'vehicle_id' => $vehicle->id,
        'driver_id' => $driver->id,
    ]);

    $cancelledTrip = app(CancelTripAction::class)($trip, 'Cliente canceló el traslado');

    expect($cancelledTrip->status)->toBe(TripStatusEnum::CANCELADO)
        ->and($cancelledTrip->notes)->toContain('[Cancelación]: Cliente canceló el traslado')
        ->and($vehicle->fresh()->status)->toBe(VehicleStatusEnum::DISPONIBLE);
});

test('admin can create trip from filament form', function () {
    $this->actingAs($this->adminUser);

    $requester = Requester::factory()->create();

    Livewire::test(CreateTrip::class)
        ->fillForm([
            'requester_id' => $requester->id,
            'origin' => 'Cartagena Centro',
            'destination' => 'Barranquilla Norte',
            'scheduled_departure_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'scheduled_arrival_at' => now()->addDays(2)->addHours(3)->format('Y-m-d H:i:s'),
            'notes' => 'Viaje ejecutivo',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('trips', [
        'requester_id' => $requester->id,
        'origin' => 'Cartagena Centro',
        'destination' => 'Barranquilla Norte',
        'status' => TripStatusEnum::PROGRAMADO->value,
    ]);
});

test('admin can assign resources when editing trip from filament form', function () {
    $this->actingAs($this->adminUser);

    $trip = Trip::factory()->scheduled()->create();
    $vehicle = Vehicle::factory()->available()->create();
    $driver = Driver::factory()->active()->create();

    Livewire::test(EditTrip::class, ['record' => $trip->getKey()])
        ->fillForm([
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($trip->fresh()->status)->toBe(TripStatusEnum::ASIGNADO)
        ->and($trip->fresh()->vehicle_id)->toBe($vehicle->id)
        ->and($trip->fresh()->driver_id)->toBe($driver->id)
        ->and($vehicle->fresh()->status)->toBe(VehicleStatusEnum::ASIGNADO);
});

test('filament trip form requires both vehicle and driver if one is selected', function () {
    $this->actingAs($this->adminUser);

    $requester = Requester::factory()->create();
    $vehicle = Vehicle::factory()->available()->create();
    $driver = Driver::factory()->active()->create();

    // Fill vehicle only -> driver_id must have required_with error
    Livewire::test(CreateTrip::class)
        ->fillForm([
            'requester_id' => $requester->id,
            'origin' => 'Origen',
            'destination' => 'Destino',
            'scheduled_departure_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'vehicle_id' => $vehicle->id,
            'driver_id' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['driver_id' => 'required_with']);

    // Fill driver only -> vehicle_id must have required_with error
    Livewire::test(CreateTrip::class)
        ->fillForm([
            'requester_id' => $requester->id,
            'origin' => 'Origen',
            'destination' => 'Destino',
            'scheduled_departure_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'vehicle_id' => null,
            'driver_id' => $driver->id,
        ])
        ->call('create')
        ->assertHasFormErrors(['vehicle_id' => 'required_with']);
});

test('deleting an assigned trip automatically releases the assigned vehicle back to available', function () {
    $vehicle = Vehicle::factory()->assigned()->create();
    $driver = Driver::factory()->active()->create();
    $trip = Trip::factory()->assigned()->create([
        'vehicle_id' => $vehicle->id,
        'driver_id' => $driver->id,
    ]);

    expect($vehicle->fresh()->status)->toBe(VehicleStatusEnum::ASIGNADO);

    $trip->delete();

    expect($vehicle->fresh()->status)->toBe(VehicleStatusEnum::DISPONIBLE);
});
