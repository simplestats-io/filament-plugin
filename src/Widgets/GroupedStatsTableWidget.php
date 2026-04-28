<?php

namespace SimpleStatsIo\FilamentPlugin\Widgets;

use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\HtmlString;
use SimpleStatsIo\FilamentPlugin\Concerns\InteractsWithSimplestatsApi;

abstract class GroupedStatsTableWidget extends TableWidget
{
    use InteractsWithSimplestatsApi;

    protected int|string|array $columnSpan = 'full';

    protected ?array $cachedResponse = null;

    abstract protected function getStatsType(): string;

    abstract protected function getGradientColor(): string;

    public function table(Table $table): Table
    {
        return $table
            ->heading($this->getTableHeading())
            ->records(function (?string $sortColumn = null) {
                $this->cachedResponse = null;

                return $this->getRecords($sortColumn);
            })
            ->columns($this->getTableColumns())
            ->defaultSort('visitors', 'desc')
            ->paginated(false);
    }

    protected function fetchResponse(?string $sortColumn = null): array
    {
        if ($this->cachedResponse === null) {
            $filters = $this->getApiFilters();

            if ($sortColumn) {
                $filters['stats_sort'] = $sortColumn;
            }

            $this->cachedResponse = $this->getApiClient()->getGroupedStats($this->getStatsType(), $filters);
        }

        return $this->cachedResponse;
    }

    protected function hasField(string $field): bool
    {
        $data = $this->fetchResponse()['data'] ?? [];

        return ! empty($data) && array_key_exists($field, $data[0]);
    }

    protected function getRecords(?string $sortColumn = null): array
    {
        $response = $this->fetchResponse($sortColumn);
        $data = $response['data'] ?? [];
        $previousByTypeId = [];

        foreach ($response['data_previous'] ?? [] as $prev) {
            if (isset($prev['type_id'])) {
                $previousByTypeId[$prev['type_id']] = $prev;
            }
        }

        return array_map(function (array $record, int $index) use ($previousByTypeId) {
            $record['key'] = $record['type_id'] ?? $index;
            $record['_previous'] = $previousByTypeId[$record['type_id'] ?? null] ?? null;

            return $record;
        }, $data, array_keys($data));
    }

    protected function getMaxVisitors(): int
    {
        $data = $this->fetchResponse()['data'] ?? [];

        if (empty($data)) {
            return 0;
        }

        return (int) max(array_column($data, 'visitors'));
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label('Name')
                ->limit(30)
                ->sortable(query: fn () => null)
                ->size(TextSize::ExtraSmall)
                ->extraCellAttributes(function (array $record): array {
                    $percentage = $this->calculatePercentage($record);
                    $color = $this->getGradientColor();

                    return [
                        'style' => "background: linear-gradient(to right, rgba({$color}, 0.3) {$percentage}%, transparent {$percentage}%);",
                    ];
                }),

            TextColumn::make('visitors')
                ->label('Visitors')
                ->html()
                ->formatStateUsing(fn ($state, array $record): HtmlString => $this->formatWithComparison($state, $record, 'visitors', number_format((int) $state)))
                ->sortable(query: fn () => null)
                ->size(TextSize::ExtraSmall)
                ->alignEnd()
                ->visible(fn (): bool => $this->hasField('visitors')),

            TextColumn::make('reg')
                ->label('Regs')
                ->html()
                ->formatStateUsing(fn ($state, array $record): HtmlString => $this->formatWithComparison($state, $record, 'reg', number_format((int) $state)))
                ->sortable(query: fn () => null)
                ->size(TextSize::ExtraSmall)
                ->alignEnd()
                ->visible(fn (): bool => $this->hasField('reg')),

            TextColumn::make('cr')
                ->label('CR')
                ->html()
                ->formatStateUsing(fn ($state, array $record): HtmlString => $this->formatWithComparison($state, $record, 'cr', $state.'%'))
                ->sortable(query: fn () => null)
                ->size(TextSize::ExtraSmall)
                ->alignEnd()
                ->visible(fn (): bool => $this->hasField('cr')),

            TextColumn::make('dau')
                ->label(fn (): string => $this->getActiveUsersLabel())
                ->html()
                ->formatStateUsing(fn ($state, array $record): HtmlString => $this->formatWithComparison($state, $record, 'dau', number_format((int) $state)))
                ->sortable(query: fn () => null)
                ->size(TextSize::ExtraSmall)
                ->alignEnd()
                ->visible(fn (): bool => $this->hasField('dau')),

            TextColumn::make('net')
                ->label('Revenue')
                ->html()
                ->formatStateUsing(fn ($state, array $record): HtmlString => $this->formatWithComparison($state, $record, 'net', number_format($state / 100, 2)))
                ->sortable(query: fn () => null)
                ->size(TextSize::ExtraSmall)
                ->alignEnd()
                ->visible(fn (): bool => $this->hasField('net')),
        ];
    }

    protected function formatWithComparison(mixed $state, array $record, string $field, string $formatted): HtmlString
    {
        $previous = $record['_previous'][$field] ?? null;

        if ($previous === null || ! is_numeric($state)) {
            return new HtmlString(e($formatted));
        }

        $current = (float) $state;
        $previous = (float) $previous;

        if ($previous == 0.0 && $current == 0.0) {
            return new HtmlString(e($formatted));
        }

        if ($previous == 0.0) {
            $change = 100.0;
        } else {
            $change = (($current - $previous) / $previous) * 100;
        }

        $rounded = round($change, abs($change) >= 10 ? 0 : 1);
        $prefix = $rounded > 0 ? '+' : '';
        $color = $rounded > 0 ? '#16a34a' : ($rounded < 0 ? '#dc2626' : '#6b7280');

        $badge = sprintf(
            '<span style="display:block;font-size:0.7rem;font-weight:600;line-height:1;color:%s;">%s%s%%</span>',
            $color,
            e($prefix),
            e((string) $rounded),
        );

        return new HtmlString(e($formatted).$badge);
    }

    protected function calculatePercentage(array $record): int
    {
        $max = $this->getMaxVisitors();

        if ($max <= 0) {
            return 0;
        }

        return (int) floor(($record['visitors'] / $max) * 100);
    }

    protected function getActiveUsersLabel(): string
    {
        $rangeType = $this->fetchResponse()['meta']['range_type'] ?? 'days';

        return match ($rangeType) {
            'weeks' => 'WAU',
            'months' => 'MAU',
            default => 'DAU',
        };
    }
}
