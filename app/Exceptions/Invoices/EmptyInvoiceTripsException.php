<?php

declare(strict_types=1);

namespace App\Exceptions\Invoices;

use DomainException;

final class EmptyInvoiceTripsException extends DomainException
{
    public static function make(): self
    {
        return new self('Debes seleccionar al menos un viaje cerrado para poder generar una factura.');
    }
}
