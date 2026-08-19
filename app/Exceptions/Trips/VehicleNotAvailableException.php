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
}
