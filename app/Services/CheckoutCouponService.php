<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CheckoutCouponService
{
    public function inspect($coupon, string $email, $user = null): array
    {
        if (! $coupon) {
            return $this->failure('not_found');
        }
        if (! (bool) $coupon->is_active) {
            return $this->failure('inactive');
        }
        $type = 'friend';
        foreach (['is_dates_sale' => 'date', 'is_30_40_free' => '30_40',
            'is_universal' => 'universal', 'is_facebook' => 'facebook',
            'is_1free' => '1free', 'free_delivery' => 'free_delivery',
            'is_40_60' => '40_60', 'is_abandoned_basket' => 'abandoned_basket',
            'is_giftcard' => 'giftcard'] as $flag => $name) {
            if (! empty($coupon->$flag)) {
                $type = $name;
            }
        }
        $email = strtolower(trim($user ? $user->email : $email));
        $personal = in_array($type, ['date', 'facebook', 'abandoned_basket', 'friend'], true);
        if ($personal && $email === '') {
            return $this->failure('email_required');
        }
        if (in_array($type, ['date', 'facebook', 'abandoned_basket'], true)) {
            $recipient = $coupon->recipient_email ?? null;
            if (! $recipient && ! empty($coupon->user_id)) {
                $recipient = DB::table('users')->where('id', $coupon->user_id)->value('email');
            }
            if (! $recipient || strtolower(trim($recipient)) !== $email) {
                return $this->failure('recipient_mismatch');
            }
        }
        if ($type === 'friend') {
            $friend = DB::table('users')->where('id', $coupon->user_id)
                ->where('inv_sale_code', $coupon->text)->first();
            $customer = $user ?: User::where('email', $email)->first();
            if (! $friend || strtolower($friend->email) === $email ||
                ($customer && (int) $customer->is_active_friend_inv !== 0)) {
                return $this->failure('referral_unavailable');
            }
        }
        if (in_array($type, ['universal', 'abandoned_basket', 'giftcard'], true) &&
            ! preg_match('/^\d+(?:[.,]\d+)?%?$/', trim((string) $coupon->value))) {
            return $this->failure('invalid_value');
        }
        if ($type === 'giftcard' && strpos((string) $coupon->value, '%') !== false) {
            return $this->failure('invalid_value');
        }

        return ['valid' => true, 'reason' => null, 'type' => $type,
            'provisional' => $personal && ! $user, 'coupon' => $coupon];
    }

    public function failure(string $reason): array
    {
        return ['valid' => false, 'reason' => $reason, 'message' => __('checkout_coupon.'.$reason)];
    }

    public function remember($coupon, array $result): void
    {
        session()->put(['coupon_id' => $coupon->id, 'coupon_type' => $result['type'],
            'coupon_val' => $coupon->value, 'coupon_provisional' => $result['provisional']]);
        session()->forget('coupon_error');
    }

    public function clear(): void
    {
        session()->put(['coupon_id' => 0, 'coupon_type' => 'none', 'coupon_val' => 0]);
        session()->forget('coupon_provisional');
    }

    public function refresh(array $basket): array
    {
        if ((int) session('coupon_id', 0) <= 0) {
            return $basket;
        }
        $coupon = DB::table('coupons')->where('id', session('coupon_id'))->first();
        $result = $this->inspect($coupon, (string) session('email', ''), Auth::user());
        if (! $result['valid']) {
            $this->clear();
            session()->put('coupon_error', $result['message']);
            Log::notice('Checkout coupon rejected', ['coupon_id' => $coupon->id ?? null,
                'reason' => $result['reason']]);
            unset($basket['sale_price']);

            return array_merge($basket, ['coupon_id' => 0, 'coupon_type' => 'none', 'coupon_val' => 0]);
        }
        $this->remember($coupon, $result);

        return array_merge($basket, ['coupon_id' => $coupon->id, 'coupon_type' => $result['type'],
            'coupon_val' => $coupon->value, 'coupon_provisional' => $result['provisional'],
            'sale_price' => $this->calculate($basket, $result['type'], $coupon->value)]);
    }

    public function isPromotional(array $item): bool
    {
        return ! empty($item['has_special_label']) || hasSpecialLabel($item['sizeId'] ?? $item['size_name'] ?? $item['size'] ?? '') || ! empty($item['is_recommendation']) ||
            ! empty($item['is_canvas_recommendation']) || ! empty($item['recommendation_discount']);
    }

    public function calculate(array $basket, string $type, $value): string
    {
        $total = (float) ($basket['totalPrice'] ?? 0);
        $eligible = 0;
        $items = [];
        $count = 0;
        foreach ($basket as $item) {
            if (! is_array($item) || ! isset($item['sumPrice'])) {
                continue;
            }
            $count += (int) ($item['count'] ?? 1);
            if (! $this->isPromotional($item) && (int) ($item['pid'] ?? 0) !== 5) {
                // The size upgrade's extras are never discounted, including by a coupon.
                $eligible += isset($item['checkout_size_discounted_base']) ?
                    min((float) $item['sumPrice'], (float) $item['checkout_size_discounted_base'] * max(1, (int) ($item['count'] ?? 1))) :
                    (float) $item['sumPrice'];
                $items[] = $item;
            }
        }
        $discount = 0;
        if ($type === 'giftcard') {
            // Certificates pay for goods, including promotional goods, not delivery.
            $discount = min($total, (float) str_replace(',', '.', (string) $value));
        } elseif (in_array($type, ['universal', 'abandoned_basket'], true)) {
            $number = (float) str_replace(',', '.', (string) $value);
            $discount = strpos((string) $value, '%') !== false ? $eligible * min(100, $number) / 100 : $number;
            $discount = min($eligible, $discount);
        } elseif ($type === 'date') {
            $percent = $eligible < 21 ? 30 : ($eligible < 31 ? 20 : ($eligible < 100 ? 10 : 5));
            session()->put('sale_dated', $percent);
            $discount = $eligible * $percent / 100;
        } elseif ($type === 'facebook') {
            $discount = $eligible * .02;
        } elseif ($type === 'friend') {
            $discount = min(5, $eligible);
        } elseif (in_array($type, ['30_40', '40_60', '1free'], true)) {
            $largeCanvas = false;
            foreach ($basket as $item) {
                if (is_array($item) && in_array($item['sizeId'] ?? '', ['80x120', '120x80'], true)) {
                    $largeCanvas = true;
                }
            }
            $prices = [];
            foreach ($items as $item) {
                $size = $item['sizeId'] ?? $item['size_name'] ?? '';
                $canvas = ($item['name'] ?? '') === 'Canvas';
                if (($type === '30_40' && $canvas && $size === '30x40') ||
                    ($type === '40_60' && $largeCanvas && $canvas && in_array($size, ['40x60', '60x40'], true)) ||
                    ($type === '1free' && $count >= 4)) {
                    $prices[] = (float) $item['sumPrice'] / max(1, (int) ($item['count'] ?? 1));
                }
            }
            $discount = $prices ? min($prices) : 0;
        }

        return number_format(max(0, $total - min($total, max(0, $discount))), 2, '.', '');
    }

    /** Must be called inside the order transaction; lock remains held until commit. */
    public function confirm(array $basket, User $user): array
    {
        if ((int) ($basket['coupon_id'] ?? 0) <= 0) {
            return $basket;
        }
        $coupon = DB::table('coupons')->where('id', $basket['coupon_id'])->lockForUpdate()->first();
        $result = $this->inspect($coupon, $user->email, $user);
        if (! $result['valid']) {
            Log::notice('Checkout final coupon rejected', ['coupon_id' => $basket['coupon_id'],
                'reason' => $result['reason']]);
            throw ValidationException::withMessages(['coupon' => $result['message']]);
        }
        if (! empty($coupon->recipient_email) && ! (int) $coupon->user_id) {
            DB::table('coupons')->where('id', $coupon->id)->update(['user_id' => $user->id]);
        }

        return array_merge($basket, ['coupon_type' => $result['type'], 'coupon_val' => $coupon->value,
            'coupon_provisional' => false, 'sale_price' => $this->calculate($basket, $result['type'], $coupon->value)]);
    }

    public function consume(array $basket): void
    {
        $coupon = DB::table('coupons')->where('id', $basket['coupon_id'])->lockForUpdate()->first();
        if (! $coupon || ! $coupon->is_active) {
            throw ValidationException::withMessages(['coupon' => __('checkout_coupon.inactive')]);
        }
        if ($basket['coupon_type'] === 'giftcard' || ! $coupon->is_multiuse) {
            // Preserve the row for audit and recovery-event idempotency.
            DB::table('coupons')->where('id', $coupon->id)->update(['is_active' => 0, 'updated_at' => now()]);
        }
    }
}
