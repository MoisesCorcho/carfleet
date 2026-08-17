# Design — F07: Firma Digital y Cierre de Viaje

## 1. Domain Entities & Schemas

### Model: `App\Models\DigitalSignature`

```php
// Attributes
- id: int (PK)
- trip_id: int (FK -> trips.id, unique)
- signer_name: string (128)
- signature_path: string (255)
- signed_at: datetime
- created_at, updated_at
```

---

## 2. Actions & DTOs

### DTO: `App\DTOs\Trips\CaptureSignatureDTO`
```php
declare(strict_types=1);

namespace App\DTOs\Trips;

readonly class CaptureSignatureDTO
{
    public function __construct(
        public int $tripId,
        public string $signerName,
        public string $signatureBase64, // PNG Base64 string from canvas
    ) {}
}
```

### Action: `App\Actions\Trips\CaptureTripSignatureAction`
* Invokable: `__invoke(CaptureSignatureDTO $dto): DigitalSignature`
* Decodes Base64 signature and stores PNG image in `public/evidences/signatures`.
* Creates `DigitalSignature` record.

### Action: `App\Actions\Trips\CloseTripAction`
* Invokable: `__invoke(Trip $trip): Trip`
* Validates `initial_mileage`, `final_mileage`, odometer evidences, and `DigitalSignature`.
* Atomically updates trip status to `cerrado` and vehicle status to `disponible` inside `DB::transaction`.

---

## 3. UI Layer (Filament v4)

* **Filament Custom Field / Action**: `CaptureSignatureAction` using Canvas Signature Pad script.
* **Infolist Component**: Displays signed PNG image in Trip detail view.
