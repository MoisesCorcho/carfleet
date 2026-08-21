<?php

declare(strict_types=1);

namespace App\Actions\Trips;

use App\Enums\Trips\TripStatusEnum;
use App\Enums\Vehicles\VehicleStatusEnum;
use App\Exceptions\Trips\DriverNotEligibleException;
use App\Exceptions\Trips\DriverScheduleConflictException;
use App\Exceptions\Trips\InvalidTripStateException;
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
     * @throws InvalidTripStateException
     * @throws DriverNotEligibleException
     * @throws DriverScheduleConflictException
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

            if (! ($lockedTrip->isScheduled() || $lockedTrip->isAssigned())) {
                throw InvalidTripStateException::cannotReassign($lockedTrip->code, $lockedTrip->status);
            }

            /** @var Driver $driver */
            $driver = Driver::where('id', $driverId)->lockForUpdate()->firstOrFail();

            if (! $driver->isActive()) {
                throw DriverNotEligibleException::inactive($driver->full_name);
            }

            if ($driver->isLicenseExpired()) {
                throw DriverNotEligibleException::expiredLicense($driver->full_name);
            }

            // Time-slot conflict validation: Check if driver is already scheduled in an overlapping interval
            $tripStart = $lockedTrip->scheduled_departure_at;
            $tripEnd = $lockedTrip->scheduled_arrival_at ?? $tripStart->copy()->addHours(2);

            $driverTrips = Trip::query()
                ->where('driver_id', $driver->id)
                ->where('id', '!=', $lockedTrip->id)
                ->whereIn('status', [TripStatusEnum::ASIGNADO, TripStatusEnum::EN_CURSO])
                ->get();

            foreach ($driverTrips as $otherTrip) {
                $otherStart = $otherTrip->scheduled_departure_at;
                $otherEnd = $otherTrip->scheduled_arrival_at ?? $otherStart->copy()->addHours(2);

                if ($tripStart < $otherEnd && $tripEnd > $otherStart) {
                    $conflictRange = $otherStart->format('d/m/Y H:i').' - '.$otherEnd->format('H:i');

                    throw DriverScheduleConflictException::forDriver($driver->full_name, $otherTrip->code, $conflictRange);
                }
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
