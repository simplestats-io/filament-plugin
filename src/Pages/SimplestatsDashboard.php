<?php

namespace SimpleStatsIo\FilamentPlugin\Pages;

use Filament\Forms;
use Filament\Pages\Dashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;
use SimpleStatsIo\FilamentPlugin\SimplestatsPlugin;
use SimpleStatsIo\FilamentPlugin\Widgets\ActiveFiltersWidget;
use SimpleStatsIo\FilamentPlugin\Widgets\EntryPagesWidget;
use SimpleStatsIo\FilamentPlugin\Widgets\RevenueChartWidget;
use SimpleStatsIo\FilamentPlugin\Widgets\StatsOverviewWidget;
use SimpleStatsIo\FilamentPlugin\Widgets\TopCountriesWidget;
use SimpleStatsIo\FilamentPlugin\Widgets\TopReferrersWidget;
use SimpleStatsIo\FilamentPlugin\Widgets\TopSourcesWidget;
use SimpleStatsIo\FilamentPlugin\Widgets\VisitorsChartWidget;

class SimplestatsDashboard extends Dashboard
{
    use HasFiltersForm;

    protected static string $routePath = '/simplestats';

    protected static ?string $title = 'SimpleStats';

    public static function getNavigationLabel(): string
    {
        return SimplestatsPlugin::get()->getNavigationLabel() ?? 'SimpleStats';
    }

    public static function getNavigationIcon(): string
    {
        return SimplestatsPlugin::get()->getNavigationIcon();
    }

    public static function getNavigationGroup(): ?string
    {
        return SimplestatsPlugin::get()->getNavigationGroup();
    }

    public static function getNavigationSort(): ?int
    {
        return SimplestatsPlugin::get()->getNavigationSort();
    }

    public function getWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
            VisitorsChartWidget::class,
            RevenueChartWidget::class,
            ActiveFiltersWidget::class,
            TopReferrersWidget::class,
            TopSourcesWidget::class,
            TopCountriesWidget::class,
            EntryPagesWidget::class,
        ];
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Select::make('preset')
                ->label('Time Range')
                ->options([
                    'today' => 'Today',
                    'yesterday' => 'Yesterday',
                    'last_7_days' => 'Last 7 Days',
                    'last_30_days' => 'Last 30 Days',
                    'last_12_weeks' => 'Last 12 Weeks',
                    'last_6_months' => 'Last 6 Months',
                    'this_month' => 'This Month',
                    'last_month' => 'Last Month',
                    'this_year' => 'This Year',
                    'last_year' => 'Last Year',
                    'all_time' => 'All Time',
                ])
                ->default('last_7_days'),
            Forms\Components\Select::make('comparison')
                ->label('Comparison')
                ->options([
                    '0' => 'No comparison',
                    'period' => 'Previous period',
                    'cycle' => 'Previous cycle',
                    'year' => 'Year over year',
                ])
                ->default('0')
                ->selectablePlaceholder(false),
        ]);
    }
}
