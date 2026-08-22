<?php

declare(strict_types=1);

namespace App\Enums\Invoices;

enum InvoiceStatusEnum: string
{
    case EMITIDA = 'issued';
    case PAGADA = 'paid';
    case ANULADA = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::EMITIDA => 'Emitida',
            self::PAGADA => 'Pagada',
            self::ANULADA => 'Anulada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::EMITIDA => 'warning',
            self::PAGADA => 'success',
            self::ANULADA => 'danger',
        };
    }
}
