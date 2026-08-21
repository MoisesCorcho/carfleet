<?php

declare(strict_types=1);

namespace Tests\Feature\Fuel;

use App\Actions\Fuel\RegisterFuelLogAction;
use App\DTOs\Fuel\RegisterFuelLogDTO;
use App\Enums\Evidences\EvidenceTypeEnum;
use App\Exceptions\Fuel\FuelVehicleMismatchException;
use App\Exceptions\Fuel\FutureRefuelDateException;
use App\Exceptions\Fuel\InvalidFuelCostException;
use App\Exceptions\Fuel\InvalidFuelMileageException;
use App\Exceptions\Fuel\InvalidFuelQuantityException;
use App\Exceptions\Trips\TripImmutableException;
use App\Models\Driver;
use App\Models\FuelLog;
use App\Models\Trip;
use App\Models\TripEvidence;
use App\Models\Vehicle;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

test('can register fuel log for vehicle without trip (R1, US6.1, US6.3)', function () {
    $vehicle = Vehicle::factory()->create(['current_mileage' => 10000]);
    $file = UploadedFile::fake()->image('voucher_01.jpg');

    $dto = new RegisterFuelLogDTO(
        vehicleId: $vehicle->id,
        refuelDate: now()->subHours(2)->toDateTimeString(),
        mileageAtRefuel: 10150,
        gallons: 12.50,
        totalCost: 185000,
        voucherNumber: 'V-987654',
        voucherPhoto: $file,
        notes: 'Tanqueo en estación de servicio aliada'
    );

    $action = app(RegisterFuelLogAction::class);
    $fuelLog = $action($dto);

    expect($fuelLog)->toBeInstanceOf(FuelLog::class)
        ->and($fuelLog->vehicle_id)->toBe($vehicle->id)
        ->and($fuelLog->trip_id)->toBeNull()
        ->and($fuelLog->driver_id)->toBeNull()
        ->and((float) $fuelLog->gallons)->toBe(12.50)
        ->and($fuelLog->total_cost)->toBe(185000)
        ->and($fuelLog->mileage_at_refuel)->toBe(10150)
        ->and($fuelLog->voucher_number)->toBe('V-987654')
        ->and($fuelLog->voucher_photo_path)->not->toBeNull()
        ->and($vehicle->fresh()->current_mileage)->toBe(10150);

    Storage::disk('public')->assertExists($fuelLog->voucher_photo_path);
});

test('can register fuel log during active trip and creates synchronized trip evidence with voucher (R1, US6.1, US6.2)', function () {
    $vehicle = Vehicle::factory()->inTrip()->create(['current_mileage' => 20000]);
    $driver = Driver::factory()->active()->create();
    $trip = Trip::factory()->inProgress()->create([
        'vehicle_id' => $vehicle->id,
        'driver_id' => $driver->id,
        'initial_mileage' => 20000,
    ]);

    $file = UploadedFile::fake()->image('voucher_trip.jpg');

    $dto = new RegisterFuelLogDTO(
        vehicleId: $vehicle->id,
        refuelDate: now()->subMinutes(30)->toDateTimeString(),
        mileageAtRefuel: 20120,
        gallons: 8.75,
        totalCost: 130000,
        tripId: $trip->id,
        driverId: $driver->id,
        voucherNumber: 'VOUCH-1122',
        voucherPhoto: $file,
        notes: 'Tanqueo a mitad de ruta'
    );

    $action = app(RegisterFuelLogAction::class);
    $fuelLog = $action($dto);

    expect($fuelLog->trip_id)->toBe($trip->id)
        ->and($fuelLog->driver_id)->toBe($driver->id)
        ->and($vehicle->fresh()->current_mileage)->toBe(20120);

    // Verify synchronized TripEvidence
    $evidence = TripEvidence::query()
        ->where('trip_id', $trip->id)
        ->where('type', EvidenceTypeEnum::VOUCHER_COMBUSTIBLE)
        ->first();

    expect($evidence)->not->toBeNull()
        ->and($evidence->file_path)->toBe($fuelLog->voucher_photo_path)
        ->and($evidence->recorded_mileage)->toBe(20120);

    Storage::disk('public')->assertExists($fuelLog->voucher_photo_path);
});

test('updates vehicle current mileage if refuel mileage is greater (invariants)', function () {
    $vehicle = Vehicle::factory()->create(['current_mileage' => 15000]);

    $dto = new RegisterFuelLogDTO(
        vehicleId: $vehicle->id,
        refuelDate: now()->subHour()->toDateTimeString(),
        mileageAtRefuel: 15300,
        gallons: 10.0,
        totalCost: 150000,
    );

    $action = app(RegisterFuelLogAction::class);
    $action($dto);

    expect($vehicle->fresh()->current_mileage)->toBe(15300);
});

test('does not decrease vehicle current mileage on valid historic refuel (invariants)', function () {
    $vehicle = Vehicle::factory()->create(['current_mileage' => 16000]);

    $dto = new RegisterFuelLogDTO(
        vehicleId: $vehicle->id,
        refuelDate: now()->subDays(2)->toDateTimeString(),
        mileageAtRefuel: 15500,
        gallons: 10.0,
        totalCost: 150000,
    );

    $action = app(RegisterFuelLogAction::class);
    $action($dto);

    expect($vehicle->fresh()->current_mileage)->toBe(16000);
});

test('throws exception when gallons is zero or negative (R2)', function () {
    $vehicle = Vehicle::factory()->create();

    $dto = new RegisterFuelLogDTO(
        vehicleId: $vehicle->id,
        refuelDate: now()->toDateTimeString(),
        mileageAtRefuel: 1000,
        gallons: 0.0,
        totalCost: 50000,
    );

    app(RegisterFuelLogAction::class)($dto);
})->throws(InvalidFuelQuantityException::class);

test('throws exception when total cost is zero or negative (R2)', function () {
    $vehicle = Vehicle::factory()->create();

    $dto = new RegisterFuelLogDTO(
        vehicleId: $vehicle->id,
        refuelDate: now()->toDateTimeString(),
        mileageAtRefuel: 1000,
        gallons: 5.0,
        totalCost: 0,
    );

    app(RegisterFuelLogAction::class)($dto);
})->throws(InvalidFuelCostException::class);

test('throws exception when refuel mileage is negative', function () {
    $vehicle = Vehicle::factory()->create();

    $dto = new RegisterFuelLogDTO(
        vehicleId: $vehicle->id,
        refuelDate: now()->toDateTimeString(),
        mileageAtRefuel: -10,
        gallons: 5.0,
        totalCost: 50000,
    );

    app(RegisterFuelLogAction::class)($dto);
})->throws(InvalidFuelMileageException::class);

test('throws exception when refuel mileage in active trip is less than trip initial mileage', function () {
    $vehicle = Vehicle::factory()->inTrip()->create(['current_mileage' => 25000]);
    $trip = Trip::factory()->inProgress()->create([
        'vehicle_id' => $vehicle->id,
        'initial_mileage' => 25000,
    ]);

    $dto = new RegisterFuelLogDTO(
        vehicleId: $vehicle->id,
        refuelDate: now()->toDateTimeString(),
        mileageAtRefuel: 24900,
        gallons: 10.0,
        totalCost: 150000,
        tripId: $trip->id,
    );

    app(RegisterFuelLogAction::class)($dto);
})->throws(InvalidFuelMileageException::class);

test('throws exception when trip and vehicle do not match (aggregate integrity)', function () {
    $vehicle1 = Vehicle::factory()->create();
    $vehicle2 = Vehicle::factory()->inTrip()->create();
    $trip = Trip::factory()->inProgress()->create([
        'vehicle_id' => $vehicle2->id,
        'initial_mileage' => 5000,
    ]);

    $dto = new RegisterFuelLogDTO(
        vehicleId: $vehicle1->id,
        refuelDate: now()->toDateTimeString(),
        mileageAtRefuel: 5100,
        gallons: 10.0,
        totalCost: 150000,
        tripId: $trip->id,
    );

    app(RegisterFuelLogAction::class)($dto);
})->throws(FuelVehicleMismatchException::class);

test('throws exception when attempting to register fuel log for immutable trip', function () {
    $vehicle = Vehicle::factory()->create();
    $trip = Trip::factory()->cancelled()->create([
        'vehicle_id' => $vehicle->id,
    ]);

    $dto = new RegisterFuelLogDTO(
        vehicleId: $vehicle->id,
        refuelDate: now()->toDateTimeString(),
        mileageAtRefuel: 10000,
        gallons: 10.0,
        totalCost: 150000,
        tripId: $trip->id,
    );

    app(RegisterFuelLogAction::class)($dto);
})->throws(TripImmutableException::class);

test('throws exception when refuel date is in the future', function () {
    $vehicle = Vehicle::factory()->create();

    $dto = new RegisterFuelLogDTO(
        vehicleId: $vehicle->id,
        refuelDate: now()->addHours(3)->toDateTimeString(),
        mileageAtRefuel: 10000,
        gallons: 10.0,
        totalCost: 150000,
    );

    app(RegisterFuelLogAction::class)($dto);
})->throws(FutureRefuelDateException::class);

test('deletes voucher photo from storage when fuel log is deleted (lifecycle teardown)', function () {
    $vehicle = Vehicle::factory()->create();
    $file = UploadedFile::fake()->image('voucher_delete.jpg');

    $dto = new RegisterFuelLogDTO(
        vehicleId: $vehicle->id,
        refuelDate: now()->toDateTimeString(),
        mileageAtRefuel: 10000,
        gallons: 10.0,
        totalCost: 150000,
        voucherPhoto: $file,
    );

    $fuelLog = app(RegisterFuelLogAction::class)($dto);
    $path = $fuelLog->voucher_photo_path;

    Storage::disk('public')->assertExists($path);

    $fuelLog->delete();

    Storage::disk('public')->assertMissing($path);
});
