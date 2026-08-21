<?php

declare(strict_types=1);

namespace App\Exceptions\Fuel;

use DomainException;

class InvalidFuelMileageException extends DomainException
{
    public static function negativeMileage(int $mileage): self
    {
        return new self("El odómetro en el tanqueo no puede ser negativo ({$mileage} km).");
    }

    public static function belowTripInitialMileage(int $initialMileage, int $refuelMileage): self
    {
        return new self("El kilometraje del tanqueo ({$refuelMileage} km) no puede ser menor al kilometraje de inicio del viaje ({$initialMileage} km).");
    }
}
