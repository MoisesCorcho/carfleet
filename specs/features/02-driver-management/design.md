# Design — F02: Gestión de Conductores

## 1. Domain Entities & Schemas

### Backed Enums

* **`App\Enums\Drivers\DriverStatusEnum`** (string): `activo`, `inactivo`, `suspendido`.

### Model: `App\Models\Driver`

```php
// Attributes
- id: int (PK)
- user_id: int (FK -> users.id, unique)
- full_name: string (128)
- document_number: string (unique, 32)
- phone: string (32)
- license_number: string (unique, 32)
- license_expires_at: date (nullable)
- status: DriverStatusEnum (default: activo)
- created_at, updated_at
```

---

## 2. Actions & DTOs

### DTO: `App\DTOs\Drivers\RegisterDriverDTO`
```php
declare(strict_types=1);

namespace App\DTOs\Drivers;

use App\Enums\Drivers\DriverStatusEnum;

readonly class RegisterDriverDTO
{
    public function __construct(
        public int $userId,
        public string $fullName,
        public string $documentNumber,
        public string $phone,
        public string $licenseNumber,
        public ?string $licenseExpiresAt = null,
        public DriverStatusEnum $status = DriverStatusEnum::ACTIVO,
    ) {}
}
```

### Action: `App\Actions\Drivers\RegisterDriverAction`
* Invokable: `__invoke(RegisterDriverDTO $dto): Driver`
* Executes inside `DB::transaction`.

---

## 3. UI Layer (Filament v4)

* **Resource**: `App\Filament\Resources\DriverResource`
* **Form Schema**: TextInputs for `full_name`, `document_number`, `phone`, `license_number`, DatePicker for `license_expires_at`, Select for `status`.
* **Table Columns**: TextColumns for `full_name`, `document_number`, `phone`, `license_number`, BadgeColumn for `status`.
