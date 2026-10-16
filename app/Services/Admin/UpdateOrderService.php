<?php

namespace App\Services\Admin;

use App\Models\Orders;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateOrderService
{
    public const STATUSES = [
        'new', 'watching', 'pegging', 'in_production', 'sended',
        'send_lubanas', 'completed',
    ];

    public const PAYMENT_STATUSES = ['not_payed', 'prepayment', 'payed'];

    public const COMPANY_FIELDS = [
        'ur_name_l', 'ur_reg_num', 'ur_legal_addr', 'ur_pnr_nr',
        'ur_bank_name', 'ur_bank_code', 'ur_bank_acc_code',
    ];

    public function update(Orders $order, array $data): Orders
    {
        if (array_key_exists('ur_name', $data)) {
            $data['ur_name'] = match ($data['ur_name']) {
                'on' => true,
                '', null => false,
                default => $data['ur_name'],
            };
        }
        validator($data, [
            'status' => ['required', Rule::in(self::STATUSES)],
            'payment_status' => ['required', Rule::in(self::PAYMENT_STATUSES)],
            'manager_id' => ['nullable', 'integer', 'min:0', Rule::when((int) ($data['manager_id'] ?? 0) !== 0, ['exists:users,id'])],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'prepayment_price' => ['nullable', 'numeric', 'min:0'],
            'admin_comment' => ['nullable', 'string', 'max:10000'],
            'delivery_edit' => ['sometimes', 'array'],
            'sale_eur' => ['nullable', 'numeric', 'min:0'],
            'sale_percent' => ['nullable', 'numeric', 'between:0,100'],
            'a_order_from' => ['nullable', 'integer', 'exists:a_order_from,id'],
            // IDs 1 and 2 represent synthetic quiz categories, not gallery_items rows.
            'catid' => ['nullable', 'integer', 'min:0'],
            'approved_date' => ['nullable', 'string', 'max:255'],
            'ur_name' => ['sometimes', 'boolean'],
            ...array_fill_keys(self::COMPANY_FIELDS, ['nullable', 'string', 'max:255']),
        ])->validate();

        return DB::transaction(function () use ($order, $data): Orders {
            $order = Orders::query()->lockForUpdate()->findOrFail($order->getKey());
            $attributes = Arr::only($data, [
                'manager_id', 'status', 'payment_status', 'payment', 'price',
                'sale_price', 'prepayment_price', 'admin_comment',
                'sale_eur', 'sale_percent', 'a_order_from', 'catid',
            ]);

            if (array_key_exists('manager_id', $attributes)) {
                $attributes['manager_id'] = $attributes['manager_id'] ?: 0;
            }
            // Original preserves the previous date when the text input is cleared.
            if (filled($data['approved_date'] ?? null)) {
                $attributes['approved_date'] = $data['approved_date'];
            }
            if (array_key_exists('ur_name', $data)) {
                $attributes['ur_name'] = $data['ur_name'] ? 'on' : '';
            }
            foreach (self::COMPANY_FIELDS as $field) {
                if (array_key_exists($field, $data)) {
                    $attributes[$field] = $data[$field] ?? '';
                }
            }

            if (array_key_exists('delivery_edit', $data)) {
                $attributes = array_replace($attributes, app(OrderDeliveryEditor::class)->attributes($order, $data['delivery_edit']));
            }

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

            if (! $order->forceFill($attributes)->save()) {
                throw ValidationException::withMessages(['status' => 'Не удалось сохранить заказ.']);
            }

            return $order->refresh();
        });
    }
}
