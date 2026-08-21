<?php

declare(strict_types=1);

namespace App\Filament\Resources\Vehicles;

use App\Actions\Vehicles\AdjustVehicleMileageAction;
use App\Enums\Trips\TripStatusEnum;
use App\Enums\Vehicles\FuelTypeEnum;
use App\Enums\Vehicles\ServiceTypeEnum;
use App\Enums\Vehicles\VehicleStatusEnum;
use App\Enums\Vehicles\VehicleTypeEnum;
use App\Exceptions\Vehicles\InvalidMileageException;
use App\Filament\Resources\Vehicles\Pages\CreateVehicle;
use App\Filament\Resources\Vehicles\Pages\EditVehicle;
use App\Filament\Resources\Vehicles\Pages\ListVehicles;
use App\Filament\Resources\Vehicles\Pages\ViewVehicle;
use App\Filament\Resources\Vehicles\RelationManagers\FuelLogsRelationManager;
use App\Filament\Resources\Vehicles\RelationManagers\TripsRelationManager;
use App\Models\Vehicle;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Override;
use UnitEnum;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static string|UnitEnum|null $navigationGroup = 'Gestión de Flota';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'plate_number';

    public static function getModelLabel(): string
    {
        return 'Vehículo';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Vehículos';
    }

    public static function getNavigationLabel(): string
    {
        return 'Vehículos';
    }

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->schema([
                        Section::make('Identificación y Características del Vehículo')
                            ->description('Datos de registro oficial y características de la carrocería.')
                            ->schema([
                                TextInput::make('plate_number')
                                    ->label('Placa del Vehículo')
                                    ->placeholder('Ej: ABC-123 o ABC123')
                                    ->prefixIcon('heroicon-m-identification')
                                    ->required()
                                    ->maxLength(16)
                                    ->regex('/^[A-Z]{3}-?[0-9]{3}$|^[A-Z]{3}-?[0-9]{2}[A-Z]$/i')
                                    ->validationMessages([
                                        'regex' => 'La placa debe tener un formato válido (ej: ABC-123 o ABC123).',
                                    ])
                                    ->unique(ignoreRecord: true)
                                    ->extraInputAttributes(['style' => 'text-transform: uppercase;'])
                                    ->dehydrateStateUsing(fn (?string $state): string => strtoupper(trim((string) $state)))
                                    ->disabled(fn (?Vehicle $record): bool => $record?->trips()->whereIn('status', [TripStatusEnum::ASIGNADO, TripStatusEnum::EN_CURSO])->exists() ?? false)
                                    ->helperText(fn (?Vehicle $record): string => $record?->trips()->whereIn('status', [TripStatusEnum::ASIGNADO, TripStatusEnum::EN_CURSO])->exists()
                                        ? '⚠️ Bloqueado: El vehículo tiene un viaje activo o asignado.'
                                        : 'Formato alfanumérico oficial del vehículo.'),

                                Select::make('service_type')
                                    ->label('Tipo de Servicio')
                                    ->prefixIcon('heroicon-m-shield-check')
                                    ->options(collect(ServiceTypeEnum::cases())->mapWithKeys(
                                        fn (ServiceTypeEnum $service): array => [$service->value => $service->label()]
                                    )->all())
                                    ->default(ServiceTypeEnum::PUBLICO->value)
                                    ->required()
                                    ->disabled(fn (?Vehicle $record): bool => $record?->trips()->whereIn('status', [TripStatusEnum::ASIGNADO, TripStatusEnum::EN_CURSO])->exists() ?? false)
                                    ->helperText('Modalidad de servicio asignada al vehículo (Público o Particular).'),

                                Select::make('vehicle_type')
                                    ->label('Tipo de Carrocería')
                                    ->prefixIcon('heroicon-m-truck')
                                    ->options(collect(VehicleTypeEnum::cases())->mapWithKeys(
                                        fn (VehicleTypeEnum $type): array => [$type->value => $type->label()]
                                    )->all())
                                    ->default(VehicleTypeEnum::CAMIONETA->value)
                                    ->required()
                                    ->disabled(fn (?Vehicle $record): bool => $record?->trips()->whereIn('status', [TripStatusEnum::ASIGNADO, TripStatusEnum::EN_CURSO])->exists() ?? false)
                                    ->helperText('Clasificación vehicular de la flota.'),

                                TextInput::make('brand')
                                    ->label('Marca')
                                    ->placeholder('Ej: Toyota, Chevrolet, Nissan')
                                    ->prefixIcon('heroicon-m-tag')
                                    ->required()
                                    ->maxLength(64),

                                TextInput::make('model')
                                    ->label('Línea / Modelo')
                                    ->placeholder('Ej: Hilux 4x4, D-Max, Frontier')
                                    ->prefixIcon('heroicon-m-cube')
                                    ->required()
                                    ->maxLength(64),

                                TextInput::make('year')
                                    ->label('Año de Fabricación')
                                    ->prefixIcon('heroicon-m-calendar-days')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1950)
                                    ->maxValue((int) date('Y') + 1)
                                    ->placeholder((string) date('Y')),
                            ])
                            ->columns([
                                'sm' => 1,
                                'md' => 3,
                            ])
                            ->columnSpanFull(),

                        Section::make('Operación y Odómetro')
                            ->description('Parámetros de kilometraje inicial y estado operativo.')
                            ->schema([
                                TextInput::make('current_mileage')
                                    ->label('Kilometraje Actual')
                                    ->prefixIcon('heroicon-m-variable')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->default(0)
                                    ->suffix('km')
                                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                                    ->helperText(fn (string $operation): string => $operation === 'edit'
                                        ? 'Odómetro protegido: se actualiza automáticamente al registrar viajes o mediante la acción "Ajustar Odómetro".'
                                        : 'Lectura inicial del odómetro en kilómetros al registrar el vehículo.'
                                    ),

                                Select::make('status')
                                    ->label('Estado Operacional')
                                    ->prefixIcon('heroicon-m-check-circle')
                                    ->options(collect(VehicleStatusEnum::cases())->mapWithKeys(
                                        fn (VehicleStatusEnum $status): array => [$status->value => $status->label()]
                                    )->all())
                                    ->default(VehicleStatusEnum::DISPONIBLE->value)
                                    ->required()
                                    ->disabled(fn (?Vehicle $record): bool => $record?->trips()->whereIn('status', [TripStatusEnum::ASIGNADO, TripStatusEnum::EN_CURSO])->exists() ?? false)
                                    ->helperText(fn (?Vehicle $record): ?string => $record?->trips()->whereIn('status', [TripStatusEnum::ASIGNADO, TripStatusEnum::EN_CURSO])->exists()
                                        ? '⚠️ Bloqueado: El vehículo tiene un viaje activo o asignado.'
                                        : null),

                                Select::make('fuel_type')
                                    ->label('Tipo de Combustible')
                                    ->prefixIcon('heroicon-m-fire')
                                    ->options(collect(FuelTypeEnum::cases())->mapWithKeys(
                                        fn (FuelTypeEnum $fuel): array => [$fuel->value => $fuel->label()]
                                    )->all())
                                    ->default(FuelTypeEnum::GASOLINA->value)
                                    ->required(),
                            ])
                            ->columns([
                                'sm' => 1,
                                'md' => 3,
                            ])
                            ->columnSpanFull(),

                        Section::make('Observaciones')
                            ->schema([
                                Textarea::make('notes')
                                    ->label('Notas Adicionales / Bitácora')
                                    ->rows(3)
                                    ->maxLength(2000)
                                    ->placeholder('Especificaciones técnicas, detalles de carrocería, pólizas o notas de auditoría.'),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('plate_number')
                    ->label('Placa')
                    ->badge()
                    ->color(fn (Vehicle $record): string => $record->service_type === ServiceTypeEnum::PUBLICO ? 'warning' : 'gray')
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Placa copiada al portapapeles'),

                TextColumn::make('brand')
                    ->label('Marca')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('model')
                    ->label('Modelo')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('vehicle_type')
                    ->label('Carrocería')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (VehicleTypeEnum $state): string => $state->label())
                    ->sortable(),

                TextColumn::make('service_type')
                    ->label('Servicio')
                    ->badge()
                    ->color(fn (ServiceTypeEnum $state): string => $state->color())
                    ->formatStateUsing(fn (ServiceTypeEnum $state): string => $state === ServiceTypeEnum::PUBLICO ? 'Público' : 'Particular')
                    ->sortable(),

                TextColumn::make('year')
                    ->label('Año')
                    ->sortable(),

                TextColumn::make('current_mileage')
                    ->label('Kilometraje')
                    ->formatStateUsing(fn (int $state): string => number_format($state, 0, ',', '.').' km')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (VehicleStatusEnum $state): string => $state->color())
                    ->formatStateUsing(fn (VehicleStatusEnum $state): string => $state->label())
                    ->sortable(),

                TextColumn::make('fuel_type')
                    ->label('Combustible')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (FuelTypeEnum $state): string => $state->label())
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado Operacional')
                    ->options(collect(VehicleStatusEnum::cases())->mapWithKeys(
                        fn (VehicleStatusEnum $status): array => [$status->value => $status->label()]
                    )->all()),

                SelectFilter::make('vehicle_type')
                    ->label('Tipo de Carrocería')
                    ->options(collect(VehicleTypeEnum::cases())->mapWithKeys(
                        fn (VehicleTypeEnum $type): array => [$type->value => $type->label()]
                    )->all()),

                SelectFilter::make('service_type')
                    ->label('Tipo de Servicio')
                    ->options(collect(ServiceTypeEnum::cases())->mapWithKeys(
                        fn (ServiceTypeEnum $service): array => [$service->value => $service->label()]
                    )->all()),

                SelectFilter::make('fuel_type')
                    ->label('Tipo de Combustible')
                    ->options(collect(FuelTypeEnum::cases())->mapWithKeys(
                        fn (FuelTypeEnum $fuel): array => [$fuel->value => $fuel->label()]
                    )->all()),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    Action::make('adjustMileage')
                        ->label('Ajustar Odómetro')
                        ->icon('heroicon-m-variable')
                        ->color('warning')
                        ->modalHeading(fn (Vehicle $record): string => "Ajustar Odómetro — Placa {$record->plate_number}")
                        ->modalDescription('Registra una calibración o ajuste excepcional del odómetro con su debida justificación de auditoría.')
                        ->modalSubmitActionLabel('Guardar Ajuste')
                        ->modalIcon('heroicon-o-variable')
                        ->schema([
                            TextInput::make('new_mileage')
                                ->label('Nuevo Kilometraje')
                                ->prefixIcon('heroicon-m-variable')
                                ->numeric()
                                ->suffix('km')
                                ->required()
                                ->minValue(0)
                                ->default(fn (Vehicle $record): int => $record->current_mileage)
                                ->helperText(fn (Vehicle $record): string => 'Kilometraje actual: '.number_format($record->current_mileage, 0, ',', '.').' km. Se permite corregir hacia arriba o hacia abajo con la debida justificación.'),

                            Textarea::make('reason')
                                ->label('Motivo del Ajuste / Justificación')
                                ->placeholder('Ej: Corrección por error de tipeo, cambio de tablero o calibración técnica.')
                                ->required()
                                ->minLength(10)
                                ->maxLength(500)
                                ->helperText('Esta justificación quedará registrada permanentemente en la bitácora del vehículo.'),
                        ])
                        ->action(function (Vehicle $record, array $data): void {
                            $action = app(AdjustVehicleMileageAction::class);

                            try {
                                $action($record, (int) $data['new_mileage'], (string) $data['reason']);

                                Notification::make()
                                    ->title('Odómetro Actualizado')
                                    ->body("El odómetro del vehículo {$record->plate_number} fue ajustado a ".number_format((int) $data['new_mileage'], 0, ',', '.').' km.')
                                    ->success()
                                    ->send();
                            } catch (InvalidMileageException $e) {
                                Notification::make()
                                    ->title('Error al Ajustar Odómetro')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                    DeleteAction::make(),
                    RestoreAction::make(),
                    ForceDeleteAction::make(),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->tooltip('Opciones del Vehículo'),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
                RestoreBulkAction::make(),
                ForceDeleteBulkAction::make(),
            ])
            ->emptyStateHeading('No hay vehículos registrados')
            ->emptyStateDescription('Registra el primer vehículo de la flota para comenzar.')
            ->emptyStateIcon('heroicon-o-truck')
            ->defaultPaginationPageOption(25);
    }

    public static function getRelations(): array
    {
        return [
            FuelLogsRelationManager::class,
            TripsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVehicles::route('/'),
            'create' => CreateVehicle::route('/create'),
            'view' => ViewVehicle::route('/{record}'),
            'edit' => EditVehicle::route('/{record}/edit'),
        ];
    }
}
