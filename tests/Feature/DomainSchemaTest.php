<?php

declare(strict_types=1);

use App\Enums\Drivers\DriverStatusEnum;
use App\Enums\Evidences\EvidenceTypeEnum;
use App\Enums\Trips\TripStatusEnum;
use App\Enums\Vehicles\FuelTypeEnum;
use App\Enums\Vehicles\VehicleStatusEnum;
use App\Models\DigitalSignature;
use App\Models\Driver;
use App\Models\FuelLog;
use App\Models\Invoice;
use App\Models\Requester;
use App\Models\Trip;
use App\Models\TripEvidence;
use App\Models\User;
use App\Models\Vehicle;

test('can create and relate all domain entities', function () {
    // 1. User & Driver
    $user = User::factory()->create();
    $driver = Driver::create([
        'user_id' => $user->id,
        'full_name' => 'Carlos Pérez',
        'document_number' => '1098765432',
        'phone' => '3001234567',
        'license_number' => 'LIC-1098765432',
        'status' => DriverStatusEnum::ACTIVO,
    ]);

    // 2. Vehicle
    $vehicle = Vehicle::create([
        'plate_number' => 'XYZ-123',
        'brand' => 'Chevrolet',
        'model' => 'NPR',
        'year' => 2024,
        'current_mileage' => 15000,
        'status' => VehicleStatusEnum::DISPONIBLE,
        'fuel_type' => FuelTypeEnum::DIESEL,
    ]);

    // 3. Requester
    $requester = Requester::create([
        'name' => 'Empresa Pueblo Nuevo S.A.S',
        'document_number' => '900123456-1',
        'phone' => '6017654321',
        'email' => 'contacto@pueblonuevo.co',
    ]);

    // 4. Trip
    $trip = Trip::create([
        'code' => 'TRIP-2026-0001',
        'requester_id' => $requester->id,
        'vehicle_id' => $vehicle->id,
        'driver_id' => $driver->id,
        'origin' => 'Pueblo Nuevo',
        'destination' => 'Montería',
        'scheduled_departure_at' => now(),
        'status' => TripStatusEnum::EN_CURSO,
        'initial_mileage' => 15000,
    ]);

    // 5. TripEvidence
    $evidence = TripEvidence::create([
        'trip_id' => $trip->id,
        'type' => EvidenceTypeEnum::KILOMETRAJE_SALIDA,
        'file_path' => 'evidences/odometers/salida.jpg',
        'recorded_mileage' => 15000,
    ]);

    // 6. FuelLog
    $fuelLog = FuelLog::create([
        'vehicle_id' => $vehicle->id,
        'trip_id' => $trip->id,
        'driver_id' => $driver->id,
        'refuel_date' => now(),
        'mileage_at_refuel' => 15050,
        'gallons' => 12.50,
        'total_cost' => 150000,
        'voucher_number' => 'VOUCH-9876',
    ]);

    // 7. DigitalSignature
    $signature = DigitalSignature::create([
        'trip_id' => $trip->id,
        'signer_name' => 'Juan Solicitante',
        'signature_path' => 'evidences/signatures/firma.png',
        'signed_at' => now(),
    ]);

    // 8. Invoice & Pivot
    $invoice = Invoice::create([
        'invoice_number' => 'FACT-2026-0001',
        'requester_id' => $requester->id,
        'issue_date' => now(),
        'total_amount' => 500000,
        'status' => 'issued',
    ]);
    $invoice->trips()->attach($trip->id, ['subtotal_amount' => 500000]);

    // Assertions using Pest expectations & database assertions
    $this->assertDatabaseHas('drivers', ['document_number' => '1098765432']);
    $this->assertDatabaseHas('vehicles', ['plate_number' => 'XYZ-123']);
    $this->assertDatabaseHas('trips', ['code' => 'TRIP-2026-0001']);
    expect($trip->driver->id)->toBe($driver->id);
    expect($trip->vehicle->id)->toBe($vehicle->id);
    expect($trip->requester->id)->toBe($requester->id);
    expect($trip->evidences)->toHaveCount(1);
    expect($trip->fuelLogs)->toHaveCount(1);
    expect($trip->signature)->not->toBeNull();
    expect($invoice->trips)->toHaveCount(1);
});
