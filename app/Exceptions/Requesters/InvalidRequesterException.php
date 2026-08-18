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
}
