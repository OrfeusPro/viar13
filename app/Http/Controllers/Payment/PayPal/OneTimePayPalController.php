<?php

namespace App\Http\Controllers\Payment\PayPal;

use App\Http\Controllers\Controller;
use App\Models\Orders;
use App\Services\SynvolveWebhookService;
use App\Services\Payment\PayPal\OneTimePayPalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

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

        if (app(OneTimePayPalService::class)->checkPayment($request->token)) {
            $order->payment_status = "payed";
            $order->save();
            app(SynvolveWebhookService::class)->notifyOrderSnapshotById((int) $order->id, 'payment_status_changed_paypal');

            return Redirect::route('thanks', ['order_id' => $order->id, 'payed' => true]);
        } else {
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
