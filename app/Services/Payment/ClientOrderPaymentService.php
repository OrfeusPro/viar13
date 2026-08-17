<?php

namespace App\Services\Payment;

use App\Http\Controllers\Libwebtopay\PayseraController;
use App\Models\Orders;
use App\Services\Payment\PayPal\OneTimePayPalService;

class ClientOrderPaymentService
{
    public function __construct(private readonly OrderPaymentAmountCalculator $amountCalculator)
    {
    }

    public function start(Orders $order, string $paymentMethod)
    {
        $delivery = json_decode($order->delivery, true) ?: [];
        $items = json_decode($order->items, true) ?: [];

        $delivery['payment'] = $paymentMethod;
        $order->payment = $paymentMethod;
        $order->delivery = json_encode($delivery, JSON_UNESCAPED_UNICODE);
        $order->save();

        $payload = $this->buildGatewayPayload($order, $delivery, $items, $paymentMethod);

        if ($paymentMethod === 'paypalOnetimePayment') {
            return app(OneTimePayPalService::class)->getRequisites($payload);
        }

        return PayseraController::index($payload);
    }

    private function buildGatewayPayload(Orders $order, array $delivery, array $items, string $paymentMethod): array
    {
        $discountedSubtotal = $this->resolveDiscountedSubtotal($order);
        $termsPrice = $this->resolveTermsPrice($items);
        $deliveryPrice = $this->toFloat($delivery['deliv_price'] ?? 0);
        $total = $this->amountCalculator->inCents($order) / 100;

        $user = auth()->user();

        return [
            'order_id' => $order->id,
            'country' => strtoupper((string) ($delivery['country'] ?? $order->country ?? 'LV')),
            'name' => (string) ($delivery['first_name'] ?? ($user->first_name ?? '')),
            'last_name' => (string) ($delivery['last_name'] ?? ($user->last_name ?? '')),
            'email' => (string) ($delivery['email'] ?? ($user->email ?? '')),
            'payment' => $paymentMethod,
            'deliv_price' => number_format($deliveryPrice, 2, '.', ''),
            'terms_price' => number_format($termsPrice, 2, '.', ''),
            'totalPrice' => number_format($discountedSubtotal, 2, '.', ''),
            'payseraTotalPrice' => number_format($total, 2, '', ''),
            'paypalTotalPrice' => number_format($total, 2, '.', ''),
        ];
    }

    private function resolveDiscountedSubtotal(Orders $order): float
    {
        $originalSubtotal = $this->toFloat($order->price ?? 0);
        $saleSubtotalRaw = $order->sale_price ?? null;
        $saleSubtotal = ($saleSubtotalRaw !== null && $saleSubtotalRaw !== '')
            ? $this->toFloat($saleSubtotalRaw)
            : null;

        $baseSubtotal = $originalSubtotal;
        if ($saleSubtotal !== null && $saleSubtotal > 0 && $saleSubtotal < $originalSubtotal) {
            $baseSubtotal = $saleSubtotal;
        }

        $discountedSubtotal = $baseSubtotal;

        $saleEur = $this->toFloat($order->sale_eur ?? 0);
        if ($saleEur > 0) {
            $discountedSubtotal -= $saleEur;
        }

        $salePercent = $this->toFloat($order->sale_percent ?? 0);
        if ($salePercent > 0) {
            $discountedSubtotal -= $discountedSubtotal * ($salePercent / 100);
        }

        if ($discountedSubtotal < 0) {
            $discountedSubtotal = 0;
        }

        return round($discountedSubtotal, 2);
    }

    private function resolveTermsPrice(array $items): float
    {
        if (isset($items['total_terms_price'])) {
            return round($this->toFloat($items['total_terms_price']), 2);
        }

        $termsPrice = 0.0;
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $termsPrice += $this->toFloat($item['terms_price'] ?? 0);
        }

        return round($termsPrice, 2);
    }

    private function toFloat($value): float
    {
        if (is_float($value) || is_int($value)) {
            return (float) $value;
        }

        $value = preg_replace('/[^0-9,\\.\\-]/', '', (string) $value);
        $value = str_replace(',', '.', (string) $value);

        return (float) $value;
    }
}
