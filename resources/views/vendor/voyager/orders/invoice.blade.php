@php
    $user_link = \URL::to('/') . '/admin/user/' . $order['user_id'];
    $shouldRenderInvoice = ($order['has_pdf'] == 1)
        || (($order_vr_id ?? '') != '' && ($order_vr_id ?? null) != null)
        || !empty($order['ur_name']);
@endphp

@if($shouldRenderInvoice)
<div class="order-invoice-card">
    <ul class="list__adm__items order-invoice-links">
        @if ($order['has_pdf'] == 1)
            <li>
                <a target="_blank" href='/storage/pdf/{{ $order['id'] }}.pdf?ver={{ \Carbon::now() }}'>Счет</a>
            </li>
            <li>
                <a href='{{ route('generate_checkout', ['order_id' => $order['id']]) }}'>Обновить счет</a>
            </li>
        @else
            @if ($order_vr_id != '' && $order_vr_id != null)
                <li>
                    <a href='{{ route('generate_checkout', ['order_id' => $order['id']]) }}'>Генерировать счет</a>
                </li>
            @endif
        @endif

        @if ($order['has_pdf'] == 1)
            @if ($order['pdf_approved'] != 1)
                @php
                    $ac_link = str_replace('admin/', '', $user_link);
                @endphp
                <li>
                    <a href='{{ $ac_link }}/approve_checkout/{{ $order['id'] }}'>Подтвердить счет</a>
                </li>
            @else
                @php
                    $ac_link = str_replace('admin/', '', $user_link);
                @endphp
                <li>
                    <a href='{{ $ac_link }}/approve_checkout/{{ $order['id'] }}'>Повторно подтвердить счет</a>
                </li>
            @endif
        @endif

        @if($order['ur_name'])
            <li><b>Юр. лицо</b></li>
            <li>Имя Юр. лица: {{ $order['ur_name_l'] }}</li>
            <li>Рег. номер: {{ $order['ur_reg_num'] }}</li>
            <li>Адрес: {{ $order['ur_legal_addr'] }}</li>
            <li>VAT номер: {{ $order['ur_pnr_nr'] }}</li>
            <li>Имя банка: {{ $order['ur_bank_name'] }}</li>
            <li>Код банка: {{ $order['ur_bank_code'] }}</li>
            <li>Номер счета банка: {{ $order['ur_bank_acc_code'] }}</li>
        @endif
    </ul>
</div>
@endif
