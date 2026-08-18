<?php

declare(strict_types=1);

namespace App\Filament\Resources\Requesters\Pages;

use App\Actions\Requesters\UpdateRequesterAction;
use App\DTOs\Requesters\UpsertRequesterDTO;
use App\Exceptions\Requesters\InvalidRequesterException;
use App\Filament\Resources\Requesters\RequesterResource;
use App\Models\Requester;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Override;

class EditRequester extends EditRecord
{
    protected static string $resource = RequesterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            RestoreAction::make(),
            ForceDeleteAction::make(),
        ];
    }

    #[Override]
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Requester $record */
        $dto = UpsertRequesterDTO::fromArray($data);
        $action = app(UpdateRequesterAction::class);

        try {
            return $action($record, $dto);
        } catch (InvalidRequesterException $e) {
            Notification::make()
                ->title('Error de Actualización')
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
