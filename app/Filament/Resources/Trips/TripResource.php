<?php

declare(strict_types=1);

namespace App\Filament\Resources\Trips;

use App\Actions\Trips\AssignTripResourcesAction;
use App\Actions\Trips\CancelTripAction;
use App\Actions\Trips\CloseTripAction;
use App\Enums\Trips\TripStatusEnum;
use App\Exceptions\Trips\DriverNotEligibleException;
use App\Exceptions\Trips\DriverScheduleConflictException;
use App\Exceptions\Trips\InvalidTripMileageException;
use App\Exceptions\Trips\InvalidTripStateException;
use App\Exceptions\Trips\TripImmutableException;
use App\Exceptions\Trips\TripMissingEvidenceException;
use App\Exceptions\Trips\TripMissingSignatureException;
use App\Exceptions\Trips\VehicleNotAvailableException;
use App\Filament\Resources\Trips\Pages\CreateTrip;
use App\Filament\Resources\Trips\Pages\EditTrip;
use App\Filament\Resources\Trips\Pages\ListTrips;
use App\Filament\Resources\Trips\Pages\ViewTrip;
use App\Filament\Resources\Trips\RelationManagers\EvidencesRelationManager;
use App\Filament\Resources\Trips\RelationManagers\FuelLogsRelationManager;
use App\Models\Driver;
use App\Models\Requester;
use App\Models\Trip;
use App\Models\Vehicle;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;
use UnitEnum;

class TripResource extends Resource
{
    protected static ?string $model = Trip::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    protected static string|UnitEnum|null $navigationGroup = 'Gestión de Flota';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'code';

    public static function getModelLabel(): string
    {
        return 'Viaje';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Viajes';
    }

    public static function getNavigationLabel(): string
    {
        return 'Viajes';
    }

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->schema([
                        Section::make('Información del Servicio y Solicitante')
                            ->description('Detalle del cliente solicitante y código identificador.')
                            ->schema([
                                TextInput::make('code')
                                    ->label('Código Consecutivo')
                                    ->placeholder('Autogenerado: TRIP-YYYY-NNNN')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->visible(fn (?Trip $record): bool => $record !== null),

                                Select::make('requester_id')
                                    ->label('Solicitante / Empresa')
                                    ->prefixIcon('heroicon-m-building-office')
                                    ->options(fn (): array => Requester::query()
                                        ->where('is_active', true)
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->toArray())
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->disabled(fn (?Trip $record): bool => $record?->isImmutable() ?? false)
                                    ->helperText('Entidad o persona que requiere el traslado.'),
                            ])
                            ->columns([
                                'sm' => 1,
                                'md' => 2,
                            ])
                            ->columnSpanFull(),

                        Section::make('Ruta y Programación')
                            ->description('Puntos de origen, destino y tiempos programados.')
                            ->schema([
                                TextInput::make('origin')
                                    ->label('Origen')
                                    ->placeholder('Ej: Sede Principal - Av. El Dorado #100')
                                    ->prefixIcon('heroicon-m-map-pin')
                                    ->required()
                                    ->maxLength(128)
                                    ->disabled(fn (?Trip $record): bool => $record?->isImmutable() ?? false),

                                TextInput::make('destination')
                                    ->label('Destino')
                                    ->placeholder('Ej: Planta Industrial Norte - Km 15')
                                    ->prefixIcon('heroicon-m-flag')
                                    ->required()
                                    ->maxLength(128)
                                    ->disabled(fn (?Trip $record): bool => $record?->isImmutable() ?? false),

                                DateTimePicker::make('scheduled_departure_at')
                                    ->label('Salida Programada')
                                    ->prefixIcon('heroicon-m-calendar')
                                    ->required()
                                    ->native(false)
                                    ->disabled(fn (?Trip $record): bool => $record?->isImmutable() ?? false),

                                DateTimePicker::make('scheduled_arrival_at')
                                    ->label('Llegada Programada Estimada')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->native(false)
                                    ->after('scheduled_departure_at')
                                    ->validationMessages([
                                        'after' => 'La fecha de llegada estimada debe ser posterior a la fecha de salida programada.',
                                    ])
                                    ->disabled(fn (?Trip $record): bool => $record?->isImmutable() ?? false),
                            ])
                            ->columns([
                                'sm' => 1,
                                'md' => 2,
                            ])
                            ->columnSpanFull(),

                        Section::make('Asignación de Recursos (Opcional en Creación)')
                            ->description('Vehículo y conductor que ejecutarán el servicio.')
                            ->schema([
                                Select::make('vehicle_id')
                                    ->label('Vehículo')
                                    ->prefixIcon('heroicon-m-truck')
                                    ->requiredWith('driver_id')
                                    ->validationMessages([
                                        'required_with' => 'Debes seleccionar un vehículo si asignas un conductor.',
                                    ])
                                    ->options(function (?Trip $record): array {
                                        return Vehicle::query()
                                            ->where(function (Builder $query) use ($record): void {
                                                $query->available();
                                                if ($record?->vehicle_id) {
                                                    $query->orWhere('id', $record->vehicle_id);
                                                }
                                            })
                                            ->get()
                                            ->mapWithKeys(fn (Vehicle $v): array => [
                                                $v->id => "{$v->plate_number} — {$v->brand} {$v->model} ({$v->status->label()})",
                                            ])
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->disabled(fn (?Trip $record): bool => $record?->isImmutable() ?? false)
                                    ->helperText('Solo se muestran vehículos en estado Disponible.'),

                                Select::make('driver_id')
                                    ->label('Conductor Asignado')
                                    ->prefixIcon('heroicon-m-user')
                                    ->requiredWith('vehicle_id')
                                    ->validationMessages([
                                        'required_with' => 'Debes seleccionar un conductor si asignas un vehículo.',
                                    ])
                                    ->options(function (?Trip $record): array {
                                        return Driver::query()
                                            ->where(function (Builder $query) use ($record): void {
                                                $query->eligibleForTrip();
                                                if ($record?->driver_id) {
                                                    $query->orWhere('id', $record->driver_id);
                                                }
                                            })
                                            ->get()
                                            ->mapWithKeys(fn (Driver $d): array => [
                                                $d->id => "{$d->full_name} — Lic: {$d->license_number} (Cat: {$d->license_category->value})",
                                            ])
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->disabled(fn (?Trip $record): bool => $record?->isImmutable() ?? false)
                                    ->helperText('Conductores activos con licencia vigente.'),

                                Textarea::make('notes')
                                    ->label('Observaciones / Instrucciones')
                                    ->placeholder('Instrucciones especiales para el traslado o puntos de referencia...')
                                    ->rows(3)
                                    ->columnSpanFull()
                                    ->disabled(fn (?Trip $record): bool => $record?->isImmutable() ?? false),
                            ])
                            ->columns([
                                'sm' => 1,
                                'md' => 2,
                            ])
                            ->columnSpanFull(),

                        Section::make('Control de Kilometraje y Recorrido')
                            ->description('Lecturas de odómetro registradas durante el servicio.')
                            ->schema([
                                TextInput::make('initial_mileage')
                                    ->label('Kilometraje Inicial')
                                    ->numeric()
                                    ->suffix('km')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('final_mileage')
                                    ->label('Kilometraje Final')
                                    ->numeric()
                                    ->suffix('km')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('distance_traveled')
                                    ->label('Distancia Recorrida')
                                    ->numeric()
                                    ->suffix('km')
                                    ->disabled()
                                    ->dehydrated(false),
                            ])
                            ->columns([
                                'sm' => 1,
                                'md' => 3,
                            ])
                            ->columnSpanFull()
                            ->visible(fn (?Trip $record): bool => $record !== null),

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
            ->columns([
                TextColumn::make('code')
                    ->label('Código')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->copyable()
                    ->copyMessage('Código copiado'),

                TextColumn::make('requester.name')
                    ->label('Solicitante')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Trip $record): string => $record->requester?->company_name ?? ''),

                TextColumn::make('origin')
                    ->label('Origen')
                    ->searchable()
                    ->limit(25),

                TextColumn::make('destination')
                    ->label('Destino')
                    ->searchable()
                    ->limit(25),

                TextColumn::make('vehicle.plate_number')
                    ->label('Vehículo')
                    ->placeholder('Sin asignar')
                    ->badge()
                    ->color(fn (?string $state): string => $state ? 'info' : 'gray')
                    ->searchable(),

                TextColumn::make('driver.full_name')
                    ->label('Conductor')
                    ->placeholder('Sin asignar')
                    ->searchable(),

                TextColumn::make('scheduled_departure_at')
                    ->label('Salida Programada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (TripStatusEnum $state): string => $state->color())
                    ->formatStateUsing(fn (TripStatusEnum $state): string => $state->label()),
            ])
            ->defaultSort('scheduled_departure_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado del Viaje')
                    ->options(collect(TripStatusEnum::cases())->mapWithKeys(
                        fn (TripStatusEnum $status): array => [$status->value => $status->label()]
                    )),

                SelectFilter::make('requester_id')
                    ->label('Solicitante')
                    ->relationship('requester', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()
                        ->visible(fn (Trip $record): bool => ! $record->isImmutable()),
                    Action::make('assignResources')
                        ->label('Asignar Recursos')
                        ->icon('heroicon-m-user-plus')
                        ->color('info')
                        ->visible(fn (Trip $record): bool => $record->canBeAssigned())
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
                        ->action(function (Trip $record, array $data): void {
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
                            } catch (VehicleNotAvailableException|DriverNotEligibleException|DriverScheduleConflictException|TripImmutableException $e) {
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
                        ->visible(fn (Trip $record): bool => $record->canBeCancelled())
                        ->requiresConfirmation()
                        ->schema([
                            Textarea::make('reason')
                                ->label('Motivo de Cancelación')
                                ->placeholder('Explique brevemente la razón de la cancelación...')
                                ->required(),
                        ])
                        ->action(function (Trip $record, array $data): void {
                            try {
                                app(CancelTripAction::class)($record, (string) ($data['reason'] ?? ''));

                                Notification::make()
                                    ->title('Viaje Cancelado')
                                    ->body("El viaje {$record->code} fue cancelado y los recursos liberados.")
                                    ->warning()
                                    ->send();
                            } catch (TripImmutableException|InvalidTripStateException $e) {
                                Notification::make()
                                    ->title('Error al Cancelar')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Action::make('closeTrip')
                        ->label('Cerrar Viaje')
                        ->icon('heroicon-m-lock-closed')
                        ->color('success')
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

                    DeleteAction::make()
                        ->visible(fn (Trip $record): bool => $record->canBeCancelled()),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            EvidencesRelationManager::class,
            FuelLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrips::route('/'),
            'create' => CreateTrip::route('/create'),
            'view' => ViewTrip::route('/{record}'),
            'edit' => EditTrip::route('/{record}/edit'),
        ];
    }
}
