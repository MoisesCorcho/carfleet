<?php

declare(strict_types=1);

namespace App\Actions\Drivers;

use App\DTOs\Drivers\UpsertDriverDTO;
use App\Exceptions\Drivers\InvalidDriverException;
use App\Models\Driver;
use Illuminate\Support\Facades\DB;

class UpdateDriverAction
{
    /**
     * Update an existing driver's information and operational status.
     *
     * @throws InvalidDriverException
     */
    public function __invoke(Driver $driver, UpsertDriverDTO $dto): Driver
    {
        if ($dto->userId !== $driver->user_id && Driver::query()->where('user_id', $dto->userId)->where('id', '!=', $driver->id)->exists()) {
            throw InvalidDriverException::userAlreadyAssigned($dto->userId);
        }

        return DB::transaction(function () use ($driver, $dto): Driver {
            $driver->update([
                'user_id' => $dto->userId,
                'full_name' => $dto->fullName,
                'document_type' => $dto->documentType,
                'document_number' => $dto->documentNumber,
                'phone' => $dto->phone,
                'license_number' => $dto->licenseNumber,
                'license_category' => $dto->licenseCategory,
                'license_expires_at' => $dto->licenseExpiresAt,
                'status' => $dto->status,
            ]);

            return $driver->refresh();
        });
    }
}
