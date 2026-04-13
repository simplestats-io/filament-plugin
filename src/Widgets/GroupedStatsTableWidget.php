<?php

namespace SimpleStatsIo\FilamentPlugin\Widgets;

use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
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
            ->records(function (string $sortColumn = null) {
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
        $data = $this->fetchResponse($sortColumn)['data'] ?? [];

        return array_map(function (array $record, int $index) {
            $record['key'] = $record['type_id'] ?? $index;

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
                ->numeric()
                ->sortable(query: fn () => null)
                ->size(TextSize::ExtraSmall)
                ->alignEnd()
                ->visible(fn (): bool => $this->hasField('visitors')),

            TextColumn::make('reg')
                ->label('Regs')
                ->numeric()
                ->sortable(query: fn () => null)
                ->size(TextSize::ExtraSmall)
                ->alignEnd()
                ->visible(fn (): bool => $this->hasField('reg')),

            TextColumn::make('cr')
                ->label('CR')
                ->suffix('%')
                ->sortable(query: fn () => null)
                ->size(TextSize::ExtraSmall)
                ->alignEnd()
                ->visible(fn (): bool => $this->hasField('cr')),

            TextColumn::make('dau')
                ->label(fn (): string => $this->getActiveUsersLabel())
                ->numeric()
                ->sortable(query: fn () => null)
                ->size(TextSize::ExtraSmall)
                ->alignEnd()
                ->visible(fn (): bool => $this->hasField('dau')),

            TextColumn::make('net')
                ->label('Revenue')
                ->formatStateUsing(fn ($state): string => number_format($state / 100, 2))
                ->sortable(query: fn () => null)
                ->size(TextSize::ExtraSmall)
                ->alignEnd()
                ->visible(fn (): bool => $this->hasField('net')),
        ];
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
