<?php

declare(strict_types=1);

namespace App\Filament\Resources\Vehicles\Widgets;

use App\Models\Vehicle;
use App\Services\Reports\FleetPerformanceCalculatorService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VehiclePerformanceWidget extends BaseWidget
{
    public ?Vehicle $record = null;

    protected function getStats(): array
    {
        if (! $this->record instanceof Vehicle) {
            return [];
        }

        $service = app(FleetPerformanceCalculatorService::class);
        $metrics = $service->calculateVehiclePerformance($this->record);

        return [
            Stat::make('Kilómetros en Viajes', number_format($metrics->totalKm, 0, ',', '.').' km')
                ->description("{$metrics->completedTripsCount} viajes completados/cerrados")
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('info'),

            Stat::make('Rendimiento de Combustible', number_format($metrics->kmPerGallon, 2, ',', '.').' km/gal')
                ->description($metrics->totalGallons > 0 ? number_format($metrics->totalGallons, 2, ',', '.').' galones consumidos' : 'Sin tanqueos registrados')
                ->descriptionIcon('heroicon-m-bolt')
                ->color($metrics->kmPerGallon > 0 ? 'success' : 'gray'),

            Stat::make('Combustible Tanqueado', number_format($metrics->totalGallons, 2, ',', '.').' gal')
                ->description('Costo: $'.number_format($metrics->totalFuelCost, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-fire')
                ->color('warning'),

            Stat::make('Costo por Kilómetro', '$'.number_format($metrics->costPerKm, 2, ',', '.').' / km')
                ->description('Gasto de combustible por km')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary'),
        ];
    }
}
