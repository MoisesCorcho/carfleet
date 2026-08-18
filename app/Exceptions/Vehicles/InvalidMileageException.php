<?php

declare(strict_types=1);

namespace App\Exceptions\Vehicles;

use DomainException;

class InvalidMileageException extends DomainException
{
    public static function negativeMileage(int $mileage): self
    {
        return new self("El kilometraje inicial no puede ser negativo ({$mileage} km).");
    }

    public static function decreasingMileage(int $currentMileage, int $newMileage): self
    {
        return new self("El nuevo kilometraje ({$newMileage} km) no puede ser menor al kilometraje actual registrado ({$currentMileage} km).");
    }
}
