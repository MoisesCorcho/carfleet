# Design — F01: Gestión de Vehículos

## 1. Domain Entities & Schemas

### Backed Enums

* **`App\Enums\Vehicles\VehicleStatusEnum`** (string): `disponible`, `asignado`, `en_viaje`, `mantenimiento`, `fuera_de_servicio`.
* **`App\Enums\Vehicles\FuelTypeEnum`** (string): `gasolina`, `diesel`, `gas`, `electrico`.
* **`App\Enums\Vehicles\VehicleTypeEnum`** (string): `automovil`, `camioneta`, `van`, `microbus`, `buseta`, `camion`, `furgon`, `tractocamion`.
* **`App\Enums\Vehicles\ServiceTypeEnum`** (string): `publico`, `particular`.

### Model: `App\Models\Vehicle`

```php
// Attributes
- id: int (PK)
- plate_number: string (unique, 16)
- brand: string (64)
- model: string (64)
- year: int
- vehicle_type: VehicleTypeEnum (default: camioneta)
- service_type: ServiceTypeEnum (default: publico)
- current_mileage: int (default: 0)
- status: VehicleStatusEnum (default: disponible)
- fuel_type: FuelTypeEnum (default: gasolina)
- notes: string (nullable)
- deleted_at: timestamp (nullable)
- created_at, updated_at

// Helpers & Scopes
- isAvailable(): bool
- isPublicService(): bool
- canBeAssigned(): bool
- scopeAvailable(Builder $query): Builder
- scopeInService(Builder $query): Builder
```

---

## 2. Actions & DTOs

### DTO: `App\DTOs\Vehicles\UpsertVehicleDTO`
```php
declare(strict_types=1);

namespace App\DTOs\Vehicles;

use App\Enums\Vehicles\FuelTypeEnum;
use App\Enums\Vehicles\ServiceTypeEnum;
use App\Enums\Vehicles\VehicleStatusEnum;
use App\Enums\Vehicles\VehicleTypeEnum;

readonly class UpsertVehicleDTO
{
    public function __construct(
        public string $plateNumber,
        public string $brand,
        public string $model,
        public int $year,
        public int $currentMileage,
        public VehicleTypeEnum $vehicleType = VehicleTypeEnum::CAMIONETA,
        public ServiceTypeEnum $serviceType = ServiceTypeEnum::PUBLICO,
        public VehicleStatusEnum $status = VehicleStatusEnum::DISPONIBLE,
        public FuelTypeEnum $fuelType = FuelTypeEnum::GASOLINA,
        public ?string $notes = null,
    ) {}
}
```

### Action: `App\Actions\Vehicles\RegisterVehicleAction`
* Invokable: `__invoke(UpsertVehicleDTO $dto): Vehicle`
* Executes inside `DB::transaction`.

### Action: `App\Actions\Vehicles\UpdateVehicleAction`
* Invokable: `__invoke(Vehicle $vehicle, UpsertVehicleDTO $dto): Vehicle`
* Preserves and validates odometer invariant.

---

## 3. UI Layer (Filament v4)

* **Resource**: `App\Filament\Resources\Vehicles\VehicleResource`
* **Form Schema**:
  * Inputs with semantic prefix icons (`heroicon-m-identification`, `heroicon-m-tag`, `heroicon-m-cube`, `heroicon-m-truck`, `heroicon-m-shield-check`, etc.).
  * Regex validation for Colombian plate formats (`ABC-123`).
  * `current_mileage` disabled on Edit form to protect odometer integrity.
* **Table Columns**: Badges for `plate_number` (colored by service type), `vehicle_type`, `service_type`, `status`. Filters by `status`, `vehicle_type`, `service_type`, `fuel_type`, `TrashedFilter`.
