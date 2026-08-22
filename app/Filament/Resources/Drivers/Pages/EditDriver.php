<?php

declare(strict_types=1);

namespace App\Filament\Resources\Drivers\Pages;

use App\Actions\Drivers\UpdateDriverAction;
use App\DTOs\Drivers\UpsertDriverDTO;
use App\Filament\Resources\Drivers\DriverResource;
use App\Models\Driver;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Override;

class EditDriver extends EditRecord
{
    protected static string $resource = DriverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->using(function (Driver $record, DeleteAction $action): bool {
                    try {
                        return (bool) $record->delete();
                    } catch (\DomainException $e) {
                        Notification::make()
                            ->title('No se puede eliminar el conductor')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();

                        return false;
                    }
                }),
            RestoreAction::make(),
            ForceDeleteAction::make()
                ->using(function (Driver $record, ForceDeleteAction $action): bool {
                    try {
                        return (bool) $record->forceDelete();
                    } catch (\DomainException $e) {
                        Notification::make()
                            ->title('No se puede eliminar el conductor')
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
        /** @var Driver $record */
        $dto = UpsertDriverDTO::fromArray($data);
        $action = app(UpdateDriverAction::class);

        try {
            return $action($record, $dto);
        } catch (\DomainException $e) {
            Notification::make()
                ->title('Error al Actualizar Conductor')
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
