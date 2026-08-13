<?php

namespace App\Providers;

use Mcamara\LaravelLocalization\LaravelLocalizationServiceProvider as BaseServiceProvider;

class LaravelLocalizationServiceProvider extends BaseServiceProvider
{
    /**
     * The package's translated route-list command currently shadows Laravel
     * 13's route:list command under Symfony Console 7. Public localization
     * does not require those optional cache commands.
     */
    protected function registerCommands(): void
    {
        // Intentionally disabled until the upstream command is Laravel 13 safe.
    }
}
