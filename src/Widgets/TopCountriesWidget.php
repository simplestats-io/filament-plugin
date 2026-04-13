<?php

namespace SimpleStatsIo\FilamentPlugin\Widgets;

class TopCountriesWidget extends GroupedStatsTableWidget
{
    protected static ?string $heading = 'Top Countries';

    protected function getStatsType(): string
    {
        return 'location_country';
    }

    protected function getGradientColor(): string
    {
        return '16, 185, 129';
    }
}
