<?php

namespace App\Services\Admin;

use App\Models\Orders;
use App\Models\VrNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateOrderVrNumberService
{
    public function update(Orders $order, ?string $number): ?VrNumber
    {
        $number = strtoupper(trim((string) $number));

        return DB::transaction(function () use ($order, $number): ?VrNumber {
            if ($number === '') {
                $order->vrNumber()->delete();

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

            return VrNumber::query()->updateOrCreate(
                ['order_id' => $order->getKey()],
                array_merge(
                    ['vrv_1' => null, 'vrv_2' => null, 'vrv_3' => null, 'vrv_4' => null],
                    [$column => (int) $matches[2]],
                ),
            );
        });
    }
}
