<?php
namespace App\Helpers;
use Stevebauman\Location\Facades\Location;
use App;
use DB;

class GalleryTopMail
{

    public function get_topmail_data($mail=false)
    {
        $locale = App::getLocale();
        if ($mail) {
            $topmail = DB::table('a_mail_top_sale')
                ->join('translations', function ($join) use ($locale) {
                    $join->on('translations.foreign_key', '=', 'a_mail_top_sale.id')
                        ->where('translations.table_name', '=', 'a_mail_top_sale')
                        ->where('translations.column_name', '=', 'title')
                        ->where('translations.locale', '=', $locale);
                })
                ->orderByRaw('RAND()') // Randomize the order of results (for older Laravel versions)
                ->limit(2) // Retrieve only 2 elements
                ->get();
        } else {
            $topmail = DB::table('a_mail_top_sale')
                ->join('translations', function ($join) use ($locale) {
                    $join->on('translations.foreign_key', '=', 'a_mail_top_sale.id')
                        ->where('translations.table_name', '=', 'a_mail_top_sale')
                        ->where('translations.column_name', '=', 'title')
                        ->where('translations.locale', '=', $locale);
                })
                ->get();
        }

        return $topmail;
    }

    public function get_gallery_price()
    {

        if ($position = Location::get(request()->ip())) {
            $country_code = $position->countryCode;
            if ($country_code == 'FI') {
                $country_code = 'FIN';
            }

            $contry_mult = \DB::table('country_tels')->where('country_code', $country_code)->pluck('price_country_mltpr')->first();
            if ($contry_mult == null) {
                $contry_mult = 1;
            }
        } else {
            $contry_mult = 1;
        }

        return $contry_mult;
    }
}
