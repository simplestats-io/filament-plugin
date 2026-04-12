<?php

namespace SimpleStatsIo\FilamentPlugin\Widgets;

use Filament\Widgets\ChartWidget;
use SimpleStatsIo\FilamentPlugin\Concerns\InteractsWithSimplestatsApi;

class EntryPagesWidget extends ChartWidget
{
    use InteractsWithSimplestatsApi;

    protected ?string $heading = 'Entry Pages';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '300px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $response = $this->getApiClient()->getGroupedStats('page_entry', $this->getApiFilters());
        $data = $response['data'] ?? [];

        return [
            'labels' => $this->truncateLabels(array_column($data, 'name')),
            'datasets' => [
                [
                    'label' => 'Visitors',
                    'data' => array_column($data, 'visitors'),
                    'backgroundColor' => '#0ea5e9',
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
