<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\Trips\TripStatusEnum;
use App\Models\Requester;
use Filament\Widgets\ChartWidget;
use Override;

class TopRequestersChartWidget extends ChartWidget
{
    protected ?string $heading = 'Top Solicitantes por Viajes';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '280px';

    protected ?string $pollingInterval = '60s';

    #[Override]
    protected function getType(): string
    {
        return 'bar';
    }

    #[Override]
    protected function getData(): array
    {
        $topRequesters = Requester::query()
            ->withCount(['trips' => fn ($q) => $q->whereIn('status', [TripStatusEnum::FINALIZADO, TripStatusEnum::CERRADO])])
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
