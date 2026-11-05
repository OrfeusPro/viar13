<?php

namespace App\Services\Payment;

use App\Models\Orders;
use DomainException;

class OrderPaymentAmountCalculator
{
    public function inCents(Orders $order): int
    {
        $items = json_decode((string) $order->items, true);
        $delivery = json_decode((string) $order->delivery, true);

        $originalAmount = $this->decimalInCents($order->price ?? 0);
        $saleAmount = $order->sale_price !== null && $order->sale_price !== ''
            ? $this->decimalInCents($order->sale_price)
            : null;
        $baseAmount = $saleAmount !== null && $saleAmount > 0 && $saleAmount < $originalAmount
            ? $saleAmount
            : $originalAmount;

        $baseAmount -= $this->decimalInCents($order->sale_eur ?? 0);
        $salePercent = (float) ($order->sale_percent ?? 0);
        if ($salePercent > 0) {
            $baseAmount -= (int) round($baseAmount * ($salePercent / 100));
        }

        $termsAmount = is_array($items)
            ? $this->termsAmountInCents($items)
            : 0;
        $deliveryAmount = is_array($delivery) ? ($delivery['deliv_price'] ?? 0) : 0;

        return max(0, $baseAmount)
            + $termsAmount
            + $this->decimalInCents($deliveryAmount);
    }

    public function decimalInCents($amount): int
    {
        $amount = trim((string) $amount);
        if (! preg_match('/^\d+(?:\.\d{1,2})?$/', $amount)) {
            throw new DomainException('Stored payment amount is invalid.');
        }

        [$units, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        return ((int) $units * 100) + (int) str_pad($fraction, 2, '0');
    }

    private function termsAmountInCents(array $items): int
    {
        if (array_key_exists('total_terms_price', $items)) {
            return $this->decimalInCents($items['total_terms_price']);
        }

        $total = 0;
        foreach ($items as $item) {
            if (is_array($item)) {
                $total += $this->decimalInCents($item['terms_price'] ?? 0);
            }
        }

        return $total;
    }
}
