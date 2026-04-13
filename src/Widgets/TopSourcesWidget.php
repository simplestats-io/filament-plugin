<?php

namespace SimpleStatsIo\FilamentPlugin\Widgets;

class TopSourcesWidget extends GroupedStatsTableWidget
{
    protected static ?string $heading = 'Top Sources';

    protected function getStatsType(): string
    {
        return 'track_source';
    }

    protected function getGradientColor(): string
    {
        return '99, 102, 241';
    }
}
