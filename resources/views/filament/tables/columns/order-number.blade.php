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
@endphp

<div style="min-width: 260px; padding: 4px 0; font-size: 13px;">
    <div style="margin-bottom: 28px; text-align: center; color: #7b8190; font-size: 20px; font-weight: 300;">
        №: {{ $record->id }}
    </div>

    <select
        aria-label="Накладная заказа №{{ $record->id }}"
        x-on:change="if ($el.value === 'manage') { $wire.mountTableAction('manageVrNumber', '{{ $record->getKey() }}'); } $el.selectedIndex = 0"
        style="display: block; width: 100%; margin-bottom: 16px; padding: 7px 10px; border: 1px solid #9ca3af; border-radius: 4px; background: white; color: #7b8190; font-size: 18px;"
    >
        <option value="">Накладная {{ $vrNumber ? '— ' . $vrNumber : '-' }}</option>
        <option value="manage">{{ $vrNumber ? 'Изменить / удалить номер' : 'Добавить номер' }}</option>
    </select>

    @if((int) $record->has_pdf === 1)
        <a
            href="{{ asset('storage/pdf/' . $record->id . '.pdf') }}"
            target="_blank"
            style="display: block; margin: -4px 0 16px; text-align: center; color: #2563eb; font-weight: 500; text-decoration: underline;"
        >
            Открыть счёт PDF
        </a>
    @endif

    <div style="margin-top: 74px; padding: 18px; border: 1px solid #d5d7dc; border-radius: 12px; background: #fff;">
        <div style="margin-bottom: 16px; color: #111827; font-size: 21px; font-weight: 700;">Заявка на оплату</div>
        <button
            type="button"
            x-on:click.stop="$wire.mountTableAction('createPaymentRequest', '{{ $record->getKey() }}')"
            style="display: block; width: 100%; padding: 10px 16px; border: 0; border-radius: 4px; background: #31a9eb; color: #fff; font-size: 20px; font-weight: 500; cursor: pointer;"
        >
            Создать платеж
        </button>
    </div>

    @if(filled($record->ur_name))
        <details class="rounded border border-gray-200 p-1.5 dark:border-gray-700">
            <summary class="cursor-pointer font-medium">Данные фирмы</summary>
            <div class="mt-1 space-y-0.5 text-gray-600 dark:text-gray-300">
                <div>{{ $record->ur_name_l ?: $record->ur_name }}</div>
                @if($record->ur_reg_num)<div>Рег. № {{ $record->ur_reg_num }}</div>@endif
                @if($record->ur_pnr_nr)<div>VAT {{ $record->ur_pnr_nr }}</div>@endif
                @if($record->ur_legal_addr)<div>{{ $record->ur_legal_addr }}</div>@endif
            </div>
        </details>
    @endif

    @foreach($paymentRequests as $paymentRequest)
        <div class="rounded border border-gray-200 p-1.5 dark:border-gray-700">
            <div class="flex items-center justify-between gap-2">
                <strong>{{ $paymentRequest->public_number }}</strong>
                <span class="{{ $paymentRequest->status === 'paid' ? 'text-success-600' : 'text-warning-600' }}">
                    {{ $paymentRequest->status === 'paid' ? 'Оплачено' : 'Ожидает' }}
                </span>
            </div>
            <div>{{ number_format((float) $paymentRequest->amount, 2) }} {{ $paymentRequest->currency }}</div>
            <a class="text-primary-600 underline" href="{{ $paymentRequest->publicUrl() }}" target="_blank">Открыть платёжную ссылку</a>
        </div>
    @endforeach
</div>
