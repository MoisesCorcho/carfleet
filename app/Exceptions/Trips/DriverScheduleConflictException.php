<?php

declare(strict_types=1);

namespace App\Exceptions\Trips;

use DomainException;

class DriverScheduleConflictException extends DomainException
{
    public static function forDriver(string $driverName, string $conflictingTripCode, string $conflictRange): self
    {
        return new self("El conductor {$driverName} presenta conflicto de horario con el viaje {$conflictingTripCode} ({$conflictRange}).");
    }
}
