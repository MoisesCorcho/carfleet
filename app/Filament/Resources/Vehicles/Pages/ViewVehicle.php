<?php

declare(strict_types=1);

namespace App\Filament\Resources\Vehicles\Pages;

use App\Filament\Resources\Vehicles\VehicleResource;
use App\Filament\Resources\Vehicles\Widgets\VehiclePerformanceWidget;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewVehicle extends ViewRecord
{
    protected static string $resource = VehicleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
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
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            VehiclePerformanceWidget::class,
        ];
    }
}
