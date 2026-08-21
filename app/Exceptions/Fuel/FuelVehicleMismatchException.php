<?php

declare(strict_types=1);

namespace App\Exceptions\Fuel;

use DomainException;

class FuelVehicleMismatchException extends DomainException
{
    public static function mismatch(int $givenVehicleId, int $tripVehicleId): self
    {
        return new self("El vehículo indicado (ID: {$givenVehicleId}) no coincide con el vehículo asignado al viaje (ID: {$tripVehicleId}).");
    }
}
