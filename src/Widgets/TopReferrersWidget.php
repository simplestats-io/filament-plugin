<?php

namespace SimpleStatsIo\FilamentPlugin\Widgets;

class TopReferrersWidget extends GroupedStatsTableWidget
{
    protected static ?string $heading = 'Top Referrers';

    protected function getStatsType(): string
    {
        return 'track_referer';
    }

    protected function getGradientColor(): string
    {
        return '139, 92, 246';
    }
}
