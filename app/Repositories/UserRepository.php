<?php

namespace App\Repositories;

use App\Models\User;
use DB;

class UserRepository
{
    public function getForAdminMailSender()
    {
        return User::when(request()->has('user_types') && !is_null(request()->get('user_types')), function ($query) {
            return $query->whereIn('type_id', request()->get('user_types'));
        })
            ->when(request()->has('locales') && !is_null(request()->get('locales')), function ($query) {
                return $query->whereIn('settings->locale', request()->get('locales'));
            })
            ->when(request()->has('users') && !is_null(request()->get('users')), function ($query) {
                return $query->whereIn('id', request()->get('users'));
            })
            ->get();
    }

    public function generateCouponUser($email): ?string
    {
        $user = User::where('email', $email)->first();
        if ($user) {
            $rand_code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz';
            $coupon_code = mb_substr(str_shuffle($rand_code), 0, 10);
            DB::table('coupons')->insert([
                [
                    'text'                => $coupon_code,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                    'value'               => "5%",
                    'is_active'           => 1,
                    'is_multiuse'         => 0,
                    'user_id'             => $user->id,
                    'is_abandoned_basket' => true,
                ]
            ]);
            return $coupon_code;
        }
        return null;
    }

    public function generateCouponUserGiftCard($email, $value, $order_id, $type = false): ?string
    {
        $user = User::where('email', $email)->first();
        if ($user) {
            $rand_code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz';
            $coupon_code = mb_substr(str_shuffle($rand_code), 0, 10);
            DB::table('coupons')->insert([
                [
                    'text'                => $coupon_code,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                    'value'               => "$value",
                    'user_id'             => $user->id,
                    'order_id'             => $order_id,
                    'is_giftcard' => true,
                    'is_offline' => $type,
                ]
            ]);
            return $coupon_code;
        }
        return null;
    }
}
