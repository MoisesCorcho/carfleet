<?php

declare(strict_types=1);

namespace App\Exceptions\Trips;

use DomainException;

class InvalidSignatureDataException extends DomainException
{
    public static function emptySignerName(): self
    {
        return new self('El nombre del solicitante / firmante es obligatorio y no puede estar vacío.');
    }

    public static function emptySignature(): self
    {
        return new self('El trazo de la firma digital no puede estar vacío.');
    }

    public static function invalidFormat(): self
    {
        return new self('El formato del trazo de firma digital no es una imagen Base64 válida.');
    }
}
