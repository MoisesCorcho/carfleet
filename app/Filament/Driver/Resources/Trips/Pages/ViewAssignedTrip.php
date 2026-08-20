<?php

declare(strict_types=1);

namespace App\Filament\Driver\Resources\Trips\Pages;

use App\Actions\Trips\RecordTripMileageAction;
use App\DTOs\Trips\RecordMileageDTO;
use App\Exceptions\Trips\DriverAlreadyInTripException;
use App\Exceptions\Trips\InvalidTripMileageException;
use App\Exceptions\Trips\InvalidTripStateException;
use App\Exceptions\Trips\TripImmutableException;
use App\Filament\Driver\Resources\Trips\AssignedTripResource;
use App\Models\Trip;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Utilities\Get;

class ViewAssignedTrip extends ViewRecord
{
    protected static string $resource = AssignedTripResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('startTrip')
                ->label('INICIAR SERVICIO')
                ->icon('heroicon-m-play')
                ->color('success')
                ->visible(fn (): bool => $this->getRecord()->canBeStarted())
                ->modalHeading('Iniciar Salida de Viaje')
                ->modalDescription('Ingresa la lectura inicial del odómetro y adjunta la foto de evidencia para iniciar el recorrido.')
                ->schema([
                    TextInput::make('initial_mileage')
                        ->label('Kilometraje Inicial (Salida)')
                        ->prefixIcon('heroicon-m-calculator')
                        ->numeric()
                        ->required()
                        ->minValue(fn (): int => $this->getRecord()->vehicle?->current_mileage ?? 0)
                        ->default(fn (): ?int => $this->getRecord()->vehicle?->current_mileage)
                        ->helperText(fn (): string => 'Odómetro actual del vehículo: '.number_format($this->getRecord()->vehicle?->current_mileage ?? 0).' km'),

                    ToggleButtons::make('photo_source')
                        ->label('Origen de la Foto')
                        ->options([
                            'camera' => 'Tomar Foto',
                            'gallery' => 'Elegir de Galería',
                        ])
                        ->icons([
                            'camera' => 'heroicon-m-camera',
                            'gallery' => 'heroicon-m-photo',
                        ])
                        ->colors([
                            'camera' => 'primary',
                            'gallery' => 'gray',
                        ])
                        ->default('camera')
                        ->inline()
                        ->live()
                        ->dehydrated(false),

                    FileUpload::make('photo_evidence')
                        ->label('Foto del Odómetro de Salida')
                        ->key(fn (Get $get): string => 'photo_evidence_view_start_'.($get('photo_source') ?? 'camera'))
                        ->image()
                        ->extraInputAttributes(fn (Get $get): array => ($get('photo_source') ?? 'camera') === 'camera' ? ['capture' => 'environment'] : [])
                        ->directory('evidences/odometers')
                        ->disk('public')
                        ->imageEditor()
                        ->maxSize(5120)
                        ->required()
                        ->helperText(fn (Get $get): string => ($get('photo_source') ?? 'camera') === 'camera'
                            ? 'Se abrirá la cámara de tu celular para capturar el odómetro en tiempo real.'
                            : 'Selecciona una fotografía nítida del tablero desde tu galería o archivos.'),

                    Textarea::make('notes')
                        ->label('Observaciones de Salida')
                        ->placeholder('Observaciones opcionales sobre el estado de salida...')
                        ->rows(2),
                ])
                ->action(function (array $data): void {
                    /** @var Trip $record */
                    $record = $this->getRecord();

                    try {
                        app(RecordTripMileageAction::class)(new RecordMileageDTO(
                            tripId: $record->id,
                            mileage: (int) $data['initial_mileage'],
                            photoEvidence: $data['photo_evidence'],
                            isFinal: false,
                            notes: isset($data['notes']) ? (string) $data['notes'] : null,
                        ));

                        Notification::make()
                            ->title('Servicio Iniciado')
                            ->body("El viaje {$record->code} ha iniciado exitosamente.")
                            ->success()
                            ->send();

                        $record->refresh();
                        $this->refreshFormData(['status', 'actual_departure_at', 'initial_mileage']);
                    } catch (TripImmutableException|InvalidTripStateException|InvalidTripMileageException|DriverAlreadyInTripException $e) {
                        Notification::make()
                            ->title('Error al Iniciar')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('finishTrip')
                ->label('FINALIZAR SERVICIO')
                ->icon('heroicon-m-check-circle')
                ->color('primary')
                ->visible(fn (): bool => $this->getRecord()->canBeFinished())
                ->modalHeading('Finalizar Servicio y Registrar Llegada')
                ->modalDescription('Ingresa la lectura final del odómetro y adjunta la foto de evidencia para completar el recorrido.')
                ->schema([
                    TextInput::make('final_mileage')
                        ->label('Kilometraje Final (Llegada)')
                        ->prefixIcon('heroicon-m-calculator')
                        ->numeric()
                        ->required()
                        ->minValue(fn (): int => ($this->getRecord()->initial_mileage ?? 0) + 1)
                        ->helperText(fn (): string => 'Kilometraje de salida registrado: '.number_format($this->getRecord()->initial_mileage ?? 0).' km'),

                    ToggleButtons::make('photo_source')
                        ->label('Origen de la Foto')
                        ->options([
                            'camera' => 'Tomar Foto',
                            'gallery' => 'Elegir de Galería',
                        ])
                        ->icons([
                            'camera' => 'heroicon-m-camera',
                            'gallery' => 'heroicon-m-photo',
                        ])
                        ->colors([
                            'camera' => 'primary',
                            'gallery' => 'gray',
                        ])
                        ->default('camera')
                        ->inline()
                        ->live()
                        ->dehydrated(false),

                    FileUpload::make('photo_evidence')
                        ->label('Foto del Odómetro de Llegada')
                        ->key(fn (Get $get): string => 'photo_evidence_view_finish_'.($get('photo_source') ?? 'camera'))
                        ->image()
                        ->extraInputAttributes(fn (Get $get): array => ($get('photo_source') ?? 'camera') === 'camera' ? ['capture' => 'environment'] : [])
                        ->directory('evidences/odometers')
                        ->disk('public')
                        ->imageEditor()
                        ->maxSize(5120)
                        ->required()
                        ->helperText(fn (Get $get): string => ($get('photo_source') ?? 'camera') === 'camera'
                            ? 'Se abrirá la cámara de tu celular para capturar el odómetro en tiempo real.'
                            : 'Selecciona una fotografía nítida del tablero desde tu galería o archivos.'),

                    Textarea::make('notes')
                        ->label('Observaciones de Llegada')
                        ->placeholder('Observaciones opcionales de la entrega o estado final...')
                        ->rows(2),
                ])
                ->action(function (array $data): void {
                    /** @var Trip $record */
                    $record = $this->getRecord();

                    try {
                        app(RecordTripMileageAction::class)(new RecordMileageDTO(
                            tripId: $record->id,
                            mileage: (int) $data['final_mileage'],
                            photoEvidence: $data['photo_evidence'],
                            isFinal: true,
                            notes: isset($data['notes']) ? (string) $data['notes'] : null,
                        ));

                        Notification::make()
                            ->title('Servicio Finalizado')
                            ->body("El viaje {$record->code} ha sido finalizado con éxito.")
                            ->success()
                            ->send();

                        $record->refresh();
                        $this->refreshFormData(['status', 'actual_arrival_at', 'final_mileage', 'distance_traveled']);
                    } catch (TripImmutableException|InvalidTripStateException|InvalidTripMileageException $e) {
                        Notification::make()
                            ->title('Error al Finalizar')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
