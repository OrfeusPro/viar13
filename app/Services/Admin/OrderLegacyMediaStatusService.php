<?php

namespace App\Services\Admin;

use App\Models\Orders;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderLegacyMediaStatusService
{
    public function update(Orders $order, User $author, string $type, mixed $status): void
    {
        Gate::forUser($author)->authorize('update', $order);
        $data = validator(compact('type', 'status'), [
            'type' => ['required', Rule::in(['sketch', 'painting'])],
            'status' => ['required', 'integer', 'exists:a_painter_images_status,id'],
        ])->validate();
        DB::transaction(function () use ($order, $data): void {
            $locked = Orders::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $prefix = $data['type'] === 'sketch' ? 'painter_sketch_images' : 'painter_images';
            $flag = $data['type'] === 'sketch' ? 'is_img_sketch' : 'is_img_painter';
            if (OrderMediaRemovalService::paths($locked->{$prefix}) === [] || $locked->order_painter_images()->where($flag, 1)->exists()) {
                throw ValidationException::withMessages(['status' => 'Обновите галерею: общий статус доступен только старому списку изображений.']);
            }
            // Original endpoints update status/date only, preserving the order's updated_at.
            DB::table('orders')->where('id', $locked->id)->update([
                $prefix.'_status' => $data['status'], $prefix.'_status_date' => now(),
            ]);
        });
        $order->refresh();
    }
}
