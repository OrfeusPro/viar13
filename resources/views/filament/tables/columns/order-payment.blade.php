@php
    $record = $getRecord();
    $status = match ($record->payment_status) {
        'payed' => ['оплачено', '#169b16'],
        'prepayment' => ['предоплата', '#c19300'],
        default => ['не оплачено', '#ff1515'],
    };
@endphp

<div style="width: 190px; min-width: 180px; padding: 2px 5px; text-align: center; font-size: 13px; line-height: 1.35; color: #20242c;">
    <div style="margin-bottom: 8px;">
        <div>Дата заказа:</div>
        <strong style="display: block; font-size: 14px; line-height: 1.25;">
            {{ $record->created_at?->format('d/m/Y') }}<br>
            {{ $record->created_at?->format('H:i') }}
        </strong>
    </div>

    <div style="display: block; width: 100%; box-sizing: border-box; margin: 5px 0 12px; padding: 6px 10px; border-radius: 7px; background: {{ $status[1] }}; color: white; font-weight: 500; text-align: center;">
        {{ $status[0] }}
    </div>

    <label style="display: block; margin-bottom: 12px; text-align: center;">
        <span style="display: block; margin-bottom: 4px;">Статус оплаты:</span>
        <select
            aria-label="Статус оплаты заказа №{{ $record->id }}"
            x-on:change.stop="$wire.mountTableAction('updatePaymentStatus', '{{ $record->getKey() }}', { payment_status: $el.value }); $el.value = @js($record->payment_status)"
            style="display: inline-block; width: 132px; padding: 3px 5px; border: 1px solid #9ca3af; border-radius: 3px; background: white; color: #374151; font-size: 12px;"
        >
            <option value="not_payed" @selected($record->payment_status === 'not_payed')>Не оплачено</option>
            <option value="prepayment" @selected($record->payment_status === 'prepayment')>Предоплата</option>
            <option value="payed" @selected($record->payment_status === 'payed')>Оплачено</option>
        </select>
    </label>

    @if($record->payment_status === 'prepayment')
        <div style="margin: 10px 0 14px; text-align: center;">
            <div style="margin-bottom: 4px;">Сумма предоплаты:</div>
            <input type="text" readonly value="{{ number_format((float) $record->prepayment_price, 2, '.', '') }}" style="display: block; width: 100%; box-sizing: border-box; padding: 4px 6px; border: 1px solid #9ca3af; border-radius: 3px; text-align: center;">
            <button
                type="button"
                x-on:click.stop="$wire.mountTableAction('updatePrepayment', '{{ $record->getKey() }}')"
                style="display: block; width: 100%; margin-top: 5px; padding: 5px 8px; border: 0; border-radius: 3px; background: #337ab7; color: white; font-size: 12px; cursor: pointer;"
            >
                Обновить
            </button>
        </div>
    @endif

    @if(filled(trim((string) $record->labels, ',')))
        <div style="margin-top: 10px; text-align: center;">
            <div>Номера этикеток:</div>
            @foreach(array_filter(explode(',', trim((string) $record->labels, ','))) as $label)
                <div style="margin-top: 5px; overflow-wrap: anywhere;">{{ $label }}</div>
            @endforeach
        </div>
    @endif
</div>
