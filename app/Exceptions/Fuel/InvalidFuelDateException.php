<?php

declare(strict_types=1);

namespace App\Exceptions\Fuel;

use DomainException;

class InvalidFuelDateException extends DomainException
{
    public static function beforeTripDeparture(string $departureDate, string $refuelDate): self
    {
        return new self("La fecha y hora del tanqueo ({$refuelDate}) no puede ser anterior a la salida del viaje ({$departureDate}).");
    }

    public static function afterTripArrival(string $arrivalDate, string $refuelDate): self
    {
        return new self("La fecha y hora del tanqueo ({$refuelDate}) no puede ser posterior a la llegada registrada del viaje ({$arrivalDate}).");
    }
}
