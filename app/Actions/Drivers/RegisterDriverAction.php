<?php

declare(strict_types=1);

namespace App\Actions\Drivers;

use App\DTOs\Drivers\UpsertDriverDTO;
use App\Exceptions\Drivers\InvalidDriverException;
use App\Models\Driver;
use Illuminate\Support\Facades\DB;

class RegisterDriverAction
{
    /**
     * Register a new driver profile in the system.
     *
     * @throws InvalidDriverException
     */
    public function __invoke(UpsertDriverDTO $dto): Driver
    {
        if (Driver::query()->where('user_id', $dto->userId)->exists()) {
            throw InvalidDriverException::userAlreadyAssigned($dto->userId);
        }

        return DB::transaction(function () use ($dto): Driver {
            return Driver::create([
                'user_id' => $dto->userId,
                'full_name' => $dto->fullName,
                'document_type' => $dto->documentType,
                'document_number' => $dto->documentNumber,
                'phone' => $dto->phone,
                'license_number' => $dto->licenseNumber,
                'license_expires_at' => $dto->licenseExpiresAt,
                'status' => $dto->status,
            ]);
        });
    }
}
