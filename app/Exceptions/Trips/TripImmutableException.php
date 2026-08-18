<?php

declare(strict_types=1);

namespace App\Exceptions\Trips;

use App\Enums\Trips\TripStatusEnum;
use DomainException;

class TripImmutableException extends DomainException
{
    public static function forTrip(string $code, TripStatusEnum $status): self
    {
        return new self("El viaje {$code} se encuentra en estado '{$status->label()}' y no puede ser modificado.");
    }
}
