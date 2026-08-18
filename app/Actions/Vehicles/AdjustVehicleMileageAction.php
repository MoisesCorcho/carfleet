<?php

declare(strict_types=1);

namespace App\Actions\Vehicles;

use App\Exceptions\Vehicles\InvalidMileageException;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class AdjustVehicleMileageAction
{
    /**
     * Adjust a vehicle's odometer with an audit trail note.
     *
     * @throws InvalidMileageException
     */
    public function __invoke(Vehicle $vehicle, int $newMileage, string $reason): Vehicle
    {
        if ($newMileage < 0) {
            throw InvalidMileageException::negativeMileage($newMileage);
        }

        if ($newMileage < $vehicle->current_mileage) {
            throw InvalidMileageException::decreasingMileage($vehicle->current_mileage, $newMileage);
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
