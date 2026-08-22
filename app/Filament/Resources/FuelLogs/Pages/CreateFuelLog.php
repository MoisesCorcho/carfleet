<?php

declare(strict_types=1);

namespace App\Filament\Resources\FuelLogs\Pages;

use App\Actions\Fuel\RegisterFuelLogAction;
use App\DTOs\Fuel\RegisterFuelLogDTO;
use App\Filament\Resources\FuelLogs\FuelLogResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Override;

class CreateFuelLog extends CreateRecord
{
    protected static string $resource = FuelLogResource::class;

    #[Override]
    protected function handleRecordCreation(array $data): Model
    {
        $dto = RegisterFuelLogDTO::fromArray($data);
        $action = app(RegisterFuelLogAction::class);

        try {
            return $action($dto);
        } catch (\DomainException $e) {
            Notification::make()
                ->title('Error al Registrar Tanqueo')
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
