@php
    $record = $getRecord();
    $status = match ($record->payment_status) {
        'payed' => ['Оплачено', 'bg-success-600'],
        'prepayment' => ['Предоплата', 'bg-warning-600'],
        default => ['Не оплачено', 'bg-danger-600'],
    };
    $method = match ((string) $record->payment) {
        'transfer' => 'Перевод',
        'paypalOnetimePayment' => 'PayPal',
        'paysera' => 'Paysera',
        'cash' => 'Наличные',
        default => $record->payment ?: 'Не указан',
    };
@endphp

<div class="min-w-36 space-y-1.5 text-xs">
    <span class="inline-flex rounded px-2 py-1 font-semibold text-white {{ $status[1] }}">
        {{ $status[0] }}
    </span>
    <div><strong>{{ $method }}</strong></div>
    <div>Заказ: <strong>{{ number_format((float) $record->price, 2) }} €</strong></div>
    @if(filled($record->sale_price) && (float) $record->sale_price !== (float) $record->price)
        <div>Итого: <strong>{{ number_format((float) $record->sale_price, 2) }} €</strong></div>
    @endif
    @if($record->payment_status === 'prepayment')
        <div>Предоплата: <strong>{{ number_format((float) $record->prepayment_price, 2) }} €</strong></div>
    @endif
    @if(filled(trim((string) $record->labels, ',')))
        <details class="rounded border border-gray-200 p-1 dark:border-gray-700">
            <summary class="cursor-pointer">Этикетки</summary>
            <div class="mt-1 break-all">{{ trim((string) $record->labels, ',') }}</div>
        </details>
    @endif
</div>
