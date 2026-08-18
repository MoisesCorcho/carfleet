# Design — F02: Gestión de Conductores

## 1. Domain Entities & Schemas

### Backed Enums

* **`App\Enums\Drivers\DriverStatusEnum`** (string): `activo`, `inactivo`, `suspendido`.
  * `label(): string` -> `Activo`, `Inactivo`, `Suspendido`
  * `color(): string` -> `success`, `gray`, `danger`
* **`App\Enums\Drivers\DocumentTypeEnum`** (string): `CC`, `CE`, `PA`, `PPT`, `PEP`.
  * `label(): string` -> Nombre completo de documento colombiano
  * `shortLabel(): string` -> Acrónimo

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
- license_expires_at: date (nullable)
- status: DriverStatusEnum (default: activo)
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
- hasValidLicense(): bool
- isEligibleForTrip(): bool
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

readonly class UpsertDriverDTO
{
    public function __construct(
        public int $userId,
        public string $fullName,
        public DocumentTypeEnum $documentType,
        public string $documentNumber,
        public string $phone,
        public string $licenseNumber,
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

* Maps actions to Spatie permissions (`ViewAny:Driver`, `View:Driver`, `Create:Driver`, `Update:Driver`, `Delete:Driver`, etc.).
* Enforced automatically on `DriverResource`.

---

## 4. UI Layer (Filament v4)

* **Resource**: `App\Filament\Resources\Drivers\DriverResource`
  * Navigation Group: `'Gestión de Flota'`, icon: `'heroicon-o-identification'`, sort: `2`.
  * Model Label: `'Conductor'` / `'Conductores'`.
* **Form Schema**:
  * Section "Información del Conductor y Cuenta": Select `user_id`, TextInput `full_name`, Select `document_type`, TextInput `document_number` (con validación de unicidad compuesta), TextInput `phone` (con validación regex de formato).
  * Section "Habilitación y Licencia de Conducción": TextInput `license_number`, DatePicker `license_expires_at`, Select `status` (DriverStatusEnum options).
* **Table Schema**:
  * Columns: `full_name` (sortable, searchable), `document` (formatted `CC 10203040`, searchable, sortable, copyable), `phone` (searchable), `license_number` (searchable, badge), `license_expires_at` (date format d/m/Y, warning indicator if expired), `status` (badge with colors).
  * Filters: `status` (SelectFilter), `document_type` (SelectFilter), Filter for active with valid license (`eligible_for_trip`).
  * Actions: ViewAction, EditAction, DeleteAction.
  * Empty State: "No hay conductores registrados".
* **Pages**:
  * `ListDrivers`: list table with header create button.
  * `CreateDriver`: uses `UpsertDriverDTO` and `RegisterDriverAction`.
  * `EditDriver`: uses `UpsertDriverDTO` and `UpdateDriverAction`.
  * `ViewDriver`: read-only view.
