# Design — F03: Gestión de Solicitantes

## 1. Domain Entities & Schemas

### Model: `App\Models\Requester`

```php
// Attributes
- id: int (PK)
- name: string (128)
- company_name: string (nullable, 128)
- document_number: string (unique, 32)
- phone: string (32)
- email: string (nullable, 128)
- notes: text (nullable)
- created_at, updated_at
```

---

## 2. Actions & DTOs

### DTO: `App\DTOs\Requesters\UpsertRequesterDTO`
```php
declare(strict_types=1);

namespace App\DTOs\Requesters;

readonly class UpsertRequesterDTO
{
    public function __construct(
        public string $name,
        public string $documentNumber,
        public string $phone,
        public ?string $companyName = null,
        public ?string $email = null,
        public ?string $notes = null,
    ) {}
}
```

### Action: `App\Actions\Requesters\RegisterRequesterAction`
* Invokable: `__invoke(UpsertRequesterDTO $dto): Requester`

---

## 3. UI Layer (Filament v4)

* **Resource**: `App\Filament\Resources\RequesterResource`
* **Form Schema**: TextInputs for `name`, `company_name`, `document_number`, `phone`, `email`.
* **Table Columns**: TextColumns for `name`, `company_name`, `document_number`, `phone`.
