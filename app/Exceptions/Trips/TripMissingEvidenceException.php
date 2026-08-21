<?php

declare(strict_types=1);

namespace App\Exceptions\Trips;

use DomainException;

class TripMissingEvidenceException extends DomainException
{
    public static function missingDepartureOdometer(string $code): self
    {
        return new self("El viaje {$code} no cuenta con la fotografía de odómetro de salida.");
    }

    public static function missingArrivalOdometer(string $code): self
    {
        return new self("El viaje {$code} no cuenta con la fotografía de odómetro de llegada.");
    }
}
