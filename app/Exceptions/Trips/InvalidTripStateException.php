<?php

declare(strict_types=1);

namespace App\Exceptions\Trips;

use App\Enums\Trips\TripStatusEnum;
use DomainException;

class InvalidTripStateException extends DomainException
{
    public static function cannotStart(string $code, TripStatusEnum $status): self
    {
        return new self("No se puede iniciar el viaje {$code} porque su estado actual es '{$status->label()}' (Se requiere estado 'Asignado').");
    }

    public static function cannotCancel(string $code, TripStatusEnum $status): self
    {
        return new self("No se puede cancelar el viaje {$code} porque su estado actual es '{$status->label()}'.");
    }

    public static function cannotFinish(string $code, TripStatusEnum $status): self
    {
        return new self("No se puede finalizar el viaje {$code} porque su estado actual es '{$status->label()}' (Se requiere estado 'En Curso').");
    }

    public static function cannotSign(string $code, TripStatusEnum $status): self
    {
        return new self("No se puede capturar la firma para el viaje {$code} porque su estado actual es '{$status->label()}' (Se requiere estado 'Finalizado').");
    }

    public static function cannotClose(string $code, TripStatusEnum $status): self
    {
        return new self("No se puede cerrar el viaje {$code} porque su estado actual es '{$status->label()}' (Se requiere estado 'Finalizado').");
    }
}
