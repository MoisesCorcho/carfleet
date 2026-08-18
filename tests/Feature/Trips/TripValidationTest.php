<?php

declare(strict_types=1);

namespace Tests\Feature\Trips;

use App\Actions\Trips\AssignTripResourcesAction;
use App\Actions\Trips\CancelTripAction;
use App\Actions\Trips\CreateTripAction;
use App\Actions\Trips\StartTripAction;
use App\DTOs\Trips\CreateTripDTO;
use App\Enums\Drivers\LicenseCategoryEnum;
use App\Exceptions\Trips\DriverNotEligibleException;
use App\Exceptions\Trips\InvalidTripDatesException;
use App\Exceptions\Trips\InvalidTripStateException;
use App\Exceptions\Trips\TripImmutableException;
use App\Exceptions\Trips\VehicleNotAvailableException;
use App\Models\Driver;
use App\Models\Requester;
use App\Models\Trip;
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
