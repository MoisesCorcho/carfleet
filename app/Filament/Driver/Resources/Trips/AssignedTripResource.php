<?php

declare(strict_types=1);

namespace App\Filament\Driver\Resources\Trips;

use App\Actions\Fuel\RegisterFuelLogAction;
use App\Actions\Trips\CaptureTripSignatureAction;
use App\Actions\Trips\CloseTripAction;
use App\Actions\Trips\RecordTripMileageAction;
use App\DTOs\Fuel\RegisterFuelLogDTO;
use App\DTOs\Trips\CaptureSignatureDTO;
use App\DTOs\Trips\RecordMileageDTO;
use App\Enums\Trips\TripStatusEnum;
use App\Exceptions\Fuel\FuelVehicleMismatchException;
use App\Exceptions\Fuel\FutureRefuelDateException;
use App\Exceptions\Fuel\InvalidFuelCostException;
use App\Exceptions\Fuel\InvalidFuelMileageException;
use App\Exceptions\Fuel\InvalidFuelQuantityException;
use App\Exceptions\Trips\DriverAlreadyInTripException;
use App\Exceptions\Trips\InvalidSignatureDataException;
use App\Exceptions\Trips\InvalidTripMileageException;
use App\Exceptions\Trips\InvalidTripStateException;
use App\Exceptions\Trips\TripImmutableException;
use App\Exceptions\Trips\TripMissingEvidenceException;
use App\Exceptions\Trips\TripMissingSignatureException;
use App\Filament\Driver\Resources\Trips\Pages\ListAssignedTrips;
use App\Filament\Driver\Resources\Trips\Pages\ViewAssignedTrip;
use App\Models\Trip;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

class AssignedTripResource extends Resource
{
    protected static ?string $model = Trip::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Mis Viajes';

    protected static ?string $recordTitleAttribute = 'code';

    public static function getModelLabel(): string
    {
        return 'Mi Viaje';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Mis Viajes';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        $driverId = auth()->user()?->driver?->id ?? 0;

        return parent::getEloquentQuery()
            ->where('driver_id', $driverId);
    }

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->schema([
                        Section::make('Detalle del Servicio Asignado')
                            ->description('Información operativa del viaje para su ejecución.')
                            ->schema([
                                TextInput::make('code')
                                    ->label('Código de Viaje')
                                    ->disabled(),

                                TextInput::make('status')
                                    ->label('Estado')
                                    ->formatStateUsing(fn ($state) => $state instanceof TripStatusEnum ? $state->label() : (string) $state)
                                    ->disabled(),

                                TextInput::make('origin')
                                    ->label('Punto de Origen')
                                    ->prefixIcon('heroicon-m-map-pin')
                                    ->disabled(),

                                TextInput::make('destination')
                                    ->label('Punto de Destino')
                                    ->prefixIcon('heroicon-m-flag')
                                    ->disabled(),

                                DateTimePicker::make('scheduled_departure_at')
                                    ->label('Salida Programada')
                                    ->prefixIcon('heroicon-m-calendar')
                                    ->native(false)
                                    ->disabled(),

                                DateTimePicker::make('actual_departure_at')
                                    ->label('Salida Real Registrada')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->native(false)
                                    ->disabled(),

                                DateTimePicker::make('actual_arrival_at')
                                    ->label('Llegada Real Registrada')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->native(false)
                                    ->disabled(),

                                Textarea::make('notes')
                                    ->label('Instrucciones / Observaciones')
                                    ->rows(3)
                                    ->columnSpanFull()
                                    ->disabled(),
                            ])
                            ->columns([
                                'sm' => 1,
                                'md' => 2,
                            ])
                            ->columnSpanFull(),

                        Section::make('Kilometraje y Rendimiento')
                            ->description('Lecturas de odómetro registradas durante el servicio.')
                            ->schema([
                                TextInput::make('initial_mileage')
                                    ->label('Kilometraje Inicial')
                                    ->numeric()
                                    ->suffix('km')
                                    ->disabled(),

                                TextInput::make('final_mileage')
                                    ->label('Kilometraje Final')
                                    ->numeric()
                                    ->suffix('km')
                                    ->disabled(),

                                TextInput::make('distance_traveled')
                                    ->label('Distancia Recorrida')
                                    ->numeric()
                                    ->suffix('km')
                                    ->disabled(),
                            ])
                            ->columns([
                                'sm' => 1,
                                'md' => 3,
                            ])
                            ->columnSpanFull(),

                        Section::make('Firma Digital de Conformidad')
                            ->description('Firma capturada del solicitante como respaldo del servicio completado.')
                            ->schema([
                                ViewField::make('signature_preview')
                                    ->view('filament.resources.trips.signature-preview')
                                    ->hiddenLabel()
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull()
                            ->visible(fn (?Trip $record): bool => $record !== null && ($record->signature !== null || $record->isCompleted() || $record->isClosed())),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'default' => 1,
                'md' => 1,
                'lg' => 2,
            ])
            ->columns([
                Stack::make([
                    Split::make([
                        TextColumn::make('code')
                            ->badge()
                            ->color('gray')
                            ->weight(FontWeight::Bold)
                            ->searchable(),

                        TextColumn::make('status')
                            ->badge()
                            ->color(fn (TripStatusEnum $state): string => $state->color())
                            ->formatStateUsing(fn (TripStatusEnum $state): string => $state->label())
                            ->alignEnd(),
                    ]),

                    TextColumn::make('origin')
                        ->icon('heroicon-m-map-pin')
                        ->formatStateUsing(fn (Trip $record): string => "{$record->origin}  ➔  {$record->destination}")
                        ->weight(FontWeight::SemiBold)
                        ->searchable(),

                    Split::make([
                        TextColumn::make('vehicle.plate_number')
                            ->icon('heroicon-m-truck')
                            ->badge()
                            ->color('info')
                            ->formatStateUsing(fn (Trip $record): string => $record->vehicle ? "{$record->vehicle->plate_number} ({$record->vehicle->brand} {$record->vehicle->model})" : 'Sin Vehículo'),

                        TextColumn::make('scheduled_departure_at')
                            ->icon('heroicon-m-calendar')
                            ->dateTime('d/m/Y H:i')
                            ->color('gray')
                            ->alignEnd(),
                    ]),
                ])->space(3),
            ])
            ->defaultSort('scheduled_departure_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(collect(TripStatusEnum::cases())->mapWithKeys(
                        fn (TripStatusEnum $status): array => [$status->value => $status->label()]
                    )),
            ])
            ->recordActions([
                Action::make('startTrip')
                    ->label('INICIAR SERVICIO')
                    ->icon('heroicon-m-play')
                    ->color('success')
                    ->button()
                    ->visible(fn (Trip $record): bool => $record->canBeStarted())
                    ->modalHeading('Iniciar Salida de Viaje')
                    ->modalDescription('Ingresa la lectura inicial del odómetro y adjunta la foto de evidencia para iniciar el recorrido.')
                    ->schema([
                        TextInput::make('initial_mileage')
                            ->label('Kilometraje Inicial (Salida)')
                            ->prefixIcon('heroicon-m-calculator')
                            ->numeric()
                            ->required()
                            ->minValue(fn (Trip $record): int => $record->vehicle?->current_mileage ?? 0)
                            ->default(fn (Trip $record): ?int => $record->vehicle?->current_mileage)
                            ->helperText(fn (Trip $record): string => 'Odómetro actual del vehículo: '.number_format($record->vehicle?->current_mileage ?? 0).' km'),

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
                            ->key(fn (Get $get): string => 'photo_evidence_start_'.($get('photo_source') ?? 'camera'))
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
                    ->action(function (Trip $record, array $data): void {
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
                        } catch (TripImmutableException|InvalidTripStateException|InvalidTripMileageException|DriverAlreadyInTripException $e) {
                            Notification::make()
                                ->title('Error al Iniciar')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('registerFuel')
                    ->label('REGISTRAR COMBUSTIBLE')
                    ->icon('heroicon-m-fire')
                    ->color('warning')
                    ->button()
                    ->visible(fn (Trip $record): bool => $record->isInProgress())
                    ->modalHeading('Registrar Tanqueo de Combustible')
                    ->modalDescription('Ingresa los datos del abastecimiento realizado durante el servicio y adjunta la foto del voucher.')
                    ->schema([
                        DateTimePicker::make('refuel_date')
                            ->label('Fecha y Hora del Tanqueo')
                            ->prefixIcon('heroicon-m-calendar')
                            ->required()
                            ->maxDate(now())
                            ->default(now())
                            ->native(false),

                        TextInput::make('mileage_at_refuel')
                            ->label('Kilometraje al Tanquear')
                            ->prefixIcon('heroicon-m-calculator')
                            ->numeric()
                            ->required()
                            ->minValue(fn (Trip $record): int => $record->initial_mileage ?? ($record->vehicle?->current_mileage ?? 0))
                            ->default(fn (Trip $record): ?int => $record->vehicle?->current_mileage ?? $record->initial_mileage)
                            ->helperText(fn (Trip $record): string => 'Kilometraje de salida del viaje: '.number_format($record->initial_mileage ?? 0).' km'),

                        TextInput::make('gallons')
                            ->label('Cantidad de Galones')
                            ->prefixIcon('heroicon-m-fire')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0.01)
                            ->required()
                            ->suffix('gal')
                            ->placeholder('Ej: 10.50'),

                        TextInput::make('total_cost')
                            ->label('Costo Total ($)')
                            ->prefixIcon('heroicon-m-banknotes')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->prefix('$')
                            ->placeholder('Ej: 150000'),

                        TextInput::make('voucher_number')
                            ->label('Número de Voucher / Factura')
                            ->prefixIcon('heroicon-m-document-text')
                            ->maxLength(64)
                            ->placeholder('Ej: V-123456'),

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

                        FileUpload::make('voucher_photo')
                            ->label('Foto del Voucher / Recibo')
                            ->key(fn (Get $get): string => 'voucher_photo_driver_'.($get('photo_source') ?? 'camera'))
                            ->image()
                            ->extraInputAttributes(fn (Get $get): array => ($get('photo_source') ?? 'camera') === 'camera' ? ['capture' => 'environment'] : [])
                            ->directory('evidences/vouchers')
                            ->disk('public')
                            ->imageEditor()
                            ->maxSize(5120)
                            ->required()
                            ->helperText(fn (Get $get): string => ($get('photo_source') ?? 'camera') === 'camera'
                                ? 'Se abrirá la cámara de tu celular para capturar el comprobante.'
                                : 'Selecciona una foto clara del comprobante desde tu galería.'),

                        Textarea::make('notes')
                            ->label('Observaciones / Estación')
                            ->placeholder('Nombre de la gasolinera, forma de pago...')
                            ->rows(2),
                    ])
                    ->action(function (Trip $record, array $data): void {
                        try {
                            app(RegisterFuelLogAction::class)(new RegisterFuelLogDTO(
                                vehicleId: (int) $record->vehicle_id,
                                refuelDate: (string) $data['refuel_date'],
                                mileageAtRefuel: (int) $data['mileage_at_refuel'],
                                gallons: (float) $data['gallons'],
                                totalCost: (int) $data['total_cost'],
                                tripId: $record->id,
                                driverId: $record->driver_id,
                                voucherNumber: isset($data['voucher_number']) ? (string) $data['voucher_number'] : null,
                                voucherPhoto: $data['voucher_photo'] ?? null,
                                notes: isset($data['notes']) ? (string) $data['notes'] : null,
                            ));

                            Notification::make()
                                ->title('Tanqueo Registrado')
                                ->body("El abastecimiento de combustible para el viaje {$record->code} se registró correctamente.")
                                ->success()
                                ->send();
                        } catch (
                            InvalidFuelQuantityException|
                            InvalidFuelCostException|
                            InvalidFuelMileageException|
                            FutureRefuelDateException|
                            FuelVehicleMismatchException|
                            TripImmutableException $e
                        ) {
                            Notification::make()
                                ->title('Error al Registrar Combustible')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('finishTrip')
                    ->label('FINALIZAR SERVICIO')
                    ->icon('heroicon-m-check-circle')
                    ->color('primary')
                    ->button()
                    ->visible(fn (Trip $record): bool => $record->canBeFinished())
                    ->modalHeading('Finalizar Servicio y Registrar Llegada')
                    ->modalDescription('Ingresa la lectura final del odómetro y adjunta la foto de evidencia para completar el recorrido.')
                    ->schema([
                        TextInput::make('final_mileage')
                            ->label('Kilometraje Final (Llegada)')
                            ->prefixIcon('heroicon-m-calculator')
                            ->numeric()
                            ->required()
                            ->minValue(fn (Trip $record): int => ($record->initial_mileage ?? 0) + 1)
                            ->helperText(fn (Trip $record): string => 'Kilometraje de salida registrado: '.number_format($record->initial_mileage ?? 0).' km'),

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
                            ->key(fn (Get $get): string => 'photo_evidence_finish_'.($get('photo_source') ?? 'camera'))
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
                    ->action(function (Trip $record, array $data): void {
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
                        } catch (TripImmutableException|InvalidTripStateException|InvalidTripMileageException $e) {
                            Notification::make()
                                ->title('Error al Finalizar')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('captureSignature')
                    ->label('FIRMAR CONFORMIDAD')
                    ->icon('heroicon-m-pencil-square')
                    ->color('warning')
                    ->button()
                    ->visible(fn (Trip $record): bool => $record->canBeSigned())
                    ->modalHeading('Firma Digital de Conformidad')
                    ->modalDescription('El solicitante del servicio debe estampar su firma digital para validar la entrega y permitir el cierre formal.')
                    ->modalSubmitActionLabel('Guardar Firma')
                    ->schema([
                        TextInput::make('signer_name')
                            ->label('Nombre del Solicitante / Firmante')
                            ->prefixIcon('heroicon-m-user')
                            ->required()
                            ->maxLength(128)
                            ->default(fn (Trip $record): string => $record->requester?->name ?? ''),

                        ViewField::make('signature_data')
                            ->label('Trazo de Firma de Conformidad')
                            ->required()
                            ->validationMessages([
                                'required' => 'Debes dibujar la firma de conformidad en el lienzo antes de guardar.',
                            ])
                            ->helperText('Dibuja el trazo de la firma sobre el recuadro blanco usando el dedo o el mouse.')
                            ->view('filament.forms.components.signature-pad'),
                    ])
                    ->action(function (Trip $record, array $data): void {
                        try {
                            app(CaptureTripSignatureAction::class)(new CaptureSignatureDTO(
                                tripId: $record->id,
                                signerName: (string) $data['signer_name'],
                                signatureBase64: (string) $data['signature_data'],
                            ));

                            Notification::make()
                                ->title('Firma Registrada')
                                ->body("La firma de conformidad para el viaje {$record->code} fue capturada exitosamente.")
                                ->success()
                                ->send();
                        } catch (TripImmutableException|InvalidTripStateException|InvalidSignatureDataException $e) {
                            Notification::make()
                                ->title('Error al Capturar Firma')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('closeTrip')
                    ->label('CERRAR VIAJE')
                    ->icon('heroicon-m-lock-closed')
                    ->color('success')
                    ->button()
                    ->visible(fn (Trip $record): bool => $record->canBeClosed())
                    ->requiresConfirmation()
                    ->modalHeading('Cierre Formal del Servicio')
                    ->modalDescription('¿Confirmas el cierre formal del viaje? Esta acción es definitiva, el registro quedará inmutable y el vehículo pasará a estado disponible.')
                    ->modalSubmitActionLabel('Confirmar Cierre')
                    ->action(function (Trip $record): void {
                        try {
                            app(CloseTripAction::class)($record);

                            Notification::make()
                                ->title('Viaje Cerrado')
                                ->body("El viaje {$record->code} ha sido cerrado formalmente.")
                                ->success()
                                ->send();
                        } catch (TripImmutableException|InvalidTripStateException|InvalidTripMileageException|TripMissingEvidenceException|TripMissingSignatureException $e) {
                            Notification::make()
                                ->title('Error al Cerrar Viaje')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                ViewAction::make()
                    ->button()
                    ->color('gray'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAssignedTrips::route('/'),
            'view' => ViewAssignedTrip::route('/{record}'),
        ];
    }
}
