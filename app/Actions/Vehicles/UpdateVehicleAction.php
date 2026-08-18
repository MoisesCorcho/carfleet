<?php

declare(strict_types=1);

namespace App\Actions\Vehicles;

use App\DTOs\Vehicles\UpsertVehicleDTO;
use App\Exceptions\Vehicles\InvalidMileageException;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class UpdateVehicleAction
{
    /**
     * Update an existing vehicle's details and invariants.
     *
     * @throws InvalidMileageException
     */
    public function __invoke(Vehicle $vehicle, UpsertVehicleDTO $dto): Vehicle
    {
        if ($dto->currentMileage < 0) {
            throw InvalidMileageException::negativeMileage($dto->currentMileage);
        }

        if ($dto->currentMileage < $vehicle->current_mileage) {
            throw InvalidMileageException::decreasingMileage($vehicle->current_mileage, $dto->currentMileage);
        }

        return DB::transaction(function () use ($vehicle, $dto): Vehicle {
            $vehicle->update([
                'plate_number' => $dto->plateNumber,
                'brand' => $dto->brand,
                'model' => $dto->model,
                'year' => $dto->year,
                'vehicle_type' => $dto->vehicleType,
                'service_type' => $dto->serviceType,
                'current_mileage' => $dto->currentMileage,
                'status' => $dto->status,
                'fuel_type' => $dto->fuelType,
                'notes' => $dto->notes,
            ]);

            return $vehicle->refresh();
        });
    }
}
