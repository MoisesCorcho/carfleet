<?php

declare(strict_types=1);

namespace App\Filament\Resources\Trips\Pages;

use App\Actions\Trips\CreateTripAction;
use App\DTOs\Trips\CreateTripDTO;
use App\Exceptions\Trips\DriverNotEligibleException;
use App\Exceptions\Trips\InvalidTripDatesException;
use App\Exceptions\Trips\VehicleNotAvailableException;
use App\Filament\Resources\Trips\TripResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Override;

class CreateTrip extends CreateRecord
{
    protected static string $resource = TripResource::class;

    #[Override]
    protected function handleRecordCreation(array $data): Model
    {
        $dto = CreateTripDTO::fromArray($data);
        $action = app(CreateTripAction::class);

        try {
            return $action($dto);
        } catch (InvalidTripDatesException|VehicleNotAvailableException|DriverNotEligibleException $e) {
            Notification::make()
                ->title('Error al Crear Viaje')
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
