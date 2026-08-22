<?php

declare(strict_types=1);

namespace App\Http\Controllers\Invoices;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Contracts\View\View;

class InvoicePdfController extends Controller
{
    public function __invoke(Invoice $invoice): View
    {
        $invoice->load(['requester', 'trips.vehicle']);

        return view('invoices.pdf', [
            'invoice' => $invoice,
        ]);
    }
}
