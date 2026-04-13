<?php

namespace SimpleStatsIo\FilamentPlugin\Widgets;

class EntryPagesWidget extends GroupedStatsTableWidget
{
    protected static ?string $heading = 'Entry Pages';

    protected function getStatsType(): string
    {
        return 'page_entry';
    }

    protected function getGradientColor(): string
    {
        return '14, 165, 233';
    }
}
