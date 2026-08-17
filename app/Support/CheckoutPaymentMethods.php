<?php

namespace App\Support;

use App\Http\Controllers\Libwebtopay\WebToPay;

final class CheckoutPaymentMethods
{
    public const ON_DELIVERY = 'on_delivery';
    public const PAYPAL = 'paypalOnetimePayment';
    public const TRANSFER = 'transfer';
    public const PREPAYMENT = 'prepayment';

    public static function all(): array
    {
        return array_merge(array_keys(WebToPay::PAYSERA_METHODS_MAP), [
            self::ON_DELIVERY,
            self::PAYPAL,
            self::TRANSFER,
            self::PREPAYMENT,
        ]);
    }

    public static function isAllowedForDelivery(string $method, ?array $delivery): bool
    {
        if (! in_array($method, self::all(), true)) {
            return false;
        }

        if (empty($delivery['delivery_type']) || empty($delivery['country'])) {
            return false;
        }

        if ($method !== self::ON_DELIVERY) {
            return true;
        }

        return ($delivery['delivery_type'] ?? null) === 'to_the_door'
            && in_array($delivery['country'] ?? null, ['LV', 'LT', 'EE'], true);
    }
}
