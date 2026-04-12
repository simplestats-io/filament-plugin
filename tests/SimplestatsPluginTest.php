<?php

use SimpleStatsIo\FilamentPlugin\SimplestatsPlugin;

it('has correct plugin id', function () {
    $plugin = SimplestatsPlugin::make();

    expect($plugin->getId())->toBe('simplestats');
});

it('accepts api token via fluent api', function () {
    $plugin = SimplestatsPlugin::make()
        ->apiToken('my-secret-token');

    expect($plugin)->toBeInstanceOf(SimplestatsPlugin::class);
});

it('accepts api url via fluent api', function () {
    $plugin = SimplestatsPlugin::make()
        ->apiUrl('https://custom.example.com/api/v1');

    expect($plugin)->toBeInstanceOf(SimplestatsPlugin::class);
});

it('accepts navigation configuration', function () {
    $plugin = SimplestatsPlugin::make()
        ->navigationGroup('Analytics')
        ->navigationSort(5)
        ->navigationLabel('Stats')
        ->navigationIcon('heroicon-o-presentation-chart-line');

    expect($plugin->getNavigationGroup())->toBe('Analytics')
        ->and($plugin->getNavigationSort())->toBe(5)
        ->and($plugin->getNavigationLabel())->toBe('Stats')
        ->and($plugin->getNavigationIcon())->toBe('heroicon-o-presentation-chart-line');
});

it('has default navigation icon', function () {
    $plugin = SimplestatsPlugin::make();

    expect($plugin->getNavigationIcon())->toBe('heroicon-o-chart-bar-square');
});

it('has null defaults for optional navigation config', function () {
    $plugin = SimplestatsPlugin::make();

    expect($plugin->getNavigationGroup())->toBeNull()
        ->and($plugin->getNavigationSort())->toBeNull()
        ->and($plugin->getNavigationLabel())->toBeNull();
});

it('returns static instance from make', function () {
    $plugin = SimplestatsPlugin::make();

    expect($plugin)->toBeInstanceOf(SimplestatsPlugin::class);
});
