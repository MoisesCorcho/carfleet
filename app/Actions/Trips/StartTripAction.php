<?php

declare(strict_types=1);

namespace App\Actions\Trips;

use App\Enums\Trips\TripStatusEnum;
use App\Enums\Vehicles\VehicleStatusEnum;
use App\Exceptions\Trips\InvalidTripStateException;
use App\Exceptions\Trips\TripImmutableException;
use App\Models\Trip;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class StartTripAction
{
    /**
     * Start an assigned trip by setting actual departure and updating statuses.
     *
     * @throws TripImmutableException
     * @throws InvalidTripStateException
     */
    public function __invoke(Trip $trip, ?int $initialMileage = null): Trip
    {
        return DB::transaction(function () use ($trip, $initialMileage): Trip {
            /** @var Trip $lockedTrip */
            $lockedTrip = Trip::where('id', $trip->id)->lockForUpdate()->firstOrFail();

            if ($lockedTrip->isImmutable()) {
                throw TripImmutableException::forTrip($lockedTrip->code, $lockedTrip->status);
            }

            if (! $lockedTrip->isAssigned()) {
                throw InvalidTripStateException::cannotStart($lockedTrip->code, $lockedTrip->status);
            }

            /** @var Vehicle $vehicle */
            $vehicle = Vehicle::where('id', $lockedTrip->vehicle_id)->lockForUpdate()->firstOrFail();

            $updateData = [
                'status' => TripStatusEnum::EN_CURSO,
                'actual_departure_at' => now(),
            ];

            if ($initialMileage !== null) {
                $updateData['initial_mileage'] = $initialMileage;
            }

            $lockedTrip->update($updateData);
            $vehicle->update(['status' => VehicleStatusEnum::EN_VIAJE]);

            return $lockedTrip->fresh(['requester', 'vehicle', 'driver']);
        });
    }
}
