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

class CancelTripAction
{
    /**
     * Cancel a scheduled or assigned trip and release reserved vehicle.
     *
     * @throws TripImmutableException
     * @throws InvalidTripStateException
     */
    public function __invoke(Trip $trip, ?string $reason = null): Trip
    {
        return DB::transaction(function () use ($trip, $reason): Trip {
            /** @var Trip $lockedTrip */
            $lockedTrip = Trip::where('id', $trip->id)->lockForUpdate()->firstOrFail();

            if ($lockedTrip->isImmutable()) {
                throw TripImmutableException::forTrip($lockedTrip->code, $lockedTrip->status);
            }

            if ($lockedTrip->isInProgress() || $lockedTrip->isCompleted()) {
                throw InvalidTripStateException::cannotCancel($lockedTrip->code, $lockedTrip->status);
            }

            if ($lockedTrip->vehicle_id) {
                $vehicle = Vehicle::where('id', $lockedTrip->vehicle_id)->lockForUpdate()->first();
                if ($vehicle && $vehicle->status === VehicleStatusEnum::ASIGNADO) {
                    $vehicle->update(['status' => VehicleStatusEnum::DISPONIBLE]);
                }
            }

            $notes = $lockedTrip->notes;
            if ($reason !== null && trim($reason) !== '') {
                $formattedReason = '[Cancelación]: '.trim($reason);
                $notes = $notes ? "{$notes}\n{$formattedReason}" : $formattedReason;
            }

            $lockedTrip->update([
                'status' => TripStatusEnum::CANCELADO,
                'notes' => $notes,
            ]);

            return $lockedTrip->fresh(['requester', 'vehicle', 'driver']);
        });
    }
}
