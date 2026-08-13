<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Stevebauman\Location\Facades\Location;

class UserLocationServiceProvider extends ServiceProvider
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
        if (isset($_SERVER['REQUEST_URI']) && !str_contains($_SERVER['REQUEST_URI'], '/admin/')){
            if ($position = Location::get(request()->ip())) {
                $country_code = $position->countryCode;
                if ($country_code == 'FI') {
                    $country_code = 'FIN';
                }
                // LV => ЛАТВИЯ
                // LT => ЛИТВА
                // EE => ЭСТОНИЯ
                // FI => ФИНЛЯНДИЯ
                // DE => ГЕРМАНИЯ
                // PL => ПОЛЬША
                $contry_mult = \DB::table('country_tels')->where('country_code', $country_code)->pluck('price_country_mltpr')->first();
                if ($contry_mult == null) {
                    $contry_mult = 1;
                }
            } else {
                $contry_mult = 1;
            }

            \View::share('contry_mult', $contry_mult);
        }
    }
}
