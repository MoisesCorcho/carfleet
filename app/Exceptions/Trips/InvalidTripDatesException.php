<?php

declare(strict_types=1);

namespace App\Exceptions\Trips;

use DomainException;

class InvalidTripDatesException extends DomainException
{
    public static function arrivalBeforeDeparture(string $departureAt, string $arrivalAt): self
    {
        return new self("La fecha de llegada programada ({$arrivalAt}) no puede ser anterior o igual a la fecha de salida ({$departureAt}).");
    }
}
