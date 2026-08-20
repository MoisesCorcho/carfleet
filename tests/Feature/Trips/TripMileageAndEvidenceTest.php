<?php

declare(strict_types=1);

namespace Tests\Feature\Trips;

use App\Actions\Trips\RecordTripMileageAction;
use App\DTOs\Trips\RecordMileageDTO;
use App\Enums\Evidences\EvidenceTypeEnum;
use App\Enums\Trips\TripStatusEnum;
use App\Enums\Vehicles\VehicleStatusEnum;
use App\Filament\Driver\Resources\Trips\Pages\ListAssignedTrips;
use App\Filament\Resources\Trips\Pages\ViewTrip;
use App\Models\Driver;
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

    $this->driverUser = User::factory()->create();
    $this->driverUser->assignRole('driver');
    $this->driverProfile = Driver::factory()->active()->create([
        'user_id' => $this->driverUser->id,
        'full_name' => 'Carlos Conductor',
    ]);

    $this->adminUser = User::factory()->create();
    $this->adminUser->assignRole('super_admin');
});

test('driver records initial mileage and evidence photo to start trip (US5.1 & R1)', function () {
    $vehicle = Vehicle::factory()->assigned()->create(['current_mileage' => 12500]);
    $trip = Trip::factory()->assigned()->create([
        'driver_id' => $this->driverProfile->id,
        'vehicle_id' => $vehicle->id,
    ]);

    $file = UploadedFile::fake()->image('odometer_start.jpg');
    $dto = new RecordMileageDTO(
        tripId: $trip->id,
        mileage: 12550,
        photoEvidence: $file,
        isFinal: false,
        notes: 'Salida sin novedades',
    );

    $updatedTrip = app(RecordTripMileageAction::class)($dto);

    expect($updatedTrip->status)->toBe(TripStatusEnum::EN_CURSO)
        ->and($updatedTrip->initial_mileage)->toBe(12550)
        ->and($updatedTrip->actual_departure_at)->not->toBeNull()
        ->and($vehicle->fresh()->status)->toBe(VehicleStatusEnum::EN_VIAJE)
        ->and($vehicle->fresh()->current_mileage)->toBe(12550);

    $evidence = TripEvidence::where('trip_id', $trip->id)->first();
    expect($evidence)->not->toBeNull()
        ->and($evidence->type)->toBe(EvidenceTypeEnum::KILOMETRAJE_SALIDA)
        ->and($evidence->recorded_mileage)->toBe(12550)
        ->and($evidence->notes)->toBe('Salida sin novedades');

    Storage::disk('public')->assertExists($evidence->file_path);
});

test('driver records final mileage, evidence photo and calculates distance traveled (US5.2 & R2)', function () {
    $vehicle = Vehicle::factory()->inTrip()->create(['current_mileage' => 12550]);
    $trip = Trip::factory()->inProgress()->create([
        'driver_id' => $this->driverProfile->id,
        'vehicle_id' => $vehicle->id,
        'initial_mileage' => 12550,
    ]);

    $file = UploadedFile::fake()->image('odometer_finish.jpg');
    $dto = new RecordMileageDTO(
        tripId: $trip->id,
        mileage: 12800,
        photoEvidence: $file,
        isFinal: true,
        notes: 'Llegada a destino',
    );

    $updatedTrip = app(RecordTripMileageAction::class)($dto);

    expect($updatedTrip->status)->toBe(TripStatusEnum::FINALIZADO)
        ->and($updatedTrip->final_mileage)->toBe(12800)
        ->and($updatedTrip->distance_traveled)->toBe(250) // 12800 - 12550
        ->and($updatedTrip->actual_arrival_at)->not->toBeNull()
        ->and($vehicle->fresh()->status)->toBe(VehicleStatusEnum::DISPONIBLE)
        ->and($vehicle->fresh()->current_mileage)->toBe(12800);

    $evidence = TripEvidence::where('trip_id', $trip->id)
        ->where('type', EvidenceTypeEnum::KILOMETRAJE_LLEGADA)
        ->first();

    expect($evidence)->not->toBeNull()
        ->and($evidence->recorded_mileage)->toBe(12800)
        ->and($evidence->notes)->toBe('Llegada a destino');

    Storage::disk('public')->assertExists($evidence->file_path);
});

test('driver can start trip with mileage and photo in Driver Panel (US5.1)', function () {
    Filament::setCurrentPanel(Filament::getPanel('driver'));
    $this->actingAs($this->driverUser);

    $vehicle = Vehicle::factory()->assigned()->create(['current_mileage' => 20000]);
    $trip = Trip::factory()->assigned()->create([
        'driver_id' => $this->driverProfile->id,
        'vehicle_id' => $vehicle->id,
    ]);

    $file = UploadedFile::fake()->image('odometer_modal.jpg');

    Livewire::test(ListAssignedTrips::class)
        ->callTableAction('startTrip', $trip, data: [
            'initial_mileage' => 20050,
            'photo_evidence' => $file,
            'notes' => 'Inicio de servicio desde app',
        ])
        ->assertHasNoTableActionErrors();

    expect($trip->fresh()->status)->toBe(TripStatusEnum::EN_CURSO)
        ->and($trip->fresh()->initial_mileage)->toBe(20050)
        ->and($vehicle->fresh()->status)->toBe(VehicleStatusEnum::EN_VIAJE);
});

test('driver can finish trip with mileage and photo in Driver Panel (US5.2)', function () {
    Filament::setCurrentPanel(Filament::getPanel('driver'));
    $this->actingAs($this->driverUser);

    $vehicle = Vehicle::factory()->inTrip()->create(['current_mileage' => 20050]);
    $trip = Trip::factory()->inProgress()->create([
        'driver_id' => $this->driverProfile->id,
        'vehicle_id' => $vehicle->id,
        'initial_mileage' => 20050,
    ]);

    $file = UploadedFile::fake()->image('odometer_finish_modal.jpg');

    Livewire::test(ListAssignedTrips::class)
        ->callTableAction('finishTrip', $trip, data: [
            'final_mileage' => 20300,
            'photo_evidence' => $file,
            'notes' => 'Fin de servicio',
        ])
        ->assertHasNoTableActionErrors();

    expect($trip->fresh()->status)->toBe(TripStatusEnum::FINALIZADO)
        ->and($trip->fresh()->final_mileage)->toBe(20300)
        ->and($trip->fresh()->distance_traveled)->toBe(250)
        ->and($vehicle->fresh()->status)->toBe(VehicleStatusEnum::DISPONIBLE);
});

test('admin can see mileage and evidence in Trip view (US5.3)', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs($this->adminUser);

    $trip = Trip::factory()->completed()->create([
        'initial_mileage' => 50000,
        'final_mileage' => 50350,
        'distance_traveled' => 350,
    ]);

    $evidence1 = TripEvidence::factory()->departureMileage()->create([
        'trip_id' => $trip->id,
        'recorded_mileage' => 50000,
    ]);

    $evidence2 = TripEvidence::factory()->arrivalMileage()->create([
        'trip_id' => $trip->id,
        'recorded_mileage' => 50350,
    ]);

    Livewire::test(ViewTrip::class, ['record' => $trip->id])
        ->assertSuccessful()
        ->assertSchemaStateSet([
            'initial_mileage' => 50000,
            'final_mileage' => 50350,
            'distance_traveled' => 350,
        ]);
});
