<?php

declare(strict_types=1);

namespace App\Actions\Invoices;

use App\Enums\Invoices\InvoiceStatusEnum;
use App\Exceptions\Invoices\InvoiceImmutableException;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

final class MarkInvoicePaidAction
{
    public function __invoke(Invoice $invoice): Invoice
    {
        if (! $invoice->canBeMarkedPaid()) {
            throw InvoiceImmutableException::cannotModify($invoice->invoice_number, $invoice->status);
        }

        return DB::transaction(function () use ($invoice): Invoice {
            $invoice->update([
                'status' => InvoiceStatusEnum::PAGADA,
            ]);

            return $invoice->fresh();
        });
    }
}
