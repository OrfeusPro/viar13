<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;

class OrderListSummary
{
    public static function forTable(HasTable $livewire): ?string
    {
        $user = Filament::auth()->user();
        if (! $user instanceof User || ! self::isVisibleTo($user)) {
            return null;
        }

        return self::describe($livewire->getFilteredTableQuery());
    }

    public static function isVisibleTo(User $user): bool
    {
        return ! in_array($user->role?->name, ['printing', 'lite_manager'], true)
            && $user->hasPermission('browse_orders');
    }

    public static function describe(?Builder $query): ?string
    {
        if (! $query) {
            return null;
        }

        $totals = (clone $query)->reorder()->toBase()->select([])
            ->selectRaw('COUNT(*) AS order_count, COALESCE(SUM(orders.price), 0) AS order_sum')
            ->first();
        $sum = rtrim(rtrim(number_format((float) $totals->order_sum, 2, '.', ''), '0'), '.');

        return 'Заказов: '.$totals->order_count.' · Сумма: '.$sum.' €';
    }
}
