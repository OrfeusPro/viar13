@php
    $record = $getRecord();
    $vr = $record->vrNumber;
    $vrNumber = match (true) {
        filled($vr?->vrv_1) => 'VR00' . $vr->vrv_1,
        filled($vr?->vrv_2) => 'BAW' . $vr->vrv_2,
        filled($vr?->vrv_3) => 'VRR445' . str_pad((string) $vr->vrv_3, 3, '0', STR_PAD_LEFT),
        filled($vr?->vrv_4) => 'DS020' . $vr->vrv_4,
        default => null,
    };
    $paymentRequests = $record->order_payment_requests;
    $shouldRenderInvoice = (int) $record->has_pdf === 1 || filled($vrNumber) || filled($record->ur_name);
    $defaultFirm = str_starts_with((string) $vrNumber, 'VR00')
        ? [
            'name' => 'SIA "ViarStudia"',
            'address' => 'Druvas iela4, Daugavpils nov., LV-5459',
            'bank' => 'Swedbank',
            'account' => 'LV85HABA0551039079593',
            'office' => 'Lubānas 63/65, Rīga',
        ]
        : [
            'name' => 'Viarcanvas',
            'address' => 'Sėlių a. 16-2, Zarasai',
            'bank' => 'LT193250067283712555',
            'account' => null,
            'office' => null,
        ];
    $paymentMethodLabels = [
        'online_paysera' => 'Онлайн банкинг',
        'creditcart' => 'Картой онлайн',
        'google_pay' => 'Google Pay',
        'apple_pay' => 'Apple Pay',
        'paypalOnetimePayment' => 'PayPal',
        'transfer' => 'Оплата перечислением',
    ];
@endphp

<div style="width: 180px; min-width: 170px; padding: 2px 0; text-align: center; font-size: 13px; line-height: 1.4;">
    <div style="margin-bottom: 18px; color: #7b8190; font-size: 14px; font-weight: 400;">
        №: {{ $record->id }}
    </div>

    @if(!$vrNumber)
        <select
            aria-label="Накладная заказа №{{ $record->id }}"
            x-on:change="if ($el.value) { $wire.mountTableAction('manageVrNumber', '{{ $record->getKey() }}'); } $el.selectedIndex = 0"
            style="display: block; width: 100%; margin-bottom: 18px; padding: 5px 8px; border: 1px solid #9ca3af; border-radius: 4px; background: white; color: #7b8190; font-size: 13px;"
        >
            <option value="">Накладная -</option>
            <option value="VR00">D-Art-Solutions SIA</option>
            <option value="BAW">D-Art-Solutions UAB</option>
            <option value="VRR445">SIA ViarStudia</option>
            <option value="DS020">SIA VR-Technology</option>
        </select>
    @else
        <div style="margin-bottom: 10px;">
            <input type="text" value="{{ $vrNumber }}" readonly style="width: 100%; padding: 5px 7px; border: 1px solid #9ca3af; border-radius: 4px; text-align: center; font-size: 13px;">
            <div style="display: flex; justify-content: center; gap: 5px; margin-top: 8px;">
                <button type="button" x-on:click.stop="$wire.mountTableAction('manageVrNumber', '{{ $record->getKey() }}')" style="padding: 4px 8px; border: 0; border-radius: 3px; background: #337ab7; color: white; font-size: 12px; cursor: pointer;">Обновить</button>
                <button type="button" aria-label="Удалить накладную заказа №{{ $record->id }}" x-on:click.stop="$wire.mountTableAction('manageVrNumber', '{{ $record->getKey() }}')" style="padding: 4px 8px; border: 0; border-radius: 3px; background: #d9534f; color: white; font-size: 12px; cursor: pointer;">×</button>
            </div>
        </div>

        <details style="margin-bottom: 10px; text-align: left;">
            <summary style="display: inline-block; padding: 5px 9px; border: 1px solid #d1d5db; border-radius: 4px; background: #f3f4f6; cursor: pointer;">данные фирмы</summary>
            <div style="margin-top: 6px; font-size: 11px; color: #4b5563;">
                <div><strong>{{ $defaultFirm['name'] }}</strong></div>
                <div>{{ $defaultFirm['address'] }}</div>
                <div>{{ $defaultFirm['bank'] }}</div>
                @if($defaultFirm['account'])<div>{{ $defaultFirm['account'] }}</div>@endif
                @if($defaultFirm['office'])<div>{{ $defaultFirm['office'] }}</div>@endif
            </div>
        </details>
    @endif

    @if($shouldRenderInvoice)
        <div style="margin-top: 10px; padding: 10px 12px; border: 1px solid #e8e8e8; border-radius: 8px; background: white; text-align: center;">
            @if((int) $record->has_pdf === 1)
                <div><a href="{{ asset('storage/pdf/' . $record->id . '.pdf') }}" target="_blank" style="color: #2563eb; text-decoration: underline;">Счет</a></div>
                <div style="margin-top: 5px; color: #6b7280;">{{ (int) $record->pdf_approved === 1 ? 'Счет подтвержден' : 'Счет не подтвержден' }}</div>
            @elseif($vrNumber)
                <div style="color: #6b7280;">Счет не создан</div>
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
                <button type="button" x-on:click="navigator.clipboard.writeText(@js($paymentRequest->publicUrl()))" style="display: block; width: 100%; margin-top: 6px; padding: 3px 6px; border: 1px solid #d1d5db; border-radius: 3px; background: white; font-size: 11px; cursor: pointer;">Скопировать ссылку</button>
            </div>
        @endforeach
    </div>
</div>
