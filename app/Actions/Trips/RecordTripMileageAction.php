<?php

declare(strict_types=1);

namespace App\Actions\Trips;

use App\DTOs\Trips\RecordMileageDTO;
use App\Enums\Evidences\EvidenceTypeEnum;
use App\Enums\Trips\TripStatusEnum;
use App\Enums\Vehicles\VehicleStatusEnum;
use App\Exceptions\Trips\DriverAlreadyInTripException;
use App\Exceptions\Trips\InvalidTripMileageException;
use App\Exceptions\Trips\InvalidTripStateException;
use App\Exceptions\Trips\TripImmutableException;
use App\Models\Trip;
use App\Models\TripEvidence;
use App\Models\Vehicle;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class RecordTripMileageAction
{
    /**
     * Record odometer reading and evidence photo for trip departure or arrival.
     *
     * @throws TripImmutableException
     * @throws InvalidTripStateException
     * @throws InvalidTripMileageException
     * @throws DriverAlreadyInTripException
     */
    public function __invoke(RecordMileageDTO $dto): Trip
    {
        return DB::transaction(function () use ($dto): Trip {
            /** @var Trip $lockedTrip */
            $lockedTrip = Trip::where('id', $dto->tripId)->lockForUpdate()->firstOrFail();

            if ($lockedTrip->isImmutable()) {
                throw TripImmutableException::forTrip($lockedTrip->code, $lockedTrip->status);
            }

            if ($dto->mileage < 0) {
                throw InvalidTripMileageException::negativeMileage($dto->mileage);
            }

            if (! $dto->isFinal) {
                return $this->handleDeparture($lockedTrip, $dto);
            }

            return $this->handleArrival($lockedTrip, $dto);
        });
    }

    private function handleDeparture(Trip $trip, RecordMileageDTO $dto): Trip
    {
        if (! $trip->isAssigned()) {
            throw InvalidTripStateException::cannotStart($trip->code, $trip->status);
        }

        if ($trip->driver_id) {
            /** @var Trip|null $activeTrip */
            $activeTrip = Trip::query()
                ->where('driver_id', $trip->driver_id)
                ->where('status', TripStatusEnum::EN_CURSO)
                ->where('id', '!=', $trip->id)
                ->first();

            if ($activeTrip) {
                $driverName = $trip->driver?->full_name ?? 'asignado';

                throw DriverAlreadyInTripException::forDriver($driverName, $activeTrip->code);
            }
        }

        /** @var Vehicle $vehicle */
        $vehicle = Vehicle::where('id', $trip->vehicle_id)->lockForUpdate()->firstOrFail();

        if ($dto->mileage < $vehicle->current_mileage) {
            throw InvalidTripMileageException::initialMileageDecreasing($vehicle->current_mileage, $dto->mileage);
        }

        $filePath = $dto->photoEvidence instanceof UploadedFile
            ? $dto->photoEvidence->store('evidences/odometers', 'public')
            : (string) $dto->photoEvidence;

        TripEvidence::create([
            'trip_id' => $trip->id,
            'type' => EvidenceTypeEnum::KILOMETRAJE_SALIDA,
            'file_path' => $filePath,
            'recorded_mileage' => $dto->mileage,
            'notes' => $dto->notes,
        ]);

        $trip->update([
            'status' => TripStatusEnum::EN_CURSO,
            'actual_departure_at' => now(),
            'initial_mileage' => $dto->mileage,
        ]);

        $vehicle->update([
            'status' => VehicleStatusEnum::EN_VIAJE,
            'current_mileage' => $dto->mileage,
        ]);

        return $trip->fresh(['requester', 'vehicle', 'driver', 'evidences']);
    }

    private function handleArrival(Trip $trip, RecordMileageDTO $dto): Trip
    {
        if (! $trip->isInProgress()) {
            throw InvalidTripStateException::cannotFinish($trip->code, $trip->status);
        }

        if ($trip->initial_mileage === null) {
            throw InvalidTripMileageException::missingInitialMileage($trip->code);
        }

        if ($dto->mileage <= $trip->initial_mileage) {
            throw InvalidTripMileageException::finalMileageMustExceedInitial($trip->initial_mileage, $dto->mileage);
        }

        /** @var Vehicle $vehicle */
        $vehicle = Vehicle::where('id', $trip->vehicle_id)->lockForUpdate()->firstOrFail();

        $filePath = $dto->photoEvidence instanceof UploadedFile
            ? $dto->photoEvidence->store('evidences/odometers', 'public')
            : (string) $dto->photoEvidence;

        TripEvidence::create([
            'trip_id' => $trip->id,
            'type' => EvidenceTypeEnum::KILOMETRAJE_LLEGADA,
            'file_path' => $filePath,
            'recorded_mileage' => $dto->mileage,
            'notes' => $dto->notes,
        ]);

        $distanceTraveled = $dto->mileage - $trip->initial_mileage;

        $trip->update([
            'status' => TripStatusEnum::FINALIZADO,
            'actual_arrival_at' => now(),
            'final_mileage' => $dto->mileage,
            'distance_traveled' => $distanceTraveled,
        ]);

        $vehicle->update([
            'status' => VehicleStatusEnum::DISPONIBLE,
            'current_mileage' => $dto->mileage,
        ]);

        return $trip->fresh(['requester', 'vehicle', 'driver', 'evidences']);
    }
}
