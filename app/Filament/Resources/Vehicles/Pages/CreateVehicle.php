<?php

declare(strict_types=1);

namespace App\Filament\Resources\Vehicles\Pages;

use App\Actions\Vehicles\RegisterVehicleAction;
use App\DTOs\Vehicles\UpsertVehicleDTO;
use App\Exceptions\Vehicles\InvalidMileageException;
use App\Filament\Resources\Vehicles\VehicleResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Override;

class CreateVehicle extends CreateRecord
{
    protected static string $resource = VehicleResource::class;

    #[Override]
    protected function handleRecordCreation(array $data): Model
    {
        $dto = UpsertVehicleDTO::fromArray($data);
        $action = app(RegisterVehicleAction::class);

        try {
            return $action($dto);
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
