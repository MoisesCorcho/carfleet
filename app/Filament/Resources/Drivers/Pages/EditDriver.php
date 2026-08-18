<?php

declare(strict_types=1);

namespace App\Filament\Resources\Drivers\Pages;

use App\Actions\Drivers\UpdateDriverAction;
use App\DTOs\Drivers\UpsertDriverDTO;
use App\Exceptions\Drivers\InvalidDriverException;
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
            DeleteAction::make(),
            RestoreAction::make(),
            ForceDeleteAction::make(),
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
        } catch (InvalidDriverException $e) {
            Notification::make()
                ->title('Error de Actualización')
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
