<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrdersResource;
use App\Filament\Resources\Orders\Schemas\VenipakCourierAction;
use App\Http\Controllers\OrdersController;
use App\Models\Orders;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrdersResource::class;

    public function getDefaultActiveTab(): string|int|null
    {
        return 'current';
    }

    public function updatedActiveTab(): void
    {
        // Voyager's tab links start a fresh list without the previous search query.
        $this->resetTableFiltersForm();
        $this->resetTableSearch();
        $this->resetTableColumnSearches();
        $this->tableSort = null;
        $this->updatedTableSort();

        parent::updatedActiveTab();
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Создать заказ')
                ->visible(fn (): bool => Filament::auth()->user()?->role?->name !== 'printing'),
            VenipakCourierAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $isPrinting = Filament::auth()->user()?->role?->name === 'printing';
        $tabs = [
            'current' => Tab::make('Текущие')
                ->visible(! $isPrinting)
                ->badge(fn (): ?int => Orders::query()->where('status', '!=', 'completed')->count() ?: null)
                ->modifyQueryUsing(fn (Builder $query): Builder => $this->applyLegacyRotation(
                    $query->where('status', '!=', 'completed'),
                    Orders::query()->where('status', '!=', 'completed'),
                )),
            'completed' => Tab::make('Завершённые')
                ->visible(! $isPrinting)
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'completed')->orderByDesc('created_at')),
            'unpaid' => Tab::make('Неоплаченные')
                ->visible(! $isPrinting)
                ->badge(fn (): ?int => Orders::query()->where('status', '!=', 'completed')->where('payment_status', 'not_payed')->count() ?: null)
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query): Builder => $this->applyLegacyRotation(
                    $query->where('status', '!=', 'completed')->where('payment_status', 'not_payed'),
                    Orders::query()->where('status', '!=', 'completed')->where('payment_status', 'not_payed'),
                )),
            'production' => Tab::make('В производстве')
                ->badge(fn (): ?int => Orders::query()->where('status', 'in_production')->count() ?: null)
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'in_production')->orderByDesc('orders.id')),
            'printing' => Tab::make('У печатника')
                ->badge(fn (): ?int => Orders::query()->where('status', '!=', 'completed')->whereHas('printingAssignment')->count() ?: null)
                ->modifyQueryUsing(fn (Builder $query): Builder => $this->applyLegacyRotation(
                    $query->where('status', '!=', 'completed')->whereHas('printingAssignment'),
                    Orders::query()->where('status', '!=', 'completed')->whereHas('printingAssignment'),
                )),
            'express' => Tab::make($isPrinting ? 'Все текущие заказы' : 'Надо отправить сегодня')
                ->modifyQueryUsing(fn (Builder $query): Builder => $this->applyLegacyRotation(
                    $query->where('status', '!=', 'completed'),
                    Orders::query()->where('status', '!=', 'completed'),
                    true,
                )),
            'sent_today' => Tab::make('Отправлены сегодня')
                ->badge(fn (): ?int => Orders::query()->where('status', 'sended')->whereDate('send_date', today())->count() ?: null)
                ->modifyQueryUsing(fn (Builder $query): Builder => $query
                    ->where('status', 'sended')
                    ->whereDate('send_date', today())->orderByDesc('send_date')),
            'sent' => Tab::make('Отправленные')
                ->badge(fn (): ?int => Orders::query()->where('status', 'sended')->count() ?: null)
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'sended')->orderByDesc('orders.id')),
            'new_recently' => Tab::make('Новые сегодня/вчера')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query
                    ->whereBetween('created_at', [today()->subDay()->startOfDay(), today()->endOfDay()])->orderByDesc('orders.id')),
        ];

        foreach ($tabs as $key => $tab) {
            $tab->extraAttributes(['class' => 'viar-order-tab viar-order-tab--'.$key]);
        }

        $order = $isPrinting
            ? ['current', 'completed', 'production', 'printing', 'sent_today', 'sent', 'new_recently', 'unpaid', 'express']
            : ['current', 'completed', 'express', 'production', 'printing', 'sent_today', 'sent', 'new_recently', 'unpaid'];

        return array_combine($order, array_map(fn (string $key): Tab => $tabs[$key], $order));
    }

    protected function modifyQueryWithActiveTab(Builder $query, bool $isResolvingRecord = false): Builder
    {
        // Original order_filter is an alternative to the tab branches, not an extra condition.
        if ($this->hasActiveOrderFilters()) {
            return $query->orderByDesc('orders.created_at');
        }

        return parent::modifyQueryWithActiveTab($query, $isResolvingRecord);
    }

    public function hasActiveOrderFilters(): bool
    {
        foreach ($this->tableFilters ?? [] as $filter) {
            foreach ((array) $filter as $value) {
                if (filled(is_string($value) ? trim($value) : $value)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getTableRecordsPerPage(): int|string|null
    {
        $perPage = parent::getTableRecordsPerPage();
        if (in_array($perPage, [10, 13, '10', '13'], true)) {
            return $this->hasActiveOrderFilters() ? 10 : 13;
        }

        return $perPage;
    }

    protected function applySortingToTableQuery(Builder $query): Builder
    {
        $column = $this->getTableSortColumn();
        if (filled($column) && $this->getTable()->getSortableVisibleColumn($column)) {
            // Explicit sorting replaces the tab's date/FIELD order, as in Voyager.
            $query->reorder();
        }

        return parent::applySortingToTableQuery($query);
    }

    public function sortTable(?string $column = null, ?string $direction = null): void
    {
        if ($column !== 'number_controls' || $direction !== null) {
            parent::sortTable($column, $direction);

            return;
        }

        $next = $this->getTableSortColumn() === $column
            ? match ($this->getTableSortDirection()) {
                'desc' => 'asc',
                'asc' => null,
                default => 'desc',
            }
        : 'desc';
        $this->tableSort = $next ? $column.':'.$next : null;
        $this->updatedTableSort();
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
