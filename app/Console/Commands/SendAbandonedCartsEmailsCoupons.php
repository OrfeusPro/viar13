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
            try {
                $sent = \DB::transaction(function () use ($cart, $userRepository) {
                    $cart = AbandonedCart::where('id', $cart->id)->lockForUpdate()->first();
                    if (!$cart || $cart->is_coupon_sent || !$cart->is_send_email_twelve_hours || !$cart->recovery_token ||
                        $cart->updated_at >= Carbon::now()->subHours(24) ||
                        $cart->hasPaidOrderAfterUpdate()) { return false; }
                    App::setLocale($cart->locale);
                    $eventKey = hash('sha256', $cart->id.':'.$cart->recovery_token);
                    $coupon = $userRepository->generateCouponUser($cart->email, $eventKey);
                    if (!$coupon) { return false; }
                    $url_cart = route('cart.recover', ['redirect' => 'cart', 'token' => $cart->recovery_token]);
                    $url_checkout = route('cart.recover', ['redirect' => 'checkout', 'token' => $cart->recovery_token]);
                    Mail::to($cart->email)->queue((new AbandonedCartMailTwelve($url_cart, $url_checkout, $cart, $coupon))->afterCommit());
                    $cart->is_coupon_sent = true;
                    $cart->save();
                    return true;
                });
                if ($sent) { $sentCount++; }
            } catch (Exception $exception) {
                Log::error('Abandoned cart coupon email failed', ['cart_id' => $cart->id,
                    'exception' => get_class($exception)]);
            }
        }
        Log::info("Отправлено $sentCount email(ов) пользователям с заброшенной корзиной с купоном.");
        $this->info("Отправлено $sentCount email(ов) пользователям с заброшенной корзиной с купоном.");
    }
}
