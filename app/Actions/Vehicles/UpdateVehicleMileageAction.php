<?php

declare(strict_types=1);

namespace App\Actions\Vehicles;

use App\Exceptions\Vehicles\InvalidMileageException;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class UpdateVehicleMileageAction
{
    /**
     * Update the vehicle's current mileage enforcing the non-decreasing invariant.
     *
     * @throws InvalidMileageException
     */
    public function __invoke(Vehicle $vehicle, int $newMileage): Vehicle
    {
        if ($newMileage < 0) {
            throw InvalidMileageException::negativeMileage($newMileage);
        }

        if ($newMileage < $vehicle->current_mileage) {
            throw InvalidMileageException::decreasingMileage($vehicle->current_mileage, $newMileage);
        }

        return DB::transaction(function () use ($vehicle, $newMileage): Vehicle {
            $vehicle->update([
                'current_mileage' => $newMileage,
            ]);

            return $vehicle->refresh();
        });
    }
}
