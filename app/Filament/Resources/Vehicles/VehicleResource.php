<?php

declare(strict_types=1);

namespace App\Filament\Resources\Vehicles;

use App\Enums\Vehicles\FuelTypeEnum;
use App\Enums\Vehicles\VehicleStatusEnum;
use App\Filament\Resources\Vehicles\Pages\CreateVehicle;
use App\Filament\Resources\Vehicles\Pages\EditVehicle;
use App\Filament\Resources\Vehicles\Pages\ListVehicles;
use App\Filament\Resources\Vehicles\Pages\ViewVehicle;
use App\Models\Vehicle;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
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
                        Section::make('Información del Vehículo')
                            ->description('Datos básicos de identificación del recurso de transporte.')
                            ->schema([
                                TextInput::make('plate_number')
                                    ->label('Placa / Identificador')
                                    ->placeholder('Ej: ABC-123')
                                    ->required()
                                    ->maxLength(16)
                                    ->unique(ignoreRecord: true)
                                    ->extraInputAttributes(['style' => 'text-transform: uppercase;'])
                                    ->dehydrateStateUsing(fn (?string $state): string => strtoupper(trim((string) $state)))
                                    ->helperText('Identificador alfanumérico único para la flota.'),

                                TextInput::make('brand')
                                    ->label('Marca')
                                    ->placeholder('Ej: Toyota, Chevrolet')
                                    ->required()
                                    ->maxLength(64),

                                TextInput::make('model')
                                    ->label('Modelo')
                                    ->placeholder('Ej: Hilux, D-Max')
                                    ->required()
                                    ->maxLength(64),

                                TextInput::make('year')
                                    ->label('Año de Fabricación')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1950)
                                    ->maxValue((int) date('Y') + 1)
                                    ->placeholder((string) date('Y')),
                            ])
                            ->columns([
                                'sm' => 1,
                                'md' => 2,
                            ])
                            ->columnSpanFull(),

                        Section::make('Operación y Combustible')
                            ->description('Parámetros de kilometraje inicial y estado operacional.')
                            ->schema([
                                TextInput::make('current_mileage')
                                    ->label('Kilometraje Actual')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->default(0)
                                    ->suffix('km')
                                    ->helperText('Lectura inicial del odómetro en kilómetros.'),

                                Select::make('status')
                                    ->label('Estado Inicial')
                                    ->options(collect(VehicleStatusEnum::cases())->mapWithKeys(
                                        fn (VehicleStatusEnum $status): array => [$status->value => $status->label()]
                                    )->all())
                                    ->default(VehicleStatusEnum::DISPONIBLE->value)
                                    ->required(),

                                Select::make('fuel_type')
                                    ->label('Tipo de Combustible')
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
                                    ->label('Notas Adicionales')
                                    ->rows(3)
                                    ->maxLength(1000)
                                    ->placeholder('Especificaciones técnicas, detalles de carrocería o notas generales.'),
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
                    ->color('gray')
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

                SelectFilter::make('fuel_type')
                    ->label('Tipo de Combustible')
                    ->options(collect(FuelTypeEnum::cases())->mapWithKeys(
                        fn (FuelTypeEnum $fuel): array => [$fuel->value => $fuel->label()]
                    )->all()),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->emptyStateHeading('No hay vehículos registrados')
            ->emptyStateDescription('Registra el primer vehículo de la flota para comenzar.')
            ->emptyStateIcon('heroicon-o-truck')
            ->defaultPaginationPageOption(25);
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
