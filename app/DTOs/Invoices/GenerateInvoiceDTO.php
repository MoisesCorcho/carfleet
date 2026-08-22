<?php

declare(strict_types=1);

namespace App\DTOs\Invoices;

final readonly class GenerateInvoiceDTO
{
    /**
     * @param  array<int>  $tripIds
     */
    public function __construct(
        public int $requesterId,
        public array $tripIds,
        public string $issueDate = '',
        public int $baseRatePerTrip = 50000,
        public int $ratePerKm = 3500,
        public ?string $notes = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $rawTripIds = $data['trip_ids'] ?? [];
        if (! is_array($rawTripIds)) {
            $rawTripIds = [$rawTripIds];
        }

        $tripIds = array_values(array_filter(
            array_map(fn (mixed $id): int => (int) $id, $rawTripIds),
            fn (int $id): bool => $id > 0
        ));

        return new self(
            requesterId: (int) ($data['requester_id'] ?? 0),
            tripIds: $tripIds,
            issueDate: (string) ($data['issue_date'] ?? now()->toDateString()),
            baseRatePerTrip: isset($data['base_rate_per_trip']) ? (int) $data['base_rate_per_trip'] : 50000,
            ratePerKm: isset($data['rate_per_km']) ? (int) $data['rate_per_km'] : 3500,
            notes: isset($data['notes']) ? (string) $data['notes'] : null,
        );
    }
}
