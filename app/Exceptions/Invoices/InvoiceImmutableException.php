<?php

declare(strict_types=1);

namespace App\Exceptions\Invoices;

use App\Enums\Invoices\InvoiceStatusEnum;
use DomainException;

final class InvoiceImmutableException extends DomainException
{
    public static function cannotCancel(string $invoiceNumber, InvoiceStatusEnum $status): self
    {
        return new self("No se puede anular la factura {$invoiceNumber} porque se encuentra en estado '{$status->label()}'. Solo las facturas en estado 'Emitida' pueden ser anuladas.");
    }

    public static function cannotModify(string $invoiceNumber, InvoiceStatusEnum $status): self
    {
        return new self("La factura {$invoiceNumber} es inmutable porque se encuentra en estado '{$status->label()}'.");
    }
}
