<?php

namespace App\Http\Controllers;

use App\Models\AbandonedCart;
use App\Models\User;
use App\Repositories\BasketRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbandonedCartController extends Controller
{
    /**
     * Відновлює кошик за токеном
     */
    public function processRecovery($redirect, $token)
    {
        $redirect = $redirect == 'cart' ? 'cart.index' : 'cart.step2';
        $cart = AbandonedCart::where('recovery_token', $token)
            ->where('token_expires_at', '>', now())
            ->first();

        if (!$cart) {
            return redirect()->route('home')->with('error', __('cart_new.token_is_invalid_or_expired'));
        }

        $user = User::where('email', $cart->email)->first();

        if (!$user) {
            session()->put('email', $cart->email);
        } elseif (!Auth::check()) {
            Auth::login($user);
        }

        $basketRepository = resolve(BasketRepository::class);
        $normalized = $basketRepository->normalizeBasket($cart->cart_data);
        session()->put('basket', $normalized);

        if ($cart->cart_data !== $normalized) {
            $cart->cart_data = $normalized;
            $cart->save();
        }

        $cart->clearRecoveryToken();

        return redirect()->route($redirect)->with('success', __('cart_new.cart_has_been_restored'));
    }

    public function setEmail(Request $request)
    {
        request()->session()->put('email', $request->get('email'));
        return redirect()->route('cart.index');
    }
}
