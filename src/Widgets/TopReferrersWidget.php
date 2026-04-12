<?php

namespace SimpleStatsIo\FilamentPlugin\Widgets;

use Filament\Widgets\ChartWidget;
use SimpleStatsIo\FilamentPlugin\Concerns\InteractsWithSimplestatsApi;

class TopReferrersWidget extends ChartWidget
{
    use InteractsWithSimplestatsApi;

    protected ?string $heading = 'Top Referrers';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '300px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $response = $this->getApiClient()->getGroupedStats('track_referer', $this->getApiFilters());
        $data = $response['data'] ?? [];

        return [
            'labels' => $this->truncateLabels(array_column($data, 'name')),
            'datasets' => [
                [
                    'label' => 'Visitors',
                    'data' => array_column($data, 'visitors'),
                    'backgroundColor' => '#8b5cf6',
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
