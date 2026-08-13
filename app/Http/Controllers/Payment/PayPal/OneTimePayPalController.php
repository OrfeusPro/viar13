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
        $order = Orders::where('billing_invoice_uuid', $request->token)->first();

        if ((new OneTimePayPalService)->checkPayment($request->token)) {
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
        $order = Orders::where('billing_invoice_uuid', $request->token)->first();

        return Redirect::route('thanks', ['order_id' => $order->id]);
    }
}
