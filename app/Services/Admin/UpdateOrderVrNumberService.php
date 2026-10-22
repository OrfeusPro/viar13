<?php

namespace App\Services\Admin;

use App\Models\Orders;
use App\Models\OrderString;
use App\Models\VrNumber;
use App\Services\Invoice\InvoicePdfWriter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateOrderVrNumberService
{
    private const COLUMNS = ['VR00' => 'vrv_1', 'BAW' => 'vrv_2', 'VRR445' => 'vrv_3', 'DS020' => 'vrv_4'];

    public static function prefixOptions(): array
    {
        return once(function (): array {
            $fallback = ['VR00' => 'D-Art-Solutions SIA', 'BAW' => 'D-Art-Solutions UAB', 'VRR445' => 'SIA ViarStudia', 'DS020' => 'SIA VR-Technology'];
            $strings = Schema::hasTable('order_strings') ? OrderString::query()->find(1) : null;
            $fields = ['VR00' => 'consignor_text', 'BAW' => 'consignor_text_vrv', 'VRR445' => 'consignor_text_vrr', 'DS020' => 'consignor_text_vra'];
            foreach ($fields as $prefix => $field) {
                if (filled($strings?->{$field})) {
                    $fallback[$prefix] = (string) $strings->{$field};
                }
            }

            return $fallback;
        });
    }

    public function generate(Orders $order, string $prefix): VrNumber
    {
        validator(['prefix' => $prefix], ['prefix' => ['required', Rule::in(array_keys(self::COLUMNS))]])->validate();

        return DB::transaction(function () use ($order, $prefix): VrNumber {
            $order = Orders::query()->lockForUpdate()->findOrFail($order->id);
            if ($order->vrNumber()->exists()) {
                throw ValidationException::withMessages(['prefix' => 'Накладная уже создана. Обновите заказ.']);
            }
            $column = self::COLUMNS[$prefix];
            $last = VrNumber::query()->whereNotNull($column)->latest('id')->lockForUpdate()->first();

            return $this->update($order, $prefix.((int) $last?->{$column} + 1));
        });
    }

    public function update(Orders $order, ?string $number): ?VrNumber
    {
        $number = strtoupper(trim((string) $number));

        return DB::transaction(function () use ($order, $number): ?VrNumber {
            $order = Orders::query()->lockForUpdate()->findOrFail($order->id);
            if ($number === '') {
                $order->vrNumber()->delete();
                DB::table('orders')->where('id', $order->id)->update(['has_pdf' => null]);
                app(InvoicePdfWriter::class)->remove(public_path('storage/pdf/'.$order->id.'.pdf'));

                return null;
            }

            if (! preg_match('/^(VR00|BAW|VRR445|DS020)(\d+)$/', $number, $matches)) {
                throw ValidationException::withMessages([
                    'number' => 'Допустимые форматы: VR00…, BAW…, VRR445… или DS020…',
                ]);
            }

            $column = match ($matches[1]) {
                'VR00' => 'vrv_1',
                'BAW' => 'vrv_2',
                'VRR445' => 'vrv_3',
                default => 'vrv_4',
            };

            $vr = VrNumber::query()->firstOrNew(['order_id' => $order->getKey()]);
            if (! $vr->forceFill(array_merge(
                ['vrv_1' => null, 'vrv_2' => null, 'vrv_3' => null, 'vrv_4' => null],
                [$column => (int) $matches[2]],
            ))->save()) {
                throw ValidationException::withMessages(['number' => 'Не удалось сохранить номер накладной.']);
            }
            app(OrderInvoiceService::class)->refreshDocument($order);

            return $vr;
        });
    }
}
