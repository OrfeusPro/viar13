@php
    $record = $getRecord();
    $isPrinting = auth('filament')->user()?->role?->name === 'printing';
    $canEdit = auth('filament')->user()?->can('update', $record) ?? false;
    $vr = $record->vrNumber;
    $vrNumber = match (true) {
        filled($vr?->vrv_1) => 'VR00' . $vr->vrv_1,
        filled($vr?->vrv_2) => 'BAW' . $vr->vrv_2,
        filled($vr?->vrv_3) => 'VRR445' . str_pad((string) $vr->vrv_3, 3, '0', STR_PAD_LEFT),
        filled($vr?->vrv_4) => 'DS020' . $vr->vrv_4,
        default => null,
    };
    $paymentRequests = $isPrinting ? collect() : $record->order_payment_requests;
    $shouldRenderInvoice = (int) $record->has_pdf === 1 || filled($vrNumber) || filled($record->ur_name);
    $defaultFirm = $vrNumber ? app(\App\Services\Admin\OrderInvoiceService::class)->defaultFirmData($record, $vrNumber) : [];
    $paymentMethodLabels = [
        'online_paysera' => 'Онлайн банкинг',
        'creditcart' => 'Картой онлайн',
        'google_pay' => 'Google Pay',
        'apple_pay' => 'Apple Pay',
        'paypalOnetimePayment' => 'PayPal',
        'transfer' => 'Оплата перечислением',
    ];
@endphp

<div x-data="{ save(field, value) { $wire.mountTableAction('updateOrderInlineField', '{{ $record->getKey() }}', { field, value }) } }" style="width: 180px; min-width: 170px; padding: 2px 0; text-align: center; font-size: 13px; line-height: 1.4;">
    <div style="margin-bottom: 18px; color: #7b8190; font-size: 14px; font-weight: 400;">
        №: {{ $record->id }}
    </div>

    @if(!$vrNumber)
        <select
            aria-label="Накладная заказа №{{ $record->id }}"
            x-on:change="if ($el.value) save('vr_prefix', $el.value)" @disabled(! $canEdit || $isPrinting) wire:loading.attr="disabled"
            style="display: block; width: 100%; margin-bottom: 18px; padding: 5px 8px; border: 1px solid #9ca3af; border-radius: 4px; background: white; color: #7b8190; font-size: 13px;"
        >
            <option value="">Накладная -</option>
            @foreach(\App\Services\Admin\UpdateOrderVrNumberService::prefixOptions() as $prefix => $title)
                <option value="{{ $prefix }}">{{ $title }}</option>
            @endforeach
        </select>
    @else
        <div style="margin-bottom: 10px;">
            <input type="text" aria-label="Номер накладной заказа №{{ $record->id }}" x-ref="vrNumber" value="{{ $vrNumber }}" @readonly(! $canEdit || $isPrinting) style="width: 100%; padding: 3px 5px; border: 1px solid #9ca3af; border-radius: 2px; text-align: center; font-size: 13px;">
            @if(! $isPrinting && $canEdit)
            <div style="display: flex; justify-content: center; gap: 5px; margin-top: 8px;">
                <button type="button" x-on:click.stop="save('vr_number', $refs.vrNumber.value)" wire:loading.attr="disabled" style="padding: 4px 8px; border: 0; border-radius: 3px; background: #22a7e8; color: white; font-size: 12px; cursor: pointer;">Обновить</button>
                <button type="button" aria-label="Удалить накладную заказа №{{ $record->id }}" x-on:click.stop="save('vr_number', null)" wire:loading.attr="disabled" style="padding: 4px 8px; border: 0; border-radius: 3px; background: #ff442a; color: white; font-size: 12px; cursor: pointer;">×</button>
            </div>
            @endif
        </div>

        <details wire:key="order-firm-{{ $record->id }}-{{ $vrNumber }}" style="margin-bottom: 10px; text-align: left;">
            <summary style="display: inline-block; padding: 5px 9px; border: 1px solid #d1d5db; border-radius: 4px; background: #f3f4f6; cursor: pointer;">данные фирмы</summary>
            <form x-data="{ firm: @js($defaultFirm) }" @if($canEdit && ! $isPrinting) x-on:submit.prevent="$wire.mountTableAction('updateOrderInvoiceFirm', '{{ $record->getKey() }}', firm)" @else x-on:submit.prevent @endif style="margin-top: 6px; font-size: 11px; color: #4b5563;">
                @foreach(['name' => 'Фирма', 'reg_num' => 'Рег. ном.', 'addr' => 'Адрес', 'bank' => 'Банк', 'vat_num' => 'Банк VAT', 'bank_code' => 'Код банка', 'office_addr' => 'Адрес офиса', 'acc_num' => 'Номер аккаунта'] as $field => $label)
                    <label style="display: block; margin-top: 7px;">{{ $label }}
                        <input type="text" name="{{ $field }}" aria-label="{{ $label }} счёта заказа №{{ $record->id }}" x-model="firm.{{ $field }}" value="{{ $defaultFirm[$field] ?? '' }}" @required(in_array($field, ['name', 'addr'], true)) maxlength="{{ in_array($field, ['addr', 'office_addr'], true) ? 500 : 255 }}" @readonly(! $canEdit || $isPrinting) style="display: block; width: 100%; min-width: 0; padding: 4px 5px; border: 1px solid #d1d5db; border-radius: 3px; background: white;">
                    </label>
                @endforeach
                @if($canEdit && ! $isPrinting)
                    <button type="submit" wire:loading.attr="disabled" style="display: block; margin-top: 10px; padding: 4px 8px; border: 0; border-radius: 3px; background: #22a7e8; color: white; font-size: 12px; cursor: pointer;">Обновить</button>
                @endif
            </form>
        </details>
    @endif

    @if(! $isPrinting)
    @if($shouldRenderInvoice)
        <div style="margin-top: 10px; padding: 10px 12px; border: 1px solid #e8e8e8; border-radius: 8px; background: white; text-align: center;">
            @if((int) $record->has_pdf === 1)
                <div><a href="{{ asset('storage/pdf/' . $record->id . '.pdf') }}" target="_blank" style="color: #2563eb; text-decoration: underline;">Счет</a></div>
                <button type="button" x-on:click.stop="$wire.mountTableAction('generateInvoice', '{{ $record->getKey() }}')" style="margin-top: 6px; padding: 0; border: 0; background: transparent; color: #2563eb; font-size: 12px; text-decoration: underline; cursor: pointer;">Обновить счет</button>
            @elseif($vrNumber)
                <button type="button" x-on:click.stop="$wire.mountTableAction('generateInvoice', '{{ $record->getKey() }}')" style="padding: 0; border: 0; background: transparent; color: #2563eb; font-size: 12px; text-decoration: underline; cursor: pointer;">Генерировать счет</button>
            @endif

            @if((int) $record->has_pdf === 1)
                <button type="button" x-on:click.stop="$wire.mountTableAction('approveInvoice', '{{ $record->getKey() }}')" style="display: block; width: 100%; margin-top: 6px; padding: 0; border: 0; background: transparent; color: #2563eb; font-size: 12px; text-decoration: underline; cursor: pointer;">{{ (int) $record->pdf_approved === 1 ? 'Повторно подтвердить счет' : 'Подтвердить счет' }}</button>
            @endif

            @if(filled($record->ur_name))
                <div style="margin-top: 8px;"><strong>Юр. лицо</strong></div>
                @foreach([
                    'Имя Юр. лица' => $record->ur_name_l,
                    'Рег. номер' => $record->ur_reg_num,
                    'Адрес' => $record->ur_legal_addr,
                    'VAT номер' => $record->ur_pnr_nr,
                    'Имя банка' => $record->ur_bank_name,
                    'Код банка' => $record->ur_bank_code,
                    'Номер счета банка' => $record->ur_bank_acc_code,
                ] as $label => $value)
                    @if(filled($value))<div style="margin-top: 3px; text-align: left; font-size: 11px;">{{ $label }}: {{ $value }}</div>@endif
                @endforeach
            @endif
        </div>
    @endif

    <div style="margin-top: 18px; padding: 12px; border: 1px solid #dcdcdc; border-radius: 8px; background: #fafafa; text-align: left;">
        <div style="margin-bottom: 10px; color: #1e2533; font-size: 14px; font-weight: 600;">Заявка на оплату</div>
        <button
            type="button"
            x-on:click.stop="$wire.mountTableAction('createPaymentRequest', '{{ $record->getKey() }}')"
            style="display: block; width: 100%; padding: 5px 10px; border: 0; border-radius: 3px; background: #337ab7; color: #fff; font-size: 12px; font-weight: 500; cursor: pointer;"
        >
            Создать платеж
        </button>

        @foreach($paymentRequests as $paymentRequest)
            <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #ececec; font-size: 12px; line-height: 1.4;">
                <div><strong>{{ $paymentRequest->public_number }}</strong></div>
                <div>{{ number_format((float) $paymentRequest->amount, 2) }} {{ $paymentRequest->currency }}</div>
                @if($paymentRequest->purpose)<div>{{ $paymentRequest->purpose }}</div>@endif
                @if($paymentRequest->selected_payment_method)
                    <div>Метод: {{ $paymentMethodLabels[$paymentRequest->selected_payment_method] ?? $paymentRequest->selected_payment_method }}</div>
                @endif
                <span style="display: inline-block; margin-top: 4px; padding: 2px 8px; border-radius: 999px; background: {{ $paymentRequest->status === 'paid' ? '#e8f7e8' : '#fff0d4' }}; color: {{ $paymentRequest->status === 'paid' ? '#247a24' : '#8a5a00' }}; font-size: 11px; font-weight: 600;">
                    {{ $paymentRequest->status === 'paid' ? 'Оплачено' : 'Ожидает оплаты' }}
                </span>
                <div x-data="adminPaymentRequestCopy()">
                    <button type="button" x-on:click.stop="copy(@js($paymentRequest->publicUrl()))" x-bind:disabled="copying" style="display: block; width: 100%; margin-top: 6px; padding: 3px 6px; border: 1px solid #d1d5db; border-radius: 3px; background: white; font-size: 11px; cursor: pointer;">Скопировать ссылку</button>
                    <span x-cloak x-show="copied" role="status" style="display: block; margin-top: 4px; color: #247a24;">Ссылка скопирована</span>
                    <div x-cloak x-show="manual" style="margin-top: 4px;">
                        <label style="display: block;">Скопируйте ссылку вручную
                            <input type="text" readonly value="{{ $paymentRequest->publicUrl() }}" x-on:click.stop="$el.select()" style="width: 100%; padding: 4px; border: 1px solid #d1d5db;">
                        </label>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @endif
</div>
