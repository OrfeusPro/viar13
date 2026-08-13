<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SetLangServiceProvider extends ServiceProvider
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
        // $lng = preg_split('/,|;/', \Request::server('HTTP_ACCEPT_LANGUAGE'));

        // try {
        //     $first = $lng[0];
        // } catch (\Throwable $th) {
        //     $first = 'lv';
        // }

        // $langs = ['en', 'ee', 'lt', 'lv', 'pl', 'de', 'ru'];
        // if (!in_array($first, $langs)) {
        //     $first = 'lv';
        // }

        // if(!\Session::has('locale')){
        //     \Session::put('locale', $first);
        //     \App::setLocale($first);
        // }

    }
}
