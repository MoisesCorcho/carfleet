<?php

declare(strict_types=1);

namespace App\Filament\Driver\Resources\Trips\Pages;

use App\Actions\Trips\StartTripAction;
use App\Exceptions\Trips\InvalidTripStateException;
use App\Exceptions\Trips\TripImmutableException;
use App\Filament\Driver\Resources\Trips\AssignedTripResource;
use App\Models\Trip;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewAssignedTrip extends ViewRecord
{
    protected static string $resource = AssignedTripResource::class;

    protected function getHeaderActions(): array
    {
        /** @var Trip $record */
        $record = $this->getRecord();

        return [
            Action::make('startTrip')
                ->label('INICIAR SERVICIO')
                ->icon('heroicon-m-play')
                ->color('success')
                ->visible(fn (): bool => $record->canBeStarted())
                ->requiresConfirmation()
                ->modalHeading('Iniciar Salida de Viaje')
                ->modalDescription('¿Confirmas que inicias el recorrido del servicio ahora?')
                ->action(function () use ($record): void {
                    try {
                        app(StartTripAction::class)($record);

                        Notification::make()
                            ->title('Servicio Iniciado')
                            ->body("El viaje {$record->code} ha iniciado exitosamente.")
                            ->success()
                            ->send();

                        $this->refreshFormData(['status', 'actual_departure_at']);
                    } catch (TripImmutableException|InvalidTripStateException $e) {
                        Notification::make()
                            ->title('Error al Iniciar')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
