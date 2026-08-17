<?php

namespace App\Services\Payment;

use App\Models\OrderPaymentRequest;
use App\Models\Orders;
use App\Services\SynvolveWebhookService;
use DomainException;
use Illuminate\Support\Facades\DB;

class PayPalCaptureService
{
    public function __construct(private readonly OrderPaymentAmountCalculator $amountCalculator) {}

    public function confirmOrder(Orders $sourceOrder, object $captureResult): Orders
    {
        $changed = false;

        $order = DB::transaction(function () use ($sourceOrder, $captureResult, &$changed): Orders {
            $order = Orders::query()->lockForUpdate()->find($sourceOrder->id);
            if (! $order) {
                throw new DomainException('Payment order was not found.');
            }

            $this->validateCapture(
                $captureResult,
                (string) $order->id,
                $this->amountCalculator->inCents($order),
                'EUR'
            );

            if ((string) $order->payment !== 'paypalOnetimePayment') {
                throw new DomainException('Order is not configured for PayPal payment.');
            }

            if ($order->payment_status === 'payed') {
                return $order;
            }

            if ($order->payment_status !== 'not_payed') {
                throw new DomainException('Order payment status transition is not allowed.');
            }

            $order->payment_status = 'payed';
            $order->save();
            $changed = true;

            return $order;
        });

        if ($changed) {
            app(SynvolveWebhookService::class)->notifyOrderSnapshotById(
                (int) $order->id,
                'payment_status_changed_paypal'
            );
        }

        return $order;
    }

    public function confirmPaymentRequest(
        OrderPaymentRequest $sourcePaymentRequest,
        object $captureResult
    ): OrderPaymentRequest {
        return DB::transaction(function () use ($sourcePaymentRequest, $captureResult): OrderPaymentRequest {
            $paymentRequest = OrderPaymentRequest::query()
                ->lockForUpdate()
                ->find($sourcePaymentRequest->id);

            if (! $paymentRequest) {
                throw new DomainException('Payment request was not found.');
            }

            $this->validateCapture(
                $captureResult,
                (string) $paymentRequest->public_number,
                $this->amountCalculator->decimalInCents($paymentRequest->amount),
                (string) $paymentRequest->currency
            );

            if ((string) $paymentRequest->selected_payment_method !== 'paypalOnetimePayment') {
                throw new DomainException('Payment request is not configured for PayPal payment.');
            }

            if ($paymentRequest->status === OrderPaymentRequest::STATUS_PAID) {
                return $paymentRequest;
            }

            if ($paymentRequest->status !== OrderPaymentRequest::STATUS_PENDING) {
                throw new DomainException('Payment request status transition is not allowed.');
            }

            $paymentRequest->status = OrderPaymentRequest::STATUS_PAID;
            $paymentRequest->paid_at = now();
            $paymentRequest->save();

            return $paymentRequest;
        });
    }

    private function validateCapture(
        object $captureResult,
        string $expectedReference,
        int $expectedAmount,
        string $expectedCurrency
    ): void {
        if ((string) data_get($captureResult, 'status') !== 'COMPLETED') {
            throw new DomainException('PayPal order was not completed.');
        }

        $purchaseUnits = data_get($captureResult, 'purchase_units');
        if (! is_array($purchaseUnits) || count($purchaseUnits) !== 1) {
            throw new DomainException('Unexpected PayPal purchase units.');
        }

        $purchaseUnit = $purchaseUnits[0];
        if ((string) data_get($purchaseUnit, 'reference_id') !== $expectedReference) {
            throw new DomainException('Wrong PayPal order reference.');
        }

        $captures = data_get($purchaseUnit, 'payments.captures');
        if (! is_array($captures) || count($captures) !== 1) {
            throw new DomainException('Unexpected PayPal captures.');
        }

        $capture = $captures[0];
        if ((string) data_get($capture, 'status') !== 'COMPLETED') {
            throw new DomainException('PayPal capture was not completed.');
        }

        $currency = strtoupper(trim((string) data_get($capture, 'amount.currency_code')));
        $amount = data_get($capture, 'amount.value');
        if ($this->amountCalculator->decimalInCents($amount) !== $expectedAmount
            || $currency !== strtoupper(trim($expectedCurrency))) {
            throw new DomainException('Wrong PayPal payment amount or currency.');
        }
    }
}
