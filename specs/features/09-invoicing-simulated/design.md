# Design — F09: Facturación Simulada

## 1. Domain Entities & Schemas

### Model: `App\Models\Invoice`

```php
// Attributes
- id: int (PK)
- invoice_number: string (unique, 32) // e.g. FACT-2026-0001
- requester_id: int (FK -> requesters.id)
- issue_date: date
- total_amount: int // in integer currency units
- status: string (draft, issued, paid)
- notes: text (nullable)
- created_at, updated_at
```

### Pivot Model: `App\Models\InvoiceTrip`
* `invoice_id` (FK -> invoices.id)
* `trip_id` (FK -> trips.id)
* `subtotal_amount`: int

---

## 2. Actions & DTOs

### Action: `App\Actions\Invoices\GenerateInvoiceAction`
* Invokable: `__invoke(int $requesterId, array $tripIds, int $totalAmount): Invoice`
* Validates trips are `cerrado` and belong to `$requesterId`.
* Generates PDF view via `barryvdh/laravel-dompdf` or Blade view render.

---

## 3. UI Layer (Filament v4)

* **Resource**: `App\Filament\Resources\InvoiceResource`
* **Table Action**: `DownloadPdf` action on `InvoiceResource`.
