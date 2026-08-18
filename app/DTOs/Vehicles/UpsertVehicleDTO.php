<?php

declare(strict_types=1);

namespace App\DTOs\Vehicles;

use App\Enums\Vehicles\FuelTypeEnum;
use App\Enums\Vehicles\ServiceTypeEnum;
use App\Enums\Vehicles\VehicleStatusEnum;
use App\Enums\Vehicles\VehicleTypeEnum;

readonly class UpsertVehicleDTO
{
    public function __construct(
        public string $plateNumber,
        public string $brand,
        public string $model,
        public int $year,
        public int $currentMileage,
        public VehicleTypeEnum $vehicleType = VehicleTypeEnum::CAMIONETA,
        public ServiceTypeEnum $serviceType = ServiceTypeEnum::PUBLICO,
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

        $vehicleType = match (true) {
            isset($data['vehicle_type']) && $data['vehicle_type'] instanceof VehicleTypeEnum => $data['vehicle_type'],
            isset($data['vehicleType']) && $data['vehicleType'] instanceof VehicleTypeEnum => $data['vehicleType'],
            isset($data['vehicle_type']) && is_string($data['vehicle_type']) => VehicleTypeEnum::from($data['vehicle_type']),
            isset($data['vehicleType']) && is_string($data['vehicleType']) => VehicleTypeEnum::from($data['vehicleType']),
            default => VehicleTypeEnum::CAMIONETA,
        };

        $serviceType = match (true) {
            isset($data['service_type']) && $data['service_type'] instanceof ServiceTypeEnum => $data['service_type'],
            isset($data['serviceType']) && $data['serviceType'] instanceof ServiceTypeEnum => $data['serviceType'],
            isset($data['service_type']) && is_string($data['service_type']) => ServiceTypeEnum::from($data['service_type']),
            isset($data['serviceType']) && is_string($data['serviceType']) => ServiceTypeEnum::from($data['serviceType']),
            default => ServiceTypeEnum::PUBLICO,
        };

        return new self(
            plateNumber: strtoupper(trim((string) ($data['plate_number'] ?? $data['plateNumber'] ?? ''))),
            brand: trim((string) ($data['brand'] ?? '')),
            model: trim((string) ($data['model'] ?? '')),
            year: (int) ($data['year'] ?? 0),
            currentMileage: (int) ($data['current_mileage'] ?? $data['currentMileage'] ?? 0),
            vehicleType: $vehicleType,
            serviceType: $serviceType,
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
            'vehicle_type' => $this->vehicleType,
            'service_type' => $this->serviceType,
            'current_mileage' => $this->currentMileage,
            'status' => $this->status,
            'fuel_type' => $this->fuelType,
            'notes' => $this->notes,
        ];
    }
}
