<?php

use Illuminate\Support\Facades\URL;
use SimpleStatsIo\FilamentPlugin\Concerns\InteractsWithSimplestatsApi;

beforeEach(function () {
    URL::forceRootUrl('https://example.test');

    $this->subject = new class
    {
        use InteractsWithSimplestatsApi;

        public function exposeGetApiFilters(): array
        {
            return $this->getApiFilters();
        }

        public function exposeGetActiveDrillDown(string $statsType): mixed
        {
            return $this->getActiveDrillDown($statsType);
        }

        public function exposeBuildDrilldownToggleUrl(string $statsType, mixed $typeId): string
        {
            return $this->buildDrilldownToggleUrl($statsType, $typeId);
        }
    };
});

it('returns default api filters when no page filters are set', function () {
    $this->subject->pageFilters = null;

    expect($this->subject->exposeGetApiFilters())->toBe([
        'preset' => 'last_7_days',
    ]);
});

it('forwards comparison when set', function () {
    $this->subject->pageFilters = [
        'preset' => 'last_30_days',
        'comparison' => 'period',
    ];

    expect($this->subject->exposeGetApiFilters())->toBe([
        'preset' => 'last_30_days',
        'comparison' => 'period',
    ]);
});

it('drops comparison when set to disabled value', function () {
    $this->subject->pageFilters = [
        'preset' => 'today',
        'comparison' => '0',
    ];

    expect($this->subject->exposeGetApiFilters())->toBe([
        'preset' => 'today',
    ]);
});

it('forwards a single drilldown filter', function () {
    $this->subject->pageFilters = [
        'preset' => 'last_7_days',
        'track_referer' => 54,
    ];

    expect($this->subject->exposeGetApiFilters())->toBe([
        'preset' => 'last_7_days',
        'track_referer' => 54,
    ]);
});

it('forwards multiple stacked drilldown filters', function () {
    $this->subject->pageFilters = [
        'preset' => 'last_7_days',
        'track_referer' => 54,
        'page_entry' => 12,
        'location_country' => 7,
    ];

    expect($this->subject->exposeGetApiFilters())->toBe([
        'preset' => 'last_7_days',
        'track_referer' => 54,
        'location_country' => 7,
        'page_entry' => 12,
    ]);
});

it('ignores empty drilldown values', function () {
    $this->subject->pageFilters = [
        'preset' => 'last_7_days',
        'track_referer' => '',
        'page_entry' => null,
    ];

    expect($this->subject->exposeGetApiFilters())->toBe([
        'preset' => 'last_7_days',
    ]);
});

it('ignores unknown filter keys when forwarding to api', function () {
    $this->subject->pageFilters = [
        'preset' => 'last_7_days',
        'not_a_filter' => 99,
    ];

    expect($this->subject->exposeGetApiFilters())->toBe([
        'preset' => 'last_7_days',
    ]);
});

it('returns active drilldown value', function () {
    $this->subject->pageFilters = [
        'track_referer' => 54,
    ];

    expect($this->subject->exposeGetActiveDrillDown('track_referer'))->toBe(54);
    expect($this->subject->exposeGetActiveDrillDown('page_entry'))->toBeNull();
});

it('treats empty string and null as inactive drilldown', function () {
    $this->subject->pageFilters = [
        'track_referer' => '',
        'page_entry' => null,
    ];

    expect($this->subject->exposeGetActiveDrillDown('track_referer'))->toBeNull();
    expect($this->subject->exposeGetActiveDrillDown('page_entry'))->toBeNull();
});

it('builds toggle url that sets a new drilldown filter', function () {
    $this->subject->pageFilters = [
        'preset' => 'last_7_days',
    ];

    $url = $this->subject->exposeBuildDrilldownToggleUrl('track_referer', 54);

    parse_str(parse_url($url, PHP_URL_QUERY), $query);
    expect($query['filters'])->toBe([
        'preset' => 'last_7_days',
        'track_referer' => '54',
    ]);
});

it('builds toggle url that clears the drilldown when clicking the same value again', function () {
    $this->subject->pageFilters = [
        'preset' => 'last_7_days',
        'track_referer' => 54,
    ];

    $url = $this->subject->exposeBuildDrilldownToggleUrl('track_referer', 54);

    parse_str(parse_url($url, PHP_URL_QUERY), $query);
    expect($query['filters'])->toBe([
        'preset' => 'last_7_days',
    ]);
});

it('builds toggle url that swaps the value when clicking a different row', function () {
    $this->subject->pageFilters = [
        'preset' => 'last_7_days',
        'track_referer' => 54,
    ];

    $url = $this->subject->exposeBuildDrilldownToggleUrl('track_referer', 99);

    parse_str(parse_url($url, PHP_URL_QUERY), $query);
    expect($query['filters'])->toBe([
        'preset' => 'last_7_days',
        'track_referer' => '99',
    ]);
});

it('preserves other drilldown filters when toggling one', function () {
    $this->subject->pageFilters = [
        'preset' => 'last_7_days',
        'track_referer' => 54,
        'page_entry' => 12,
    ];

    $url = $this->subject->exposeBuildDrilldownToggleUrl('page_entry', 12);

    parse_str(parse_url($url, PHP_URL_QUERY), $query);
    expect($query['filters'])->toBe([
        'preset' => 'last_7_days',
        'track_referer' => '54',
    ]);
});

it('lets users stack a second drilldown on top of an existing one', function () {
    $this->subject->pageFilters = [
        'preset' => 'last_7_days',
        'track_referer' => 54,
    ];

    $url = $this->subject->exposeBuildDrilldownToggleUrl('page_entry', 12);

    parse_str(parse_url($url, PHP_URL_QUERY), $query);
    expect($query['filters'])->toBe([
        'preset' => 'last_7_days',
        'track_referer' => '54',
        'page_entry' => '12',
    ]);
});

it('uses -1 as type id for null rows (direct/unknown)', function () {
    $this->subject->pageFilters = [];

    $url = $this->subject->exposeBuildDrilldownToggleUrl('track_referer', null);

    parse_str(parse_url($url, PHP_URL_QUERY), $query);
    expect($query['filters'])->toBe([
        'track_referer' => '-1',
    ]);
});
