<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPaymentRequest extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';

    protected $fillable = [
        'order_id',
        'user_id',
        'created_by',
        'public_number',
        'token',
        'amount',
        'currency',
        'purpose',
        'status',
        'selected_payment_method',
        'billing_invoice_uuid',
        'customer_email',
        'customer_first_name',
        'customer_last_name',
        'customer_country',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Orders::class, 'order_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function resolveLocale(): string
    {
        $country = strtoupper(trim((string) $this->customer_country));
        $map = [
            'LV' => 'lv',
            'LT' => 'lt',
            'EE' => 'ee',
            'ET' => 'ee',
            'PL' => 'pl',
            'DE' => 'de',
            'EN' => 'en',
            'GB' => 'en',
            'RU' => 'ru',
        ];

        if ($country && isset($map[$country])) {
            return $map[$country];
        }

        $customer = $this->relationLoaded('customer') ? $this->customer : $this->customer()->first();
        if ($customer && is_string($customer->preferredLocale()) && $customer->preferredLocale() !== '') {
            return $customer->preferredLocale();
        }

        $fallback = app()->getLocale();

        return is_string($fallback) && $fallback !== '' ? $fallback : 'ru';
    }

    public function publicUrl(?string $locale = null): string
    {
        $locale = trim((string) ($locale ?: $this->resolveLocale()), '/');

        return url($locale . '/payment-request/' . $this->token);
    }
}
