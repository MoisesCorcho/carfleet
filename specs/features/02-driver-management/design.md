# Design — F02: Gestión de Conductores

## 1. Domain Entities & Schemas

### Backed Enums

* **`App\Enums\Drivers\DriverStatusEnum`** (string): `activo`, `inactivo`, `suspendido`.
  * `label(): string` -> `Activo`, `Inactivo`, `Suspendido`
  * `color(): string` -> `success`, `gray`, `danger`
* **`App\Enums\Drivers\DocumentTypeEnum`** (string): `CC`, `CE`, `PA`, `PPT`, `PEP`.
  * `label(): string` -> Nombre completo de documento colombiano
  * `shortLabel(): string` -> Acrónimo
* **`App\Enums\Drivers\LicenseCategoryEnum`** (string): `B1`, `B2`, `B3`, `C1`, `C2`, `C3`.
  * `label(): string` -> Categoría oficial RUNT (Particular vs Servicio Público)
  * `isPublicService(): bool` -> true si es C1, C2 o C3
  * `color(): string` -> `warning` (público) / `info` (particular)

### Model: `App\Models\Driver`

```php
// Attributes
- id: int (PK)
- user_id: int (FK -> users.id, unique)
- full_name: string (128)
- document_type: DocumentTypeEnum (default: CC)
- document_number: string (32)
- phone: string (32)
- license_number: string (unique, 32)
- license_category: LicenseCategoryEnum (default: C1)
- license_expires_at: date (nullable)
- status: DriverStatusEnum (default: activo)
- deleted_at: timestamp (nullable, SoftDeletes)
- created_at, updated_at

// Composite Unique Constraints
- unique(['document_type', 'document_number'])

// Relationships
- user(): BelongsTo (User::class)
- trips(): HasMany (Trip::class)
- fuelLogs(): HasMany (FuelLog::class)

// Domain Helper Methods & Scopes
- formattedDocument(): string
- isActive(): bool
- isLicenseExpired(): bool
- hasValidLicense(): bool
- isEligibleForTrip(): bool
- canDrivePublicService(): bool
- scopeActive(Builder $query): Builder
- scopeEligibleForTrip(Builder $query): Builder
```

---

## 2. Actions & DTOs

### DTO: `App\DTOs\Drivers\UpsertDriverDTO`

```php
declare(strict_types=1);

namespace App\DTOs\Drivers;

use App\Enums\Drivers\DocumentTypeEnum;
use App\Enums\Drivers\DriverStatusEnum;
use App\Enums\Drivers\LicenseCategoryEnum;

readonly class UpsertDriverDTO
{
    public function __construct(
        public int $userId,
        public string $fullName,
        public DocumentTypeEnum $documentType,
        public string $documentNumber,
        public string $phone,
        public string $licenseNumber,
        public LicenseCategoryEnum $licenseCategory = LicenseCategoryEnum::C1,
        public ?string $licenseExpiresAt = null,
        public DriverStatusEnum $status = DriverStatusEnum::ACTIVO,
    ) {}

    public static function fromArray(array $data): self;
    public function toArray(): array;
}
```

### Action: `App\Actions\Drivers\RegisterDriverAction`

* Invokable: `__invoke(UpsertDriverDTO $dto): Driver`
* Validates unique `user_id` assignment to prevent orphan duplicates.
* Executes inside `DB::transaction`.

### Action: `App\Actions\Drivers\UpdateDriverAction`

* Invokable: `__invoke(Driver $driver, UpsertDriverDTO $dto): Driver`
* Updates driver attributes inside `DB::transaction`.

### Exceptions: `App\Exceptions\Drivers\InvalidDriverException`

* Static factories for domain invariant errors (e.g. user already has a driver profile).

---

## 3. Authorization & Security

### Policy: `App\Policies\DriverPolicy`

* Maps actions to Spatie permissions (`ViewAny:Driver`, `View:Driver`, `Create:Driver`, `Update:Driver`, `Delete:Driver`, `Restore:Driver`, `ForceDelete:Driver`).
* Enforced automatically on `DriverResource`.

---

## 4. UI Layer (Filament v4)

* **Resource**: `App\Filament\Resources\Drivers\DriverResource`
  * Navigation Group: `'Gestión de Flota'`, icon: `'heroicon-o-identification'`, sort: `2`.
  * Model Label: `'Conductor'` / `'Conductores'`.
* **Form Schema**:
  * Inputs con íconos de prefijo semánticos (`heroicon-m-user`, `heroicon-m-identification`, `heroicon-m-phone`, `heroicon-m-credit-card`, `heroicon-m-truck`, `heroicon-m-calendar-days`).
  * Modal integrado de creación de usuario con `createOptionForm` asignando rol `driver` en `user_id`.
  * Selector oficial de `license_category` (B1..B3, C1..C3).
* **Table Schema**:
  * Columnas: `full_name`, `document` (formateado, copiable), `phone`, `license_number`, `license_category` (badge), `license_expires_at` (con alerta visual si vencida), `status` (badge).
  * Menú de acciones agrupadas en dropdown `ActionGroup` (`⋮`).
  * Filtros: `status`, `document_type`, `license_category`, `eligible_for_trip`, `TrashedFilter`.
