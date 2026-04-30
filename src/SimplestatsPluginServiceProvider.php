<?php

namespace SimpleStatsIo\FilamentPlugin;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SimplestatsPluginServiceProvider extends PackageServiceProvider
{
    public static string $name = 'simplestats-filament';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile();
    }
}
