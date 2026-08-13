<?php

namespace App\Services\Localization;

use Illuminate\Support\Facades\DB;

class Localization
{
    public function locale()
    {
        $locale = request()->segment(1, '');

        $locale_prefixs = [];

        $locales = DB::table('locales')->select('prefix')->get();

        foreach ($locales as $key => $value) {
            $locale_prefixs[] = $value->prefix;
        }

        if ($locale && in_array($locale, $locale_prefixs)) {
            return $locale;
        }

        return '';
    }
}
