<?php

declare(strict_types=1);

namespace Tests\Feature\Invoices;

use App\Actions\Invoices\CancelInvoiceAction;
use App\Actions\Invoices\GenerateInvoiceAction;
use App\Actions\Invoices\MarkInvoicePaidAction;
use App\DTOs\Invoices\GenerateInvoiceDTO;
use App\Enums\Invoices\InvoiceStatusEnum;
use App\Enums\Trips\TripStatusEnum;
use App\Exceptions\Invoices\EmptyInvoiceTripsException;
use App\Exceptions\Invoices\InvoiceImmutableException;
use App\Exceptions\Invoices\RequesterMismatchException;
use App\Exceptions\Invoices\TripAlreadyInvoicedException;
use App\Exceptions\Invoices\TripNotEligibleForInvoicingException;
use App\Models\Invoice;
use App\Models\Requester;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\RoleSeeder;

describe('Invoice Management Feature & Invariant Tests (F09)', function (): void {
    beforeEach(function (): void {
        $this->seed(RoleSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('super_admin');
        $this->actingAs($this->admin);

        $this->requester = Requester::factory()->create(['is_active' => true]);
        $this->otherRequester = Requester::factory()->create(['is_active' => true]);

        $this->vehicle = Vehicle::factory()->create();
    });

    test('generates invoice successfully for closed trips with automatic code and totals (R1, US9.1)', function (): void {
        $trip1 = Trip::factory()->create([
            'requester_id' => $this->requester->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => TripStatusEnum::CERRADO,
            'initial_mileage' => 10000,
            'final_mileage' => 10050, // 50 km
            'distance_traveled' => 50,
        ]);

        $trip2 = Trip::factory()->create([
            'requester_id' => $this->requester->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => TripStatusEnum::CERRADO,
            'initial_mileage' => 10050,
            'final_mileage' => 10100, // 50 km
            'distance_traveled' => 50,
        ]);

        $dto = new GenerateInvoiceDTO(
            requesterId: $this->requester->id,
            tripIds: [$trip1->id, $trip2->id],
            issueDate: '2026-08-21',
            baseRatePerTrip: 50000,
            ratePerKm: 3000,
            notes: 'Servicios ejecutados en ruta norte',
        );

        $action = app(GenerateInvoiceAction::class);
        $invoice = $action($dto);

        expect($invoice)->toBeInstanceOf(Invoice::class)
            ->and($invoice->invoice_number)->toMatch('/^FACT-\d{4}-\d{4}$/')
            ->and($invoice->requester_id)->toBe($this->requester->id)
            ->and($invoice->status)->toBe(InvoiceStatusEnum::EMITIDA)
            ->and($invoice->notes)->toBe('Servicios ejecutados en ruta norte');

        // Subtotal trip 1 = max(50000, 50 * 3000 = 150000) = 150000
        // Subtotal trip 2 = max(50000, 50 * 3000 = 150000) = 150000
        // Total amount = 300000
        expect($invoice->total_amount)->toBe(300000)
            ->and($invoice->trips)->toHaveCount(2);

        expect($invoice->trips->first()->pivot->subtotal_amount)->toBe(150000);
    });

    test('rejects generating invoice when any trip is not closed (Invariant: Only Closed Trips)', function (): void {
        $openTrip = Trip::factory()->create([
            'requester_id' => $this->requester->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => TripStatusEnum::EN_CURSO,
        ]);

        $dto = new GenerateInvoiceDTO(
            requesterId: $this->requester->id,
            tripIds: [$openTrip->id],
        );

        $action = app(GenerateInvoiceAction::class);
        $action($dto);
    })->throws(TripNotEligibleForInvoicingException::class);

    test('rejects generating invoice when a trip is already invoiced in an active invoice (Invariant: No Double Invoicing)', function (): void {
        $closedTrip = Trip::factory()->create([
            'requester_id' => $this->requester->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => TripStatusEnum::CERRADO,
            'initial_mileage' => 1000,
            'final_mileage' => 1020,
            'distance_traveled' => 20,
        ]);

        $dto = new GenerateInvoiceDTO(
            requesterId: $this->requester->id,
            tripIds: [$closedTrip->id],
        );

        $action = app(GenerateInvoiceAction::class);
        $action($dto); // First invoice created successfully

        // Attempting to invoice the same closed trip again
        $action($dto);
    })->throws(TripAlreadyInvoicedException::class);

    test('rejects generating invoice with mixed requesters (Invariant: Single Requester)', function (): void {
        $trip1 = Trip::factory()->create([
            'requester_id' => $this->requester->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => TripStatusEnum::CERRADO,
            'initial_mileage' => 1000,
            'final_mileage' => 1020,
            'distance_traveled' => 20,
        ]);

        $tripOther = Trip::factory()->create([
            'requester_id' => $this->otherRequester->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => TripStatusEnum::CERRADO,
            'initial_mileage' => 1020,
            'final_mileage' => 1040,
            'distance_traveled' => 20,
        ]);

        $dto = new GenerateInvoiceDTO(
            requesterId: $this->requester->id,
            tripIds: [$trip1->id, $tripOther->id],
        );

        $action = app(GenerateInvoiceAction::class);
        $action($dto);
    })->throws(RequesterMismatchException::class);

    test('rejects generating invoice with empty trips array', function (): void {
        $dto = new GenerateInvoiceDTO(
            requesterId: $this->requester->id,
            tripIds: [],
        );

        $action = app(GenerateInvoiceAction::class);
        $action($dto);
    })->throws(EmptyInvoiceTripsException::class);

    test('can mark invoice as paid and transition state cleanly', function (): void {
        $trip = Trip::factory()->create([
            'requester_id' => $this->requester->id,
            'status' => TripStatusEnum::CERRADO,
            'initial_mileage' => 1000,
            'final_mileage' => 1050,
            'distance_traveled' => 50,
        ]);

        $invoice = app(GenerateInvoiceAction::class)(new GenerateInvoiceDTO(
            requesterId: $this->requester->id,
            tripIds: [$trip->id],
        ));

        app(MarkInvoicePaidAction::class)($invoice);

        expect($invoice->fresh()->status)->toBe(InvoiceStatusEnum::PAGADA);
    });

    test('cancelling an invoice frees its trips so they can be invoiced again in a new invoice', function (): void {
        $trip = Trip::factory()->create([
            'requester_id' => $this->requester->id,
            'status' => TripStatusEnum::CERRADO,
            'initial_mileage' => 1000,
            'final_mileage' => 1050,
            'distance_traveled' => 50,
        ]);

        $dto = new GenerateInvoiceDTO(
            requesterId: $this->requester->id,
            tripIds: [$trip->id],
        );

        $invoice = app(GenerateInvoiceAction::class)($dto);
        expect($invoice->status)->toBe(InvoiceStatusEnum::EMITIDA);

        app(CancelInvoiceAction::class)($invoice, 'Error en tarifas de cliente');
        expect($invoice->fresh()->status)->toBe(InvoiceStatusEnum::ANULADA);

        // Now the trip can be invoiced again in a new invoice
        $newInvoice = app(GenerateInvoiceAction::class)($dto);
        expect($newInvoice)->toBeInstanceOf(Invoice::class)
            ->and($newInvoice->id)->not->toBe($invoice->id)
            ->and($newInvoice->status)->toBe(InvoiceStatusEnum::EMITIDA);
    });

    test('cannot cancel an already paid invoice', function (): void {
        $trip = Trip::factory()->create([
            'requester_id' => $this->requester->id,
            'status' => TripStatusEnum::CERRADO,
            'initial_mileage' => 1000,
            'final_mileage' => 1050,
            'distance_traveled' => 50,
        ]);

        $invoice = app(GenerateInvoiceAction::class)(new GenerateInvoiceDTO(
            requesterId: $this->requester->id,
            tripIds: [$trip->id],
        ));

        app(MarkInvoicePaidAction::class)($invoice);

        app(CancelInvoiceAction::class)($invoice, 'Intento de anulación');
    })->throws(InvoiceImmutableException::class);
});
