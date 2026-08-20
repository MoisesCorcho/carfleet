<?php

declare(strict_types=1);

namespace App\DTOs\Trips;

use Illuminate\Http\UploadedFile;

readonly class RecordMileageDTO
{
    public function __construct(
        public int $tripId,
        public int $mileage,
        public UploadedFile|string $photoEvidence,
        public bool $isFinal = false,
        public ?string $notes = null,
    ) {}
}
