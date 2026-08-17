<?php

namespace App\Http\Controllers\Payment\PayPal;

use App\Http\Controllers\Controller;
use App\Models\Orders;
use App\Services\Payment\PayPalCaptureService;
use App\Services\Payment\PayPal\OneTimePayPalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Throwable;

class OneTimePayPalController extends Controller
{
    public function pay_accept(Request $request)
    {
        if (! $request->filled('token')) {
            return Redirect::route('cart.index')->with('error', __('Payment reference is missing.'));
        }

        $order = Orders::where('billing_invoice_uuid', $request->token)->first();
        if (! $order) {
            return Redirect::route('cart.index')->with('error', __('Payment order was not found.'));
        }

        if ($order->payment_status === 'payed') {
            return Redirect::route('thanks', ['order_id' => $order->id, 'payed' => true]);
        }

        $captureResult = app(OneTimePayPalService::class)->capturePayment($request->token);
        if (! $captureResult) {
            return Redirect::route('thanks', ['order_id' => $order->id]);
        }

        try {
            app(PayPalCaptureService::class)->confirmOrder($order, $captureResult);

            return Redirect::route('thanks', ['order_id' => $order->id, 'payed' => true]);
        } catch (Throwable $exception) {
            Log::warning('PayPal capture was rejected.', [
                'order_id' => $order->id,
                'message' => $exception->getMessage(),
            ]);

            return Redirect::route('thanks', ['order_id' => $order->id]);
        }
    }

    public function pay_cancel(Request $request)
    {
        if (! $request->filled('token')) {
            return Redirect::route('cart.index')->with('error', __('Payment reference is missing.'));
        }

        $order = Orders::where('billing_invoice_uuid', $request->token)->first();
        if (! $order) {
            return Redirect::route('cart.index')->with('error', __('Payment order was not found.'));
        }

        return Redirect::route('thanks', ['order_id' => $order->id]);
    }
}
