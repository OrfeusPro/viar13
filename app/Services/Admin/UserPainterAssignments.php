<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Models\Orders;
use App\Models\PainterOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserPainterAssignments
{
    public function authorize(User $actor, User $painter): void
    {
        abort_unless($actor->hasPermission('browse_admin') && $actor->hasPermission('edit_users')
            && $actor->hasPermission('edit_orders'), 403);
        abort_unless($painter->role?->name === 'painter', 422);
    }

    public function options(User $actor, User $painter, string $search = ''): array
    {
        $this->authorize($actor, $painter);
        return Orders::query()->where(function ($query) use ($painter): void {
            $query->where('status', '!=', 'completed')->orWhereIn('id', DB::table('painter_orders')->where('user_id', $painter->id)->select('order_id'));
        })->when($search !== '', fn ($query) => $query->where('id', 'like', '%'.$search.'%'))
            ->orderByDesc('id')->limit(50)->pluck('id')->mapWithKeys(fn ($id): array => [$id => 'Заказ №'.$id])->all();
    }

    public function sync(User $actor, User $painter, array $orderIds): void
    {
        $this->authorize($actor, $painter);
        $validated = validator(['orders' => $orderIds], ['orders' => 'array|max:1000', 'orders.*' => 'required|integer|distinct|exists:orders,id'])->validate();
        $ids = array_map('intval', $validated['orders']);
        DB::transaction(function () use ($actor, $painter, $ids): void {
            $lockedPainter = User::query()->lockForUpdate()->findOrFail($painter->id);
            $this->authorize($actor, $lockedPainter);
            $existing = PainterOrder::where('user_id', $painter->id)->lockForUpdate()->pluck('order_id')->map(fn ($id): int => (int) $id)->all();
            $orders = Orders::whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get(['id', 'status']);
            if ($orders->count() !== count($ids) || $orders->contains(fn ($order): bool => $order->status === 'completed' && ! in_array((int) $order->id, $existing, true))) {
                throw ValidationException::withMessages(['orders' => 'Новый выбор должен содержать существующие незавершённые заказы.']);
            }
            PainterOrder::where('user_id', $painter->id)->whereNotIn('order_id', $ids)->delete();
            foreach (array_diff($ids, $existing) as $id) {
                PainterOrder::firstOrCreate(['user_id' => $painter->id, 'order_id' => $id]);
            }
        }, 3);
    }
}
