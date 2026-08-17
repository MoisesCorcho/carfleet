# Design — F05: Kilometraje y Evidencias Fotográficas

## 1. Domain Entities & Schemas

### Backed Enums

* **`App\Enums\Evidences\EvidenceTypeEnum`** (string): `kilometraje_salida`, `kilometraje_llegada`, `voucher_combustible`.

### Model: `App\Models\TripEvidence`

```php
// Attributes
- protected $table = 'trip_evidences'
- id: int (PK)
- trip_id: int (FK -> trips.id)
- type: EvidenceTypeEnum
- file_path: string (255)
- recorded_mileage: int (nullable)
- notes: string (nullable)
- created_at, updated_at
```

### Columns added to `trips` table:
* `initial_mileage`: int (nullable)
* `final_mileage`: int (nullable)
* `distance_traveled`: int (nullable, calculated: `final_mileage - initial_mileage`)

---

## 2. Actions & DTOs

### DTO: `App\DTOs\Trips\RecordMileageDTO`
```php
declare(strict_types=1);

namespace App\DTOs\Trips;

use Illuminate\Http\UploadedFile;

readonly class RecordMileageDTO
{
    public function __construct(
        public int $tripId,
        public int $mileage,
        public UploadedFile $photoEvidence,
        public bool $isFinal = false,
    ) {}
}
```

### Action: `App\Actions\Trips\RecordTripMileageAction`
* Invokable: `__invoke(RecordMileageDTO $dto): Trip`
* Stores evidence photo in `public/evidences/odometers`.
* Creates `TripEvidence` record.
* Updates `Trip` initial or final mileage and calculates `distance_traveled`.
* Updates associated `Vehicle` `current_mileage`.

---

## 3. UI Layer (Filament v4)

* **Filament FileUpload**: Component for odometer photo in Trip Form / Custom Action.
* **RelationManager / Infolist**: `EvidencesRelationManager` on `TripResource` to display photos with image preview modal.
