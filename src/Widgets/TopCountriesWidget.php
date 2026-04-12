<?php

namespace SimpleStatsIo\FilamentPlugin\Widgets;

use Filament\Widgets\ChartWidget;
use SimpleStatsIo\FilamentPlugin\Concerns\InteractsWithSimplestatsApi;

class TopCountriesWidget extends ChartWidget
{
    use InteractsWithSimplestatsApi;

    protected ?string $heading = 'Top Countries';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '300px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $response = $this->getApiClient()->getGroupedStats('location_country', $this->getApiFilters());
        $data = $response['data'] ?? [];

        return [
            'labels' => $this->truncateLabels(array_column($data, 'name')),
            'datasets' => [
                [
                    'label' => 'Visitors',
                    'data' => array_column($data, 'visitors'),
                    'backgroundColor' => '#10b981',
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
