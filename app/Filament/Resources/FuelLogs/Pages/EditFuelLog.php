<?php

declare(strict_types=1);

namespace App\Filament\Resources\FuelLogs\Pages;

use App\Actions\Fuel\UpdateFuelLogAction;
use App\DTOs\Fuel\UpdateFuelLogDTO;
use App\Filament\Resources\FuelLogs\FuelLogResource;
use App\Models\FuelLog;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Override;

class EditFuelLog extends EditRecord
{
    protected static string $resource = FuelLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->visible(fn (FuelLog $record): bool => ! $record->isImmutable())
                ->using(function (FuelLog $record, DeleteAction $action): bool {
                    try {
                        return (bool) $record->delete();
                    } catch (\DomainException $e) {
                        Notification::make()
                            ->title('No se puede eliminar el tanqueo')
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
        $dto = UpdateFuelLogDTO::fromArray($record->id, $data);
        $action = app(UpdateFuelLogAction::class);

        try {
            return $action($dto);
        } catch (\DomainException $e) {
            Notification::make()
                ->title('Error al Actualizar Tanqueo')
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
