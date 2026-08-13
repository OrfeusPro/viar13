@php
    $orderUser = $order['user'] ?? null;
    $orderDelivery = (isset($order['delivery']) && is_array($order['delivery'])) ? $order['delivery'] : [];
    $fallbackName = trim(
        ((string) ($orderDelivery['first_name'] ?? '')) . ' ' .
        ((string) ($orderDelivery['last_name'] ?? ''))
    );
    $fallbackPhone = (string) ($orderDelivery['payer_phone'] ?? ($orderDelivery['phone'] ?? ''));
    $fallbackEmail = (string) ($orderDelivery['email'] ?? '');
@endphp

<p>Данные пользователя:</p>
<div>
    <p>
        @if ($user_status_id != null && $orderUser)
            @isset($orderUser->first_name)
                {{ $orderUser->first_name }}
            @endisset

            @isset($orderUser->last_name)
                {{ $orderUser->last_name }}
            @endisset
        @elseif ($fallbackName !== '')
            {{ $fallbackName }}
        @endif
    </p>

    @if ($orderUser && !empty($orderUser->phone))
        <p>{{ $orderUser->phone }}</p>
    @elseif ($fallbackPhone !== '')
        <p>{{ $fallbackPhone }}</p>
    @endif

    @if ($orderUser && !empty($orderUser->email))
        <p>{{ $orderUser->email }}</p>
    @elseif ($fallbackEmail !== '')
        <p>{{ $fallbackEmail }}</p>
    @endif

    @if ($orderUser && !empty($orderUser->status))
        <p>{{ $orderUser->status }}</p>
    @endif

    @if ($orderUser)
        <p>Заказов было: {{ $orderUser->getOrdersCount($order['user_id']) }}</p>
    @elseif (!empty($order['user_id']))
        <p style="color: #b94a48;">Пользователь не найден (user_id: {{ $order['user_id'] }})</p>
    @endif

    @if ($orderUser && $orderUser->preferredLocale())
        <p>Язык пользователя: {{ $orderUser->preferredLocale() }}</p>
    @endif

    <div class="form-group">
        <label for="pdf_locale">Язык счета: </label>
        @if ($orderUser)
            <select id="pdf_locale" name="pdf_locale" onchange="change_pdf_locale(this.options[this.selectedIndex].value, {{ $order['user_id'] }})">
                @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                    <option value="{{ $localeCode }}"
                        @if($orderUser->DPFLocale())
                            {{ $orderUser->DPFLocale() == $localeCode ? 'selected' : '' }}
                        @else
                            {{ $orderUser->preferredLocale() == $localeCode ? 'selected' : '' }}
                        @endif
                    >{{ $localeCode }}</option>
                @endforeach
            </select>
        @else
            <div style="color: #999;">Недоступно: у заказа отсутствует связанный пользователь</div>
        @endif
    </div>
</div>
