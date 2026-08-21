<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\Vehicles\VehicleStatusEnum;
use App\Models\Vehicle;
use Filament\Widgets\ChartWidget;
use Override;

class FleetStatusDoughnutWidget extends ChartWidget
{
    protected ?string $heading = 'Disponibilidad de Flota';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    protected ?string $maxHeight = '280px';

    protected ?string $pollingInterval = '30s';

    #[Override]
    protected function getType(): string
    {
        return 'doughnut';
    }

    #[Override]
    protected function getData(): array
    {
        $counts = Vehicle::query()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $disponible = $counts[VehicleStatusEnum::DISPONIBLE->value] ?? 0;
        $asignado = $counts[VehicleStatusEnum::ASIGNADO->value] ?? 0;
        $enViaje = $counts[VehicleStatusEnum::EN_VIAJE->value] ?? 0;
        $mantenimiento = $counts[VehicleStatusEnum::MANTENIMIENTO->value] ?? 0;
        $fueraDeServicio = $counts[VehicleStatusEnum::FUERA_DE_SERVICIO->value] ?? 0;

        return [
            'datasets' => [
                [
                    'label' => 'Vehículos',
                    'data' => [$disponible, $asignado, $enViaje, $mantenimiento, $fueraDeServicio],
                    'backgroundColor' => [
                        '#10b981', // Verde - Disponible
                        '#0ea5e9', // Azul - Asignado
                        '#f59e0b', // Ámbar - En Viaje
                        '#ef4444', // Rojo - Mantenimiento
                        '#6b7280', // Gris - Fuera de servicio
                    ],
                ],
            ],
            'labels' => [
                'Disponible',
                'Asignado',
                'En Viaje',
                'En Mantenimiento',
                'Fuera de Servicio',
            ],
        ];
    }
}
