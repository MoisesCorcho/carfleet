<?php

declare(strict_types=1);

namespace App\Filament\Resources\Vehicles\Pages;

use App\Actions\Vehicles\RegisterVehicleAction;
use App\DTOs\Vehicles\UpsertVehicleDTO;
use App\Filament\Resources\Vehicles\VehicleResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Override;

class CreateVehicle extends CreateRecord
{
    protected static string $resource = VehicleResource::class;

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
    protected function handleRecordCreation(array $data): Model
    {
        $dto = UpsertVehicleDTO::fromArray($data);
        $action = app(RegisterVehicleAction::class);

        try {
            return $action($dto);
        } catch (\DomainException $e) {
            Notification::make()
                ->title('Error al Registrar Vehículo')
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
