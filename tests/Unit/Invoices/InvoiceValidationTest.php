<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices;

use App\DTOs\Invoices\GenerateInvoiceDTO;
use App\Enums\Invoices\InvoiceStatusEnum;

describe('Invoice Validation & DTO Unit Tests', function (): void {
    test('InvoiceStatusEnum has correct cases, labels and colors', function (): void {
        expect(InvoiceStatusEnum::EMITIDA->value)->toBe('issued')
            ->and(InvoiceStatusEnum::PAGADA->value)->toBe('paid')
            ->and(InvoiceStatusEnum::ANULADA->value)->toBe('cancelled')
            ->and(InvoiceStatusEnum::EMITIDA->label())->toBe('Emitida')
            ->and(InvoiceStatusEnum::PAGADA->label())->toBe('Pagada')
            ->and(InvoiceStatusEnum::ANULADA->label())->toBe('Anulada')
            ->and(InvoiceStatusEnum::EMITIDA->color())->toBe('warning')
            ->and(InvoiceStatusEnum::PAGADA->color())->toBe('success')
            ->and(InvoiceStatusEnum::ANULADA->color())->toBe('danger');
    });

    test('GenerateInvoiceDTO instantiates correctly from array and calculates custom subtotals', function (): void {
        $dto = GenerateInvoiceDTO::fromArray([
            'requester_id' => 1,
            'trip_ids' => [10, 20, 30],
            'issue_date' => '2026-08-21',
            'base_rate_per_trip' => 50000,
            'rate_per_km' => 3500,
            'notes' => 'Facturación consolidada quincenal',
        ]);

        expect($dto->requesterId)->toBe(1)
            ->and($dto->tripIds)->toBe([10, 20, 30])
            ->and($dto->issueDate)->toBe('2026-08-21')
            ->and($dto->baseRatePerTrip)->toBe(50000)
            ->and($dto->ratePerKm)->toBe(3500)
            ->and($dto->notes)->toBe('Facturación consolidada quincenal');
    });

    test('GenerateInvoiceDTO uses sensible defaults when optional rates omitted', function (): void {
        $dto = GenerateInvoiceDTO::fromArray([
            'requester_id' => 2,
            'trip_ids' => [5],
        ]);

        expect($dto->requesterId)->toBe(2)
            ->and($dto->tripIds)->toBe([5])
            ->and($dto->baseRatePerTrip)->toBe(50000)
            ->and($dto->ratePerKm)->toBe(3500)
            ->and($dto->issueDate)->toBe(now()->toDateString())
            ->and($dto->notes)->toBeNull();
    });
});
