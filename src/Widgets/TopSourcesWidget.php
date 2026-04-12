<?php

namespace SimpleStatsIo\FilamentPlugin\Widgets;

use Filament\Widgets\ChartWidget;
use SimpleStatsIo\FilamentPlugin\Concerns\InteractsWithSimplestatsApi;

class TopSourcesWidget extends ChartWidget
{
    use InteractsWithSimplestatsApi;

    protected static ?string $heading = 'Top Sources';

    protected int|string|array $columnSpan = 1;

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $response = $this->getApiClient()->getGroupedStats('track_source', $this->getApiFilters());
        $data = $response['data'] ?? [];

        return [
            'labels' => array_column($data, 'name'),
            'datasets' => [
                [
                    'label' => 'Visitors',
                    'data' => array_column($data, 'visitors'),
                    'backgroundColor' => '#6366f1',
                ],
            ],
        ];
    }

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
