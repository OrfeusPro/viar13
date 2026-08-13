<div style="background: #f8fafc;
																																													padding: 5px;																																				color: #313942;">
	<p>Даные получателя:</p>
    <p>Email: {{ $order['delivery']['email'] ?? \App\Models\Orders::getFieldPortraitCalc($order, 'Email') ?? '-' }}</p>
	<p>
		Имя: {{ $order['delivery']['first_name'] ?? '-' }}</p>
	<p>
		Фамилия: {{ $order['delivery']['last_name'] ?? '-' }}</p>
    <?php
    $recipientPhone = $order['delivery']['phone'] ?? \App\Models\Orders::getFieldPortraitCalc($order, 'Phone') ?? '-';
    $payerPhone = $order['delivery']['payer_phone'] ?? (isset($order['user']) ? $order['user']->phone : null);
    if (!$payerPhone || $payerPhone === '-') {
        $payerPhone = \App\Models\Orders::getFieldPortraitCalc($order, 'Phone') ?? '-';
    }
    $hasRecipientPhone = array_key_exists('payer_phone', $order['delivery'] ?? [])
        || !empty($order['delivery']['is_no_payer']);
    $normalizePhone = function ($phone) {
        return preg_replace('/\\D+/', '', (string) $phone);
    };
    $isSamePhone = $payerPhone && $recipientPhone && $normalizePhone($payerPhone) === $normalizePhone($recipientPhone);
    $displayPhone = ($payerPhone && $payerPhone !== '-') ? $payerPhone : $recipientPhone;
    $chatPhone = $displayPhone;
    $whatsup_number = ltrim((string) $chatPhone, '+');
    ?>
	@if($hasRecipientPhone && !$isSamePhone)
		<p>Телефон заказчика: {{ $payerPhone }}</p>
		<p>Телефон получателя: {{ $recipientPhone }}</p>
	@else
		<p>Телефон: {{ $displayPhone }}</p>
	@endif
    <p><a href="viber://chat?number={{ $chatPhone }}">Начать чат в Viber</a> </p>
    <p><a href="https://wa.me/<?=$whatsup_number;?>">Начать чат в WhatsApp</a> </p>
    <p><a href="/admin/email-sender?id=<?=$order['user_id'];?>">Написать email </a> </p>
	<p>
		Страна: {{ $order['delivery']['country'] ?? '-' }}</p>
	@if (isset($order['delivery']['city']))
		<p> Город: {{ $order['delivery']['city'] ?? '-' }}</p>
	@endif
	<p>
		Адресс: {{ $order['delivery']['address'] ?? '-' }}</p>
	<p>
		Индекс: {{ $order['delivery']['postal_index'] ?? '-' }}</p>
	@if (isset($order['delivery']['when_send']))
		@php
			$is_time = 1;
			try {
			$when_send1 = new DateTime($order['delivery']['when_send']);
			} catch (\Throwable $th) {
			$when_send1 = $order['delivery']['when_send'];
			$is_time = 0;
			}
			echo '<div class="data_time">' . $order['delivery']['when_send'] . '</div>';
			if ($is_time == 1) {
			echo '<p>Дата доставки:' . $when_send1->format('d/m/Y') . '</p>';
			} else {
			echo "<p class='err_date_time'>Дата доставки:" . $when_send1 . '</p>';
			}
		@endphp
	@endif
	@if (isset($order['delivery']['lang']))
		<p>Язык пользователя: {{ $order['delivery']['lang'] }}</p>
	@endif
	@if ($order['order_image'])
		<p><a target="_blank" href="{{ $order['order_image'] }}">Картинка
				в заказе</a></p>
	@endif
    @if (isset($order['delivery']['sposob']))
        @if ($order['delivery']['sposob'] == 'to_the_door')
            <p>Доставка до дверей дома или работы</p>
        @elseif($order['delivery']['sposob'] == 'pickup_Riga')
            <p>Самовывоз Рига</p>
        @elseif($order['delivery']['sposob'] == 'pickup_Daugavplis')
            <p>Самовывоз Даугавпилс</p>
        @elseif($order['delivery']['sposob'] == 'venipak')
            <p>Доставка Venipak</p>
        @elseif($order['delivery']['sposob'] == 'pickup_at_viar_workshop')
            <p>Забрать в мастерской VIAR</p>
        @elseif($order['delivery']['sposob'] == 'city_delivery')
            <p>Доставка по {{$order['delivery']['city']}}</p>
        @endif
    @endif
	@if ($order['payment'] == 'cash_in_office')
		<p>Наличными при получении в офисе</p>
	@elseif($order['payment'] == 'on_delivery')
		<p>Оплата во время доставки</p>
	@elseif($order['payment'] == 'transfer')
		<p>Оплата перечислением</p>
	@elseif($order['payment'] == 'online_paysera')
		<p>Онлайн банкинг</p>
	@elseif($order['payment'] == 'google_pay')
		<p>Google Pay</p>
	@elseif($order['payment'] == 'apple_pay')
		<p>Apple Pay</p>
	@elseif($order['payment'] == 'paypalOnetimePayment')
		<p>PayPal</p>
    @elseif($order['payment'] == 'prepayment')
        <p>Предоплата</p>
	@elseif($order['payment'] == 'creditcart')
		<p>Картой онлайн</p>
	@endif

</div>
<p>
	Страна:{{ $order['country'] ?? '-' }}. <br>
	Адресс: {{ $order['delivery']['address'] ?? '-' }}.
	<br>
	Индекс: {{ $order['delivery']['postal_index'] ?? '-' }}
</p>

<p>
	@if ($order['payment'] == 'cash_in_office')
		<img style="max-width: 60px;" src="/storage/images/cash.png" data-payment="1" title="Наличными при получении в офисе" alt="Наличными при получении в офисе" />
	@elseif($order['payment'] == 'on_delivery')
		<img style="max-width: 60px;" data-payment="1" src="/storage/images/delivery-payment.png" title="Оплата во время доставки" alt="Оплата во время доставки" />
	@elseif($order['payment'] == 'transfer')
		<img style="max-width: 60px;" src="/storage/images/visa.png" data-payment="1" title="Оплата перечислением" alt="Оплата перечислением" />
	@elseif($order['payment'] == 'online_paysera')
		<img style="max-width: 60px;" src="/storage/images/paysera.svg" style="max-width:100px;" title="Онлайн банкинг" alt="Онлайн банкинг" />
    @elseif($order['payment'] == 'google_pay')
        <img style="max-width: 60px;" src="/theme/viar/img/cart/pbl_ap.png" style="max-width:100px;" title="Google Pay" alt="Google Pay" />
    @elseif($order['payment'] == 'apple_pay')
        <img style="max-width: 60px;" src="/theme/viar/img/cart/pbl_jp.png" style="max-width:100px;" title="Apple Pay" alt="AppApple PayePay" />
    @elseif($order['payment'] == 'paypalOnetimePayment')
        <img style="max-width: 60px;" src="/theme/viar/img/icons/PayPal.svg" style="max-width:100px;" title="PayPal" alt="PayPal" />
	@elseif($order['payment'] == 'creditcart')
		<img style="max-width: 60px;" src="/theme/viar/img/cart/payments.png" title="Картой онлайн" alt="Картой онлайн" />
        @elseif($order['payment'] == 'prepayment')
            <img style="max-width: 60px;" src="/theme/viar/img/cart/prepaid2.png" title="Предоплата" alt="Предоплата" />
        @endif
</p>
<p>
    @if (isset($order['delivery']['sposob']))
        @if ($order['delivery']['sposob'] == 'to_the_door')
            <img style="max-width: 60px;" data-delivery="1" src="/storage/images/truck_icon.png" title="Доставка до дверей дома или работы" alt="Доставка до дверей дома или работы" />
        @elseif ($order['delivery']['sposob'] == 'venipak')
            <img style="max-width: 60px;" data-delivery="1" src="/images/venipak_new.png" style="max-width:100px;" title="Доставка Venipak" alt="Доставка Venipak" />
        @elseif ($order['delivery']['sposob'] == 'pickup_at_viar_workshop')
            <img style="max-width: 60px;" data-delivery="1" src="/images/pickup_at_viar_workshop.png" style="max-width:100px;" title="Доставка Venipak" alt="Доставка Venipak" />
        @elseif ($order['delivery']['sposob'] == 'city_delivery')
            <img style="max-width: 60px;" src="/storage/images/truck_icon.png" data-delivery="1" title="" alt="" />

        @elseif($order['delivery']['sposob'] == 'pickup_Riga')
            @if (intval($order['delivery']['deliv_price'] > 0))
                <img style="max-width: 60px;" src="/storage/images/truck_icon.png" data-delivery="1" title="" alt="" />
            @else
                <img style="max-width: 60px;" src="/storage/images/riga.png" data-delivery="1" title="Самовывоз Рига"
                    alt="Самовывоз Рига" />
            @endif
        @elseif($order['delivery']['sposob'] == 'pickup_Daugavplis')
            @if (intval($order['delivery']['deliv_price'] > 0))
                <img style="max-width: 60px;" src="/storage/images/truck_icon.png" data-delivery="1" title="" alt="" />
            @else
                <img style="max-width: 60px;" src="/storage/images/riga.png" data-delivery="1" title="Самовывоз Даугавпилс"
                    alt="Самовывоз Даугавпилс" />
            @endif
        @endif
    @endif
</p>
