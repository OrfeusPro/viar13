<?php

namespace App\Http\Controllers;

use DB;
use App;
use Carbon\Carbon;
use App\Models\User;
use App\Models\AbandonedCart;
use App\Mail\AbandonedCartMail;
use App\Repositories\UserRepository;
use App\Mail\AbandonedCartMailTwelve;
use App\Mail\SendUserYourOrderGiven;

class MailController extends Controller
{
    public function index()
    {
        dd(config('session.driver'), config('session.lifetime'));
        // $userRepository = app(UserRepository::class);
        // $now = Carbon::now();
        // $from = $now->copy()->subHours(27);
        // $to = $now->copy()->subHours(24);

        // $carts = AbandonedCart::whereBetween('created_at', [$from, $to])
        //     ->where('is_send_email_twelve_hours', true) // ті, кому вже надсилали перший лист
        //     ->get();

        // $sentCount = 0;

        // foreach ($carts as $cart) {
        //     App::setLocale($cart->locale);

        //     if ($cart->email) {
        //         // Генеруємо токен та посилання для відновлення
        //         $coupon = $userRepository->generateCouponUser($cart->email);
        //         $token = $cart->recovery_token;
        //         $url_cart = route('cart.recover', ['redirect' => 'cart', 'token' => $token]);
        //         $url_checkout = route('cart.recover', ['redirect' => 'checkout', 'token' => $token]);

        //         return new AbandonedCartMailTwelve($url_cart, $url_checkout, $cart, $coupon);
        //     }
        // }
    }

    public function abandoned_cart24()
    {
        $userRepository = app(UserRepository::class);
        $now = Carbon::now();
        // $from = $now->copy()->subHours(27);
        // $to = $now->copy()->subHours(24);

        // $carts = AbandonedCart::whereBetween('created_at', [$from, $to])
        //     ->where('is_send_email_twelve_hours', true) // ті, кому вже надсилали перший лист
        //     ->get();
        $carts = AbandonedCart::where('email', 'v.heart.breaking@gmail.com')
        ->get();

        $sentCount = 0;

        foreach ($carts as $cart) {
            App::setLocale($cart->locale);

            if ($cart->email) {
                // Генеруємо токен та посилання для відновлення
                $coupon = $userRepository->generateCouponUser($cart->email);
                $token = $cart->recovery_token;
                $url_cart = route('cart.recover', ['redirect' => 'cart', 'token' => $token]);
                $url_checkout = route('cart.recover', ['redirect' => 'checkout', 'token' => $token]);

                return new AbandonedCartMailTwelve($url_cart, $url_checkout, $cart, $coupon);
            }
        }
    }

    public function abandoned_cart()
    {
        $now = Carbon::now();
        $carts = AbandonedCart::where('email', 'v.heart.breaking@gmail.com')
        ->get();

        $sentCount = 0;

        foreach ($carts as $cart) {
            App::setLocale($cart->locale);

            if ($cart->email) {
                // Генеруємо токен та посилання для відновлення
                $token = $cart->generateRecoveryToken();
                $url_cart = route('cart.recover', ['redirect' => 'cart', 'token' => $token]);
                $url_checkout = route('cart.recover', ['redirect' => 'checkout', 'token' => $token]);

                return new AbandonedCartMail($url_cart, $url_checkout, $cart);
            }
        }
    }

    public function order()
    {
        $orderId = 15868;
        $user = User::where('email', 'v.heart.breaking@gmail.com')->first();
        $order = DB::table('orders')->where('id', $orderId)->first();

        $basket = json_decode($order->items, true) ?? [];
        $locale = 'ru';

        return new SendUserYourOrderGiven($basket, $user, $orderId, $locale);
    }



    public function gift_cart()
    {

        $log= '';
        $user = User::where('email', "v.heart.breaking@gmail.com")->first();
        $user_locale = strtolower($user->country);
        $user_locale = "en";

        $data['subject']=__('pages.gift_card');
        $data['first_name']=$user->first_name;
        $data['user']=$user;
        $data['order_id']="13160";

        // $sended= \Mail::to($coupon->email)->send(new \App\Mail\GiftCard($data, $user_locale));
        
        // if ($sended) {
        //     $log .= 'Пользователь: ' . $coupon->email . ' Получил макет с предолжением на 2 даты<br> |';
        // } else {
        //     $log .= 'Пользователь: ' . $coupon->email . ' Ошибка отправки email-а<br> |';
        // }

        // \DB::table('order_action')->insert(
        //     [
        //         'user'     => $coupon->email,
        //         'activity' => $log,
        //     ]
        // );

        return new \App\Mail\GiftCard($data, $user_locale);
        // Mail::to("v.heart.breaking@gmail.com")->send(new \App\Mail\Payment_successful($_GET['order_id'], $user_locale));
    }
}
