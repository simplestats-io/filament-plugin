<?php

namespace SimpleStatsIo\FilamentPlugin\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use SimpleStatsIo\FilamentPlugin\Concerns\InteractsWithSimplestatsApi;

class StatsOverviewWidget extends BaseWidget
{
    use InteractsWithSimplestatsApi;

    protected function getStats(): array
    {
        $response = $this->getApiClient()->getStats($this->getApiFilters());
        $data = array_reverse($response['data'] ?? []);
        $previousData = array_reverse($response['data_previous'] ?? []);

        if (empty($data)) {
            return [
                Stat::make('Visitors', '-'),
                Stat::make('Registrations', '-'),
                Stat::make('CR', '-'),
                Stat::make('Net Revenue', '-'),
                Stat::make('ARPU', '-'),
                Stat::make('ARPV', '-'),
            ];
        }

        $totalVisitors = array_sum(array_column($data, 'pd_visitor'));
        $totalReg = array_sum(array_column($data, 'pd_reg'));
        $totalNet = array_sum(array_column($data, 'pd_net'));
        $cr = $totalVisitors > 0 ? round(($totalReg / $totalVisitors) * 100, 2) : 0;
        $lastPeriod = end($data);
        $arpu = $lastPeriod['lt_arpu'] ?? 0;
        $arpv = $lastPeriod['lt_arpv'] ?? 0;

        $stats = [
            Stat::make('Visitors', number_format($totalVisitors))
                ->chart(array_column($data, 'pd_visitor'))
                ->color('primary'),
            Stat::make('Registrations', number_format($totalReg))
                ->chart(array_column($data, 'pd_reg'))
                ->color('success'),
            Stat::make('CR', $cr.'%')
                ->color('info'),
            Stat::make('Net Revenue', $this->formatCurrency($totalNet))
                ->chart(array_column($data, 'pd_net'))
                ->color('warning'),
            Stat::make('ARPU', $this->formatCurrency($arpu))
                ->description('Average Revenue Per User')
                ->color('gray'),
            Stat::make('ARPV', $this->formatCurrency($arpv))
                ->description('Average Revenue Per Visitor')
                ->color('gray'),
        ];

        if (! empty($previousData)) {
            $prevVisitors = array_sum(array_column($previousData, 'pd_visitor'));
            $prevReg = array_sum(array_column($previousData, 'pd_reg'));
            $prevNet = array_sum(array_column($previousData, 'pd_net'));

            $stats[0] = $stats[0]->description($this->trendDescription($totalVisitors, $prevVisitors));
            $stats[1] = $stats[1]->description($this->trendDescription($totalReg, $prevReg));
            $stats[3] = $stats[3]->description($this->trendDescription($totalNet, $prevNet));
        }

        return $stats;
    }

    protected function trendDescription(float $current, float $previous): string
    {
        if ($previous == 0) {
            return $current > 0 ? '+100%' : '0%';
        }

        $change = round((($current - $previous) / $previous) * 100, 1);
        $prefix = $change >= 0 ? '+' : '';

        return $prefix.$change.'%';
    }

    protected function formatCurrency(float|int $cents): string
    {
        return number_format($cents / 100, 2);
    }
}
