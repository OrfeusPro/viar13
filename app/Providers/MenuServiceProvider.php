<?php

namespace App\Providers;

use App\Models\Locale as Loc;
use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider {
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningUnitTests()) {
            \View::share('locales', collect());
            return;
        }

        \View::share('locales', Loc::all());
    }
}
