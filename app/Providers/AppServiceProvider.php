<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        // Public runtime bindings are registered by their dedicated providers.
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        EloquentCollection::macro('translate', function (?string $locale = null, string|bool $fallback = true) {
            return $this->map(function ($model) use ($locale, $fallback) {
                return method_exists($model, 'translate')
                    ? $model->translate($locale, $fallback)
                    : $model;
            });
        });
    }
}
