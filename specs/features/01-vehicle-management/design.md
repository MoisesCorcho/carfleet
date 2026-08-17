# Design — F01: Gestión de Vehículos

## 1. Domain Entities & Schemas

### Backed Enums

* **`App\Enums\Vehicles\VehicleStatusEnum`** (string): `disponible`, `asignado`, `en_viaje`, `mantenimiento`, `fuera_de_servicio`.
* **`App\Enums\Vehicles\FuelTypeEnum`** (string): `gasolina`, `diesel`, `gas`, `electrico`.

### Model: `App\Models\Vehicle`

```php
// Attributes
- id: int (PK)
- plate_number: string (unique, 16)
- brand: string (64)
- model: string (64)
- year: int
- current_mileage: int (default: 0)
- status: VehicleStatusEnum (default: disponible)
- fuel_type: FuelTypeEnum (default: gasolina)
- notes: string (nullable)
- created_at, updated_at
```

---

## 2. Actions & DTOs

### DTO: `App\DTOs\Vehicles\UpsertVehicleDTO`
```php
declare(strict_types=1);

namespace App\DTOs\Vehicles;

use App\Enums\Vehicles\FuelTypeEnum;
use App\Enums\Vehicles\VehicleStatusEnum;

readonly class UpsertVehicleDTO
{
    public function __construct(
        public string $plateNumber,
        public string $brand,
        public string $model,
        public int $year,
        public int $currentMileage,
        public VehicleStatusEnum $status = VehicleStatusEnum::DISPONIBLE,
        public FuelTypeEnum $fuelType = FuelTypeEnum::GASOLINA,
        public ?string $notes = null,
    ) {}
}
```

### Action: `App\Actions\Vehicles\RegisterVehicleAction`
* Invokable: `__invoke(UpsertVehicleDTO $dto): Vehicle`
* Validation at edge (Filament Form / Form Request).
* Executes inside `DB::transaction`.

---

## 3. UI Layer (Filament v4)

* **Resource**: `App\Filament\Resources\VehicleResource`
* **Form Schema**: TextInputs for `plate_number`, `brand`, `model`, `year`, `current_mileage`, Selects for `status` and `fuel_type`.
* **Table Columns**: BadgeColumn for `status`, TextColumns for `plate_number`, `brand`, `model`, `current_mileage`. Filters by `status` and `fuel_type`.
