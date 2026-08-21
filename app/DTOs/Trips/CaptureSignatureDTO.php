<?php

declare(strict_types=1);

namespace App\DTOs\Trips;

readonly class CaptureSignatureDTO
{
    public function __construct(
        public int $tripId,
        public string $signerName,
        public string $signatureBase64,
    ) {}
}
