<?php

namespace App\Widgets;

use App\Models\FooterMenu;
use App\Models\HeaderMenu;
use Arrilot\Widgets\AbstractWidget;
use Stevebauman\Location\Facades\Location;
use Illuminate\Support\Facades\Auth;


class Header extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];

    public function run()
    {
        $loc = \App::getLocale();

        $menu_items1 = \Cache::remember('menu_items1_'.$loc, 10800, function () use ($loc) {
            return HeaderMenu::withTranslation($loc, false)->where('menu_pos', 1)->where('is_show', 1)->orderBy('order',
                'asc')->get();
        });

        $menu_items2 = \Cache::remember('menu_items2_'.$loc, 10800, function () use ($loc) {
            return HeaderMenu::withTranslation($loc, false)->where('menu_pos', 2)->where('is_show', 1)->orderBy('order',
                'asc')->get();
        });

        $menu_items3 = \Cache::remember('menu_items3_'.$loc, 10800, function () use ($loc) {
            return HeaderMenu::withTranslation($loc, false)->where('menu_pos', 3)->where('is_show', 1)->orderBy('order',
                'asc')->get();
        });

        if (isset($_SERVER['REQUEST_URI']) && !str_contains($_SERVER['REQUEST_URI'], '/admin/')) {
          //Проверка ip по странам
            //  Германия DE
           //   $ip = '104.122.39.255';
            //  Литва LT
          // $ip = '138.124.255.255';
            //  Латвия LV
           // $ip = '109.229.223.255';
            //  Эстония EE
           // $ip = '103.220.223.255';
            //  Польша PL
          //  $ip = '130.255.159.255';

          //  if ($position = Location::get($ip)) {

            if ($position = Location::get(request()->ip())) {
                $country_code = $position->countryCode;

                if ($country_code == 'LT') {
                  //  app()->setLocale('lt');
                    $allowed_lang = array('lt','en','ru');
                }

                elseif ($country_code == 'LV') {
                 //   app()->setLocale('lt');
                    $allowed_lang = array('lv','en','ru');
                }

                elseif ($country_code == 'EE') {
                  //  app()->setLocale('ee');
                    $allowed_lang = array('ee','en','ru');
                }

                elseif ($country_code == 'PL') {
                  //  app()->setLocale('pl');
                    $allowed_lang = array('pl');
                }

                elseif ($country_code == 'DE') {
                  //  app()->setLocale('de');
                    $allowed_lang = array('de','en');
                }
            }
        }


        if (Auth::user()) {
          $user_role = Auth::user()->role_id;
       } else {$user_role=2;}


    if(isset(Auth::user()->role_id))
    {
      $user_role = Auth::user()->role_id;
    } else {
      $user_role = 2;
    }


    if (!isset($allowed_lang) || $user_role!='2') {
        $allowed_lang = array('lv','lt','pl','ru','de','en','ee');
    }
        return view('widgets.header', [
            'allowed_lang' => $allowed_lang,
            'menu_items1' => $menu_items1,
            'menu_items2' => $menu_items2,
            'menu_items3' => $menu_items3,
        ]);
    }
}
