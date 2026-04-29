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

    /**
     * Fetch the full dashboard bundle (stats + all grouped widgets) in one shot.
     * The client memoizes results in-memory per request, so widgets that call this
     * (or the underlying getStats / getGroupedStats) repeatedly only hit the
     * upstream API once per filter combination.
     *
     * @return array{stats: array, grouped: array<string, array>}
     */
    protected function getApiData(): array
    {
        return $this->getApiClient()->getAll($this->getApiFilters());
    }

    protected function getApiStats(): array
    {
        return $this->getApiData()['stats'];
    }

    protected function getApiGroupedStats(string $type): array
    {
        return $this->getApiData()['grouped'][$type]
            ?? $this->getApiClient()->getGroupedStats($type, $this->getApiFilters());
    }
}
