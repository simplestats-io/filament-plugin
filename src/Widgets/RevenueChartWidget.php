<?php

namespace SimpleStatsIo\FilamentPlugin\Widgets;

use Filament\Widgets\ChartWidget;
use SimpleStatsIo\FilamentPlugin\Concerns\InteractsWithSimplestatsApi;

class RevenueChartWidget extends ChartWidget
{
    use InteractsWithSimplestatsApi;

    protected ?string $heading = 'Revenue';

    protected int|string|array $columnSpan = 1;

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $response = $this->getApiStats();
        $data = array_reverse($response['data'] ?? []);

        $labels = array_column($data, 'date');

        $datasets = [
            [
                'label' => 'Gross',
                'data' => array_map(fn ($v) => ($v ?? 0) / 100, array_column($data, 'pd_gross')),
                'borderColor' => '#f59e0b',
                'backgroundColor' => 'rgba(245, 158, 11, 0.18)',
                'pointBackgroundColor' => '#f59e0b',
                'fill' => true,
                'tension' => 0.35,
            ],
            [
                'label' => 'Net',
                'data' => array_map(fn ($v) => ($v ?? 0) / 100, array_column($data, 'pd_net')),
                'borderColor' => '#ec4899',
                'backgroundColor' => 'rgba(236, 72, 153, 0.18)',
                'pointBackgroundColor' => '#ec4899',
                'fill' => true,
                'tension' => 0.35,
            ],
        ];

        $previousData = array_reverse($response['data_previous'] ?? []);
        if (! empty($previousData)) {
            $datasets[] = [
                'label' => 'Gross (previous)',
                'data' => array_map(fn ($v) => ($v ?? 0) / 100, array_column($previousData, 'pd_gross')),
                'borderColor' => 'rgba(245, 158, 11, 0.55)',
                'borderDash' => [5, 5],
                'backgroundColor' => 'transparent',
                'pointBackgroundColor' => 'rgba(245, 158, 11, 0.55)',
                'tension' => 0.35,
            ];
            $datasets[] = [
                'label' => 'Net (previous)',
                'data' => array_map(fn ($v) => ($v ?? 0) / 100, array_column($previousData, 'pd_net')),
                'borderColor' => 'rgba(236, 72, 153, 0.55)',
                'borderDash' => [5, 5],
                'backgroundColor' => 'transparent',
                'pointBackgroundColor' => 'rgba(236, 72, 153, 0.55)',
                'tension' => 0.35,
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }
}
