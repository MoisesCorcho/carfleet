<?php

declare(strict_types=1);

namespace Tests\Feature\Invoices;

use App\Enums\Invoices\InvoiceStatusEnum;
use App\Enums\Trips\TripStatusEnum;
use App\Filament\Resources\Invoices\Pages\CreateInvoice;
use App\Filament\Resources\Invoices\Pages\ListInvoices;
use App\Filament\Resources\Invoices\Pages\ViewInvoice;
use App\Filament\Resources\Invoices\RelationManagers\TripsRelationManager;
use App\Models\Invoice;
use App\Models\Requester;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Livewire\Livewire;

describe('Invoice Filament Resource & UI Integration Tests', function (): void {
    beforeEach(function (): void {
        $this->seed(RoleSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('super_admin');
        $this->actingAs($this->admin);

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->requester = Requester::factory()->create(['is_active' => true]);
        $this->vehicle = Vehicle::factory()->create();
    });

    test('admin can access invoices list page and view records', function (): void {
        $invoice = Invoice::factory()->create([
            'requester_id' => $this->requester->id,
            'total_amount' => 500000,
            'status' => InvoiceStatusEnum::EMITIDA,
        ]);

        Livewire::test(ListInvoices::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$invoice])
            ->assertSee($invoice->invoice_number)
            ->assertSee($this->requester->name);
    });

    test('admin can create an invoice via CreateInvoice page', function (): void {
        $trip1 = Trip::factory()->create([
            'requester_id' => $this->requester->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => TripStatusEnum::CERRADO,
            'initial_mileage' => 1000,
            'final_mileage' => 1040,
            'distance_traveled' => 40,
        ]);

        $trip2 = Trip::factory()->create([
            'requester_id' => $this->requester->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => TripStatusEnum::CERRADO,
            'initial_mileage' => 1040,
            'final_mileage' => 1080,
            'distance_traveled' => 40,
        ]);

        Livewire::test(CreateInvoice::class)
            ->fillForm([
                'requester_id' => $this->requester->id,
                'trip_ids' => [$trip1->id, $trip2->id],
                'issue_date' => '2026-08-21',
                'base_rate_per_trip' => 50000,
                'rate_per_km' => 3000,
                'notes' => 'Factura creada desde Filament Form',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('invoices', [
            'requester_id' => $this->requester->id,
            'status' => 'issued',
            'notes' => 'Factura creada desde Filament Form',
        ]);

        $createdInvoice = Invoice::first();
        expect($createdInvoice)->not->toBeNull()
            ->and($createdInvoice->trips)->toHaveCount(2);
    });

    test('admin can view invoice details and consolidated trips in ViewInvoice page', function (): void {
        $trip = Trip::factory()->create([
            'requester_id' => $this->requester->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => TripStatusEnum::CERRADO,
            'initial_mileage' => 1000,
            'final_mileage' => 1050,
            'distance_traveled' => 50,
        ]);

        $invoice = Invoice::factory()->create([
            'requester_id' => $this->requester->id,
            'total_amount' => 175000,
            'status' => InvoiceStatusEnum::EMITIDA,
        ]);

        $invoice->trips()->attach($trip->id, ['subtotal_amount' => 175000]);

        Livewire::test(ViewInvoice::class, ['record' => $invoice->getRouteKey()])
            ->assertSuccessful()
            ->assertSee($invoice->invoice_number)
            ->assertSee($this->requester->name);

        Livewire::test(TripsRelationManager::class, [
            'ownerRecord' => $invoice,
            'pageClass' => ViewInvoice::class,
        ])
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$trip])
            ->assertSee($trip->code);
    });

    test('admin can view/print invoice PDF via controller route', function (): void {
        $trip = Trip::factory()->create([
            'requester_id' => $this->requester->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => TripStatusEnum::CERRADO,
            'initial_mileage' => 1000,
            'final_mileage' => 1050,
            'distance_traveled' => 50,
        ]);

        $invoice = Invoice::factory()->create([
            'requester_id' => $this->requester->id,
            'total_amount' => 175000,
            'status' => InvoiceStatusEnum::EMITIDA,
        ]);

        $invoice->trips()->attach($trip->id, ['subtotal_amount' => 175000]);

        $response = $this->get(route('invoices.pdf', $invoice));

        $response->assertOk()
            ->assertViewIs('invoices.pdf')
            ->assertSee($invoice->invoice_number)
            ->assertSee($this->requester->name)
            ->assertSee($trip->code);
    });
});
