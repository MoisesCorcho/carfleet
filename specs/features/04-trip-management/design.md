# Design — F04: Gestión y Asignación de Viajes

## 1. Domain Entities & Schemas

### Backed Enums

* **`App\Enums\Trips\TripStatusEnum`** (string): `programado`, `asignado`, `en_curso`, `finalizado`, `cerrado`, `cancelado`.

### Model: `App\Models\Trip`

```php
// Attributes
- id: int (PK)
- code: string (unique, 16) // e.g., TRIP-2026-0001
- requester_id: int (FK -> requesters.id)
- vehicle_id: int (nullable, FK -> vehicles.id)
- driver_id: int (nullable, FK -> drivers.id)
- origin: string (128)
- destination: string (128)
- scheduled_departure_at: datetime
- scheduled_arrival_at: datetime (nullable)
- actual_departure_at: datetime (nullable)
- actual_arrival_at: datetime (nullable)
- status: TripStatusEnum (default: programado)
- notes: text (nullable)
- created_at, updated_at
```

---

## 2. Actions & DTOs

### DTO: `App\DTOs\Trips\CreateTripDTO`
```php
declare(strict_types=1);

namespace App\DTOs\Trips;

readonly class CreateTripDTO
{
    public function __construct(
        public int $requesterId,
        public string $origin,
        public string $destination,
        public string $scheduledDepartureAt,
        public ?string $scheduledArrivalAt = null,
        public ?int $vehicleId = null,
        public ?int $driverId = null,
        public ?string $notes = null,
    ) {}
}
```

### Action: `App\Actions\Trips\AssignTripResourcesAction`
* Invokable: `__invoke(Trip $trip, int $vehicleId, int $driverId): Trip`
* Validates vehicle status == `disponible` and driver status == `activo`.
* Atomically updates trip status to `asignado` and vehicle status to `asignado` inside `DB::transaction`.

### Action: `App\Actions\Trips\StartTripAction`
* Invokable: `__invoke(Trip $trip): Trip`
* Atomically sets `actual_departure_at = now()`, trip status = `en_curso`, vehicle status = `en_viaje`.

---

## 3. UI Layer (Filament v4)

* **Resource**: `App\Filament\Resources\TripResource`
* **Form Schema**: Selects for `requester_id`, `vehicle_id`, `driver_id`, TextInputs for `origin`, `destination`, DateTimePickers for schedule.
* **Table Columns**: TextColumns for `code`, `origin`, `destination`, `requester.name`, `vehicle.plate_number`, `driver.full_name`, BadgeColumn for `status`.
* **Actions**: Custom Filament Table/Form Action `AssignResources`, `StartTrip`.
