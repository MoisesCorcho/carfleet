<?php

declare(strict_types=1);

namespace App\Filament\Resources\Trips\RelationManagers;

use App\Models\FuelLog;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Override;

class FuelLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'fuelLogs';

    protected static ?string $title = 'Tanqueos y Vouchers de Combustible';

    public static function getModelLabel(): string
    {
        return 'Tanqueo';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Tanqueos';
    }

    #[Override]
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DateTimePicker::make('refuel_date')
                    ->label('Fecha del Tanqueo')
                    ->default(now())
                    ->required(),

                TextInput::make('mileage_at_refuel')
                    ->label('Odómetro al Tanquear')
                    ->numeric()
                    ->required()
                    ->suffix('km'),

                TextInput::make('gallons')
                    ->label('Galones')
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0.01)
                    ->required()
                    ->suffix('gal'),

                TextInput::make('total_cost')
                    ->label('Costo Total ($)')
                    ->numeric()
                    ->minValue(1)
                    ->required()
                    ->prefix('$'),

                TextInput::make('voucher_number')
                    ->label('Nº de Voucher')
                    ->maxLength(64),

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

                FileUpload::make('voucher_photo_path')
                    ->label('Foto del Voucher')
                    ->key(fn (Get $get): string => 'voucher_rel_'.($get('photo_source') ?? 'camera'))
                    ->disk('public')
                    ->directory('evidences/vouchers')
                    ->image()
                    ->extraInputAttributes(fn (Get $get): array => ($get('photo_source') ?? 'camera') === 'camera' ? ['capture' => 'environment'] : []),
            ]);
    }

    #[Override]
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('voucher_number')
            ->columns([
                ImageColumn::make('voucher_photo_path')
                    ->label('Voucher')
                    ->disk('public')
                    ->width(60)
                    ->height(60)
                    ->square()
                    ->url(fn (FuelLog $record): ?string => $record->voucher_photo_path ? Storage::disk('public')->url($record->voucher_photo_path) : null)
                    ->openUrlInNewTab()
                    ->placeholder('Sin foto'),

                TextColumn::make('refuel_date')
                    ->label('Fecha / Hora')
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

                TextColumn::make('notes')
                    ->label('Observaciones')
                    ->limit(30)
                    ->placeholder('Sin observaciones'),
            ])
            ->recordActions([
                Action::make('viewVoucher')
                    ->label('Ver Voucher')
                    ->icon('heroicon-m-eye')
                    ->color('info')
                    ->visible(fn (FuelLog $record): bool => ! empty($record->voucher_photo_path))
                    ->modalHeading(fn (FuelLog $record): string => 'Voucher de Combustible'.($record->voucher_number ? " — {$record->voucher_number}" : ''))
                    ->modalWidth(Width::FourExtraLarge)
                    ->modalContent(fn (FuelLog $record): View => view('filament.resources.fuel-logs.voucher-preview', ['record' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar'),
            ])
            ->defaultSort('refuel_date', 'asc');
    }
}
