<?php

declare(strict_types=1);

namespace App\Actions\Invoices;

use App\Enums\Invoices\InvoiceStatusEnum;
use App\Exceptions\Invoices\InvoiceImmutableException;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

final class CancelInvoiceAction
{
    public function __invoke(Invoice $invoice, string $reason = ''): Invoice
    {
        if (! $invoice->canBeCancelled()) {
            throw InvoiceImmutableException::cannotCancel($invoice->invoice_number, $invoice->status);
        }

        return DB::transaction(function () use ($invoice, $reason): Invoice {
            $notes = $invoice->notes;
            if ($reason !== '') {
                $notes = $notes ? "{$notes} | Motivo anulación: {$reason}" : "Motivo anulación: {$reason}";
            }

            $invoice->update([
                'status' => InvoiceStatusEnum::ANULADA,
                'notes' => $notes,
            ]);

            return $invoice->fresh();
        });
    }
}
