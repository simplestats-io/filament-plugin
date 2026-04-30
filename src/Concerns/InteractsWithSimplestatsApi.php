<?php

namespace SimpleStatsIo\FilamentPlugin\Concerns;

use Filament\Widgets\Concerns\InteractsWithPageFilters;
use SimpleStatsIo\FilamentPlugin\SimplestatsApiClient;
use SimpleStatsIo\FilamentPlugin\Support\DrilldownFilters;

trait InteractsWithSimplestatsApi
{
    use InteractsWithPageFilters;

    protected function getApiClient(): SimplestatsApiClient
    {
        return app(SimplestatsApiClient::class);
    }

    protected function getApiFilters(): array
    {
        return DrilldownFilters::buildApiFilters($this->pageFilters ?? []);
    }

    /**
     * Returns the active drilldown value (type_id) for the given stats type, or null.
     */
    protected function getActiveDrillDown(string $statsType): mixed
    {
        $value = ($this->pageFilters ?? [])[$statsType] ?? null;

        return ($value === '' || $value === null) ? null : $value;
    }

    protected function buildDrilldownToggleUrl(string $statsType, mixed $typeId): string
    {
        return DrilldownFilters::toggleUrl($this->pageFilters ?? [], $statsType, $typeId);
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
