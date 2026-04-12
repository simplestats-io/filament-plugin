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

    protected function truncateLabels(array $labels, int $maxLength = 25): array
    {
        return array_map(
            fn (string $label) => mb_strlen($label) > $maxLength
                ? mb_substr($label, 0, $maxLength).'...'
                : $label,
            $labels,
        );
    }
}
