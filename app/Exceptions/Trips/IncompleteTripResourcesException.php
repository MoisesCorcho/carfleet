<?php

declare(strict_types=1);

namespace App\Exceptions\Trips;

use DomainException;

class IncompleteTripResourcesException extends DomainException
{
    public static function forPartialAssignment(): self
    {
        return new self('Para asignar recursos a un viaje debes seleccionar tanto el vehículo como el conductor.');
    }
}
