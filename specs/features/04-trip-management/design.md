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

### Exceptions

* `App\Exceptions\Trips\VehicleNotAvailableException`: Disparada cuando el vehículo no está en estado `disponible`.
* `App\Exceptions\Trips\DriverNotEligibleException`: Disparada cuando el conductor no está activo, tiene licencia vencida o no califica para servicio público.
* `App\Exceptions\Trips\TripImmutableException`: Disparada cuando se intenta mutar o reasignar un viaje en estado `cerrado` o `cancelado`.
* `App\Exceptions\Trips\InvalidTripDatesException`: Disparada cuando `scheduled_arrival_at` es anterior o igual a `scheduled_departure_at`.

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

### Action: `App\Actions\Trips\CreateTripAction`
* Invocable: `__invoke(CreateTripDTO $dto): Trip`
* Genera atómicamente el código secuencial anual `TRIP-YYYY-NNNN` con `lockForUpdate()`.
* Valida fechas coherentes (`arrival > departure`).
* Si vienen `vehicle_id` y `driver_id`, delega a `AssignTripResourcesAction` creando el viaje directamente en `asignado`.

### Action: `App\Actions\Trips\AssignTripResourcesAction`
* Invocable: `__invoke(Trip $trip, int $vehicleId, int $driverId): Trip`
* Valida inmutabilidad del viaje (no `cerrado` ni `cancelado`).
* Valida disponibilidad del vehículo (`disponible`).
* Valida elegibilidad del conductor (`isEligibleForTrip()`) y categoría para servicio público (`canDrivePublicService()`).
* Si el viaje ya tenía un vehículo previo asignado, lo revierte atómicamente a `disponible`.
* Actualiza viaje a `asignado` y nuevo vehículo a `asignado`.

### Action: `App\Actions\Trips\StartTripAction`
* Invocable: `__invoke(Trip $trip): Trip`
* Valida que el viaje esté en estado `asignado`.
* Actualiza atómicamente `actual_departure_at = now()`, viaje a `en_curso` y vehículo a `en_viaje`.

### Action: `App\Actions\Trips\CancelTripAction`
* Invocable: `__invoke(Trip $trip, ?string $reason = null): Trip`
* Valida que el viaje no esté `cerrado` ni `cancelado` ni `en_curso`.
* Si tenía un vehículo asignado, lo revierte a `disponible`.
* Transiciona el viaje a `cancelado`.

---

## 3. UI Layer (Filament v4 Multi-Panel Architecture)

### 3.1 Panel Administrativo (`/admin` en `App\Providers\Filament\AdminPanelProvider`)
* **Acceso**: Roles `super_admin` y `admin`.
* **Resource**: `App\Filament\Admin\Resources\Trips\TripResource`
* **Form Schema**: Selects para `requester_id`, `vehicle_id`, `driver_id`, TextInputs para `origin`, `destination`, DateTimePickers con validación temporal.
* **Table Columns**: TextColumns para `code`, `origin`, `destination`, `requester.name`, `vehicle.plate_number`, `driver.full_name`, BadgeColumn para `status`.
* **Actions**: `AssignResourcesAction`, `CancelTripAction`.

### 3.2 Panel del Conductor (`/driver` en `App\Providers\Filament\DriverPanelProvider`)
* **Acceso**: Rol `driver` con perfil activo de conductor.
* **Configuración**: `topNavigation()`, `maxContentWidth(MaxWidth::Medium)` optimizado para smartphones en campo.
* **Resource**: `App\Filament\Driver\Resources\Trips\AssignedTripResource`
* **Query Scope**: Filtrado estricto a `driver_id === auth()->user()->driver->id`.
* **Vistas / Actions**: Tarjeta de viaje asignado con botón prominente **[ INICIAR SERVICIO ]**.

