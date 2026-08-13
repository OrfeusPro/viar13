<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ComposerServiceProvider extends ServiceProvider
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
        if (isset($_SERVER['REQUEST_URI']) && !str_contains($_SERVER['REQUEST_URI'], '/admin/'))
        {
            View::composer([env('THEME_RESOURCES') . 'pages.index.emoj_4_5'], 'App\Http\View\Composers\EmojFormComposer');
            View::composer(['partials.index_new.emoj_4_5'], 'App\Http\View\Composers\EmojFormComposer');
            View::composer(['partials.all_styles.bot_form'], 'App\Http\View\Composers\EmojFormComposer');
        }
    }
}
