<?php

namespace SimpleStatsIo\FilamentPlugin\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use SimpleStatsIo\FilamentPlugin\SimplestatsPluginServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            SimplestatsPluginServiceProvider::class,
        ];
    }
}
