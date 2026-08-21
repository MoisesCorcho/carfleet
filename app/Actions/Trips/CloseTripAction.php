<?php

declare(strict_types=1);

namespace App\Actions\Trips;

use App\Enums\Evidences\EvidenceTypeEnum;
use App\Enums\Trips\TripStatusEnum;
use App\Enums\Vehicles\VehicleStatusEnum;
use App\Exceptions\Trips\InvalidTripMileageException;
use App\Exceptions\Trips\InvalidTripStateException;
use App\Exceptions\Trips\TripImmutableException;
use App\Exceptions\Trips\TripMissingEvidenceException;
use App\Exceptions\Trips\TripMissingSignatureException;
use App\Models\Trip;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class CloseTripAction
{
    /**
     * Formally close a completed trip after verifying all mileage readings,
     * photographic evidences (departure/arrival), and digital signature.
     *
     * @throws TripImmutableException
     * @throws InvalidTripStateException
     * @throws InvalidTripMileageException
     * @throws TripMissingEvidenceException
     * @throws TripMissingSignatureException
     */
    public function __invoke(Trip $trip): Trip
    {
        return DB::transaction(function () use ($trip): Trip {
            /** @var Trip $lockedTrip */
            $lockedTrip = Trip::where('id', $trip->id)->lockForUpdate()->firstOrFail();

            if ($lockedTrip->isImmutable()) {
                throw TripImmutableException::forTrip($lockedTrip->code, $lockedTrip->status);
            }

            if (! $lockedTrip->isCompleted()) {
                throw InvalidTripStateException::cannotClose($lockedTrip->code, $lockedTrip->status);
            }

            if ($lockedTrip->initial_mileage === null) {
                throw InvalidTripMileageException::missingInitialMileage($lockedTrip->code);
            }

            if ($lockedTrip->final_mileage === null || $lockedTrip->final_mileage <= $lockedTrip->initial_mileage) {
                throw InvalidTripMileageException::finalMileageMustExceedInitial(
                    $lockedTrip->initial_mileage,
                    $lockedTrip->final_mileage ?? 0
                );
            }

            $hasDepartureEvidence = $lockedTrip->evidences()
                ->where('type', EvidenceTypeEnum::KILOMETRAJE_SALIDA)
                ->exists();

            if (! $hasDepartureEvidence) {
                throw TripMissingEvidenceException::missingDepartureOdometer($lockedTrip->code);
            }

            $hasArrivalEvidence = $lockedTrip->evidences()
                ->where('type', EvidenceTypeEnum::KILOMETRAJE_LLEGADA)
                ->exists();

            if (! $hasArrivalEvidence) {
                throw TripMissingEvidenceException::missingArrivalOdometer($lockedTrip->code);
            }

            if (! $lockedTrip->signature()->exists()) {
                throw TripMissingSignatureException::forTrip($lockedTrip->code);
            }

            $lockedTrip->update([
                'status' => TripStatusEnum::CERRADO,
            ]);

            if ($lockedTrip->vehicle_id) {
                /** @var Vehicle|null $vehicle */
                $vehicle = Vehicle::where('id', $lockedTrip->vehicle_id)->lockForUpdate()->first();
                if ($vehicle && $vehicle->status !== VehicleStatusEnum::MANTENIMIENTO && $vehicle->status !== VehicleStatusEnum::FUERA_DE_SERVICIO) {
                    $vehicle->update(['status' => VehicleStatusEnum::DISPONIBLE]);
                }
            }

            return $lockedTrip->fresh(['requester', 'vehicle', 'driver', 'signature', 'evidences']);
        });
    }
}
