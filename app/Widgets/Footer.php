<?php

namespace App\Widgets;

use App\Models\FooterMenu;
use Arrilot\Widgets\AbstractWidget;

class Footer extends AbstractWidget
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

        $menu_items1_bot = \Cache::remember('$menu_items1_bot_'.$loc, 10800, function () use ($loc) {
            return FooterMenu::withTranslation($loc, false)->where('menu_pos', 1)->orderBy('order',
                'asc')->get();
        });
        $menu_items2_bot = \Cache::remember('$menu_items2_bot_'.$loc, 10800, function () use ($loc) {
            return FooterMenu::withTranslation($loc, false)->where('menu_pos', 2)->orderBy('order',
                'asc')->get();
        });
        $menu_items3_bot = \Cache::remember('$menu_items3_bot_'.$loc, 10800, function () use ($loc) {
            return FooterMenu::withTranslation($loc, false)->where('menu_pos', 3)->orderBy('order',
                'asc')->get();
        });

        return view('widgets.footer', [
            'menu_items1_bot' => $menu_items1_bot,
            'menu_items2_bot' => $menu_items2_bot,
            'menu_items3_bot' => $menu_items3_bot,
        ]);
    }
}
