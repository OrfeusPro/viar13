@php
    $record = $getRecord();
    $delivery = is_array($record->delivery) ? $record->delivery : (json_decode((string) $record->delivery, true) ?: []);
    $recipientPhone = $delivery['phone'] ?? App\Models\Orders::getFieldPortraitCalc($record, 'Phone') ?? '-';
    $payerPhone = $delivery['payer_phone'] ?? $record->user?->phone ?: (App\Models\Orders::getFieldPortraitCalc($record, 'Phone') ?? '-');
    $hasRecipientPhone = array_key_exists('payer_phone', $delivery) || ! empty($delivery['is_no_payer']);
    $normalizePhone = static fn ($phone): string => preg_replace('/\D+/', '', (string) $phone);
    $samePhone = $payerPhone && $recipientPhone && $normalizePhone($payerPhone) === $normalizePhone($recipientPhone);
    $displayPhone = ($payerPhone && $payerPhone !== '-') ? $payerPhone : $recipientPhone;
    $whatsAppPhone = ltrim($normalizePhone($displayPhone), '0');
    $method = match ($delivery['sposob'] ?? null) {
        'to_the_door' => 'Доставка до дверей дома или работы',
        'pickup_Riga' => 'Самовывоз Рига',
        'pickup_Daugavplis', 'pickup_Daugavpils' => 'Самовывоз Даугавпилс',
        'venipak' => 'Доставка Venipak',
        'pickup_at_viar_workshop' => 'Забрать в мастерской VIAR',
        'city_delivery' => 'Доставка по '.($delivery['city'] ?? ''),
        default => $delivery['sposob'] ?? null,
    };
    $payment = match ($record->payment) {
        'cash_in_office' => ['Наличными при получении в офисе', 'storage/images/cash.png'],
        'on_delivery' => ['Оплата во время доставки', 'storage/images/delivery-payment.png'],
        'transfer' => ['Оплата перечислением', 'storage/images/visa.png'],
        'online_paysera' => ['Онлайн банкинг', 'storage/images/paysera.svg'],
        'google_pay' => ['Google Pay', 'theme/viar/img/cart/pbl_ap.png'],
        'apple_pay' => ['Apple Pay', 'theme/viar/img/cart/pbl_jp.png'],
        'paypalOnetimePayment' => ['PayPal', 'theme/viar/img/icons/PayPal.svg'],
        'prepayment' => ['Предоплата', 'theme/viar/img/cart/prepaid2.png'],
        'creditcart' => ['Картой онлайн', 'theme/viar/img/cart/payments.png'],
        default => [null, null],
    };
    $deliveryIcon = match ($delivery['sposob'] ?? null) {
        'venipak' => 'images/venipak_new.png',
        'pickup_at_viar_workshop' => 'images/pickup_at_viar_workshop.png',
        'pickup_Riga', 'pickup_Daugavplis', 'pickup_Daugavpils' => ! empty($delivery['deliv_price']) ? 'storage/images/truck_icon.png' : 'storage/images/riga.png',
        'to_the_door', 'city_delivery' => 'storage/images/truck_icon.png',
        default => null,
    };
    $deliveryDate = null;
    $invalidDeliveryDate = false;
    if (filled($delivery['when_send'] ?? null)) {
        try {
            $deliveryDate = (new DateTime((string) $delivery['when_send']))->format('d/m/Y');
        } catch (Throwable) {
            $deliveryDate = (string) $delivery['when_send'];
            $invalidDeliveryDate = true;
        }
    }
@endphp

<div style="width: 205px; min-width: 190px; padding: 5px 7px; background: #f8fafc; text-align: left; font-size: 13px; line-height: 1.35; color: #313942;">
    <div style="margin-bottom: 6px;">Даные получателя:</div>
    <div>Email: {{ $delivery['email'] ?? App\Models\Orders::getFieldPortraitCalc($record, 'Email') ?? '-' }}</div>
    <div>Имя: {{ $delivery['first_name'] ?? '-' }}</div>
    <div>Фамилия: {{ $delivery['last_name'] ?? '-' }}</div>

    @if($hasRecipientPhone && ! $samePhone)
        <div>Телефон заказчика: {{ $payerPhone }}</div>
        <div>Телефон получателя: {{ $recipientPhone }}</div>
    @else
        <div>Телефон: {{ $displayPhone }}</div>
    @endif

    @if(filled($displayPhone) && $displayPhone !== '-')
        <div><a href="viber://chat?number={{ urlencode($displayPhone) }}" style="color: #2563eb; text-decoration: underline;">Начать чат в Viber</a></div>
        <div><a href="https://wa.me/{{ $whatsAppPhone }}" target="_blank" rel="noopener" style="color: #2563eb; text-decoration: underline;">Начать чат в WhatsApp</a></div>
    @endif
    @if($record->user)
        <button type="button" x-on:click.stop="$wire.mountTableAction('composeRecipientEmail', '{{ $record->getKey() }}')" style="padding: 0; border: 0; background: transparent; color: #2563eb; text-decoration: underline; cursor: pointer;">Написать email</button>
    @endif

    <div style="margin-top: 7px;">Страна: {{ $delivery['country'] ?? '-' }}</div>
    @isset($delivery['city'])<div>Город: {{ $delivery['city'] }}</div>@endisset
    <div>Адресс: {{ $delivery['address'] ?? '-' }}</div>
    <div>Индекс: {{ $delivery['postal_index'] ?? '-' }}</div>
    @if($deliveryDate)
        <div style="color: {{ $invalidDeliveryDate ? '#b94a48' : 'inherit' }};">Дата доставки: {{ $deliveryDate }}</div>
    @endif
    @isset($delivery['lang'])<div>Язык пользователя: {{ $delivery['lang'] }}</div>@endisset
    @if($record->order_image)<div><a target="_blank" href="{{ $record->order_image }}" style="color: #2563eb; text-decoration: underline;">Картинка в заказе</a></div>@endif
    @if($method)<div style="margin-top: 7px;">{{ $method }}</div>@endif
    @if($payment[0])<div>{{ $payment[0] }}</div>@endif

    <div style="margin-top: 8px; text-align: center;">
        @if($payment[1])<img src="{{ asset($payment[1]) }}" title="{{ $payment[0] }}" alt="{{ $payment[0] }}" style="display: inline-block; max-width: 60px; max-height: 38px; margin: 2px;">@endif
        @if($deliveryIcon)<img src="{{ asset($deliveryIcon) }}" title="{{ $method }}" alt="{{ $method }}" style="display: inline-block; max-width: 60px; max-height: 38px; margin: 2px;">@endif
    </div>
</div>
