<?php

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

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $documentType = match (true) {
            isset($data['document_type']) && $data['document_type'] instanceof RequesterDocumentTypeEnum => $data['document_type'],
            isset($data['documentType']) && $data['documentType'] instanceof RequesterDocumentTypeEnum => $data['documentType'],
            isset($data['document_type']) && is_string($data['document_type']) => RequesterDocumentTypeEnum::from($data['document_type']),
            isset($data['documentType']) && is_string($data['documentType']) => RequesterDocumentTypeEnum::from($data['documentType']),
            default => RequesterDocumentTypeEnum::NIT,
        };

        $isActive = true;
        if (array_key_exists('is_active', $data)) {
            $isActive = (bool) $data['is_active'];
        } elseif (array_key_exists('isActive', $data)) {
            $isActive = (bool) $data['isActive'];
        }

        $companyName = $data['company_name'] ?? $data['companyName'] ?? null;
        if (is_string($companyName)) {
            $companyName = trim($companyName);
            if ($companyName === '') {
                $companyName = null;
            }
        }

        $email = $data['email'] ?? null;
        if (is_string($email)) {
            $email = strtolower(trim($email));
            if ($email === '') {
                $email = null;
            }
        }

        $notes = $data['notes'] ?? null;
        if (is_string($notes)) {
            $notes = trim($notes);
            if ($notes === '') {
                $notes = null;
            }
        }

        return new self(
            name: trim((string) ($data['name'] ?? '')),
            documentType: $documentType,
            documentNumber: strtoupper(trim((string) ($data['document_number'] ?? $data['documentNumber'] ?? ''))),
            phone: trim((string) ($data['phone'] ?? '')),
            companyName: $companyName,
            email: $email,
            isActive: $isActive,
            notes: $notes,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'company_name' => $this->companyName,
            'document_type' => $this->documentType,
            'document_number' => $this->documentNumber,
            'phone' => $this->phone,
            'email' => $this->email,
            'is_active' => $this->isActive,
            'notes' => $this->notes,
        ];
    }
}
