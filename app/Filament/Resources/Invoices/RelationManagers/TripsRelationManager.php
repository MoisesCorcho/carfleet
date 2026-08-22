<?php

declare(strict_types=1);

namespace App\Filament\Resources\Invoices\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

class TripsRelationManager extends RelationManager
{
    protected static string $relationship = 'trips';

    protected static ?string $title = 'Servicios y Viajes Consolidados';

    public static function getModelLabel(): string
    {
        return 'Viaje Consolidado';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Viajes Consolidados';
    }

    #[Override]
    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    #[Override]
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Código')
                    ->badge()
                    ->color('gray')
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('origin')
                    ->label('Origen')
                    ->limit(20)
                    ->searchable(),

                TextColumn::make('destination')
                    ->label('Destino')
                    ->limit(20)
                    ->searchable(),

                TextColumn::make('vehicle.plate_number')
                    ->label('Vehículo')
                    ->badge()
                    ->color('info')
                    ->placeholder('N/A'),

                TextColumn::make('distance_traveled')
                    ->label('Distancia')
                    ->formatStateUsing(fn (?int $state): string => number_format((int) $state, 0, ',', '.').' km')
                    ->sortable(),

                TextColumn::make('pivot.subtotal_amount')
                    ->label('Subtotal Liquidado')
                    ->weight(FontWeight::Bold)
                    ->color('success')
                    ->formatStateUsing(fn (int $state): string => '$ '.number_format($state, 0, ',', '.').' COP')
                    ->sortable(),
            ])
            ->defaultSort('scheduled_departure_at', 'asc');
    }
}
