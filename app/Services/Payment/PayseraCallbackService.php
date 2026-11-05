<?php

namespace App\Services\Payment;

use App\Http\Controllers\Libwebtopay\WebToPay;
use App\Models\OrderPaymentRequest;
use App\Models\Orders;
use App\Services\SynvolveWebhookService;
use DomainException;
use Illuminate\Support\Facades\DB;

class PayseraCallbackService
{
    public function __construct(private readonly OrderPaymentAmountCalculator $amountCalculator) {}

    public function confirmOrder(array $response): Orders
    {
        $orderId = $this->requiredOrderId($response);
        $changed = false;

        $order = DB::transaction(function () use ($orderId, $response, &$changed): Orders {
            $order = Orders::query()->lockForUpdate()->find($orderId);
            if (! $order) {
                throw new DomainException('Payment order was not found.');
            }

            $this->validateSuccessfulResponse($response);
            $this->validateGatewayMethod((string) $order->payment);
            $this->validateAmountAndCurrency(
                $this->amountCalculator->inCents($order),
                'EUR',
                $response
            );

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
                'payment_status_changed_paysera'
            );
        }

        return $order;
    }

    public function confirmPaymentRequest(array $response): OrderPaymentRequest
    {
        $publicNumber = trim((string) ($response['orderid'] ?? ''));
        if ($publicNumber === '') {
            throw new DomainException('Payment request reference is missing.');
        }

        return DB::transaction(function () use ($publicNumber, $response): OrderPaymentRequest {
            $paymentRequest = OrderPaymentRequest::query()
                ->where('public_number', $publicNumber)
                ->lockForUpdate()
                ->first();

            if (! $paymentRequest) {
                throw new DomainException('Payment request was not found.');
            }

            $this->validateSuccessfulResponse($response);
            $this->validateGatewayMethod((string) $paymentRequest->selected_payment_method);
            $this->validateAmountAndCurrency(
                $this->amountCalculator->decimalInCents($paymentRequest->amount),
                (string) $paymentRequest->currency,
                $response
            );

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

    private function validateSuccessfulResponse(array $response): void
    {
        if (! in_array((string) ($response['status'] ?? ''), ['1', '3'], true)) {
            throw new DomainException('Payment was not successful.');
        }
    }

    private function validateGatewayMethod(string $paymentMethod): void
    {
        if (! array_key_exists($paymentMethod, WebToPay::PAYSERA_METHODS_MAP)) {
            throw new DomainException('Order is not configured for Paysera payment.');
        }
    }

    private function validateAmountAndCurrency(int $expectedAmount, string $expectedCurrency, array $response): void
    {
        $usesPaidAmount = array_key_exists('payamount', $response);
        $amountKey = $usesPaidAmount ? 'payamount' : 'amount';
        $currencyKey = $usesPaidAmount ? 'paycurrency' : 'currency';
        $actualAmount = $response[$amountKey] ?? null;
        $actualCurrency = strtoupper(trim((string) ($response[$currencyKey] ?? '')));

        if (! is_scalar($actualAmount) || ! preg_match('/^\d+$/', (string) $actualAmount)) {
            throw new DomainException('Payment amount is missing or invalid.');
        }

        if ((int) $actualAmount !== $expectedAmount
            || $actualCurrency !== strtoupper(trim($expectedCurrency))) {
            throw new DomainException('Wrong payment amount or currency.');
        }
    }

    private function requiredOrderId(array $response): int
    {
        $orderId = $response['orderid'] ?? null;
        if (! is_scalar($orderId) || ! preg_match('/^[1-9]\d*$/', (string) $orderId)) {
            throw new DomainException('Payment order reference is invalid.');
        }

        return (int) $orderId;
    }
}
