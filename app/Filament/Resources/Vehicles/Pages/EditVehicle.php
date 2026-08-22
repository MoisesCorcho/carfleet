<?php

declare(strict_types=1);

namespace App\Filament\Resources\Vehicles\Pages;

use App\Actions\Vehicles\UpdateVehicleAction;
use App\DTOs\Vehicles\UpsertVehicleDTO;
use App\Filament\Resources\Vehicles\VehicleResource;
use App\Models\Vehicle;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Override;

class EditVehicle extends EditRecord
{
    protected static string $resource = VehicleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->using(function (Vehicle $record, DeleteAction $action): bool {
                    try {
                        return (bool) $record->delete();
                    } catch (\DomainException $e) {
                        Notification::make()
                            ->title('No se puede eliminar el vehículo')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();

                        return false;
                    }
                }),
            RestoreAction::make(),
            ForceDeleteAction::make()
                ->using(function (Vehicle $record, ForceDeleteAction $action): bool {
                    try {
                        return (bool) $record->forceDelete();
                    } catch (\DomainException $e) {
                        Notification::make()
                            ->title('No se puede eliminar el vehículo')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();

                        return false;
                    }
                }),
        ];
    }

    protected function beforeValidate(): void
    {
        $plate = $this->data['plate_number'] ?? null;
        if ($plate) {
            $clean = strtoupper(trim((string) $plate));
            if (preg_match('/^[A-Z]{3}[0-9]{3}$/i', $clean) || preg_match('/^[A-Z]{3}[0-9]{2}[A-Z]$/i', $clean)) {
                $this->data['plate_number'] = substr($clean, 0, 3).'-'.substr($clean, 3);
            } else {
                $this->data['plate_number'] = $clean;
            }
        }
    }

    #[Override]
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Vehicle $record */
        $data['current_mileage'] = $record->current_mileage;
        $dto = UpsertVehicleDTO::fromArray($data);
        $action = app(UpdateVehicleAction::class);

        try {
            return $action($record, $dto);
        } catch (\DomainException $e) {
            Notification::make()
                ->title('Error al Actualizar Vehículo')
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
