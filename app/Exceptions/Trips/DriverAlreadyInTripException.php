<?php

declare(strict_types=1);

namespace App\Exceptions\Trips;

use DomainException;

class DriverAlreadyInTripException extends DomainException
{
    public static function forDriver(string $driverName, string $activeTripCode): self
    {
        return new self("El conductor {$driverName} ya tiene el viaje {$activeTripCode} en curso. Debe finalizarlo antes de iniciar un nuevo servicio.");
    }
}
