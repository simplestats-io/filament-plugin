<?php

namespace SimpleStatsIo\FilamentPlugin\Support;

use Livewire\Livewire;

class DrilldownFilters
{
    /**
     * Drilldown filter keys forwarded to the SimpleStats API. Mirrors the SaaS
     * StatsFilterRequest and lets users stack multiple filters at once
     * (e.g. track_referer=54 AND page_entry=12 to inspect entry pages of
     * visitors who came via a specific referrer).
     */
    public const KEYS = [
        'track_referer',
        'track_source',
        'track_medium',
        'track_campaign',
        'track_term',
        'track_content',
        'location_country',
        'location_region',
        'location_city',
        'device_type',
        'device_platform',
        'device_browser',
        'page_entry',
        'custom_event_name',
    ];

    /**
     * Human-readable labels for chip rendering.
     */
    public const LABELS = [
        'track_referer' => 'Referer',
        'track_source' => 'Source',
        'track_medium' => 'Medium',
        'track_campaign' => 'Campaign',
        'track_term' => 'Term',
        'track_content' => 'Content',
        'location_country' => 'Country',
        'location_region' => 'Region',
        'location_city' => 'City',
        'device_type' => 'Device',
        'device_platform' => 'Platform',
        'device_browser' => 'Browser',
        'page_entry' => 'Entry Page',
        'custom_event_name' => 'Event',
    ];

    /**
     * Extract active drilldown values keyed by stats type.
     *
     * @return array<string, mixed>
     */
    public static function active(array $filters): array
    {
        $active = [];

        foreach (self::KEYS as $key) {
            $value = $filters[$key] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            $active[$key] = $value;
        }

        return $active;
    }

    /**
     * Build the API filter payload (preset + comparison + active drilldowns).
     */
    public static function buildApiFilters(array $filters): array
    {
        $comparison = $filters['comparison'] ?? '0';

        $apiFilters = array_filter([
            'preset' => $filters['preset'] ?? 'last_7_days',
            'comparison' => $comparison !== '0' ? $comparison : null,
        ]);

        return [...$apiFilters, ...self::active($filters)];
    }

    /**
     * Build a URL for the current dashboard that toggles a single drilldown
     * filter. Other active filters (preset, comparison and other drilldowns)
     * are preserved.
     */
    public static function toggleUrl(array $filters, string $statsType, mixed $typeId): string
    {
        // -1 represents NULL/Direct/Unknown rows on the API side (TRACK_ORGANIC).
        $typeId = $typeId ?? -1;
        $current = $filters[$statsType] ?? null;

        if ($current !== null && (string) $current === (string) $typeId) {
            unset($filters[$statsType]);
        } else {
            $filters[$statsType] = $typeId;
        }

        $filters = array_filter(
            $filters,
            fn ($value) => $value !== null && $value !== '',
        );

        // Livewire::originalUrl() returns the actual page URL even during AJAX
        // updates, where url()->current() would point at /livewire/update.
        $base = strtok(Livewire::originalUrl(), '?');

        if (empty($filters)) {
            return $base;
        }

        return $base.'?'.http_build_query(['filters' => $filters]);
    }
}
