<?php

declare(strict_types=1);

namespace App\Actions\Invoices;

use App\DTOs\Invoices\GenerateInvoiceDTO;
use App\Enums\Invoices\InvoiceStatusEnum;
use App\Exceptions\Invoices\EmptyInvoiceTripsException;
use App\Exceptions\Invoices\RequesterMismatchException;
use App\Exceptions\Invoices\TripAlreadyInvoicedException;
use App\Exceptions\Invoices\TripNotEligibleForInvoicingException;
use App\Models\Invoice;
use App\Models\Requester;
use App\Models\Trip;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class GenerateInvoiceAction
{
    public function __invoke(GenerateInvoiceDTO $dto): Invoice
    {
        if (empty($dto->tripIds)) {
            throw EmptyInvoiceTripsException::make();
        }

        return DB::transaction(function () use ($dto): Invoice {
            $requester = Requester::findOrFail($dto->requesterId);

            /** @var Collection<int, Trip> $trips */
            $trips = Trip::query()
                ->whereIn('id', $dto->tripIds)
                ->lockForUpdate()
                ->get();

            if ($trips->count() !== count($dto->tripIds)) {
                throw EmptyInvoiceTripsException::make();
            }

            $subtotals = [];
            $totalAmount = 0;

            foreach ($trips as $trip) {
                if (! $trip->isClosed()) {
                    throw TripNotEligibleForInvoicingException::forTrip($trip->code, $trip->status);
                }

                if ($trip->requester_id !== $dto->requesterId) {
                    throw RequesterMismatchException::forTrip(
                        $trip->code,
                        $requester->name,
                        $trip->requester?->name ?? 'Desconocido'
                    );
                }

                if ($trip->isInvoiced()) {
                    $activeInvoice = $trip->invoices()
                        ->whereIn('status', [InvoiceStatusEnum::EMITIDA, InvoiceStatusEnum::PAGADA])
                        ->first();

                    throw TripAlreadyInvoicedException::forTrip(
                        $trip->code,
                        $activeInvoice?->invoice_number ?? 'Activa'
                    );
                }

                $distance = $trip->distance_traveled ?? 0;
                $calculatedSubtotal = $distance * $dto->ratePerKm;
                $subtotal = max($dto->baseRatePerTrip, $calculatedSubtotal);

                $subtotals[$trip->id] = $subtotal;
                $totalAmount += $subtotal;
            }

            $invoiceNumber = $this->generateNextInvoiceNumber();

            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'requester_id' => $dto->requesterId,
                'issue_date' => $dto->issueDate ?: now()->toDateString(),
                'total_amount' => $totalAmount,
                'status' => InvoiceStatusEnum::EMITIDA,
                'notes' => $dto->notes,
            ]);

            $syncData = [];
            foreach ($subtotals as $tripId => $subtotalAmount) {
                $syncData[$tripId] = ['subtotal_amount' => $subtotalAmount];
            }

            $invoice->trips()->sync($syncData);

            return $invoice->load(['requester', 'trips']);
        });
    }

    private function generateNextInvoiceNumber(): string
    {
        $year = (int) date('Y');
        $prefix = "FACT-{$year}-";

        $latestInvoice = Invoice::query()
            ->where('invoice_number', 'like', "{$prefix}%")
            ->lockForUpdate()
            ->orderByDesc('invoice_number')
            ->first();

        if (! $latestInvoice) {
            return "{$prefix}0001";
        }

        $lastSequence = (int) substr($latestInvoice->invoice_number, -4);
        $nextSequence = str_pad((string) ($lastSequence + 1), 4, '0', STR_PAD_LEFT);

        return "{$prefix}{$nextSequence}";
    }
}
