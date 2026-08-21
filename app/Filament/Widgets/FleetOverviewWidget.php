<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\Vehicles\VehicleStatusEnum;
use App\Models\Vehicle;
use App\Services\Reports\FleetPerformanceCalculatorService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FleetOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $service = app(FleetPerformanceCalculatorService::class);
        $metrics = $service->calculateFleetPerformance();

        $totalVehicles = Vehicle::count();
        $activeVehicles = Vehicle::whereIn('status', [
            VehicleStatusEnum::DISPONIBLE,
            VehicleStatusEnum::ASIGNADO,
            VehicleStatusEnum::EN_VIAJE,
        ])->count();

        return [
            Stat::make('Kilometraje Recorrido', number_format($metrics->totalKm, 0, ',', '.').' km')
                ->description("{$metrics->completedTripsCount} viajes completados")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),

            Stat::make('Rendimiento Promedio', number_format($metrics->kmPerGallon, 2, ',', '.').' km/gal')
                ->description($metrics->totalGallons > 0 ? number_format($metrics->totalGallons, 2, ',', '.').' gal consumidos' : 'Sin consumos registrados')
                ->descriptionIcon('heroicon-m-bolt')
                ->color($metrics->kmPerGallon > 0 ? 'success' : 'gray'),

            Stat::make('Combustible Total', number_format($metrics->totalGallons, 2, ',', '.').' gal')
                ->description('Costo: $'.number_format($metrics->totalFuelCost, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-fire')
                ->color('warning'),

            Stat::make('Flota Operativa', "{$activeVehicles} / {$totalVehicles}")
                ->description('Vehículos en servicio activo')
                ->descriptionIcon('heroicon-m-truck')
                ->color('primary'),
        ];
    }
}
