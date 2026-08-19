<?php

declare(strict_types=1);

namespace App\Exceptions\Trips;

use DomainException;

class InvalidTripMileageException extends DomainException
{
    public static function negativeMileage(int $mileage): self
    {
        return new self("El kilometraje no puede ser negativo ({$mileage} km).");
    }

    public static function initialMileageDecreasing(int $vehicleMileage, int $inputMileage): self
    {
        return new self("El kilometraje inicial ({$inputMileage} km) no puede ser menor al kilometraje actual del vehículo ({$vehicleMileage} km).");
    }

    public static function finalMileageMustExceedInitial(int $initialMileage, int $finalMileage): self
    {
        return new self("El kilometraje final ({$finalMileage} km) debe ser estrictamente mayor al kilometraje inicial registrado ({$initialMileage} km).");
    }

    public static function missingInitialMileage(string $code): self
    {
        return new self("No se puede registrar el kilometraje final para el viaje {$code} porque no tiene un kilometraje inicial registrado.");
    }
}
