<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ServerDataServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
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
            $is_webp = 0;
            if (isset($_SERVER['HTTP_ACCEPT']))
            {
                if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') !== false)
                {
                    $is_webp = 1;
                }
            } else
            {
                if (isset($_SERVER['HTTP_USER_AGENT']))
                {
                    if (strpos($_SERVER['HTTP_USER_AGENT'], ' Chrome/') !== false)
                    {
                        $is_webp = 1;
                    }
                }
            }

            \View::share('is_webp', $is_webp);

            $is_firefox = 0;
            if (isset($_SERVER['HTTP_USER_AGENT']))
            {
                if (strlen(strstr($_SERVER['HTTP_USER_AGENT'], 'Firefox')) > 0)
                {
                    $is_firefox = 1;
                }
            }
            \View::share('is_firefox', $is_firefox);
        }
    }
}
