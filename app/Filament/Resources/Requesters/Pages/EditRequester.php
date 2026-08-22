<?php

declare(strict_types=1);

namespace App\Filament\Resources\Requesters\Pages;

use App\Actions\Requesters\UpdateRequesterAction;
use App\DTOs\Requesters\UpsertRequesterDTO;
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
            DeleteAction::make()
                ->using(function (Requester $record, DeleteAction $action): bool {
                    try {
                        return (bool) $record->delete();
                    } catch (\DomainException $e) {
                        Notification::make()
                            ->title('No se puede eliminar el solicitante')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();

                        return false;
                    }
                }),
            RestoreAction::make(),
            ForceDeleteAction::make()
                ->using(function (Requester $record, ForceDeleteAction $action): bool {
                    try {
                        return (bool) $record->forceDelete();
                    } catch (\DomainException $e) {
                        Notification::make()
                            ->title('No se puede eliminar el solicitante')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();

                        return false;
                    }
                }),
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
        } catch (\DomainException $e) {
            Notification::make()
                ->title('Error al Actualizar Solicitante')
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
