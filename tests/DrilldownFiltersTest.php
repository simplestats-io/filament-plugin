<?php

use SimpleStatsIo\FilamentPlugin\Support\DrilldownFilters;

it('extracts active drilldown filters from a filter array', function () {
    $active = DrilldownFilters::active([
        'preset' => 'last_7_days',
        'track_referer' => 54,
        'page_entry' => 12,
        'comparison' => 'period',
    ]);

    expect($active)->toBe([
        'track_referer' => 54,
        'page_entry' => 12,
    ]);
});

it('ignores empty drilldown values when extracting active filters', function () {
    $active = DrilldownFilters::active([
        'track_referer' => '',
        'page_entry' => null,
        'location_country' => 7,
    ]);

    expect($active)->toBe([
        'location_country' => 7,
    ]);
});

it('returns empty array when no drilldowns are active', function () {
    $active = DrilldownFilters::active([
        'preset' => 'today',
    ]);

    expect($active)->toBe([]);
});

it('exposes a label for every drilldown key', function () {
    foreach (DrilldownFilters::KEYS as $key) {
        expect(DrilldownFilters::LABELS)->toHaveKey($key);
    }
});
