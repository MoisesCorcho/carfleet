<?php

declare(strict_types=1);

namespace App\Exceptions\Invoices;

use DomainException;

final class TripAlreadyInvoicedException extends DomainException
{
    public static function forTrip(string $tripCode, string $invoiceNumber): self
    {
        return new self("El viaje {$tripCode} ya fue incluido en la factura activa {$invoiceNumber}. No se permite la doble facturación de un mismo servicio.");
    }
}
