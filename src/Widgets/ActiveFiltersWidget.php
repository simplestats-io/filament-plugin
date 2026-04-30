<?php

namespace SimpleStatsIo\FilamentPlugin\Widgets;

use Filament\Widgets\Widget;
use SimpleStatsIo\FilamentPlugin\Concerns\InteractsWithSimplestatsApi;
use SimpleStatsIo\FilamentPlugin\SimplestatsApiClient;
use SimpleStatsIo\FilamentPlugin\Support\DrilldownFilters;

class ActiveFiltersWidget extends Widget
{
    use InteractsWithSimplestatsApi;

    protected string $view = 'simplestats-filament::widgets.active-filters-widget';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<int, string>
     */
    public function getChips(): array
    {
        $filters = $this->pageFilters ?? [];
        $active = DrilldownFilters::active($filters);

        if (empty($active)) {
            return [];
        }

        $apiClient = $this->getApiClient();
        $apiFilters = DrilldownFilters::buildApiFilters($filters);
        $chips = [];

        foreach ($active as $statsType => $typeId) {
            $label = DrilldownFilters::LABELS[$statsType] ?? $statsType;
            $name = $this->lookupName($apiClient, $statsType, $typeId, $apiFilters);
            $clearUrl = DrilldownFilters::toggleUrl($filters, $statsType, $typeId);

            $chips[] = sprintf(
                '<span style="display:inline-flex;align-items:center;gap:0.375rem;padding:0.25rem 0.625rem;border-radius:9999px;background:rgba(99,102,241,0.12);color:rgb(67,56,202);font-size:0.8rem;font-weight:500;line-height:1.25;">'
                .'%s: <strong>%s</strong>'
                .'<a href="%s" title="Clear filter" style="display:inline-flex;align-items:center;justify-content:center;width:1rem;height:1rem;border-radius:9999px;background:rgba(99,102,241,0.2);color:rgb(67,56,202);text-decoration:none;font-weight:600;line-height:1;">&times;</a>'
                .'</span>',
                e($label),
                e($name),
                e($clearUrl),
            );
        }

        return $chips;
    }

    protected function lookupName(
        SimplestatsApiClient $apiClient,
        string $statsType,
        mixed $typeId,
        array $apiFilters,
    ): string {
        // Hits the client's in-memory memo when this stats type is also rendered
        // by a default widget (track_referer, track_source, location_country,
        // page_entry). For other types, makes one cached call.
        $response = $apiClient->getGroupedStats($statsType, $apiFilters);

        foreach ($response['data'] ?? [] as $row) {
            $rowId = $row['type_id'] ?? -1;

            if ((string) $rowId === (string) $typeId) {
                return (string) ($row['name'] ?? "#{$typeId}");
            }
        }

        return "#{$typeId}";
    }
}
