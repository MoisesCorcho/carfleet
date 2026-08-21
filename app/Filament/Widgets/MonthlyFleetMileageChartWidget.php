<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\Trips\TripStatusEnum;
use App\Models\Trip;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Override;

class MonthlyFleetMileageChartWidget extends ChartWidget
{
    protected ?string $heading = 'Kilometraje Recorrido Mensual';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    protected ?string $maxHeight = '280px';

    protected ?string $pollingInterval = '60s';

    public ?string $filter = '6_months';

    #[Override]
    protected function getFilters(): ?array
    {
        return [
            '6_months' => 'Últimos 6 Meses',
            '12_months' => 'Últimos 12 Meses',
            'this_year' => 'Este Año',
        ];
    }

    #[Override]
    protected function getType(): string
    {
        return 'bar';
    }

    #[Override]
    protected function getData(): array
    {
        $months = [];
        $data = [];

        $monthsCount = match ($this->filter) {
            '12_months' => 12,
            'this_year' => (int) Carbon::now()->month,
            default => 6,
        };

        for ($i = $monthsCount - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth()->toDateTimeString();
            $endOfMonth = $date->copy()->endOfMonth()->toDateTimeString();

            $monthLabel = ucfirst((string) $date->locale('es')->isoFormat('MMM YYYY'));
            $months[] = $monthLabel;

            $totalKm = (int) Trip::query()
                ->whereIn('status', [TripStatusEnum::FINALIZADO, TripStatusEnum::CERRADO])
                ->where(function ($query) use ($startOfMonth, $endOfMonth): void {
                    $query->whereBetween('actual_departure_at', [$startOfMonth, $endOfMonth])
                        ->orWhere(fn ($q) => $q->whereNull('actual_departure_at')->whereBetween('scheduled_departure_at', [$startOfMonth, $endOfMonth]));
                })
                ->sum('distance_traveled');

            $data[] = $totalKm;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Distancia Recorrida (km)',
                    'data' => $data,
                    'backgroundColor' => '#f59e0b',
                    'borderColor' => '#d97706',
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $months,
        ];
    }
}
