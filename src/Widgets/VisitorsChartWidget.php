<?php

namespace SimpleStatsIo\FilamentPlugin\Widgets;

use Filament\Widgets\ChartWidget;
use SimpleStatsIo\FilamentPlugin\Concerns\InteractsWithSimplestatsApi;

class VisitorsChartWidget extends ChartWidget
{
    use InteractsWithSimplestatsApi;

    protected ?string $heading = 'Visitors & Registrations';

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
        $visitors = array_column($data, 'pd_visitor');
        $registrations = array_column($data, 'pd_reg');

        $datasets = [
            [
                'label' => 'Visitors',
                'data' => $visitors,
                'borderColor' => '#6366f1',
                'backgroundColor' => 'rgba(99, 102, 241, 0.1)',
                'fill' => true,
                'tension' => 0.3,
            ],
            [
                'label' => 'Registrations',
                'data' => $registrations,
                'borderColor' => '#10b981',
                'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                'fill' => true,
                'tension' => 0.3,
            ],
        ];

        $previousData = array_reverse($response['data_previous'] ?? []);
        if (! empty($previousData)) {
            $datasets[] = [
                'label' => 'Visitors (previous)',
                'data' => array_column($previousData, 'pd_visitor'),
                'borderColor' => '#6366f1',
                'borderDash' => [5, 5],
                'backgroundColor' => 'transparent',
                'tension' => 0.3,
            ];
            $datasets[] = [
                'label' => 'Registrations (previous)',
                'data' => array_column($previousData, 'pd_reg'),
                'borderColor' => '#10b981',
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
