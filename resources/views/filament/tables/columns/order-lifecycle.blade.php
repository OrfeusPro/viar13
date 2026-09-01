@php
    use App\Filament\Resources\Orders\OrdersResource;
    use App\Filament\Resources\Orders\Tables\OrderLifecycleActions;
    use App\Filament\Resources\Orders\Tables\OrdersTable;

    $record = $getRecord();
    $canEdit = auth('filament')->user()?->can('update', $record) ?? false;
    $canDelete = auth('filament')->user()?->can('delete', $record) ?? false;
    $delivery = json_decode((string) $record->delivery, true) ?: [];
    $whenSend = $delivery['when_send'] ?? null;
    $whenSendTimestamp = $whenSend ? strtotime((string) $whenSend) : false;
    $days = $whenSendTimestamp === false ? null : (int) floor(($whenSendTimestamp - today()->timestamp) / 86400);
    $dayColor = $days === null ? '#64748b' : ($days < 3 ? '#dc2626' : ($days < 8 ? '#d97706' : '#15803d'));
    $statusDates = [
        'На рассмотрении' => $record->watching_date,
        'В процессе' => $record->pegging_date,
        'В производстве' => $record->in_production_date,
        'Отправлен' => $record->send_date,
        'Отправлен на Лубанас' => $record->send_lubanas_date,
        'Завершён' => $record->completed_date,
    ];
    $activeOrders = collect(OrdersTable::relatedActiveOrderIds($record));
    $button = 'display:block;width:100%;margin-top:6px;padding:6px 8px;border:0;border-radius:3px;color:#fff;text-align:center;font-size:12px;line-height:1.25;cursor:pointer;text-decoration:none;';
@endphp

<style>
    .adm-fil-order-lifecycle, .adm-fil-order-lifecycle * { box-sizing: border-box; white-space: normal !important; overflow-wrap: anywhere; }
    .adm-fil-order-lifecycle select, .adm-fil-order-lifecycle input { color: #374151; }
</style>

<div class="adm-fil-order-lifecycle" style="width:190px;min-width:180px;padding:3px 5px;color:#313942;font-size:12px;line-height:1.35;">
    <div style="font-weight:600;">Статус заказа:</div>
    @if($canEdit)
        <select
            aria-label="Статус заказа №{{ $record->id }}"
            x-on:change.stop="$wire.mountTableAction('updateOrderStatus', '{{ $record->getKey() }}', { status: $el.value }); $el.value = @js($record->status)"
            style="width:100%;margin-top:4px;padding:5px;border:1px solid #9ca3af;border-radius:3px;background:#fff;font-size:12px;"
        >
            @foreach(OrderLifecycleActions::statusOptions() as $value => $label)
                <option value="{{ $value }}" @selected($record->status === $value)>{{ $label }}</option>
            @endforeach
        </select>
    @else
        <div style="margin-top:4px;">{{ OrderLifecycleActions::statusOptions()[$record->status] ?? $record->status }}</div>
    @endif

    @if($record->status_date)
        <div style="margin-top:5px;color:#64748b;">Изменено: {{ date('d.m.Y H:i', strtotime((string) $record->status_date)) }}</div>
    @endif
    <details style="margin-top:6px;">
        <summary style="color:#2563eb;cursor:pointer;">История статусов</summary>
        <div style="margin-top:4px;color:#64748b;">
            @forelse(collect($statusDates)->filter() as $label => $date)
                <div>{{ $label }}: {{ date('d.m.Y H:i', strtotime((string) $date)) }}</div>
            @empty
                <div>История пока пуста.</div>
            @endforelse
        </div>
    </details>

    @if($activeOrders->count() > 1)
        <div style="margin-top:12px;">Другие активные заказы:</div>
        <div style="color:#dc2626;">
            @foreach($activeOrders as $orderId)
                <a href="{{ OrdersResource::getUrl('index', ['activeTab' => 'all', 'tableFilters' => ['order_id' => ['value' => $orderId]]]) }}" style="color:#dc2626;text-decoration:underline;">#{{ $orderId }}</a>@if(!$loop->last), @endif
            @endforeach
        </div>
    @endif

    <div style="margin-top:18px;padding-top:10px;border-top:1px solid #e5e7eb;">
        <div><strong>номер заказа:</strong> {{ $record->id }}</div>
        <div><strong>дата заказа:</strong> {{ optional($record->created_at)->format('d/m/Y H:i') }}</div>
        @if($record->is_admin_order)<div style="margin-top:3px;color:#7c3aed;font-weight:600;">Административный заказ</div>@endif
    </div>

    <div style="margin-top:13px;">
        <strong>желаемая дата доставки:</strong>
        <div style="margin-top:3px;">{{ $whenSendTimestamp === false ? 'не задана' : date('d.m.Y', $whenSendTimestamp) }}</div>
        @if($canEdit)
            <button type="button" x-on:click.stop="$wire.mountTableAction('updateOrderDeliveryDate', '{{ $record->getKey() }}')" style="margin-top:5px;padding:4px 8px;border:0;border-radius:3px;background:#337ab7;color:#fff;font-size:11px;cursor:pointer;">Обновить</button>
        @endif
        @if($days !== null)
            <div style="margin-top:7px;"><strong>доставить через:</strong><br>
                @if($days === 0)
                    <strong style="color:{{ $dayColor }};">сегодня</strong>
                @else
                    <strong style="color:{{ $dayColor }};font-size:15px;">{{ $days }} дня(ей)</strong>
                @endif
            </div>
            <div style="margin-top:5px;"><strong>тип заказа:</strong> <span style="color:{{ $dayColor }};font-weight:600;">{{ $days < 4 ? 'Срочный' : 'Обычный' }}</span></div>
        @endif
    </div>

    <div style="margin-top:16px;padding-top:8px;border-top:1px solid #e5e7eb;">
        <span title="Будет реализовано в ADM-FIL-010" aria-disabled="true" style="{{ $button }}background:#94a3b8;cursor:not-allowed;">Создать заказ</span>
        @if($canEdit)
            <a href="{{ OrdersResource::getUrl('edit', ['record' => $record]) }}" style="{{ $button }}background:#f59e0b;">Редактировать заказ</a>
        @endif
        <button type="button" x-on:click.stop="$wire.mountTableAction('viewOrderClient', '{{ $record->getKey() }}')" style="{{ $button }}background:#16a34a;">Просмотр клиента</button>
        @if($record->user_id)
            <a href="{{ OrdersResource::getUrl('index', ['activeTab' => 'all', 'tableFilters' => ['user_id' => ['value' => $record->user_id]]]) }}" style="{{ $button }}background:#16a34a;">Все заказы ранее</a>
        @endif
        @if($canDelete)
            <button type="button" x-on:click.stop="$wire.mountTableAction('deleteOrder', '{{ $record->getKey() }}')" style="{{ $button }}background:#dc2626;">Удалить</button>
        @endif
    </div>

    @if(filled($record->photo))
        <div style="margin-top:12px;padding:7px;background:#e5e7eb;text-align:center;">Исходящая накладная</div>
        <div style="display:flex;justify-content:center;gap:8px;margin-top:5px;">
            <a href="{{ $record->photo }}" target="_blank" rel="noopener" style="color:#2563eb;text-decoration:underline;">Открыть</a>
            <a href="{{ $record->photo }}" download style="color:#2563eb;text-decoration:underline;">Скачать</a>
        </div>
    @endif
</div>
