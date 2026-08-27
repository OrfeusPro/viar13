<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrdersResource;
use App\Http\Controllers\OrdersController;
use App\Models\Orders;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrdersResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    public function getTabs(): array
    {
        return [
            'current' => Tab::make('Текущие')
                ->modifyQueryUsing(fn (Builder $query): Builder => $this->applyLegacyRotation(
                    $query->where('status', '!=', 'completed'),
                    Orders::query()->where('status', '!=', 'completed'),
                )),
            'completed' => Tab::make('Завершённые')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'completed')),
            'unpaid' => Tab::make('Неоплаченные')
                ->modifyQueryUsing(fn (Builder $query): Builder => $this->applyLegacyRotation(
                    $query->where('status', '!=', 'completed')->where('payment_status', 'not_payed'),
                    Orders::query()->where('status', '!=', 'completed')->where('payment_status', 'not_payed'),
                )),
            'production' => Tab::make('В производстве')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'in_production')),
            'printing' => Tab::make('У печатника')
                ->modifyQueryUsing(fn (Builder $query): Builder => $this->applyLegacyRotation(
                    $query->where('status', '!=', 'completed')->whereHas('printingAssignment'),
                    Orders::query()->where('status', '!=', 'completed')->whereHas('printingAssignment'),
                )),
            'express' => Tab::make('Надо отправить сегодня')
                ->modifyQueryUsing(fn (Builder $query): Builder => $this->applyLegacyRotation(
                    $query->where('status', '!=', 'completed'),
                    Orders::query()->where('status', '!=', 'completed'),
                    true,
                )),
            'sent_today' => Tab::make('Отправлены сегодня')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query
                    ->where('status', 'sended')
                    ->whereDate('send_date', today())),
            'sent' => Tab::make('Отправленные')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'sended')),
            'new_recently' => Tab::make('Новые сегодня/вчера')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query
                    ->where('status', 'new')
                    ->whereBetween('created_at', [today()->subDay()->startOfDay(), today()->endOfDay()])),
            'all' => Tab::make('Все'),
        ];
    }

    private function applyLegacyRotation(Builder $query, Builder $prioritySource, bool $express = false): Builder
    {
        $controller = app(OrdersController::class);
        // Legacy comparator intentionally preserves created_at DESC inside
        // groups that have equal/no deadline, so its input order matters.
        $records = $prioritySource->orderByDesc('created_at')->get();
        $ordered = $express
            ? $controller->sortAdminExpressOrders($records)
            : $controller->sortAdminRotationOrders($records);
        $ids = array_map(static fn ($record): int => (int) $record->id, $ordered);

        if ($ids === []) {
            return $query;
        }

        return $query->reorder()->orderByRaw('FIELD(orders.id, '.implode(',', $ids).')');
    }
}
