@php
    use App\Filament\Resources\Orders\Tables\OrdersTable;

    $record = $getRecord();
    $isPrinting = auth('filament')->user()?->role?->name === 'printing';
    $user = $record->user;
    $delivery = is_array($record->delivery) ? $record->delivery : (json_decode((string) $record->delivery, true) ?: []);
    $fallbackName = trim((string) ($delivery['first_name'] ?? '').' '.(string) ($delivery['last_name'] ?? ''));
    $name = $user && $user->client_status !== null
        ? trim((string) $user->first_name.' '.(string) $user->last_name)
        : $fallbackName;
    $phone = $user?->phone ?: ($delivery['payer_phone'] ?? $delivery['phone'] ?? null);
    $email = $user?->email ?: ($delivery['email'] ?? null);
    $userLocale = $user?->preferredLocale();
    $pdfLocale = $user?->pdf_locale ?: $userLocale;
    $clientStatuses = OrdersTable::clientStatusOptions();
    // Legacy selects the first option when no status has been saved. Display only: no DB write.
    $displayClientStatus = $user?->client_status ?? array_key_first($clientStatuses);
    $channel = $isPrinting ? null : OrdersTable::salesChannelLabel($record->a_order_from);
    $category = $isPrinting ? null : OrdersTable::categoryLabel($record->catid);
    $manager = ! $isPrinting && $record->manager && (int) $record->manager->role_id === 4
        ? trim(($record->manager->nick ? $record->manager->nick.' - ' : '').$record->manager->email)
        : 'Админ';
@endphp

<div x-data="{ save(field, value) { $wire.mountTableAction('updateOrderInlineField', '{{ $record->getKey() }}', { field, value }) } }" style="width: 205px; min-width: 190px; padding: 2px 7px; text-align: center; font-size: 13px; line-height: 1.35; color: #20242c;">
    <div style="margin-bottom: 7px;">Данные пользователя:</div>

    @if(filled($name))
        <div>{{ $name }}</div>
    @endif
    @if(filled($phone))
        <div>{{ $phone }}</div>
    @endif
    @if(filled($email))
        <div style="overflow-wrap: anywhere;">{{ $email }}</div>
    @endif
    @if(filled($user?->status))
        <div>{{ $user->status }}</div>
    @endif

    @if($user)
        <div style="margin-top: 7px;">Заказов было: {{ $user->orders_count ?? $user->getOrdersCount($record->user_id) }}</div>
    @elseif($record->user_id)
        <div style="margin-top: 7px; color: #b94a48;">Пользователь не найден (user_id: {{ $record->user_id }})</div>
    @endif

    @if(filled($userLocale))
        <div style="margin-top: 7px;">Язык пользователя: {{ $userLocale }}</div>
    @endif

    <label style="display: block; margin-top: 8px;">
        <span>Язык счета:</span>
        @if($user)
            <select
                aria-label="Язык счета пользователя заказа №{{ $record->id }}"
                x-on:change.stop="save('pdf_locale', $el.value)" wire:loading.attr="disabled"
                style="width: auto; padding: 1px 3px; border: 1px solid #9ca3af; border-radius: 2px; background: white; color: #374151; font-size: 12px;"
            >
                @foreach(OrdersTable::localeOptions() as $locale => $label)
                    <option value="{{ $locale }}" @selected($pdfLocale === $locale)>{{ $locale }}</option>
                @endforeach
            </select>
        @else
            <span style="color: #999;">Недоступно: нет пользователя</span>
        @endif
    </label>

    @if(! $isPrinting)
    <div style="margin-top: 12px;">Канал продаж:</div>
    <strong>{{ $channel ?: '—' }}</strong>

    <div style="margin-top: 12px;">Категория:</div>
    @if($category)
        <strong>{{ $category }}</strong>
    @else
        <strong style="color: red;">нет</strong>
    @endif

    <div style="margin-top: 12px;">Менеджер:</div>
    <strong style="overflow-wrap: anywhere;">{{ $manager }}</strong>
    @endif

    <label style="display: block; margin-top: 12px;">
        <span style="display: block; margin-bottom: 4px;">Статус клиента</span>
        @if($user)
            <select
                aria-label="Статус клиента заказа №{{ $record->id }}"
                x-on:change.stop="save('client_status', $el.value)" wire:loading.attr="disabled"
                style="width: 100%; padding: 3px 5px; border: 1px solid #9ca3af; border-radius: 3px; background: white; color: #374151; font-size: 12px;"
            >
                @foreach($clientStatuses as $statusId => $statusName)
                    <option value="{{ $statusId }}" @selected((int) $displayClientStatus === (int) $statusId)>{{ $statusName }}</option>
                @endforeach
            </select>
        @else
            <span style="color: #999;">Недоступно</span>
        @endif
    </label>
</div>
