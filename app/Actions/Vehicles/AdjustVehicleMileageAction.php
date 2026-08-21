<?php

declare(strict_types=1);

namespace App\Actions\Vehicles;

use App\Enums\Vehicles\VehicleStatusEnum;
use App\Exceptions\Vehicles\InvalidMileageException;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class AdjustVehicleMileageAction
{
    /**
     * Adjust a vehicle's odometer with an audit trail note.
     * Allows corrections (increase or decrease) for administrative/calibration purposes.
     *
     * @throws InvalidMileageException
     * @throws \InvalidArgumentException
     */
    public function __invoke(Vehicle $vehicle, int $newMileage, string $reason): Vehicle
    {
        if ($vehicle->status === VehicleStatusEnum::EN_VIAJE) {
            throw InvalidMileageException::cannotAdjustInTrip($vehicle->plate_number);
        }

        if ($newMileage < 0) {
            throw InvalidMileageException::negativeMileage($newMileage);
        }

        $reason = trim($reason);
        if ($reason === '') {
            throw new \InvalidArgumentException('El motivo del ajuste de odómetro es obligatorio.');
        }

        return DB::transaction(function () use ($vehicle, $newMileage, $reason): Vehicle {
            $timestamp = now()->toDateTimeString();
            $auditEntry = "[{$timestamp}] Ajuste manual de odómetro: de {$vehicle->current_mileage} km a {$newMileage} km. Motivo: {$reason}";

            $notes = trim((string) $vehicle->notes);
            $updatedNotes = $notes !== '' ? $notes."\n".$auditEntry : $auditEntry;

            $vehicle->update([
                'current_mileage' => $newMileage,
                'notes' => $updatedNotes,
            ]);

            return $vehicle->refresh();
        });
    }
}
