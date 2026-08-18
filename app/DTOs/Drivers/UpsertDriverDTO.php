<?php

declare(strict_types=1);

namespace App\DTOs\Drivers;

use App\Enums\Drivers\DocumentTypeEnum;
use App\Enums\Drivers\DriverStatusEnum;
use DateTimeInterface;
use Illuminate\Support\Carbon;

readonly class UpsertDriverDTO
{
    public function __construct(
        public int $userId,
        public string $fullName,
        public DocumentTypeEnum $documentType,
        public string $documentNumber,
        public string $phone,
        public string $licenseNumber,
        public ?string $licenseExpiresAt = null,
        public DriverStatusEnum $status = DriverStatusEnum::ACTIVO,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $status = match (true) {
            isset($data['status']) && $data['status'] instanceof DriverStatusEnum => $data['status'],
            isset($data['status']) && is_string($data['status']) => DriverStatusEnum::from($data['status']),
            default => DriverStatusEnum::ACTIVO,
        };

        $documentType = match (true) {
            isset($data['document_type']) && $data['document_type'] instanceof DocumentTypeEnum => $data['document_type'],
            isset($data['documentType']) && $data['documentType'] instanceof DocumentTypeEnum => $data['documentType'],
            isset($data['document_type']) && is_string($data['document_type']) => DocumentTypeEnum::from($data['document_type']),
            isset($data['documentType']) && is_string($data['documentType']) => DocumentTypeEnum::from($data['documentType']),
            default => DocumentTypeEnum::CC,
        };

        $expiresAt = null;
        $rawExpires = $data['license_expires_at'] ?? $data['licenseExpiresAt'] ?? null;
        if ($rawExpires instanceof DateTimeInterface) {
            $expiresAt = $rawExpires->format('Y-m-d');
        } elseif (is_string($rawExpires) && trim($rawExpires) !== '') {
            $expiresAt = Carbon::parse($rawExpires)->toDateString();
        }

        return new self(
            userId: (int) ($data['user_id'] ?? $data['userId'] ?? 0),
            fullName: trim((string) ($data['full_name'] ?? $data['fullName'] ?? '')),
            documentType: $documentType,
            documentNumber: strtoupper(trim((string) ($data['document_number'] ?? $data['documentNumber'] ?? ''))),
            phone: trim((string) ($data['phone'] ?? '')),
            licenseNumber: strtoupper(trim((string) ($data['license_number'] ?? $data['licenseNumber'] ?? ''))),
            licenseExpiresAt: $expiresAt,
            status: $status,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'full_name' => $this->fullName,
            'document_type' => $this->documentType,
            'document_number' => $this->documentNumber,
            'phone' => $this->phone,
            'license_number' => $this->licenseNumber,
            'license_expires_at' => $this->licenseExpiresAt,
            'status' => $this->status,
        ];
    }
}
