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
        $response = $this->getApiStats();
        $data = array_reverse($response['data'] ?? []);

        $labels = array_column($data, 'date');
        $visitors = array_column($data, 'pd_visitor');
        $registrations = array_column($data, 'pd_reg');

        $datasets = [
            [
                'label' => 'Visitors',
                'data' => $visitors,
                'borderColor' => '#3b82f6',
                'backgroundColor' => 'rgba(59, 130, 246, 0.18)',
                'pointBackgroundColor' => '#3b82f6',
                'fill' => true,
                'tension' => 0.35,
            ],
            [
                'label' => 'Registrations',
                'data' => $registrations,
                'borderColor' => '#10b981',
                'backgroundColor' => 'rgba(16, 185, 129, 0.18)',
                'pointBackgroundColor' => '#10b981',
                'fill' => true,
                'tension' => 0.35,
            ],
        ];

        $previousData = array_reverse($response['data_previous'] ?? []);
        if (! empty($previousData)) {
            $datasets[] = [
                'label' => 'Visitors (previous)',
                'data' => array_column($previousData, 'pd_visitor'),
                'borderColor' => 'rgba(59, 130, 246, 0.55)',
                'borderDash' => [5, 5],
                'backgroundColor' => 'transparent',
                'pointBackgroundColor' => 'rgba(59, 130, 246, 0.55)',
                'tension' => 0.35,
            ];
            $datasets[] = [
                'label' => 'Registrations (previous)',
                'data' => array_column($previousData, 'pd_reg'),
                'borderColor' => 'rgba(16, 185, 129, 0.55)',
                'borderDash' => [5, 5],
                'backgroundColor' => 'transparent',
                'pointBackgroundColor' => 'rgba(16, 185, 129, 0.55)',
                'tension' => 0.35,
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }
}
