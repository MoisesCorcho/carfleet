<?php

declare(strict_types=1);

namespace App\Exceptions\Trips;

use App\Enums\Vehicles\VehicleStatusEnum;
use DomainException;

class VehicleNotAvailableException extends DomainException
{
    public static function forVehicle(string $plateNumber, VehicleStatusEnum $status): self
    {
        return new self("El vehículo {$plateNumber} no está disponible para asignación (Estado actual: {$status->label()}).");
    }

    public static function cannotDeleteInService(string $plateNumber, VehicleStatusEnum $status): self
    {
        return new self("No se puede eliminar el vehículo {$plateNumber} porque tiene servicios activos o asignados (Estado actual: {$status->label()}).");
    }
}
