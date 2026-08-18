<?php

declare(strict_types=1);

namespace App\Actions\Requesters;

use App\DTOs\Requesters\UpsertRequesterDTO;
use App\Exceptions\Requesters\InvalidRequesterException;
use App\Models\Requester;
use Illuminate\Support\Facades\DB;

class UpdateRequesterAction
{
    /**
     * Update an existing requester's information.
     *
     * @throws InvalidRequesterException
     */
    public function __invoke(Requester $requester, UpsertRequesterDTO $dto): Requester
    {
        $duplicate = Requester::query()
            ->where('document_type', $dto->documentType)
            ->where('document_number', $dto->documentNumber)
            ->where('id', '!=', $requester->id)
            ->exists();

        if ($duplicate) {
            throw InvalidRequesterException::documentAlreadyRegistered(
                $dto->documentType->value,
                $dto->documentNumber
            );
        }

        return DB::transaction(function () use ($requester, $dto): Requester {
            $requester->update([
                'name' => $dto->name,
                'company_name' => $dto->companyName,
                'document_type' => $dto->documentType,
                'document_number' => $dto->documentNumber,
                'phone' => $dto->phone,
                'email' => $dto->email,
                'is_active' => $dto->isActive,
                'notes' => $dto->notes,
            ]);

            return $requester->refresh();
        });
    }
}
