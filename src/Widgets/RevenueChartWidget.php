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
        $response = $this->getApiClient()->getStats($this->getApiFilters());
        $data = array_reverse($response['data'] ?? []);

        $labels = array_column($data, 'date');

        $datasets = [
            [
                'label' => 'Gross',
                'data' => array_map(fn ($v) => ($v ?? 0) / 100, array_column($data, 'pd_gross')),
                'borderColor' => '#f59e0b',
                'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                'fill' => true,
                'tension' => 0.3,
            ],
            [
                'label' => 'Net',
                'data' => array_map(fn ($v) => ($v ?? 0) / 100, array_column($data, 'pd_net')),
                'borderColor' => '#ef4444',
                'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                'fill' => true,
                'tension' => 0.3,
            ],
        ];

        $previousData = array_reverse($response['data_previous'] ?? []);
        if (! empty($previousData)) {
            $datasets[] = [
                'label' => 'Gross (previous)',
                'data' => array_map(fn ($v) => ($v ?? 0) / 100, array_column($previousData, 'pd_gross')),
                'borderColor' => '#f59e0b',
                'borderDash' => [5, 5],
                'backgroundColor' => 'transparent',
                'tension' => 0.3,
            ];
            $datasets[] = [
                'label' => 'Net (previous)',
                'data' => array_map(fn ($v) => ($v ?? 0) / 100, array_column($previousData, 'pd_net')),
                'borderColor' => '#ef4444',
                'borderDash' => [5, 5],
                'backgroundColor' => 'transparent',
                'tension' => 0.3,
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }
}
