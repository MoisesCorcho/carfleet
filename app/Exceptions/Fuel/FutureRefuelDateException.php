<?php

declare(strict_types=1);

namespace App\Exceptions\Fuel;

use DomainException;

class FutureRefuelDateException extends DomainException
{
    public static function futureDate(string $date): self
    {
        return new self("La fecha del tanqueo ({$date}) no puede ser una fecha futura.");
    }
}
