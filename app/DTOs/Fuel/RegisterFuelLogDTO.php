<?php

declare(strict_types=1);

namespace App\DTOs\Fuel;

use Illuminate\Http\UploadedFile;

readonly class RegisterFuelLogDTO
{
    public function __construct(
        public int $vehicleId,
        public string $refuelDate,
        public int $mileageAtRefuel,
        public float $gallons,
        public int $totalCost,
        public ?int $tripId = null,
        public ?int $driverId = null,
        public ?string $voucherNumber = null,
        public UploadedFile|string|null $voucherPhoto = null,
        public ?string $notes = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            vehicleId: (int) ($data['vehicle_id'] ?? $data['vehicleId'] ?? 0),
            refuelDate: (string) ($data['refuel_date'] ?? $data['refuelDate'] ?? now()->toDateTimeString()),
            mileageAtRefuel: (int) ($data['mileage_at_refuel'] ?? $data['mileageAtRefuel'] ?? 0),
            gallons: (float) ($data['gallons'] ?? 0.0),
            totalCost: (int) ($data['total_cost'] ?? $data['totalCost'] ?? 0),
            tripId: ! empty($data['trip_id']) ? (int) $data['trip_id'] : (! empty($data['tripId']) ? (int) $data['tripId'] : null),
            driverId: ! empty($data['driver_id']) ? (int) $data['driver_id'] : (! empty($data['driverId']) ? (int) $data['driverId'] : null),
            voucherNumber: isset($data['voucher_number']) ? (is_string($data['voucher_number']) && trim($data['voucher_number']) !== '' ? trim($data['voucher_number']) : null) : (isset($data['voucherNumber']) && is_string($data['voucherNumber']) && trim($data['voucherNumber']) !== '' ? trim($data['voucherNumber']) : null),
            voucherPhoto: $data['voucher_photo'] ?? $data['voucherPhoto'] ?? $data['voucher_photo_path'] ?? null,
            notes: isset($data['notes']) && is_string($data['notes']) && trim($data['notes']) !== '' ? trim($data['notes']) : null,
        );
    }
}
