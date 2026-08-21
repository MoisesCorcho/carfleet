<?php

declare(strict_types=1);

namespace App\Filament\Resources\FuelLogs;

use App\Filament\Resources\FuelLogs\Pages\CreateFuelLog;
use App\Filament\Resources\FuelLogs\Pages\EditFuelLog;
use App\Filament\Resources\FuelLogs\Pages\ListFuelLogs;
use App\Filament\Resources\FuelLogs\Pages\ViewFuelLog;
use App\Models\Driver;
use App\Models\FuelLog;
use App\Models\Trip;
use App\Models\Vehicle;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Override;
use UnitEnum;

class FuelLogResource extends Resource
{
    protected static ?string $model = FuelLog::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-fire';

    protected static string|UnitEnum|null $navigationGroup = 'Gestión de Flota';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'voucher_number';

    public static function getModelLabel(): string
    {
        return 'Tanqueo de Combustible';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Tanqueos y Vouchers';
    }

    public static function getNavigationLabel(): string
    {
        return 'Combustible y Vouchers';
    }

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->schema([
                        Section::make('Información del Vehículo y Servicio')
                            ->description('Asignación del tanqueo al vehículo, viaje y conductor correspondiente.')
                            ->schema([
                                Select::make('vehicle_id')
                                    ->label('Vehículo')
                                    ->prefixIcon('heroicon-m-truck')
                                    ->options(fn (): array => Vehicle::query()
                                        ->orderBy('plate_number')
                                        ->get()
                                        ->mapWithKeys(fn (Vehicle $v): array => [
                                            $v->id => "{$v->plate_number} — {$v->brand} {$v->model}",
                                        ])
                                        ->toArray())
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->helperText('Vehículo de la flota que recibió el abastecimiento.'),

                                Select::make('trip_id')
                                    ->label('Viaje Asociado (Opcional)')
                                    ->prefixIcon('heroicon-m-map-pin')
                                    ->options(fn (): array => Trip::query()
                                        ->with('vehicle')
                                        ->latest('scheduled_departure_at')
                                        ->limit(50)
                                        ->get()
                                        ->mapWithKeys(fn (Trip $t): array => [
                                            $t->id => "{$t->code} ({$t->origin} ➔ {$t->destination}) — {$t->status->label()}",
                                        ])
                                        ->toArray())
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function (?int $state, Set $set): void {
                                        if (! $state) {
                                            return;
                                        }

                                        $trip = Trip::find($state);
                                        if ($trip) {
                                            if ($trip->vehicle_id) {
                                                $set('vehicle_id', $trip->vehicle_id);
                                            }
                                            if ($trip->driver_id) {
                                                $set('driver_id', $trip->driver_id);
                                            }
                                        }
                                    })
                                    ->helperText('Si el tanqueo ocurrió durante un viaje específico, selecciónalo para vincular la evidencia.'),

                                Select::make('driver_id')
                                    ->label('Conductor Responsable (Opcional)')
                                    ->prefixIcon('heroicon-m-user')
                                    ->options(fn (): array => Driver::query()
                                        ->orderBy('full_name')
                                        ->pluck('full_name', 'id')
                                        ->toArray())
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Conductor que realizó el abastecimiento.'),

                                DateTimePicker::make('refuel_date')
                                    ->label('Fecha y Hora del Tanqueo')
                                    ->prefixIcon('heroicon-m-calendar')
                                    ->required()
                                    ->maxDate(now())
                                    ->default(now())
                                    ->native(false)
                                    ->helperText('Momento exacto en que se efectuó la recarga de combustible.'),

                                TextInput::make('mileage_at_refuel')
                                    ->label('Kilometraje al Tanquear')
                                    ->prefixIcon('heroicon-m-calculator')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->suffix('km')
                                    ->helperText('Lectura del odómetro reportada al momento del abastecimiento.'),
                            ])
                            ->columns([
                                'sm' => 1,
                                'md' => 2,
                            ])
                            ->columnSpanFull(),

                        Section::make('Detalle del Abastecimiento y Comprobante')
                            ->description('Volumen de combustible, costo monetario y soporte digital de la transacción.')
                            ->schema([
                                TextInput::make('gallons')
                                    ->label('Cantidad de Combustible')
                                    ->prefixIcon('heroicon-m-fire')
                                    ->numeric()
                                    ->step(0.01)
                                    ->minValue(0.01)
                                    ->required()
                                    ->suffix('gal')
                                    ->placeholder('Ej: 12.50')
                                    ->helperText('Cantidad exacta en galones (con hasta 2 decimales).'),

                                TextInput::make('total_cost')
                                    ->label('Costo Total')
                                    ->prefixIcon('heroicon-m-banknotes')
                                    ->numeric()
                                    ->minValue(1)
                                    ->required()
                                    ->prefix('$')
                                    ->placeholder('Ej: 150000')
                                    ->helperText('Valor total pagado en pesos colombianos.'),

                                TextInput::make('voucher_number')
                                    ->label('Número de Voucher / Factura')
                                    ->prefixIcon('heroicon-m-document-text')
                                    ->maxLength(64)
                                    ->placeholder('Ej: V-0098472')
                                    ->helperText('Identificador impreso en el comprobante de la estación de servicio.'),

                                ToggleButtons::make('photo_source')
                                    ->label('Origen de la Foto del Voucher')
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

                                FileUpload::make('voucher_photo_path')
                                    ->label('Fotografía del Voucher de Combustible')
                                    ->key(fn (Get $get): string => 'voucher_photo_'.($get('photo_source') ?? 'camera'))
                                    ->image()
                                    ->extraInputAttributes(fn (Get $get): array => ($get('photo_source') ?? 'camera') === 'camera' ? ['capture' => 'environment'] : [])
                                    ->directory('evidences/vouchers')
                                    ->disk('public')
                                    ->imageEditor()
                                    ->maxSize(5120)
                                    ->helperText('Fotografía nítida del recibo físico o voucher convenio.'),

                                Textarea::make('notes')
                                    ->label('Observaciones / Estación de Servicio')
                                    ->placeholder('Ej: Estación Terpel Calle 26, pago con convenio institucional...')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ])
                            ->columns([
                                'sm' => 1,
                                'md' => 2,
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
                ImageColumn::make('voucher_photo_path')
                    ->label('Voucher')
                    ->disk('public')
                    ->square()
                    ->width(50)
                    ->height(50)
                    ->url(fn (FuelLog $record): ?string => $record->voucher_photo_path ? Storage::disk('public')->url($record->voucher_photo_path) : null)
                    ->openUrlInNewTab()
                    ->placeholder('Sin foto'),

                TextColumn::make('vehicle.plate_number')
                    ->label('Placa')
                    ->badge()
                    ->color('info')
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->sortable()
                    ->description(fn (FuelLog $record): string => $record->vehicle ? "{$record->vehicle->brand} {$record->vehicle->model}" : ''),

                TextColumn::make('trip.code')
                    ->label('Viaje')
                    ->badge()
                    ->color('gray')
                    ->placeholder('Sin Viaje (Patio)')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('driver.full_name')
                    ->label('Conductor')
                    ->placeholder('No asignado')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('refuel_date')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('mileage_at_refuel')
                    ->label('Odómetro')
                    ->formatStateUsing(fn (int $state): string => number_format($state, 0, ',', '.').' km')
                    ->sortable(),

                TextColumn::make('gallons')
                    ->label('Galones')
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2, ',', '.').' gal')
                    ->sortable(),

                TextColumn::make('total_cost')
                    ->label('Costo Total')
                    ->weight(FontWeight::SemiBold)
                    ->formatStateUsing(fn (int $state): string => '$'.number_format($state, 0, ',', '.'))
                    ->sortable(),

                TextColumn::make('voucher_number')
                    ->label('Nº Voucher')
                    ->placeholder('N/A')
                    ->searchable(),
            ])
            ->defaultSort('refuel_date', 'desc')
            ->filters([
                SelectFilter::make('vehicle_id')
                    ->label('Vehículo')
                    ->relationship('vehicle', 'plate_number')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('driver_id')
                    ->label('Conductor')
                    ->relationship('driver', 'full_name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()
                        ->visible(fn (FuelLog $record): bool => ! $record->isImmutable()),
                    DeleteAction::make()
                        ->visible(fn (FuelLog $record): bool => ! $record->isImmutable()),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->tooltip('Opciones de Tanqueo'),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->emptyStateHeading('No hay registros de combustible')
            ->emptyStateDescription('Registra el primer abastecimiento de combustible para iniciar el control de consumo.')
            ->emptyStateIcon('heroicon-o-fire')
            ->defaultPaginationPageOption(25);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFuelLogs::route('/'),
            'create' => CreateFuelLog::route('/create'),
            'view' => ViewFuelLog::route('/{record}'),
            'edit' => EditFuelLog::route('/{record}/edit'),
        ];
    }
}
