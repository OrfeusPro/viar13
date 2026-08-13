<?php

namespace App\Console\Commands;

use App;
use App\Mail\AbandonedCartMailTwelve;
use App\Models\AbandonedCart;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAbandonedCartsEmailsCoupons extends Command
{
    protected $signature = 'cart:send-recovery-emails-coupons';
    protected $description = 'Отправляет email с купоном пользователям которые не обновляли корзину больше 24 часов';

    public function handle()
    {
        $userRepository = app(UserRepository::class);
        $carts = AbandonedCart::where('updated_at', '<', Carbon::now()->subHours(24))
            ->where('is_send_email_twelve_hours', true)
            ->where('is_coupon_sent', false)
            ->whereNotNull('recovery_token')
            ->where('cart_data', "!=", '[[]]')
            ->where('cart_data', "!=", '[]')
            ->where('cart_data', "!=", null)
            ->get()
            ->reject(function (AbandonedCart $cart) {
                return $cart->hasPaidOrderAfterUpdate();
            });

        $sentCount = 0;

        foreach ($carts as $cart) {
            App::setLocale($cart->locale);

            if ($cart->email) {
                $coupon = $userRepository->generateCouponUser($cart->email);
                $token = $cart->recovery_token;
                $url_cart = route('cart.recover', ['redirect' => 'cart', 'token' => $token]);
                $url_checkout = route('cart.recover', ['redirect' => 'checkout', 'token' => $token]);

                try {
                    Mail::to($cart->email)->queue(new AbandonedCartMailTwelve($url_cart, $url_checkout, $cart, $coupon));
                    $cart->is_coupon_sent = true;
                    $cart->save();
                    $sentCount++;
                } catch (Exception $exception) {
                    Log::error('Ошибка отправки письма о забытой корзине с купоном. Адресат - ' . $cart->email);
                }
            }
        }
        Log::info("Отправлено $sentCount email(ов) пользователям с заброшенной корзиной с купоном.");
        $this->info("Отправлено $sentCount email(ов) пользователям с заброшенной корзиной с купоном.");
    }
}
