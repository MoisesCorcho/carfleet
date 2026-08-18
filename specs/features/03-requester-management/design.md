# Design — F03: Gestión de Solicitantes

## 1. Domain Entities & Schemas

### Enum: `App\Enums\Requesters\RequesterDocumentTypeEnum`

```php
enum RequesterDocumentTypeEnum: string
{
    case NIT = 'NIT';
    case CC = 'CC';
    case CE = 'CE';
    case PA = 'PA';
    case PPT = 'PPT';
    case PEP = 'PEP';

    public function label(): string;
    public function shortLabel(): string;
}
```

### Model: `App\Models\Requester`

```php
// Attributes
- id: int (PK)
- name: string (128)                  // Nombre del contacto / solicitante
- company_name: string (nullable, 128) // Razón social (opcional si es persona natural)
- document_type: RequesterDocumentTypeEnum (string 16)
- document_number: string (32)
- phone: string (32)
- email: string (nullable, 128)
- is_active: bool (default true)
- notes: text (nullable)
- created_at, updated_at, deleted_at (SoftDeletes)

// Indices
- unique(['document_type', 'document_number'])

// Scopes & Helpers
- scopeActive(Builder $query): Builder
- getDisplayNameAttribute(): string // "Company Name — Contact" or "Contact"
```

---

## 2. Actions & DTOs

### DTO: `App\DTOs\Requesters\UpsertRequesterDTO`
```php
declare(strict_types=1);

namespace App\DTOs\Requesters;

use App\Enums\Requesters\RequesterDocumentTypeEnum;

readonly class UpsertRequesterDTO
{
    public function __construct(
        public string $name,
        public RequesterDocumentTypeEnum $documentType,
        public string $documentNumber,
        public string $phone,
        public ?string $companyName = null,
        public ?string $email = null,
        public bool $isActive = true,
        public ?string $notes = null,
    ) {}

    public static function fromArray(array $data): self;
}
```

### Actions:
- `App\Actions\Requesters\RegisterRequesterAction`:
  * Invokable: `__invoke(UpsertRequesterDTO $dto): Requester`
  * Valida unicidad de `(document_type, document_number)` en registros activos / no eliminados.
- `App\Actions\Requesters\UpdateRequesterAction`:
  * Invokable: `__invoke(Requester $requester, UpsertRequesterDTO $dto): Requester`
  * Valida unicidad ignorando el registro actual.

---

## 3. UI Layer (Filament v4)

* **Resource**: `App\Filament\Resources\Requesters\RequesterResource`
* **Form Schema**:
  - `Section` con Grid responsivo:
    * `company_name`: Razón Social (placeholder: `Ej: Transportes del Norte S.A.S.`).
    * `name`: Nombre de Contacto (prefixIcon `heroicon-m-user`).
    * `document_type`: Select con `RequesterDocumentTypeEnum`.
    * `document_number`: TextInput con normalización mayúsculas (prefixIcon `heroicon-m-identification`).
    * `phone`: TextInput con validación de teléfono (prefixIcon `heroicon-m-phone`).
    * `email`: TextInput con validación de email (prefixIcon `heroicon-m-envelope`).
    * `is_active`: Toggle con label "Solicitante Habilitado para Viajes".
    * `notes`: Textarea (opcional).
* **Table Columns**:
  - `display_name` o `name` + `company_name` con badge/descripción.
  - `document_type` + `document_number` (copiable).
  - `phone` (copiable).
  - `email`.
  - `is_active` (IconColumn booleano o badge con color).
* **Table Filters**:
  - `SelectFilter` por `document_type`.
  - `TernaryFilter` / `SelectFilter` por `is_active`.
  - `TrashedFilter` para registros en papelera (`SoftDeletes`).

