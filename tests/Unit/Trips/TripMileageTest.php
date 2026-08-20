<?php

declare(strict_types=1);

namespace Tests\Unit\Trips;

use App\Actions\Trips\RecordTripMileageAction;
use App\DTOs\Trips\RecordMileageDTO;
use App\Exceptions\Trips\InvalidTripMileageException;
use App\Exceptions\Trips\InvalidTripStateException;
use App\Exceptions\Trips\TripImmutableException;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\Vehicle;
use Database\Seeders\RoleSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    Storage::fake('public');
});

test('throws exception when recording negative mileage', function () {
    $vehicle = Vehicle::factory()->assigned()->create(['current_mileage' => 5000]);
    $driver = Driver::factory()->active()->create();
    $trip = Trip::factory()->assigned()->create([
        'vehicle_id' => $vehicle->id,
        'driver_id' => $driver->id,
    ]);

    $file = UploadedFile::fake()->image('odometer.jpg');
    $dto = new RecordMileageDTO(
        tripId: $trip->id,
        mileage: -100,
        photoEvidence: $file,
        isFinal: false,
    );

    app(RecordTripMileageAction::class)($dto);
})->throws(InvalidTripMileageException::class, 'El kilometraje no puede ser negativo');

test('throws exception when initial mileage is lower than current vehicle mileage', function () {
    $vehicle = Vehicle::factory()->assigned()->create(['current_mileage' => 15000]);
    $driver = Driver::factory()->active()->create();
    $trip = Trip::factory()->assigned()->create([
        'vehicle_id' => $vehicle->id,
        'driver_id' => $driver->id,
    ]);

    $file = UploadedFile::fake()->image('odometer.jpg');
    $dto = new RecordMileageDTO(
        tripId: $trip->id,
        mileage: 14999,
        photoEvidence: $file,
        isFinal: false,
    );

    app(RecordTripMileageAction::class)($dto);
})->throws(InvalidTripMileageException::class, 'no puede ser menor al kilometraje actual del vehículo');

test('throws exception when final mileage is less than or equal to initial mileage (R3)', function () {
    $vehicle = Vehicle::factory()->inTrip()->create(['current_mileage' => 10000]);
    $driver = Driver::factory()->active()->create();
    $trip = Trip::factory()->inProgress()->create([
        'vehicle_id' => $vehicle->id,
        'driver_id' => $driver->id,
        'initial_mileage' => 10000,
    ]);

    $file = UploadedFile::fake()->image('odometer_final.jpg');
    $dto = new RecordMileageDTO(
        tripId: $trip->id,
        mileage: 10000, // Equal to initial
        photoEvidence: $file,
        isFinal: true,
    );

    app(RecordTripMileageAction::class)($dto);
})->throws(InvalidTripMileageException::class, 'debe ser estrictamente mayor al kilometraje inicial registrado');

test('throws exception when recording departure on a non-assigned trip', function () {
    $trip = Trip::factory()->scheduled()->create();
    $file = UploadedFile::fake()->image('odometer.jpg');

    $dto = new RecordMileageDTO(
        tripId: $trip->id,
        mileage: 5000,
        photoEvidence: $file,
        isFinal: false,
    );

    app(RecordTripMileageAction::class)($dto);
})->throws(InvalidTripStateException::class, 'Se requiere estado \'Asignado\'');

test('throws exception when recording arrival on a trip not in progress', function () {
    $vehicle = Vehicle::factory()->assigned()->create(['current_mileage' => 5000]);
    $driver = Driver::factory()->active()->create();
    $trip = Trip::factory()->assigned()->create([
        'vehicle_id' => $vehicle->id,
        'driver_id' => $driver->id,
        'initial_mileage' => 5000,
    ]);

    $file = UploadedFile::fake()->image('odometer.jpg');
    $dto = new RecordMileageDTO(
        tripId: $trip->id,
        mileage: 5200,
        photoEvidence: $file,
        isFinal: true,
    );

    app(RecordTripMileageAction::class)($dto);
})->throws(InvalidTripStateException::class, 'Se requiere estado \'En Curso\'');

test('throws exception when recording mileage on immutable trip', function () {
    $trip = Trip::factory()->closed()->create();
    $file = UploadedFile::fake()->image('odometer.jpg');

    $dto = new RecordMileageDTO(
        tripId: $trip->id,
        mileage: 20000,
        photoEvidence: $file,
        isFinal: false,
    );

    app(RecordTripMileageAction::class)($dto);
})->throws(TripImmutableException::class);
