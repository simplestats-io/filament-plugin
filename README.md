# Filament Plugin for SimpleStats.io

[![Latest Version on Packagist](https://img.shields.io/packagist/v/simplestats-io/filament-plugin.svg?style=flat-square)](https://packagist.org/packages/simplestats-io/filament-plugin)
[![Tests](https://github.com/simplestats-io/filament-plugin/actions/workflows/run-tests.yml/badge.svg?branch=main)](https://github.com/simplestats-io/filament-plugin/actions/workflows/run-tests.yml)
[![Fix PHP code style issues](https://github.com/simplestats-io/filament-plugin/actions/workflows/fix-php-code-style-issues.yml/badge.svg?branch=main)](https://github.com/simplestats-io/filament-plugin/actions/workflows/fix-php-code-style-issues.yml)
[![License](https://img.shields.io/packagist/l/simplestats-io/filament-plugin.svg?style=flat-square)](https://packagist.org/packages/simplestats-io/filament-plugin)

The official [Filament v5](https://filamentphp.com) plugin for [SimpleStats.io](https://simplestats.io). View your analytics directly inside your Filament panel: visitors, registrations, revenue, top sources, and top countries.

![screenshot](https://simplestats.io/images/screenshot_filament.jpg)

## Requirements

- PHP 8.2+
- Laravel 12+
- Filament 5+
- A SimpleStats.io account with an API token

## Installation

Install via Composer:

```bash
composer require simplestats-io/filament-plugin
```

Optionally publish the config file:

```bash
php artisan vendor:publish --tag="simplestats-filament-config"
```

Add your API token to `.env`:

```env
SIMPLESTATS_API_TOKEN=your-api-token-here
```

## Setup

Register the plugin in your Filament `PanelProvider`:

```php
use SimpleStatsIo\FilamentPlugin\SimplestatsPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(SimplestatsPlugin::make());
}
```

That's it. Navigate to `/admin/simplestats` (or your panel path + `/simplestats`) to see your dashboard.

## Configuration

All configuration is optional. The plugin works out of the box with just an API token.

### Plugin Options

```php
SimplestatsPlugin::make()
    ->apiToken('custom-token')                        // defaults to config/env
    ->apiUrl('https://simplestats.io/api/v1')         // defaults to config/env
    ->cacheTtl(60)                                    // cache duration in seconds
    ->navigationGroup('Analytics')                    // Filament nav group
    ->navigationLabel('Stats')                        // custom nav label
    ->navigationSort(5)                               // nav position
    ->navigationIcon('heroicon-o-chart-bar-square')   // default icon
```

### Config File

If you published the config, you can set defaults via environment variables:

```php
// config/simplestats-filament.php

return [

    /*
     |--------------------------------------------------------------------------
     | SimpleStats API Credentials
     |--------------------------------------------------------------------------
     |
     | Define your API credentials here. The API URL defaults to the hosted
     | SimpleStats instance. If you are self-hosting, change it to your own
     | URL. The API token is the same one used by the Laravel client package.
     |
     */

    'api_url' => env('SIMPLESTATS_API_URL', 'https://simplestats.io/api/v1'),

    'api_token' => env('SIMPLESTATS_API_TOKEN'),

    /*
     |--------------------------------------------------------------------------
     | Cache Duration
     |--------------------------------------------------------------------------
     |
     | API responses are cached to avoid unnecessary requests on every page
     | load and Livewire re-render. Multiple widgets sharing the same filters
     | will reuse a single cached response. Set to 0 to disable caching.
     |
     */

    'cache_ttl' => 60,

];
```

## Dashboard Widgets

The plugin provides seven widgets on a single dashboard page:

| Widget | Description |
|--------|-------------|
| **Stats Overview** | KPI cards: Visitors, Registrations, CR, Net Revenue, ARPU, ARPV. Includes sparklines. |
| **Visitors & Registrations** | Line chart showing visitors and registrations over time. |
| **Revenue** | Line chart showing gross and net revenue over time. |
| **Top Referrers** | Horizontal bar chart of top referrers. |
| **Top Sources** | Horizontal bar chart of top traffic sources. |
| **Top Countries** | Horizontal bar chart of top visitor countries. |
| **Entry Pages** | Horizontal bar chart of top landing pages. |

A time range filter lets you switch between presets (Today, Last 7 Days, Last 30 Days, This Year, All Time, etc.).

## Self-Hosted

If you are running a self-hosted SimpleStats instance, point the plugin to your own API:

```php
SimplestatsPlugin::make()
    ->apiToken(config('simplestats-filament.api_token'))
    ->apiUrl('https://your-simplestats-instance.com/api/v1')
```

Or via `.env`:

```env
SIMPLESTATS_API_URL=https://your-simplestats-instance.com/api/v1
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Zacharias Creutznacher](https://github.com/sairahcaz)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
