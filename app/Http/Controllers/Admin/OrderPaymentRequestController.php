<?php

namespace App\Http\Controllers\Admin;

use App\Models\Orders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\Payment\OrderPaymentRequestService;

class OrderPaymentRequestController extends Controller
{
    private $orderPaymentRequestService;

    public function __construct()
    {
        $this->orderPaymentRequestService = app(OrderPaymentRequestService::class);
    }

    public function store(Request $request, $orderId)
    {
        abort_unless(Auth::check(), 403);
        abort_unless(in_array(Auth::user()->role->name, ['admin', 'manager'], true), 403);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'purpose' => ['nullable', 'string', 'max:255'],
        ]);

        $order = Orders::findOrFail((int) $orderId);
        $paymentRequest = $this->orderPaymentRequestService->createForOrder($order, $validated, Auth::user());

        $redirectTo = trim((string) $request->input('redirect_to'));
        if ($redirectTo !== '') {
            return redirect()
                ->to($redirectTo)
                ->with('payment_request_created_link', $paymentRequest->publicUrl())
                ->with('payment_request_created_number', $paymentRequest->public_number)
                ->with('payment_request_created_order_id', (int) $order->id);
        }

        return redirect()
            ->route('edit_admin_order', $order->id)
            ->with('payment_request_created_link', $paymentRequest->publicUrl())
            ->with('payment_request_created_number', $paymentRequest->public_number)
            ->with('payment_request_created_order_id', (int) $order->id);
    }
}
