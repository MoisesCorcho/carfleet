<?php

declare(strict_types=1);

namespace App\Filament\Resources\Drivers\Pages;

use App\Actions\Drivers\RegisterDriverAction;
use App\DTOs\Drivers\UpsertDriverDTO;
use App\Exceptions\Drivers\InvalidDriverException;
use App\Filament\Resources\Drivers\DriverResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Override;

class CreateDriver extends CreateRecord
{
    protected static string $resource = DriverResource::class;

    #[Override]
    protected function handleRecordCreation(array $data): Model
    {
        $dto = UpsertDriverDTO::fromArray($data);
        $action = app(RegisterDriverAction::class);

        try {
            return $action($dto);
        } catch (InvalidDriverException $e) {
            Notification::make()
                ->title('Error de Registro')
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
