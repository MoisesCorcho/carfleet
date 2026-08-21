<?php

declare(strict_types=1);

namespace Tests\Unit\Trips;

use App\Enums\Evidences\EvidenceTypeEnum;
use App\Exceptions\Trips\TripImmutableException;
use App\Models\DigitalSignature;
use App\Models\FuelLog;
use App\Models\Trip;
use App\Models\TripEvidence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SatelliteRecordsImmutabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_update_fuel_log_of_closed_trip(): void
    {
        $trip = Trip::factory()->closed()->create(['code' => 'TRIP-CLOSED-01']);
        $fuelLog = FuelLog::factory()->create([
            'trip_id' => $trip->id,
            'gallons' => 20.0,
        ]);

        $this->expectException(TripImmutableException::class);
        $this->expectExceptionMessage("El viaje TRIP-CLOSED-01 se encuentra en estado 'Cerrado' y no puede ser modificado.");

        $fuelLog->update(['gallons' => 25.0]);
    }

    public function test_cannot_update_trip_evidence_of_closed_trip(): void
    {
        $trip = Trip::factory()->closed()->create(['code' => 'TRIP-COMP-01']);
        $evidence = TripEvidence::factory()->create([
            'trip_id' => $trip->id,
            'type' => EvidenceTypeEnum::KILOMETRAJE_SALIDA,
            'notes' => 'Nota original',
        ]);

        $this->expectException(TripImmutableException::class);
        $this->expectExceptionMessage("El viaje TRIP-COMP-01 se encuentra en estado 'Cerrado' y no puede ser modificado.");

        $evidence->update(['notes' => 'Nota alterada']);
    }

    public function test_cannot_update_digital_signature_of_closed_trip(): void
    {
        $trip = Trip::factory()->closed()->create(['code' => 'TRIP-CLOSED-02']);
        $signature = DigitalSignature::create([
            'trip_id' => $trip->id,
            'signer_name' => 'Receptor Original',
            'signature_path' => 'signatures/sig1.png',
            'signed_at' => now(),
        ]);

        $this->expectException(TripImmutableException::class);
        $this->expectExceptionMessage("El viaje TRIP-CLOSED-02 se encuentra en estado 'Cerrado' y no puede ser modificado.");

        $signature->update(['signer_name' => 'Falsificado']);
    }

    public function test_can_update_satellite_records_for_in_progress_trip(): void
    {
        $trip = Trip::factory()->inProgress()->create();
        $fuelLog = FuelLog::factory()->create([
            'trip_id' => $trip->id,
            'gallons' => 15.0,
        ]);
        $evidence = TripEvidence::factory()->create([
            'trip_id' => $trip->id,
            'notes' => 'Nota borrador',
        ]);

        $fuelLog->update(['gallons' => 18.0]);
        $evidence->update(['notes' => 'Nota corregida']);

        $this->assertEquals(18.0, $fuelLog->fresh()->gallons);
        $this->assertSame('Nota corregida', $evidence->fresh()->notes);
    }
}
