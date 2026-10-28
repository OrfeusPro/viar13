<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;

class OrderListSummary
{
    public static function forTable(HasTable $livewire, bool $pageSubtotal = false): ?string
    {
        $totals = self::totalsForTable($livewire, $pageSubtotal);

        return $totals ? self::format($totals) : null;
    }

    public static function totalsForTable(HasTable $livewire, bool $pageSubtotal = false): ?array
    {
        $user = Filament::auth()->user();
        if (! $user instanceof User || ! self::isVisibleTo($user)) {
            return null;
        }

        $totals = self::totalsForQuery($livewire->getFilteredTableQuery());
        if ($totals !== null && $pageSubtotal) {
            // Laravel 6 sums only the paginator rows for express/printing/sent_today,
            // while displaying the count of all matching orders.
            $sum = $livewire->getTableRecords()->sum(fn ($record): float => (float) $record->price);
            $totals['sum'] = rtrim(rtrim(number_format($sum, 2, '.', ''), '0'), '.');
        }

        return $totals;
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
