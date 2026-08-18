<?php

declare(strict_types=1);

namespace App\Filament\Resources\Vehicles\Pages;

use App\Actions\Vehicles\UpdateVehicleAction;
use App\DTOs\Vehicles\UpsertVehicleDTO;
use App\Exceptions\Vehicles\InvalidMileageException;
use App\Filament\Resources\Vehicles\VehicleResource;
use App\Models\Vehicle;
use Filament\Actions\DeleteAction;
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
            DeleteAction::make(),
        ];
    }

    #[Override]
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Vehicle $record */
        $dto = UpsertVehicleDTO::fromArray($data);
        $action = app(UpdateVehicleAction::class);

        try {
            return $action($record, $dto);
        } catch (InvalidMileageException $e) {
            Notification::make()
                ->title('Error de Validación')
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
