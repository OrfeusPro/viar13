<?php

namespace App\Console\Commands;

use App;
use App\Mail\AbandonedCartMail;
use App\Models\AbandonedCart;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAbandonedCartsEmails extends Command
{
    protected $signature = 'cart:send-recovery-emails';
    protected $description = 'Отправляет email пользователям которые не обновляли корзину больше 12 часов';

    public function handle()
    {
        $staleDays = 7;

        $deleted = 0;
        AbandonedCart::query()
            ->where('updated_at', '<', Carbon::now()->subDays($staleDays))
            ->orderBy('id')
            ->chunkById(500, function ($carts) use (&$deleted) {
                foreach ($carts as $cart) {
                    try {
                        $cart->delete();
                        $deleted++;
                    } catch (\Throwable $e) {
                        \Log::warning('Не удалось удалить брошенную корзину id='.$cart->id.' : '.$e->getMessage());
                    }
                }
            });

        Log::info("Cleanup: удалено {$deleted} брошенных корзин старше {$staleDays} дн.");
        $this->info("Cleanup: удалено {$deleted} брошенных корзин старше {$staleDays} дн.");


        $now = Carbon::now();
        $carts = AbandonedCart::where('updated_at', '<', $now->subHours(12))
            ->whereNull('recovery_token')
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
                $token = $cart->generateRecoveryToken();
                $url_cart = route('cart.recover', ['redirect' => 'cart', 'token' => $token]);
                $url_checkout = route('cart.recover', ['redirect' => 'checkout', 'token' => $token]);

                try {
                    Mail::to($cart->email)->queue(new AbandonedCartMail($url_cart, $url_checkout, $cart));
                    $sentCount++;
                } catch (Exception $exception) {
                    Log::error('Ошибка отправки письма о забытой корзине. Адресат - ' . $cart->email);
                }
            }
        }
        Log::info("Отправлено $sentCount email(ов) пользователям с заброшенной корзиной.");
        $this->info("Отправлено $sentCount email(ов) пользователям с заброшенной корзиной.");
    }
}
