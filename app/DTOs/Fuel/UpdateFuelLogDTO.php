<?php

declare(strict_types=1);

namespace App\DTOs\Fuel;

use Illuminate\Http\UploadedFile;

readonly class UpdateFuelLogDTO
{
    public function __construct(
        public int $fuelLogId,
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
    public static function fromArray(int $fuelLogId, array $data): self
    {
        return new self(
            fuelLogId: $fuelLogId,
            vehicleId: (int) $data['vehicle_id'],
            refuelDate: (string) $data['refuel_date'],
            mileageAtRefuel: (int) $data['mileage_at_refuel'],
            gallons: (float) $data['gallons'],
            totalCost: (int) $data['total_cost'],
            tripId: isset($data['trip_id']) && $data['trip_id'] !== '' ? (int) $data['trip_id'] : null,
            driverId: isset($data['driver_id']) && $data['driver_id'] !== '' ? (int) $data['driver_id'] : null,
            voucherNumber: isset($data['voucher_number']) ? (string) $data['voucher_number'] : null,
            voucherPhoto: $data['voucher_photo_path'] ?? null,
            notes: isset($data['notes']) ? (string) $data['notes'] : null,
        );
    }
}
