<?php

declare(strict_types=1);

namespace App\Filament\Resources\Trips\Pages;

use App\Actions\Trips\AssignTripResourcesAction;
use App\Actions\Trips\CancelTripAction;
use App\Exceptions\Trips\DriverNotEligibleException;
use App\Exceptions\Trips\InvalidTripStateException;
use App\Exceptions\Trips\TripImmutableException;
use App\Exceptions\Trips\VehicleNotAvailableException;
use App\Filament\Resources\Trips\TripResource;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\Vehicle;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewTrip extends ViewRecord
{
    protected static string $resource = TripResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('assignResources')
                ->label('Asignar Recursos')
                ->icon('heroicon-m-user-plus')
                ->color('info')
                ->visible(fn (): bool => $this->getRecord()->canBeAssigned())
                ->schema([
                    Select::make('vehicle_id')
                        ->label('Vehículo Disponible')
                        ->prefixIcon('heroicon-m-truck')
                        ->options(fn (): array => Vehicle::query()
                            ->available()
                            ->get()
                            ->mapWithKeys(fn (Vehicle $v): array => [
                                $v->id => "{$v->plate_number} — {$v->brand} {$v->model}",
                            ])
                            ->toArray())
                        ->searchable()
                        ->required(),

                    Select::make('driver_id')
                        ->label('Conductor Elegible')
                        ->prefixIcon('heroicon-m-user')
                        ->options(fn (): array => Driver::query()
                            ->eligibleForTrip()
                            ->get()
                            ->mapWithKeys(fn (Driver $d): array => [
                                $d->id => "{$d->full_name} — Lic: {$d->license_number}",
                            ])
                            ->toArray())
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    /** @var Trip $record */
                    $record = $this->getRecord();

                    try {
                        app(AssignTripResourcesAction::class)(
                            $record,
                            (int) $data['vehicle_id'],
                            (int) $data['driver_id']
                        );

                        Notification::make()
                            ->title('Recursos Asignados')
                            ->body("El viaje {$record->code} ha sido asignado correctamente.")
                            ->success()
                            ->send();

                        $record->refresh();
                        $this->refreshFormData(['vehicle_id', 'driver_id', 'status']);
                    } catch (VehicleNotAvailableException|DriverNotEligibleException|TripImmutableException $e) {
                        Notification::make()
                            ->title('Error al Asignar')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('cancelTrip')
                ->label('Cancelar Viaje')
                ->icon('heroicon-m-x-circle')
                ->color('danger')
                ->visible(fn (): bool => $this->getRecord()->canBeCancelled())
                ->requiresConfirmation()
                ->schema([
                    Textarea::make('reason')
                        ->label('Motivo de Cancelación')
                        ->placeholder('Explique brevemente la razón de la cancelación...')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    /** @var Trip $record */
                    $record = $this->getRecord();

                    try {
                        app(CancelTripAction::class)($record, (string) ($data['reason'] ?? ''));

                        Notification::make()
                            ->title('Viaje Cancelado')
                            ->body("El viaje {$record->code} fue cancelado y los recursos liberados.")
                            ->warning()
                            ->send();

                        $record->refresh();
                        $this->refreshFormData(['status', 'notes']);
                    } catch (TripImmutableException|InvalidTripStateException $e) {
                        Notification::make()
                            ->title('Error al Cancelar')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            EditAction::make()
                ->visible(fn (): bool => ! $this->getRecord()->isImmutable()),

            DeleteAction::make()
                ->visible(fn (): bool => $this->getRecord()->canBeCancelled()),
        ];
    }
}
