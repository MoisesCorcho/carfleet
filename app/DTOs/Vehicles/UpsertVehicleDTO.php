<?php

declare(strict_types=1);

namespace App\DTOs\Vehicles;

use App\Enums\Vehicles\FuelTypeEnum;
use App\Enums\Vehicles\VehicleStatusEnum;

readonly class UpsertVehicleDTO
{
    public function __construct(
        public string $plateNumber,
        public string $brand,
        public string $model,
        public int $year,
        public int $currentMileage,
        public VehicleStatusEnum $status = VehicleStatusEnum::DISPONIBLE,
        public FuelTypeEnum $fuelType = FuelTypeEnum::GASOLINA,
        public ?string $notes = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $status = match (true) {
            isset($data['status']) && $data['status'] instanceof VehicleStatusEnum => $data['status'],
            isset($data['status']) && is_string($data['status']) => VehicleStatusEnum::from($data['status']),
            default => VehicleStatusEnum::DISPONIBLE,
        };

        $fuelType = match (true) {
            isset($data['fuel_type']) && $data['fuel_type'] instanceof FuelTypeEnum => $data['fuel_type'],
            isset($data['fuelType']) && $data['fuelType'] instanceof FuelTypeEnum => $data['fuelType'],
            isset($data['fuel_type']) && is_string($data['fuel_type']) => FuelTypeEnum::from($data['fuel_type']),
            isset($data['fuelType']) && is_string($data['fuelType']) => FuelTypeEnum::from($data['fuelType']),
            default => FuelTypeEnum::GASOLINA,
        };

        return new self(
            plateNumber: strtoupper(trim((string) ($data['plate_number'] ?? $data['plateNumber'] ?? ''))),
            brand: trim((string) ($data['brand'] ?? '')),
            model: trim((string) ($data['model'] ?? '')),
            year: (int) ($data['year'] ?? 0),
            currentMileage: (int) ($data['current_mileage'] ?? $data['currentMileage'] ?? 0),
            status: $status,
            fuelType: $fuelType,
            notes: isset($data['notes']) ? (is_string($data['notes']) ? trim($data['notes']) : null) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'plate_number' => $this->plateNumber,
            'brand' => $this->brand,
            'model' => $this->model,
            'year' => $this->year,
            'current_mileage' => $this->currentMileage,
            'status' => $this->status,
            'fuel_type' => $this->fuelType,
            'notes' => $this->notes,
        ];
    }
}
