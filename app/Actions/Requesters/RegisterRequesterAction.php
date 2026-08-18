<?php

declare(strict_types=1);

namespace App\Actions\Requesters;

use App\DTOs\Requesters\UpsertRequesterDTO;
use App\Exceptions\Requesters\InvalidRequesterException;
use App\Models\Requester;
use Illuminate\Support\Facades\DB;

class RegisterRequesterAction
{
    /**
     * Register a new requester (individual or company) in the system.
     *
     * @throws InvalidRequesterException
     */
    public function __invoke(UpsertRequesterDTO $dto): Requester
    {
        $exists = Requester::query()
            ->where('document_type', $dto->documentType)
            ->where('document_number', $dto->documentNumber)
            ->exists();

        if ($exists) {
            throw InvalidRequesterException::documentAlreadyRegistered(
                $dto->documentType->value,
                $dto->documentNumber
            );
        }

        return DB::transaction(function () use ($dto): Requester {
            return Requester::create([
                'name' => $dto->name,
                'company_name' => $dto->companyName,
                'document_type' => $dto->documentType,
                'document_number' => $dto->documentNumber,
                'phone' => $dto->phone,
                'email' => $dto->email,
                'is_active' => $dto->isActive,
                'notes' => $dto->notes,
            ]);
        });
    }
}
