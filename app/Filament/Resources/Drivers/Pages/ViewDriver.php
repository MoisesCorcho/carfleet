<?php

declare(strict_types=1);

namespace App\Filament\Resources\Drivers\Pages;

use App\Filament\Resources\Drivers\DriverResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDriver extends ViewRecord
{
    protected static string $resource = DriverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
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
        ];
    }
}
