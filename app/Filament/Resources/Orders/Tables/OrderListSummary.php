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
        $totals = self::totalsForTable($livewire);

        return $totals ? self::format($totals) : null;
    }

    public static function totalsForTable(HasTable $livewire): ?array
    {
        $user = Filament::auth()->user();
        if (! $user instanceof User || ! self::isVisibleTo($user)) {
            return null;
        }

        return self::totalsForQuery($livewire->getFilteredTableQuery());
    }

    public static function isVisibleTo(User $user): bool
    {
        return ! in_array($user->role?->name, ['printing', 'lite_manager'], true)
            && $user->hasPermission('browse_orders');
    }

    public static function describe(?Builder $query): ?string
    {
        $totals = self::totalsForQuery($query);

        return $totals ? self::format($totals) : null;
    }

    private static function format(array $totals): string
    {
        return 'Заказов: '.$totals['count'].' · Сумма: '.$totals['sum'].' €';
    }

    private static function totalsForQuery(?Builder $query): ?array
    {
        if (! $query) {
            return null;
        }

        $totals = (clone $query)->reorder()->toBase()->select([])
            ->selectRaw('COUNT(*) AS order_count, COALESCE(SUM(orders.price), 0) AS order_sum')
            ->first();
        $sum = rtrim(rtrim(number_format((float) $totals->order_sum, 2, '.', ''), '0'), '.');

        return ['count' => (int) $totals->order_count, 'sum' => $sum];
    }
}
