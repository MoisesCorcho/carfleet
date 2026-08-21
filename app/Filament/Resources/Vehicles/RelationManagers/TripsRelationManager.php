<?php

declare(strict_types=1);

namespace App\Filament\Resources\Vehicles\RelationManagers;

use App\Enums\Trips\TripStatusEnum;
use App\Filament\Resources\Trips\TripResource;
use App\Models\Trip;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

class TripsRelationManager extends RelationManager
{
    protected static string $relationship = 'trips';

    protected static ?string $title = 'Historial de Viajes';

    public static function getModelLabel(): string
    {
        return 'Viaje';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Viajes';
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
            ->recordTitleAttribute('code')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withSum('fuelLogs', 'gallons'))
            ->columns([
                TextColumn::make('code')
                    ->label('Código')
                    ->badge()
                    ->color('gray')
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Código copiado'),

                TextColumn::make('requester.name')
                    ->label('Solicitante')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('driver.full_name')
                    ->label('Conductor')
                    ->placeholder('Sin asignar')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('origin')
                    ->label('Origen')
                    ->searchable()
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('destination')
                    ->label('Destino')
                    ->searchable()
                    ->limit(20),

                TextColumn::make('scheduled_departure_at')
                    ->label('Salida Programada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('actual_departure_at')
                    ->label('Salida Real')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('actual_arrival_at')
                    ->label('Llegada Real')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('distance_traveled')
                    ->label('Recorrido')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (?int $state): string => $state !== null ? number_format($state, 0, ',', '.').' km' : '—')
                    ->sortable(),

                TextColumn::make('fuel_logs_sum_gallons')
                    ->label('Combustible')
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn ($state): string => $state !== null && (float) $state > 0 ? number_format((float) $state, 2, ',', '.').' gal' : '0.00 gal')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (TripStatusEnum $state): string => $state->color())
                    ->formatStateUsing(fn (TripStatusEnum $state): string => $state->label())
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('driver_id')
                    ->label('Conductor')
                    ->relationship('driver', 'full_name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->label('Estado del Viaje')
                    ->options(collect(TripStatusEnum::cases())->mapWithKeys(
                        fn (TripStatusEnum $status): array => [$status->value => $status->label()]
                    )),

                Filter::make('date_range')
                    ->form([
                        DatePicker::make('from')->label('Desde'),
                        DatePicker::make('until')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $q, $date): Builder => $q->whereDate('scheduled_departure_at', '>=', $date))
                            ->when($data['until'], fn (Builder $q, $date): Builder => $q->whereDate('scheduled_departure_at', '<=', $date));
                    }),
            ])
            ->recordActions([
                Action::make('viewTrip')
                    ->label('Ver Detalle')
                    ->icon('heroicon-m-eye')
                    ->color('info')
                    ->url(fn (Trip $record): string => TripResource::getUrl('view', ['record' => $record])),
            ])
            ->defaultSort('scheduled_departure_at', 'desc')
            ->emptyStateHeading('No hay viajes registrados')
            ->emptyStateDescription('Este vehículo aún no cuenta con traslados programados o completados.')
            ->emptyStateIcon('heroicon-o-map-pin');
    }
}
