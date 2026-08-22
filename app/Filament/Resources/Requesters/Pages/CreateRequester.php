<?php

declare(strict_types=1);

namespace App\Filament\Resources\Requesters\Pages;

use App\Actions\Requesters\RegisterRequesterAction;
use App\DTOs\Requesters\UpsertRequesterDTO;
use App\Filament\Resources\Requesters\RequesterResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Override;

class CreateRequester extends CreateRecord
{
    protected static string $resource = RequesterResource::class;

    #[Override]
    protected function handleRecordCreation(array $data): Model
    {
        $dto = UpsertRequesterDTO::fromArray($data);
        $action = app(RegisterRequesterAction::class);

        try {
            return $action($dto);
        } catch (\DomainException $e) {
            Notification::make()
                ->title('Error al Registrar Solicitante')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->halt();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
