<?php

declare(strict_types=1);

namespace App\Actions\Trips;

use App\Enums\Trips\TripStatusEnum;
use App\Enums\Vehicles\VehicleStatusEnum;
use App\Exceptions\Trips\DriverNotEligibleException;
use App\Exceptions\Trips\TripImmutableException;
use App\Exceptions\Trips\VehicleNotAvailableException;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class AssignTripResourcesAction
{
    /**
     * Assign vehicle and driver resources to a scheduled or assigned trip.
     *
     * @throws TripImmutableException
     * @throws DriverNotEligibleException
     * @throws VehicleNotAvailableException
     */
    public function __invoke(Trip $trip, int $vehicleId, int $driverId): Trip
    {
        return DB::transaction(function () use ($trip, $vehicleId, $driverId): Trip {
            /** @var Trip $lockedTrip */
            $lockedTrip = Trip::where('id', $trip->id)->lockForUpdate()->firstOrFail();

            if ($lockedTrip->isImmutable()) {
                throw TripImmutableException::forTrip($lockedTrip->code, $lockedTrip->status);
            }

            /** @var Driver $driver */
            $driver = Driver::where('id', $driverId)->lockForUpdate()->firstOrFail();

            if (! $driver->isActive()) {
                throw DriverNotEligibleException::inactive($driver->full_name);
            }

            if ($driver->isLicenseExpired()) {
                throw DriverNotEligibleException::expiredLicense($driver->full_name);
            }

            /** @var Vehicle $vehicle */
            $vehicle = Vehicle::where('id', $vehicleId)->lockForUpdate()->firstOrFail();

            if ($vehicle->isPublicService() && ! $driver->canDrivePublicService()) {
                throw DriverNotEligibleException::notAuthorizedForPublicService($driver->full_name);
            }

            // If changing vehicle, validate availability and release previous vehicle
            if ($lockedTrip->vehicle_id !== $vehicle->id) {
                if (! $vehicle->isAvailable()) {
                    throw VehicleNotAvailableException::forVehicle($vehicle->plate_number, $vehicle->status);
                }

                if ($lockedTrip->vehicle_id) {
                    $previousVehicle = Vehicle::where('id', $lockedTrip->vehicle_id)->lockForUpdate()->first();
                    if ($previousVehicle && $previousVehicle->status === VehicleStatusEnum::ASIGNADO) {
                        $previousVehicle->update(['status' => VehicleStatusEnum::DISPONIBLE]);
                    }
                }

                $vehicle->update(['status' => VehicleStatusEnum::ASIGNADO]);
            }

            $lockedTrip->update([
                'vehicle_id' => $vehicle->id,
                'driver_id' => $driver->id,
                'status' => TripStatusEnum::ASIGNADO,
            ]);

            return $lockedTrip->fresh(['requester', 'vehicle', 'driver']);
        });
    }
}
