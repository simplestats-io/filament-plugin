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

        return array_filter([
            'preset' => $filters['preset'] ?? 'last_7_days',
        ]);
    }
}
