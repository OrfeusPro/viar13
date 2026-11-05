<?php

namespace App\Services\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class OrderCustomerSearch
{
    public function apply(Builder $query, string $value): Builder
    {
        $value = trim($value);
        if ($value === '') {
            return $query;
        }

        if (preg_match('/^(VR00|BAW|VRR445|DS020)(\d+)$/i', $value, $matches)) {
            $column = match (strtoupper($matches[1])) {
                'VR00' => 'vrv_1', 'BAW' => 'vrv_2', 'VRR445' => 'vrv_3', default => 'vrv_4',
            };
            $id = DB::table('vr_numbers')->where($column, $matches[2])->value('order_id');

            return $query->where('orders.id', $id ?? 0);
        }

        $transliterated = self::transliterate($value);
        $clients = DB::table('users')->select('id')->where(function ($query) use ($value, $transliterated): void {
            $query->where('email', $value)
                ->orWhere('first_name', 'like', '%'.$value.'%')->orWhere('first_name', 'like', '%'.$transliterated.'%')
                ->orWhere('last_name', 'like', '%'.$value.'%')->orWhere('last_name', 'like', '%'.$transliterated.'%')
                ->orWhere('phone', 'like', '%'.$value.'%')->orWhere('address', 'like', '%'.$value.'%')
                ->orWhere('postal_index', $value)->orWhere('country', $value);
            if (ctype_digit($value)) {
                $query->orWhere('id', (int) $value);
            }
        });
        $deliveryClients = DB::table('orders')->select('user_id')->where(function ($query) use ($value, $transliterated): void {
            $query->whereJsonContains('delivery->email', $value);
            foreach (['first_name', 'last_name', 'phone', 'address', 'postal_index', 'comment'] as $field) {
                $query->orWhereJsonContains('delivery->'.$field, $value);
            }
            foreach (['first_name', 'last_name'] as $field) {
                $query->orWhereJsonContains('delivery->'.$field, $transliterated);
            }
            // Legacy also searches prices; avoid MySQL coercing unrelated text to a zero-price match.
            if (is_numeric($value)) {
                $query->orWhere('price', $value)->orWhere('sale_price', $value);
            }
        });

        return $query->where(fn (Builder $query): Builder => $query
            ->whereIn('orders.user_id', $clients)->orWhereIn('orders.user_id', $deliveryClients));
    }

    public static function transliterate(string $string): string
    {
        $converter = [

            'а' => 'a', 'б' => 'b', 'в' => 'v',

            'г' => 'g', 'д' => 'd', 'е' => 'e',

            'ё' => 'e', 'ж' => 'zh', 'з' => 'z',

            'и' => 'i', 'й' => 'y', 'к' => 'k',

            'л' => 'l', 'м' => 'm', 'н' => 'n',

            'о' => 'o', 'п' => 'p', 'р' => 'r',

            'с' => 's', 'т' => 't', 'у' => 'u',

            'ф' => 'f', 'х' => 'h', 'ц' => 'c',

            'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',

            'ь' => '\'', 'ы' => 'y', 'ъ' => '\'',

            'э' => 'e', 'ю' => 'yu', 'я' => 'ya',

            'А' => 'A', 'Б' => 'B', 'В' => 'V',

            'Г' => 'G', 'Д' => 'D', 'Е' => 'E',

            'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z',

            'И' => 'I', 'Й' => 'Y', 'К' => 'K',

            'Л' => 'L', 'М' => 'M', 'Н' => 'N',

            'О' => 'O', 'П' => 'P', 'Р' => 'R',

            'С' => 'S', 'Т' => 'T', 'У' => 'U',

            'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',

            'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch',

            'Ь' => '\'', 'Ы' => 'Y', 'Ъ' => '\'',

            'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',

        ];

        return strtr($string, $converter);
    }
}
