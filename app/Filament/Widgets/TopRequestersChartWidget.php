<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\Trips\TripStatusEnum;
use App\Models\Requester;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;
use Override;

class TopRequestersChartWidget extends ChartWidget
{
    protected ?string $heading = 'Top Solicitantes por Viajes';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '280px';

    protected ?string $pollingInterval = '60s';

    public ?string $filter = 'month';

    #[Override]
    protected function getFilters(): ?array
    {
        return [
            'month' => 'Este Mes',
            'last_30_days' => 'Últimos 30 días',
            'year' => 'Este Año',
            'all' => 'Histórico Total',
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
        $activeFilter = $this->filter;

        $topRequesters = Requester::query()
            ->withCount(['trips' => function (Builder $q) use ($activeFilter): void {
                $q->whereIn('status', [TripStatusEnum::FINALIZADO, TripStatusEnum::CERRADO]);

                match ($activeFilter) {
                    'month' => $q->where(function (Builder $sub): void {
                        $sub->whereMonth('actual_departure_at', now()->month)
                            ->whereYear('actual_departure_at', now()->year);
                    }),
                    'last_30_days' => $q->whereDate('actual_departure_at', '>=', now()->subDays(30)),
                    'year' => $q->whereYear('actual_departure_at', now()->year),
                    default => null,
                };
            }])
            ->having('trips_count', '>', 0)
            ->orderByDesc('trips_count')
            ->limit(5)
            ->get();

        $labels = [];
        $data = [];

        foreach ($topRequesters as $requester) {
            $labels[] = $requester->name;
            $data[] = (int) $requester->trips_count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Viajes Completados',
                    'data' => $data,
                    'backgroundColor' => '#3b82f6',
                    'borderColor' => '#2563eb',
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    #[Override]
    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }
}
