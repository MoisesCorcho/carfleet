<?php

declare(strict_types=1);

namespace App\Actions\Vehicles;

use App\DTOs\Vehicles\UpsertVehicleDTO;
use App\Exceptions\Vehicles\InvalidMileageException;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class RegisterVehicleAction
{
    /**
     * Register a new vehicle in the fleet.
     *
     * @throws InvalidMileageException
     */
    public function __invoke(UpsertVehicleDTO $dto): Vehicle
    {
        if ($dto->currentMileage < 0) {
            throw InvalidMileageException::negativeMileage($dto->currentMileage);
        }

        return DB::transaction(function () use ($dto): Vehicle {
            return Vehicle::create([
                'plate_number' => $dto->plateNumber,
                'brand' => $dto->brand,
                'model' => $dto->model,
                'year' => $dto->year,
                'current_mileage' => $dto->currentMileage,
                'status' => $dto->status,
                'fuel_type' => $dto->fuelType,
                'notes' => $dto->notes,
            ]);
        });
    }
}
