<?php

use Illuminate\Support\Facades\File;

afterEach(function () {
    File::delete(config_path('simplestats-filament.php'));
});

it('publishes the config file and asks to star the repo', function () {
    $this->artisan('simplestats-filament:install')
        ->expectsConfirmation('Would you like to star our repo on GitHub?', 'no')
        ->assertSuccessful();

    expect(config_path('simplestats-filament.php'))->toBeFile();
});
