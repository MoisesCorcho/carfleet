<?php

declare(strict_types=1);

namespace App\Exceptions\Requesters;

use DomainException;

class InvalidRequesterException extends DomainException
{
    public static function documentAlreadyRegistered(string $documentType, string $documentNumber): self
    {
        return new self("Ya existe un solicitante registrado con el documento {$documentType} {$documentNumber}.");
    }

    public static function cannotDeleteWithActiveTrips(string $name): self
    {
        return new self("No se puede eliminar el solicitante {$name} porque tiene servicios programados o en curso.");
    }

    public static function cannotDeactivateWithActiveTrips(string $name): self
    {
        return new self("No se puede desactivar el solicitante {$name} porque tiene servicios programados o en curso.");
    }
}
