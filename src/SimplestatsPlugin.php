<?php

namespace SimpleStatsIo\FilamentPlugin;

use Filament\Contracts\Plugin;
use Filament\Panel;
use SimpleStatsIo\FilamentPlugin\Pages\SimplestatsDashboard;

class SimplestatsPlugin implements Plugin
{
    protected ?string $apiToken = null;

    protected ?string $apiUrl = null;

    protected ?int $cacheTtl = null;

    protected ?string $navigationGroup = null;

    protected ?int $navigationSort = null;

    protected ?string $navigationLabel = null;

    protected string $navigationIcon = 'heroicon-o-chart-bar-square';

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        return filament(app(static::class)->getId());
    }

    public function getId(): string
    {
        return 'simplestats';
    }

    public function register(Panel $panel): void
    {
        $panel->pages([
            SimplestatsDashboard::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        $token = $this->apiToken ?? config('simplestats-filament.api_token');
        $url = $this->apiUrl ?? config('simplestats-filament.api_url', 'https://simplestats.io/api/v1');
        $cacheTtl = $this->cacheTtl ?? config('simplestats-filament.cache_ttl', 60);

        if ($token) {
            app()->singleton(SimplestatsApiClient::class, fn () => new SimplestatsApiClient(
                apiToken: $token,
                apiUrl: $url,
                cacheTtl: $cacheTtl,
            ));
        }
    }

    public function apiToken(?string $token): static
    {
        $this->apiToken = $token;

        return $this;
    }

    public function apiUrl(string $url): static
    {
        $this->apiUrl = $url;

        return $this;
    }

    public function cacheTtl(int $seconds): static
    {
        $this->cacheTtl = $seconds;

        return $this;
    }

    public function navigationGroup(?string $group): static
    {
        $this->navigationGroup = $group;

        return $this;
    }

    public function navigationSort(?int $sort): static
    {
        $this->navigationSort = $sort;

        return $this;
    }

    public function navigationLabel(?string $label): static
    {
        $this->navigationLabel = $label;

        return $this;
    }

    public function navigationIcon(string $icon): static
    {
        $this->navigationIcon = $icon;

        return $this;
    }

    public function getNavigationGroup(): ?string
    {
        return $this->navigationGroup;
    }

    public function getNavigationSort(): ?int
    {
        return $this->navigationSort;
    }

    public function getNavigationLabel(): ?string
    {
        return $this->navigationLabel;
    }

    public function getNavigationIcon(): string
    {
        return $this->navigationIcon;
    }
}
