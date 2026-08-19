<?php

declare(strict_types=1);

namespace App\DTOs\Trips;

readonly class CreateTripDTO
{
    public function __construct(
        public int $requesterId,
        public string $origin,
        public string $destination,
        public string $scheduledDepartureAt,
        public ?string $scheduledArrivalAt = null,
        public ?int $vehicleId = null,
        public ?int $driverId = null,
        public ?string $notes = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $scheduledArrivalAt = $data['scheduled_arrival_at'] ?? $data['scheduledArrivalAt'] ?? null;
        $vehicleId = $data['vehicle_id'] ?? $data['vehicleId'] ?? null;
        $driverId = $data['driver_id'] ?? $data['driverId'] ?? null;

        return new self(
            requesterId: (int) ($data['requester_id'] ?? $data['requesterId'] ?? 0),
            origin: trim((string) ($data['origin'] ?? '')),
            destination: trim((string) ($data['destination'] ?? '')),
            scheduledDepartureAt: (string) ($data['scheduled_departure_at'] ?? $data['scheduledDepartureAt'] ?? ''),
            scheduledArrivalAt: ! empty($scheduledArrivalAt) ? (string) $scheduledArrivalAt : null,
            vehicleId: ! empty($vehicleId) ? (int) $vehicleId : null,
            driverId: ! empty($driverId) ? (int) $driverId : null,
            notes: isset($data['notes']) && is_string($data['notes']) ? trim($data['notes']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'requester_id' => $this->requesterId,
            'origin' => $this->origin,
            'destination' => $this->destination,
            'scheduled_departure_at' => $this->scheduledDepartureAt,
            'scheduled_arrival_at' => $this->scheduledArrivalAt,
            'vehicle_id' => $this->vehicleId,
            'driver_id' => $this->driverId,
            'notes' => $this->notes,
        ];
    }
}
