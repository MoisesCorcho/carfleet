<?php

declare(strict_types=1);

namespace App\Exceptions\Invoices;

use DomainException;

final class RequesterMismatchException extends DomainException
{
    public static function forTrip(string $tripCode, string $expectedRequester, string $actualRequester): self
    {
        return new self("El viaje {$tripCode} pertenece al solicitante '{$actualRequester}' y no coincide con el solicitante de la factura '{$expectedRequester}'. Todos los viajes deben ser del mismo solicitante.");
    }
}
