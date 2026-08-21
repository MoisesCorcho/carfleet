<?php

declare(strict_types=1);

namespace Tests\Feature\Trips;

use App\Actions\Trips\AssignTripResourcesAction;
use App\Actions\Trips\CancelTripAction;
use App\Actions\Trips\CreateTripAction;
use App\Actions\Trips\StartTripAction;
use App\DTOs\Trips\CreateTripDTO;
use App\Enums\Drivers\LicenseCategoryEnum;
use App\Exceptions\Trips\DriverAlreadyInTripException;
use App\Exceptions\Trips\DriverNotEligibleException;
use App\Exceptions\Trips\DriverScheduleConflictException;
use App\Exceptions\Trips\IncompleteTripResourcesException;
use App\Exceptions\Trips\InvalidTripDatesException;
use App\Exceptions\Trips\InvalidTripStateException;
use App\Exceptions\Trips\TripImmutableException;
use App\Exceptions\Trips\VehicleNotAvailableException;
use App\Models\DigitalSignature;
use App\Models\Driver;
use App\Models\Requester;
use App\Models\Trip;
use App\Models\TripEvidence;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->adminUser = User::factory()->create();
    $this->adminUser->assignRole('super_admin');
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

test('cannot assign vehicle that is not available (R4)', function () {
    $trip = Trip::factory()->scheduled()->create();
    $driver = Driver::factory()->active()->create();
    $vehicleInMaintenance = Vehicle::factory()->maintenance()->create();

    expect(fn () => app(AssignTripResourcesAction::class)($trip, $vehicleInMaintenance->id, $driver->id))
        ->toThrow(VehicleNotAvailableException::class);
});

test('cannot assign inactive driver or driver with expired license', function () {
    $trip = Trip::factory()->scheduled()->create();
    $vehicle = Vehicle::factory()->available()->create();

    $inactiveDriver = Driver::factory()->inactive()->create();
    expect(fn () => app(AssignTripResourcesAction::class)($trip, $vehicle->id, $inactiveDriver->id))
        ->toThrow(DriverNotEligibleException::class);

    $expiredDriver = Driver::factory()->expiredLicense()->create();
    expect(fn () => app(AssignTripResourcesAction::class)($trip, $vehicle->id, $expiredDriver->id))
        ->toThrow(DriverNotEligibleException::class);
});

test('cannot assign driver without public service license to public service vehicle (R8)', function () {
    $trip = Trip::factory()->scheduled()->create();
    $publicVehicle = Vehicle::factory()->available()->publicService()->create();
    $particularDriver = Driver::factory()->active()->withLicenseCategory(LicenseCategoryEnum::B1)->create();

    expect(fn () => app(AssignTripResourcesAction::class)($trip, $publicVehicle->id, $particularDriver->id))
        ->toThrow(DriverNotEligibleException::class);
});

test('cannot mutate or reassign closed or cancelled trips (R5)', function () {
    $closedTrip = Trip::factory()->closed()->create();
    $cancelledTrip = Trip::factory()->cancelled()->create();
    $vehicle = Vehicle::factory()->available()->create();
    $driver = Driver::factory()->active()->create();

    expect(fn () => app(AssignTripResourcesAction::class)($closedTrip, $vehicle->id, $driver->id))
        ->toThrow(TripImmutableException::class);

    expect(fn () => app(AssignTripResourcesAction::class)($cancelledTrip, $vehicle->id, $driver->id))
        ->toThrow(TripImmutableException::class);

    expect(fn () => app(StartTripAction::class)($closedTrip))
        ->toThrow(TripImmutableException::class);

    expect(fn () => app(CancelTripAction::class)($closedTrip))
        ->toThrow(TripImmutableException::class);
});

test('cannot start a trip that is not in assigned status', function () {
    $scheduledTrip = Trip::factory()->scheduled()->create();

    expect(fn () => app(StartTripAction::class)($scheduledTrip))
        ->toThrow(InvalidTripStateException::class);
});

test('cannot create trip with arrival date before or equal to departure date', function () {
    $requester = Requester::factory()->create();

    $dto = new CreateTripDTO(
        requesterId: $requester->id,
        origin: 'Punto A',
        destination: 'Punto B',
        scheduledDepartureAt: now()->addDay()->format('Y-m-d H:i:s'),
        scheduledArrivalAt: now()->addDay()->subHour()->format('Y-m-d H:i:s')
    );

    expect(fn () => app(CreateTripAction::class)($dto))
        ->toThrow(InvalidTripDatesException::class);
});

test('cannot start trip if driver already has another trip in progress (inconcurrencia fisica)', function () {
    $driver = Driver::factory()->active()->create();
    $vehicle1 = Vehicle::factory()->available()->create();
    $vehicle2 = Vehicle::factory()->available()->create();

    // Trip 1 already in progress with this driver
    $activeTrip = Trip::factory()->assigned()->create([
        'driver_id' => $driver->id,
        'vehicle_id' => $vehicle1->id,
    ]);
    app(StartTripAction::class)($activeTrip);
    expect($activeTrip->fresh()->isInProgress())->toBeTrue();

    // Trip 2 assigned to same driver
    $secondTrip = Trip::factory()->assigned()->create([
        'driver_id' => $driver->id,
        'vehicle_id' => $vehicle2->id,
    ]);

    expect(fn () => app(StartTripAction::class)($secondTrip))
        ->toThrow(DriverAlreadyInTripException::class);
});

test('cannot assign driver if there is a schedule conflict with another assigned trip', function () {
    $driver = Driver::factory()->active()->create();
    $vehicle1 = Vehicle::factory()->available()->create();
    $vehicle2 = Vehicle::factory()->available()->create();

    // Trip 1 on Tomorrow 10:00 - 14:00
    $trip1 = Trip::factory()->scheduled()->create([
        'scheduled_departure_at' => now()->addDay()->setTime(10, 0),
        'scheduled_arrival_at' => now()->addDay()->setTime(14, 0),
    ]);
    app(AssignTripResourcesAction::class)($trip1, $vehicle1->id, $driver->id);

    // Trip 2 on Tomorrow 12:00 - 16:00 (overlaps with Trip 1)
    $trip2 = Trip::factory()->scheduled()->create([
        'scheduled_departure_at' => now()->addDay()->setTime(12, 0),
        'scheduled_arrival_at' => now()->addDay()->setTime(16, 0),
    ]);

    expect(fn () => app(AssignTripResourcesAction::class)($trip2, $vehicle2->id, $driver->id))
        ->toThrow(DriverScheduleConflictException::class);
});

test('cannot create trip with partial resource assignment (vehicle without driver or vice versa)', function () {
    $requester = Requester::factory()->create();
    $vehicle = Vehicle::factory()->available()->create();
    $driver = Driver::factory()->active()->create();

    // Vehicle without driver
    $dtoVehicleOnly = new CreateTripDTO(
        requesterId: $requester->id,
        origin: 'Punto A',
        destination: 'Punto B',
        scheduledDepartureAt: now()->addDay()->format('Y-m-d H:i:s'),
        vehicleId: $vehicle->id,
        driverId: null
    );

    expect(fn () => app(CreateTripAction::class)($dtoVehicleOnly))
        ->toThrow(IncompleteTripResourcesException::class);

    // Driver without vehicle
    $dtoDriverOnly = new CreateTripDTO(
        requesterId: $requester->id,
        origin: 'Punto A',
        destination: 'Punto B',
        scheduledDepartureAt: now()->addDay()->format('Y-m-d H:i:s'),
        vehicleId: null,
        driverId: $driver->id
    );

    expect(fn () => app(CreateTripAction::class)($dtoDriverOnly))
        ->toThrow(IncompleteTripResourcesException::class);
});

test('cannot reassign resources to an in progress or completed trip (invariants)', function () {
    $vehicle1 = Vehicle::factory()->available()->create();
    $vehicle2 = Vehicle::factory()->available()->create();
    $driver1 = Driver::factory()->active()->create();
    $driver2 = Driver::factory()->active()->create();

    $inProgressTrip = Trip::factory()->inProgress()->create([
        'vehicle_id' => $vehicle1->id,
        'driver_id' => $driver1->id,
    ]);

    expect(fn () => app(AssignTripResourcesAction::class)($inProgressTrip, $vehicle2->id, $driver2->id))
        ->toThrow(InvalidTripStateException::class);

    $completedTrip = Trip::factory()->completed()->create([
        'vehicle_id' => $vehicle1->id,
        'driver_id' => $driver1->id,
    ]);

    expect(fn () => app(AssignTripResourcesAction::class)($completedTrip, $vehicle2->id, $driver2->id))
        ->toThrow(InvalidTripStateException::class);
});

test('cannot delete a trip that is in progress, completed or closed (invariants)', function () {
    $inProgressTrip = Trip::factory()->inProgress()->create();
    expect(fn () => $inProgressTrip->delete())->toThrow(InvalidTripStateException::class);

    $completedTrip = Trip::factory()->completed()->create();
    expect(fn () => $completedTrip->delete())->toThrow(InvalidTripStateException::class);

    $closedTrip = Trip::factory()->closed()->create();
    expect(fn () => $closedTrip->delete())->toThrow(InvalidTripStateException::class);
});

test('cannot delete trip evidence or digital signature from closed trip (inherited immutability)', function () {
    $closedTrip = Trip::factory()->closed()->create();

    $evidence = TripEvidence::factory()->create([
        'trip_id' => $closedTrip->id,
    ]);
    expect(fn () => $evidence->delete())->toThrow(TripImmutableException::class);

    $signature = DigitalSignature::factory()->create([
        'trip_id' => $closedTrip->id,
    ]);
    expect(fn () => $signature->delete())->toThrow(TripImmutableException::class);
});

test('cannot delete driver with active trips in progress or assigned (invariants)', function () {
    $driver = Driver::factory()->active()->create();
    $trip = Trip::factory()->inProgress()->create([
        'driver_id' => $driver->id,
    ]);

    expect(fn () => $driver->delete())->toThrow(DriverNotEligibleException::class);
});
