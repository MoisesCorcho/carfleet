<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\Trips\TripResource;
use App\Models\Driver;
use Filament\Actions\Action;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Override;

class ActiveDriversControlWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    #[Override]
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Driver::query()
                    ->with(['activeTrip.vehicle', 'activeTrip.requester'])
            )
            ->poll('30s')
            ->heading('Torre de Control — Conductores y Viajes Activos')
            ->description('Monitoreo en tiempo real del estado de los conductores, vehículos asignados y tiempos en ruta.')
            ->columns([
                TextColumn::make('full_name')
                    ->label('Conductor')
                    ->weight(FontWeight::Bold)
                    ->description(fn (Driver $record): string => $record->phone ? "Tel: {$record->phone}" : "Lic: {$record->license_number} ({$record->license_category->value})")
                    ->searchable()
                    ->sortable(),

                TextColumn::make('operational_status')
                    ->label('Estado')
                    ->badge()
                    ->state(function (Driver $record): string {
                        if ($record->isLicenseExpired()) {
                            return 'Licencia Vencida';
                        }

                        if ($record->activeTrip?->isInProgress()) {
                            return 'En Viaje';
                        }

                        if ($record->activeTrip?->isAssigned()) {
                            return 'Asignado';
                        }

                        if ($record->isActive()) {
                            return 'Disponible';
                        }

                        return 'Inactivo';
                    })
                    ->color(function (Driver $record): string {
                        if ($record->isLicenseExpired()) {
                            return 'danger';
                        }

                        if ($record->activeTrip?->isInProgress()) {
                            return 'warning';
                        }

                        if ($record->activeTrip?->isAssigned()) {
                            return 'info';
                        }

                        if ($record->isActive()) {
                            return 'success';
                        }

                        return 'gray';
                    })
                    ->sortable(),

                TextColumn::make('activeTrip.vehicle.plate_number')
                    ->label('Vehículo')
                    ->badge()
                    ->color(fn (?string $state): string => $state ? 'primary' : 'gray')
                    ->placeholder('Sin vehículo')
                    ->searchable(),

                TextColumn::make('activeTrip.destination')
                    ->label('Misión / Destino')
                    ->limit(30)
                    ->description(fn (Driver $record): string => $record->activeTrip?->code ?? '')
                    ->placeholder('Disponible en base'),

                TextColumn::make('activeTrip.elapsed_time_for_humans')
                    ->label('Tiempo en Ruta')
                    ->badge()
                    ->state(fn (Driver $record): string => $record->activeTrip?->isInProgress() ? ($record->activeTrip->elapsed_time_for_humans ?? '0m') : '—')
                    ->color(function (Driver $record): string {
                        if (! $record->activeTrip?->isInProgress()) {
                            return 'gray';
                        }

                        $minutes = $record->activeTrip->elapsed_minutes ?? 0;

                        if ($minutes > 480) {
                            return 'danger';
                        }

                        if ($minutes >= 240) {
                            return 'warning';
                        }

                        return 'success';
                    })
                    ->tooltip(function (Driver $record): ?string {
                        if (! $record->activeTrip?->isInProgress()) {
                            return null;
                        }

                        $minutes = $record->activeTrip->elapsed_minutes ?? 0;

                        if ($minutes > 480) {
                            return '⚠️ Alerta: Conductor supera las 8 horas de conducción continua';
                        }

                        if ($minutes >= 240) {
                            return '⏱️ Jornada intermedia (4 a 8 horas al volante)';
                        }

                        return '⏱️ En ruta normal (< 4 horas)';
                    }),
            ])
            ->recordActions([
                Action::make('viewTrip')
                    ->label('Ver Viaje')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->color('info')
                    ->visible(fn (Driver $record): bool => $record->activeTrip !== null)
                    ->url(fn (Driver $record): ?string => $record->activeTrip ? TripResource::getUrl('view', ['record' => $record->activeTrip]) : null),
            ])
            ->defaultPaginationPageOption(10)
            ->emptyStateHeading('No hay conductores registrados')
            ->emptyStateDescription('Registra conductores para visualizar la torre de control en tiempo real.')
            ->emptyStateIcon('heroicon-o-user-group');
    }
}
