<?php

namespace App\Providers;

use App\Models\GlobConfig;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class GetStringsProvider extends ServiceProvider
{
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
        if(isset($_SERVER['REQUEST_URI']) && !str_contains($_SERVER['REQUEST_URI'], '/admin/')){
            View::composer('*', function ($view) {
                $loc = \App::getLocale();
                if (!str_contains($view->getName(), 'voyager::')) {
                    $trackers = Cache::remember('glob_config_analytics', 60, function() {
                        return GlobConfig::first()->pluck('analytics')->first();
                    });

                    $trackers_body = Cache::remember('glob_config_analytics_body', 60, function() {
                        return GlobConfig::first()->pluck('analytics_body')->first();
                    });

                    //dd($trackers_body);

                    \View::share('trackers', $trackers);
                    \View::share('trackers_body', $trackers_body);
                    \View::share('cur_loc', $loc);
                }
            });
        }
    }
}
