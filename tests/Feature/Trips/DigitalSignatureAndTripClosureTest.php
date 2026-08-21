<?php

declare(strict_types=1);

namespace Tests\Feature\Trips;

use App\Actions\Trips\CaptureTripSignatureAction;
use App\Actions\Trips\CloseTripAction;
use App\DTOs\Trips\CaptureSignatureDTO;
use App\Enums\Trips\TripStatusEnum;
use App\Enums\Vehicles\VehicleStatusEnum;
use App\Exceptions\Trips\InvalidSignatureDataException;
use App\Exceptions\Trips\InvalidTripMileageException;
use App\Exceptions\Trips\InvalidTripStateException;
use App\Exceptions\Trips\TripImmutableException;
use App\Exceptions\Trips\TripMissingEvidenceException;
use App\Exceptions\Trips\TripMissingSignatureException;
use App\Filament\Driver\Resources\Trips\Pages\ListAssignedTrips;
use App\Filament\Resources\Trips\Pages\ViewTrip;
use App\Models\DigitalSignature;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\TripEvidence;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    Storage::fake('public');

    $this->driverUser = User::factory()->create();
    $this->driverUser->assignRole('driver');
    $this->driverProfile = Driver::factory()->active()->create([
        'user_id' => $this->driverUser->id,
        'full_name' => 'Mario Conductor',
    ]);

    $this->adminUser = User::factory()->create();
    $this->adminUser->assignRole('super_admin');

    // 1x1 transparent PNG Base64 sample
    $this->sampleBase64Signature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
});

test('driver captures digital signature for completed trip (US7.1 & R1)', function () {
    $trip = Trip::factory()->completed()->create([
        'driver_id' => $this->driverProfile->id,
    ]);

    $dto = new CaptureSignatureDTO(
        tripId: $trip->id,
        signerName: 'Juan Pérez - Solicitante',
        signatureBase64: $this->sampleBase64Signature,
    );

    $signature = app(CaptureTripSignatureAction::class)($dto);

    expect($signature)->toBeInstanceOf(DigitalSignature::class)
        ->and($signature->trip_id)->toBe($trip->id)
        ->and($signature->signer_name)->toBe('Juan Pérez - Solicitante')
        ->and($signature->signed_at)->not->toBeNull()
        ->and($signature->signature_path)->not->toBeEmpty();

    Storage::disk('public')->assertExists($signature->signature_path);

    expect($trip->fresh()->signature)->not->toBeNull()
        ->and($trip->fresh()->signature->id)->toBe($signature->id);
});

test('driver can re-sign (replace signature) before trip is closed and cleans old file', function () {
    $trip = Trip::factory()->completed()->create([
        'driver_id' => $this->driverProfile->id,
    ]);

    $firstSignature = app(CaptureTripSignatureAction::class)(new CaptureSignatureDTO(
        tripId: $trip->id,
        signerName: 'Juan Pérez',
        signatureBase64: $this->sampleBase64Signature,
    ));

    $oldPath = $firstSignature->signature_path;
    Storage::disk('public')->assertExists($oldPath);

    $secondSignature = app(CaptureTripSignatureAction::class)(new CaptureSignatureDTO(
        tripId: $trip->id,
        signerName: 'Juan Pérez Corregido',
        signatureBase64: $this->sampleBase64Signature,
    ));

    expect(DigitalSignature::where('trip_id', $trip->id)->count())->toBe(1)
        ->and($secondSignature->signer_name)->toBe('Juan Pérez Corregido')
        ->and(Storage::disk('public')->exists($oldPath))->toBeFalse()
        ->and(Storage::disk('public')->exists($secondSignature->signature_path))->toBeTrue();
});

test('signature capture fails if signer name or base64 is empty or invalid format', function () {
    $trip = Trip::factory()->completed()->create();

    // Empty signer name
    expect(fn () => app(CaptureTripSignatureAction::class)(new CaptureSignatureDTO(
        tripId: $trip->id,
        signerName: '   ',
        signatureBase64: $this->sampleBase64Signature,
    )))->toThrow(InvalidSignatureDataException::class);

    // Empty base64
    expect(fn () => app(CaptureTripSignatureAction::class)(new CaptureSignatureDTO(
        tripId: $trip->id,
        signerName: 'Juan Pérez',
        signatureBase64: '',
    )))->toThrow(InvalidSignatureDataException::class);

    // Corrupted base64
    expect(fn () => app(CaptureTripSignatureAction::class)(new CaptureSignatureDTO(
        tripId: $trip->id,
        signerName: 'Juan Pérez',
        signatureBase64: 'data:image/png;base64,not-a-valid-base-64-string!!!',
    )))->toThrow(InvalidSignatureDataException::class);
});

test('signature capture fails if trip is not completed or is already closed/cancelled', function () {
    $scheduledTrip = Trip::factory()->scheduled()->create();
    $inProgressTrip = Trip::factory()->inProgress()->create();
    $closedTrip = Trip::factory()->closed()->create();

    expect(fn () => app(CaptureTripSignatureAction::class)(new CaptureSignatureDTO(
        tripId: $scheduledTrip->id,
        signerName: 'Juan Pérez',
        signatureBase64: $this->sampleBase64Signature,
    )))->toThrow(InvalidTripStateException::class);

    expect(fn () => app(CaptureTripSignatureAction::class)(new CaptureSignatureDTO(
        tripId: $inProgressTrip->id,
        signerName: 'Juan Pérez',
        signatureBase64: $this->sampleBase64Signature,
    )))->toThrow(InvalidTripStateException::class);

    expect(fn () => app(CaptureTripSignatureAction::class)(new CaptureSignatureDTO(
        tripId: $closedTrip->id,
        signerName: 'Juan Pérez',
        signatureBase64: $this->sampleBase64Signature,
    )))->toThrow(TripImmutableException::class);
});

test('formal trip closure succeeds with complete evidences and digital signature (US7.2 & R2)', function () {
    $vehicle = Vehicle::factory()->available()->create(['current_mileage' => 10500]);
    $trip = Trip::factory()->completed()->create([
        'vehicle_id' => $vehicle->id,
        'driver_id' => $this->driverProfile->id,
        'initial_mileage' => 10000,
        'final_mileage' => 10500,
        'distance_traveled' => 500,
    ]);

    // Attach required evidences
    TripEvidence::factory()->departureMileage()->create([
        'trip_id' => $trip->id,
        'recorded_mileage' => 10000,
    ]);
    TripEvidence::factory()->arrivalMileage()->create([
        'trip_id' => $trip->id,
        'recorded_mileage' => 10500,
    ]);

    // Attach signature
    DigitalSignature::factory()->create([
        'trip_id' => $trip->id,
        'signer_name' => 'Carlos Solicitante',
    ]);

    $closedTrip = app(CloseTripAction::class)($trip);

    expect($closedTrip->status)->toBe(TripStatusEnum::CERRADO)
        ->and($closedTrip->isClosed())->toBeTrue()
        ->and($closedTrip->isImmutable())->toBeTrue()
        ->and($vehicle->fresh()->status)->toBe(VehicleStatusEnum::DISPONIBLE);
});

test('trip closure fails if digital signature is missing (US7.2 & R3)', function () {
    $trip = Trip::factory()->completed()->create([
        'initial_mileage' => 10000,
        'final_mileage' => 10500,
    ]);

    TripEvidence::factory()->departureMileage()->create(['trip_id' => $trip->id]);
    TripEvidence::factory()->arrivalMileage()->create(['trip_id' => $trip->id]);

    expect(fn () => app(CloseTripAction::class)($trip))
        ->toThrow(TripMissingSignatureException::class);
});

test('trip closure fails if departure or arrival odometer evidence is missing (US7.2 & R3)', function () {
    $trip = Trip::factory()->completed()->create([
        'initial_mileage' => 10000,
        'final_mileage' => 10500,
    ]);
    DigitalSignature::factory()->create(['trip_id' => $trip->id]);

    // Missing departure evidence
    TripEvidence::factory()->arrivalMileage()->create(['trip_id' => $trip->id]);

    expect(fn () => app(CloseTripAction::class)($trip))
        ->toThrow(TripMissingEvidenceException::class);

    // Add departure, remove arrival
    TripEvidence::where('trip_id', $trip->id)->delete();
    TripEvidence::factory()->departureMileage()->create(['trip_id' => $trip->id]);

    expect(fn () => app(CloseTripAction::class)($trip))
        ->toThrow(TripMissingEvidenceException::class);
});

test('trip closure fails if initial or final mileage is invalid or missing', function () {
    $tripWithoutInitial = Trip::factory()->completed()->create([
        'initial_mileage' => null,
        'final_mileage' => 10500,
    ]);
    DigitalSignature::factory()->create(['trip_id' => $tripWithoutInitial->id]);
    TripEvidence::factory()->departureMileage()->create(['trip_id' => $tripWithoutInitial->id]);
    TripEvidence::factory()->arrivalMileage()->create(['trip_id' => $tripWithoutInitial->id]);

    expect(fn () => app(CloseTripAction::class)($tripWithoutInitial))
        ->toThrow(InvalidTripMileageException::class);
});

test('trip closure fails if trip is in invalid state or already closed', function () {
    $inProgressTrip = Trip::factory()->inProgress()->create();
    expect(fn () => app(CloseTripAction::class)($inProgressTrip))
        ->toThrow(InvalidTripStateException::class);

    $closedTrip = Trip::factory()->closed()->create();
    expect(fn () => app(CloseTripAction::class)($closedTrip))
        ->toThrow(TripImmutableException::class);
});

test('driver can capture signature and close trip in Driver Panel (US7.1 & US7.2)', function () {
    Filament::setCurrentPanel(Filament::getPanel('driver'));
    $this->actingAs($this->driverUser);

    $trip = Trip::factory()->completed()->create([
        'driver_id' => $this->driverProfile->id,
        'initial_mileage' => 20000,
        'final_mileage' => 20250,
    ]);

    TripEvidence::factory()->departureMileage()->create(['trip_id' => $trip->id]);
    TripEvidence::factory()->arrivalMileage()->create(['trip_id' => $trip->id]);

    // 1. Capture signature via Table Action
    Livewire::test(ListAssignedTrips::class)
        ->callTableAction('captureSignature', $trip, data: [
            'signer_name' => 'Dra. Andrea Gómez',
            'signature_data' => $this->sampleBase64Signature,
        ])
        ->assertHasNoTableActionErrors();

    expect($trip->fresh()->signature)->not->toBeNull()
        ->and($trip->fresh()->signature->signer_name)->toBe('Dra. Andrea Gómez');

    // 2. Close trip via Table Action
    Livewire::test(ListAssignedTrips::class)
        ->callTableAction('closeTrip', $trip)
        ->assertHasNoTableActionErrors();

    expect($trip->fresh()->status)->toBe(TripStatusEnum::CERRADO)
        ->and($trip->fresh()->isClosed())->toBeTrue();
});

test('admin can see digital signature and close trip in Admin Panel (US7.2 & US7.3)', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs($this->adminUser);

    $trip = Trip::factory()->completed()->create([
        'initial_mileage' => 30000,
        'final_mileage' => 30400,
    ]);

    TripEvidence::factory()->departureMileage()->create(['trip_id' => $trip->id]);
    TripEvidence::factory()->arrivalMileage()->create(['trip_id' => $trip->id]);
    $signature = DigitalSignature::factory()->create([
        'trip_id' => $trip->id,
        'signer_name' => 'Lic. Roberto Solís',
    ]);

    Livewire::test(ViewTrip::class, ['record' => $trip->id])
        ->assertSuccessful()
        ->callAction('closeTrip')
        ->assertHasNoActionErrors();

    expect($trip->fresh()->status)->toBe(TripStatusEnum::CERRADO);
});
