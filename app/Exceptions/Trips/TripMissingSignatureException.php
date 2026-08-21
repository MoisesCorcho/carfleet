<?php

declare(strict_types=1);

namespace App\Exceptions\Trips;

use DomainException;

class TripMissingSignatureException extends DomainException
{
    public static function forTrip(string $code): self
    {
        return new self("El viaje {$code} no puede cerrarse porque no cuenta con la firma digital de conformidad del solicitante.");
    }
}
