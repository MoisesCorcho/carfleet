# Design — F06: Tanqueos y Vouchers de Combustible

## 1. Domain Entities & Schemas

### Model: `App\Models\FuelLog`

```php
// Attributes
- id: int (PK)
- vehicle_id: int (FK -> vehicles.id)
- trip_id: int (nullable, FK -> trips.id)
- driver_id: int (nullable, FK -> drivers.id)
- refuel_date: datetime
- mileage_at_refuel: int
- gallons: decimal(8,2)
- total_cost: int // stored in integer currency units (cents/pesos)
- voucher_number: string (nullable, 64)
- voucher_photo_path: string (nullable, 255)
- notes: text (nullable)
- created_at, updated_at
```

---

## 2. Actions & DTOs

### DTO: `App\DTOs\Fuel\RegisterFuelLogDTO`
```php
declare(strict_types=1);

namespace App\DTOs\Fuel;

use Illuminate\Http\UploadedFile;

readonly class RegisterFuelLogDTO
{
    public function __construct(
        public int $vehicleId,
        public string $refuelDate,
        public int $mileageAtRefuel,
        public float $gallons,
        public int $totalCost,
        public ?int $tripId = null,
        public ?int $driverId = null,
        public ?string $voucherNumber = null,
        public ?UploadedFile $voucherPhoto = null,
        public ?string $notes = null,
    ) {}
}
```

### Action: `App\Actions\Fuel\RegisterFuelLogAction`
* Invokable: `__invoke(RegisterFuelLogDTO $dto): FuelLog`
* Stores voucher image in `public/evidences/vouchers`.
* Creates `FuelLog` record and associates `TripEvidence` of type `voucher_combustible`.

---

## 3. UI Layer (Filament v4)

* **Resource**: `App\Filament\Resources\FuelLogResource`
* **RelationManager**: `FuelLogsRelationManager` attached to `VehicleResource` and `TripResource`.
