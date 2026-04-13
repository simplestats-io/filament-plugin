<?php

namespace SimpleStatsIo\FilamentPlugin\Concerns;

use Filament\Widgets\Concerns\InteractsWithPageFilters;
use SimpleStatsIo\FilamentPlugin\SimplestatsApiClient;

trait InteractsWithSimplestatsApi
{
    use InteractsWithPageFilters;

    protected function getApiClient(): SimplestatsApiClient
    {
        return app(SimplestatsApiClient::class);
    }

    protected function getApiFilters(): array
    {
        $filters = $this->pageFilters ?? [];

        $comparison = $filters['comparison'] ?? '0';

        return array_filter([
            'preset' => $filters['preset'] ?? 'last_7_days',
            'comparison' => $comparison !== '0' ? $comparison : null,
        ]);
    }

}
