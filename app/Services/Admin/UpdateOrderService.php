<?php

namespace App\Services\Admin;

use App\Models\Orders;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UpdateOrderService
{
    public const STATUSES = [
        'new', 'watching', 'pegging', 'in_production', 'sended',
        'send_lubanas', 'completed',
    ];

    public const PAYMENT_STATUSES = ['not_payed', 'prepayment', 'payed'];

    public function update(Orders $order, array $data): Orders
    {
        validator($data, [
            'status' => ['required', Rule::in(self::STATUSES)],
            'payment_status' => ['required', Rule::in(self::PAYMENT_STATUSES)],
            'manager_id' => ['nullable', 'integer', 'exists:users,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'prepayment_price' => ['nullable', 'numeric', 'min:0'],
            'admin_comment' => ['nullable', 'string', 'max:10000'],
        ])->validate();

        return DB::transaction(function () use ($order, $data): Orders {
            $attributes = Arr::only($data, [
                'manager_id', 'status', 'payment_status', 'payment', 'price',
                'sale_price', 'prepayment_price', 'admin_comment',
            ]);

            if (($attributes['status'] ?? $order->status) !== $order->status) {
                $dateColumn = match ($attributes['status']) {
                    'watching' => 'watching_date',
                    'pegging' => 'pegging_date',
                    'in_production' => 'in_production_date',
                    'sended' => 'send_date',
                    'send_lubanas' => 'send_lubanas_date',
                    'completed' => 'completed_date',
                    default => 'status_date',
                };
                $attributes[$dateColumn] = now();
                $attributes['status_date'] = now();
            }

            $order->forceFill($attributes)->save();

            return $order->refresh();
        });
    }
}
