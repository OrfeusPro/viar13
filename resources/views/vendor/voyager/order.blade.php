@extends('voyager::master')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="/css/admin_order.css?v=4">
    <style>
        .textarea__info{
            width: 500px;
            min-height: 150px;
        }
        ul.admin__painter__imgs__list {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            text-align: center;
            list-style-type: none;
        }

        .commentary__bot__inline {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .user__comments__list {
            padding: 0 0 0 15px;
            margin-top: 15px;
        }

        .painter__img__del {
            margin-bottom: 15px;
        }

        .page-content .form-group p {
            margin: 0 0 0px;
        }

        .page-content .form-group {
            margin-bottom: 10px;
        }

        .delivery-admin-block {
            border: 1px solid #d9d9d9;
            border-radius: 8px;
            padding: 14px;
            margin: 12px 0;
            background: #fafafa;
        }

        .payment-admin-block {
            border: 1px solid #d9d9d9;
            border-radius: 8px;
            padding: 14px;
            margin: 12px 0;
            background: #fafafa;
        }

        .sales-admin-block {
            border: 1px solid #d9d9d9;
            border-radius: 8px;
            padding: 14px;
            margin: 12px 0;
            background: #fafafa;
        }

        .payment-request-admin-block {
            border: 1px solid #d9d9d9;
            border-radius: 8px;
            padding: 14px;
            margin: 12px 0;
            background: #fafafa;
        }

        .payment-request-admin-block__top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 14px;
        }

        .payment-request-admin-block__top h4 {
            margin: 0 0 6px;
        }

        .payment-request-admin-block__top p {
            color: #666;
        }

        .payment-request-admin-list {
            display: grid;
            gap: 12px;
        }

        .payment-request-admin-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #fff;
            padding: 12px;
        }

        .payment-request-admin-item__row {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 10px;
        }

        .payment-request-admin-item__meta span {
            display: block;
            color: #777;
            font-size: 12px;
            margin-bottom: 4px;
        }

        .payment-request-admin-item__meta strong {
            display: block;
            word-break: break-word;
        }

        .payment-request-admin-item__link {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .payment-request-admin-item__link input {
            flex: 1 1 380px;
        }

        .payment-request-admin-block__actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .payment-request-status {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }

        .payment-request-status.pending {
            background: #fff0d4;
            color: #8a5a00;
        }

        .payment-request-status.paid {
            background: #e8f7e8;
            color: #247a24;
        }

        .payment-status-select.status-not-payed {
            background: #ff9d9d;
            color: #1e2533;
        }

        .payment-status-select.status-payed {
            background: #2ba92b;
            color: #fff;
        }

        .payment-status-select.status-prepayment {
            background: #c19300;
            color: #fff;
        }

        .order-item-block {
            border: 1px solid #d9d9d9;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 12px;
            background: #fafafa;
        }

        .order-item-images {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 6px;
            border-radius: 8px;
            box-sizing: border-box;
        }

        .order-item-images a {
            display: inline-block;
            flex: 0 0 auto;
        }

        .order-item-images .order-item-image-link {
            width: 120px;
            height: 120px;
        }

        .order-item-images img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: #fff;
            border: 1px solid #eee;
            border-radius: 6px;
            padding: 4px;
        }

    </style>
@stop

@section('page_header')
    <h1 class="page-title">
        Заказ №{{ $order['id'] }}
        @if ($order['is_admin_order'] == 1)
            <em style="font-weight: normal;font-size:small;">заказ с админки</em>
        @endif
    </h1>
@stop

@if (Session::has('updated'))
    <script>
        alert(document.referrer);
        // history.go(-1);
    </script>
@endif

@section('content')
    <div class="page-content container-fluid">
        <div class="form-edit-add">
            <div class="row">
                <div class="col-md-6">

                    <form action="{{ route('update_admin_order', $order['id']) }}" method="POST">

                        <div class="panel panel-bordered">
                            {{-- <div class="panel"> --}}
                            @if (count($errors) > 0)
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @php
                                $order['items'] = json_decode($order['items'], true);
                                $order['delivery'] = json_decode($order['delivery'], true);
                            @endphp

                            {{-- order data --}}
                            <div class="panel-body">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="order_id" value="{{ $order['id'] }}">
                                    @isset($_SERVER['HTTP_REFERER'])
                                        <input type="hidden" name="prev_url" value="{{ $_SERVER['HTTP_REFERER'] }}">
                                    @endif

                                    <div class="form-group">
                                        <label class="control-label" for="input-order-status">Менеджер:</label>
                                        <select name="manager" id="manager" class="form-control">
                                            <option value=""></option>
                                            @php $m_id = 0; @endphp
                                            @if ($managers)
                                                @foreach ($managers as $manager)
                                                    <option value="{{ $manager->id }}" @if ($order['manager_id'] == $manager->id) @php $m_id = $manager->id @endphp selected @endif>@if($manager->nick){{ $manager->nick }}
                                                        - @endif{{ $manager->email }}</option>
                                                @endforeach
                                            @endif
                                            <option value="all" @if ($m_id == 0) selected @endif>Админ</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="admin_comment">Комментарий админа</label>
                                        <input type="text" class="form-control" id="admin_comment" name="admin_comment"
                                            placeholder="" value="{{ $order['admin_comment'] }}">
                                    </div>

                                    <div class="form-group">
                                        <p>Email:</p>
                                        <input class="form-control" type="email" name="email"
                                            value="{{ $order['delivery']['email'] ?? \App\Models\Orders::getFieldPortraitCalc($order, 'Email') ?? null }}">
                                    </div>

                                    <div class="form-group">
                                        <p>Имя:</p>
                                        <input class="form-control" type="text" name="first_name"
                                            value="{{ $order['delivery']['first_name'] ?? null }}">
                                    </div>

                                    <div class="form-group">
                                        <p>Фамилия:</p>
                                        <input class="form-control" type="text" name="last_name"
                                            value="{{ $order['delivery']['last_name'] ?? null }}">
                                    </div>
                                    <div class="form-group">
                                        @php
                                            $deliveryPhone = $order['delivery']['phone'] ?? \App\Models\Orders::getFieldPortraitCalc($order, 'Phone') ?? null;
                                            $hasRecipientPhone = array_key_exists('payer_phone', $order['delivery'] ?? [])
                                                || !empty($order['delivery']['is_no_payer']);
                                            $recipientPhone = $hasRecipientPhone ? $deliveryPhone : null;
                                            $payerPhone = $order['delivery']['payer_phone'] ?? $deliveryPhone;
                                        @endphp
                                        <p>Телефон заказчика:</p>
                                        <input class="form-control" type="text" name="phone"
                                            value="{{ $payerPhone }}">
                                    </div>
                                    <div class="form-group">
                                        <p>Телефон получателя:</p>
                                        <input class="form-control" type="text" name="phone_rec"
                                            value="{{ $recipientPhone }}">
                                    </div>
                                    <div class="form-group">
                                        <p>Комментарий:</p>
                                        @php
                                            $orderCommentValue = $order['delivery']['comment'] ?? ($order['comment'] ?? null);
                                            if ($orderCommentValue === 'null') {
                                                $orderCommentValue = null;
                                            }
                                        @endphp
                                        <input class="form-control" type="text" name="comment"
                                            value="{{ $orderCommentValue }}">
                                    </div>
                                    <div class="sales-admin-block">
                                        @if(isset($order['a_order_from']) && $order['a_order_from'] != null)
                                            <div class="form-group">
                                                <p>Канал продаж:</p>
                                                <select class="form-control" name="a_order_from" placeholder="Страна">
                                                    @foreach ($order_from as $from)
                                                        <option value="{{ $from['id'] }}" @if($order['a_order_from']==$from['id']) selected @endif>{{ $from['title'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif

                                        <div class="form-group">
                                            <p>Категория:</p>
                                            <select class="form-control" name="new_catid" placeholder="Категория">
                                                <option value="0" @if($order['catid']==0) selected @endif>Нет категории
                                                </option>
                                                @foreach ($style as $catid)
                                                    <option value="{{ $catid['id'] }}" @if($order['catid']==$catid['id']) selected @endif>{{ str_replace('</span>','',str_replace('<span>','',$catid->getTranslatedAttribute('name'))) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="payment-admin-block">
                                        @if(isset($order['payment']))
                                            <div class="form-group">
                                                <p>Оплата:</p>
                                                <select class="form-control" name="payment" placeholder="Оплата">
                                                    <option @if ($order['payment'] == 'cash_in_office') selected
                                                            @endif value="cash_in_office">Наличными при
                                                        получении в офисе
                                                    </option>
                                                    <option @if ($order['payment'] == 'on_delivery') selected
                                                            @endif value="on_delivery">Оплата во время
                                                        доставки
                                                    </option>
                                                    <option @if ($order['payment'] == 'transfer') selected
                                                            @endif value="transfer">Оплата перечислением
                                                    </option>
                                                    <option @if ($order['payment'] == 'online_paysera') selected
                                                            @endif value="online_paysera">Онлайн банкинг
                                                    </option>
                                                    <option @if ($order['payment'] == 'google_pay') selected
                                                            @endif value="google_pay">Google Pay
                                                    </option>
                                                    <option @if ($order['payment'] == 'apple_pay') selected
                                                            @endif value="apple_pay">Apple Pay
                                                    </option>
                                                    <option @if ($order['payment'] == 'paypalOnetimePayment') selected
                                                            @endif value="paypalOnetimePayment">PayPal
                                                    </option>
                                                    <option @if ($order['payment'] == 'creditcart') selected
                                                            @endif value="creditcart">Картой онлайн
                                                    </option>
                                                    <option @if ($order['payment'] == 'prepayment') selected
                                                            @endif value="prepayment">Предоплата
                                                    </option>
                                                </select>
                                            </div>
                                        @endif

                                        @if(isset($order['payment_status']))
                                            <div class="form-group">
                                                <p>Статус оплаты:</p>
                                                <select class="form-control payment-status-select" name="payment_status" placeholder="Статус оплаты">
                                                    <option @if ($order['payment_status'] == 'not_payed') selected
                                                            @endif value="not_payed">Не оплачено
                                                    </option>
                                                    <option @if ($order['payment_status'] == 'prepayment') selected
                                                            @endif value="prepayment">Предоплата
                                                    </option>
                                                    <option @if ($order['payment_status'] == 'payed') selected
                                                            @endif value="payed">Оплачено
                                                    </option>
                                                </select>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="payment-request-admin-block">
                                        <div class="payment-request-admin-block__top">
                                            <div>
                                                <h4>Заявки на оплату</h4>
                                                <p>Отдельные доплаты по уникальной ссылке для клиента.</p>
                                            </div>
                                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#paymentRequestModal">
                                                Создать заявку
                                            </button>
                                        </div>

                                        @if(session('payment_request_created_link'))
                                            <div class="alert alert-success">
                                                Создана заявка {{ session('payment_request_created_number') }}.<br>
                                                Ссылка: <a href="{{ session('payment_request_created_link') }}" target="_blank">{{ session('payment_request_created_link') }}</a>
                                            </div>
                                        @endif

                                        @if($errors->has('amount') || $errors->has('purpose'))
                                            <div class="alert alert-danger">
                                                @if($errors->has('amount'))
                                                    <div>{{ $errors->first('amount') }}</div>
                                                @endif
                                                @if($errors->has('purpose'))
                                                    <div>{{ $errors->first('purpose') }}</div>
                                                @endif
                                            </div>
                                        @endif

                                        @if(isset($paymentRequests) && count($paymentRequests) > 0)
                                            <div class="payment-request-admin-list">
                                                @foreach($paymentRequests as $paymentRequest)
                                                    <div class="payment-request-admin-item">
                                                        <div class="payment-request-admin-item__row">
                                                            <div class="payment-request-admin-item__meta">
                                                                <span>Номер</span>
                                                                <strong>{{ $paymentRequest->public_number }}</strong>
                                                            </div>
                                                            <div class="payment-request-admin-item__meta">
                                                                <span>Сумма</span>
                                                                <strong>{{ number_format((float) $paymentRequest->amount, 2) }} {{ $paymentRequest->currency }}</strong>
                                                            </div>
                                                            <div class="payment-request-admin-item__meta">
                                                                <span>Статус</span>
                                                                <strong>
                                                                    <span class="payment-request-status {{ $paymentRequest->status === 'paid' ? 'paid' : 'pending' }}">
                                                                        {{ $paymentRequest->status === 'paid' ? 'Оплачено' : 'Ожидает оплаты' }}
                                                                    </span>
                                                                </strong>
                                                            </div>
                                                            <div class="payment-request-admin-item__meta">
                                                                <span>Метод</span>
                                                                <strong>
                                                                    @switch($paymentRequest->selected_payment_method)
                                                                        @case('online_paysera')
                                                                            Онлайн банкинг
                                                                            @break
                                                                        @case('creditcart')
                                                                            Картой онлайн
                                                                            @break
                                                                        @case('google_pay')
                                                                            Google Pay
                                                                            @break
                                                                        @case('apple_pay')
                                                                            Apple Pay
                                                                            @break
                                                                        @case('paypalOnetimePayment')
                                                                            PayPal
                                                                            @break
                                                                        @case('transfer')
                                                                            Оплата перечислением
                                                                            @break
                                                                        @default
                                                                            {{ $paymentRequest->selected_payment_method ?: 'не выбран' }}
                                                                    @endswitch
                                                                </strong>
                                                            </div>
                                                        </div>
                                                        <div class="payment-request-admin-item__row">
                                                            <div class="payment-request-admin-item__meta">
                                                                <span>Назначение</span>
                                                                <strong>{{ $paymentRequest->purpose ?: 'Без описания' }}</strong>
                                                            </div>
                                                            <div class="payment-request-admin-item__meta">
                                                                <span>Создано</span>
                                                                <strong>{{ optional($paymentRequest->created_at)->format('d.m.Y H:i') ?: '—' }}</strong>
                                                            </div>
                                                            <div class="payment-request-admin-item__meta">
                                                                <span>Оплачено</span>
                                                                <strong>{{ optional($paymentRequest->paid_at)->format('d.m.Y H:i') ?: '—' }}</strong>
                                                            </div>
                                                            <div class="payment-request-admin-item__meta">
                                                                <span>Email</span>
                                                                <strong>{{ $paymentRequest->customer_email ?: '—' }}</strong>
                                                            </div>
                                                        </div>
                                                        <div class="payment-request-admin-item__link">
                                                            <input type="text" readonly class="form-control js-payment-request-link" value="{{ $paymentRequest->publicUrl() }}">
                                                            <div class="payment-request-admin-block__actions">
                                                                <button type="button" class="btn btn-default js-copy-payment-request-link">Копировать ссылку</button>
                                                                <a href="{{ $paymentRequest->publicUrl() }}" target="_blank" class="btn btn-default">Открыть</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p style="margin: 0;">Пока нет созданных заявок на оплату.</p>
                                        @endif
                                    </div>
                                    <div class="delivery-admin-block">
                                        @if(isset($order['delivery']))
                                            <div class="form-group">
                                                <p>Доставка:</p>
                                                <select class="form-control" name="sposob" id="sposob" placeholder="Способ доставки">
                                                    <option {{ isset($order['delivery']['sposob']) && $order['delivery']['sposob'] == 'to_the_door' ? 'selected' : null }}
                                                            value="to_the_door">Доставка до дверей
                                                        дома или работы
                                                    </option>
                                                    <option {{ isset($order['delivery']['sposob']) && $order['delivery']['sposob'] == 'pickup_Riga' ? 'selected' : null }}
                                                            value="pickup_Riga">Самовывоз Рига
                                                    </option>
                                                    <option {{ isset($order['delivery']['sposob']) && in_array($order['delivery']['sposob'], ['pickup_Daugavplis', 'pickup_Daugavpils'], true) ? 'selected' : null }}
                                                            value="pickup_Daugavplis">Самовывоз Даугавпилс
                                                    </option>
                                                    <option {{ isset($order['delivery']['sposob']) && $order['delivery']['sposob'] == 'venipak' ? 'selected' : null }}
                                                            value="venipak">Доставка Venipak
                                                    </option>
                                                    <option {{ isset($order['delivery']['sposob']) && $order['delivery']['sposob'] == 'pickup_at_viar_workshop' ? 'selected' : null }}
                                                            value="pickup_at_viar_workshop">Забрать в мастерской VIAR
                                                    </option>
                                                    <option {{ isset($order['delivery']['sposob']) && $order['delivery']['sposob'] == 'city_delivery' ? 'selected' : null }}
                                                            value="city_delivery">Доставка по городу
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <p>Страна:</p>
                                                <select class="form-control" name="country" placeholder="Страна">
                                                    @foreach ($c_tels as $count)
                                                        <option
                                                                {{ isset($order['delivery']['country']) && $order['delivery']['country'] == $count['country_code'] ? 'selected' : null }}
                                                                value="{{ $count['country_code'] }}">{{ $count['country_code'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif
                                    @php
                                        $selectedPickupWorkshopId = (int)($order['delivery']['pickup_workshop_id'] ?? 0);
                                        $selectedDeliveryTownId = (int)($order['delivery']['delivery_town_id'] ?? 0);
                                        $pickupAddress = trim((string)($order['delivery']['address'] ?? ''));
                                        $deliveryCity = trim((string)($order['delivery']['city'] ?? ''));
                                        $deliveryMethod = (string)($order['delivery']['sposob'] ?? '');
                                    @endphp
                                    <div class="form-group" id="pickup_workshop_container" style="display: none;">
                                        <p>Самовывоз (мастерская VIAR):</p>
                                        <select class="form-control" name="pickup_workshop_id" id="pickup_workshop_id">
                                            <option value="">-- выберите пункт --</option>
                                            @foreach (($deliveryPickupAtViarWorkshop ?? []) as $pickupPoint)
                                                @php
                                                    $pickupTitle = trim((string)$pickupPoint->title);
                                                    $countryCodes = collect(explode(',', (string)($pickupPoint->country_code ?? 'ALL')))
                                                        ->map(function ($code) { return strtoupper(trim($code)); })
                                                        ->filter(function ($code) { return $code !== ''; })
                                                        ->values();
                                                    if ($countryCodes->isEmpty()) {
                                                        $countryCodes = collect(['ALL']);
                                                    }
                                                    $isCurrentPickup = $selectedPickupWorkshopId > 0
                                                        ? $selectedPickupWorkshopId === (int)$pickupPoint->id
                                                        : (
                                                            ($deliveryMethod === 'pickup_Riga' && (int)$pickupPoint->id === 1) ||
                                                            (in_array($deliveryMethod, ['pickup_Daugavplis', 'pickup_Daugavpils'], true) && (int)$pickupPoint->id === 2) ||
                                                            ($pickupAddress !== '' && mb_strtolower($pickupAddress) === mb_strtolower($pickupTitle))
                                                        );
                                                @endphp
                                                <option
                                                    value="{{ $pickupPoint->id }}"
                                                    data-country="{{ $countryCodes->implode(',') }}"
                                                    @if($isCurrentPickup) selected @endif>
                                                    {{ $pickupTitle }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group" id="delivery_town_container" style="display: none;">
                                        <p>Город (доставка по городу):</p>
                                        <select class="form-control" name="delivery_town_id" id="delivery_town_id">
                                            <option value="">-- выберите город --</option>
                                            @foreach (($deliveryTowns ?? []) as $town)
                                                @php
                                                    $townName = trim((string)$town->city);
                                                    $townCountry = strtoupper(trim((string)($town->country ?? '')));
                                                    $isCurrentTown = $selectedDeliveryTownId > 0
                                                        ? $selectedDeliveryTownId === (int)$town->id
                                                        : ($deliveryCity !== '' && mb_strtolower($deliveryCity) === mb_strtolower($townName));
                                                @endphp
                                                <option
                                                    value="{{ $town->id }}"
                                                    data-country="{{ $townCountry }}"
                                                    @if($isCurrentTown) selected @endif>
                                                    {{ $townName }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group" id="city_container">
                                        <p>Город:</p>
                                        <input class="form-control" type="text" name="city"
                                            value="{{ $order['delivery']['city'] ?? null }}">
                                    </div>
                                    <div class="form-group">
                                        <p>Адресс:</p>
                                        <input class="form-control" type="text" name="address"
                                            value="{{ $order['delivery']['address'] ?? null }}">
                                    </div>
                                    <div class="form-group">
                                        <p>Индекс:</p>
                                        <input class="form-control" type="text" name="postal_index"
                                            value="{{ $order['delivery']['postal_index'] ?? null }}">
                                    </div>
                                        <div class="form-group">
                                            <p>Когда получить:</p>
                                            <input class="form-control" type="date" name="when_send" value=@if(isset($order['delivery']['when_send']))"{{ $order['delivery']['when_send'] }}" @else "" @endif>
                                        </div>
                                        <div class="form-group">
                                            <p>Цена за доставку:</p>
                                            <input class="form-control" type="text" name="deliv_price"
                                                @if (isset($order['delivery']['deliv_price'])) value="{{ $order['delivery']['deliv_price'] }}"
                                                @else value="0" @endif>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <p>Скидка в евро:</p>
                                        <input type="number" class="form-control" name="sale_eur"
                                            @if ($order['sale_eur'] != null && $order['sale_eur'] != '') value="{{ $order['sale_eur'] }}"
                                            @else value="0" @endif>
                                    </div>
                                    <div class="form-group">
                                        <p>Скидка в %:</p>
                                        <input type="number" class="form-control" name="sale_percent"
                                            @if ($order['sale_percent'] != null && $order['sale_percent'] != '') value="{{ $order['sale_percent'] }}"
                                            @else value="0" @endif>
                                    </div>

                                    <div class="form-group" style="display:none;">
                                        <p>Оригинальная цена:</p>
                                        <input class="form-control" type="text" name="price"
                                            value="{{ $order['price'] }}">
                                    </div>

                                    <div class="form-group" style="display:none;">
                                        <p>Цена со скидкой:</p>
                                        <input class="form-control" type="text" name="sale_price"
                                            @if ($order['sale_price'] != '' && $order['sale_price'] != null)value="{{ $order['sale_price'] }}">
                                        @else value="{{ $order['price'] }}"> @endif
                                    </div>



                                    @if(isset($order['all_sales']))
                                        <div class="form-group">
                                            <p>
                                            @php
                                                $sales = json_decode($order['all_sales']);
                                            @endphp
                                            @if ($sales != null)
                                                @isset($sales->facebook_sale)
                                                    <p>Скидка facebook: @if ($sales->facebook_sale != 0)
                                                            {{ $sales->facebook_sale }} % @else нет @endif
                                                    </p>
                                                @endisset

                                                @isset($sales->used_bonuses)
                                                    <p>Скидка за
                                                        бонусы: @if ($sales->used_bonuses != 0) {{ $sales->used_bonuses }}
                                                        € @else нет @endif
                                                    </p>
                                                @endisset

                                                @isset($sales->used_coupon)
                                                    <p> Скидка за
                                                        купон: @if ($sales->used_coupon != 0) {{ $sales->used_coupon }} €
                                                        @else нет @endif
                                                    </p>
                                                @endisset

                                                @isset($sales->used_coupon_30_40)
                                                    <p>Скидка на товар 30x40: @if ($sales->used_coupon_30_40 == 1) да @else
                                                            нет
                                                        @endif
                                                    </p>
                                                @endisset

                                                @isset($sales->dates_sale)
                                                    <p>Скидка за 2
                                                        даты: @if ($sales->dates_sale != 0) {{ $sales->dates_sale }} %
                                                        @else нет @endif
                                                    </p>
                                                @endisset
                                            @endif
                                            <br>
                                            </p>
                                        </div>
                                    @endif

                                    @php
                                        $orderApprovedDate = $order['approved_date'] ?? null;
                                        $orderCreatedAt = $order['created_at'] ?? null;
                                        $orderDateValue = $orderApprovedDate;
                                        if (!$orderDateValue && $orderCreatedAt) {
                                            $orderDateValue = date('d.m.Y H:i', strtotime($orderCreatedAt));
                                        }
                                    @endphp
                                    <div class="form-group">
                                        <p>Дата заказа:</p>
                                        <input class="form-control" type="text" name="approved_date"
                                            value="{{ $orderDateValue }}">
                                    </div>

                                    @if($order['is_admin_order'] == 2)
                                        <div class="form-group">
                                            <p>Информация о заказе:</p>
                                            @foreach ($order['items'] as $product)
                                                @if(isset($product['content']))
                                                    <textarea class="richTextBox textarea__info" name="content_data">{!! str_replace('<br>', PHP_EOL, strip_tags($product['content'], '<br>')) !!}</textarea>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif

                                    <button type="submit" class="btn-primary btn primary">Обновить</button>
                            </div>
                        </div>

                        <div class="panel panel-bordered">
                            <div class="panel-heading">
                                <h3 class="panel-title">Юридическая информация</h3>
                            </div>
                            <div class="panel-body" style="padding-top: 10px;">
                                <!-- Чекбокс "Я юр. лицо" -->
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="ur_name" class="js_ur" {{ isset($order['ur_name']) && $order['ur_name'] ? 'checked' : '' }}>
                                            Юр. лицо (Включать для вывода в счете)
                                        </label>
                                    </div>
                                </div>
                                <!-- Имя юридического лица -->
                                <div class="form-group">
                                    <p>Имя Юр. лица:</p>
                                    <input type="text" class="form-control" name="ur_name_l" placeholder="Имя Юр. лица" value="{{ $order['ur_name_l'] ?? '' }}">
                                </div>
                                <!-- Регистрационный номер -->
                                <div class="form-group">
                                    <p>Рег. номер:</p>
                                    <input type="text" class="form-control" name="ur_reg_num" placeholder="Рег. номер" value="{{ $order['ur_reg_num'] ?? '' }}">
                                </div>
                                <!-- Адрес (может быть 2 строки) -->
                                <div class="form-group">
                                    <p>Адрес:</p>
                                    <textarea class="form-control" name="ur_legal_addr" placeholder="Адрес" rows="2">{{ $order['ur_legal_addr'] ?? '' }}</textarea>
                                </div>
                                <!-- VAT NUMBER -->
                                <div class="form-group">
                                    <p>VAT NUMBER:</p>
                                    <input type="text" class="form-control" name="ur_pnr_nr" placeholder="VAT NUMBER" value="{{ $order['ur_pnr_nr'] ?? '' }}">
                                </div>
                                <!-- Имя банка -->
                                <div class="form-group">
                                    <p>Имя банка:</p>
                                    <input type="text" class="form-control" name="ur_bank_name" placeholder="Имя банка" value="{{ $order['ur_bank_name'] ?? '' }}">
                                </div>
                                <!-- Код банка -->
                                <div class="form-group">
                                    <p>Код банка:</p>
                                    <input type="text" class="form-control" name="ur_bank_code" placeholder="Код банка" value="{{ $order['ur_bank_code'] ?? '' }}">
                                </div>
                                <!-- Номер счета банка -->
                                <div class="form-group">
                                    <p>Номер счета банка:</p>
                                    <input type="text" class="form-control" name="ur_bank_acc_code" placeholder="Номер счета банка" value="{{ $order['ur_bank_acc_code'] ?? '' }}">
                                </div>
                                <button type="submit" class="btn-primary btn primary">Обновить</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-6">
                    <div class="panel">
                        <div class="panel__part">
                            <div class="panel-body">
                                <h3 style="text-align: center;">Фото клиента</h3>
                                <ul class="admin__painter__imgs__list">
                                    @foreach ($order['items'] as $product)
                                        @isset($product['orig_images'])
                                            @foreach ($product['orig_images'] as $img)
                                                <li class="painter__img__del">
                                                        <span style="margin-right:5px;">
                                                            <a href="{{ $img }}" target="_blank">
                                                        <img src="{{ order_image_url($img) }}" alt=""
                                                             style="width:150px;height:150px;object-fit:contain;">
                                                            </a>
                                                        </span>
                                                </li>
                                            @endforeach
                                        @endif
                                    @endforeach
                                </ul>
                            </div>

                            <div class="panel-body">
                                <h3 style="text-align: center;">Наброски художника</h3>
                                @php
                                    $dbPainterSketchImages = collect($order['order_painter_images'] ?? [])->where('is_img_sketch', 1)->values();
                                    $legacyPainterSketchImages = collect(array_values(array_filter(array_map('trim', explode(',', trim((string) ($order['painter_sketch_images'] ?? ''), ','))))));
                                @endphp

                                @if ($dbPainterSketchImages->isNotEmpty())
                                    <ul class="admin__painter__imgs__list">
                                        @foreach ($dbPainterSketchImages as $painterImg)
                                            @php
                                                $previewPath = $painterImg->small_image ?: $painterImg->image;
                                                $previewSrc = order_image_url($previewPath);
                                                $fullSrc = order_image_url($painterImg->image);
                                            @endphp
                                            <li class="painter__img__del">
                                                <a target="_blank" href="{{ $fullSrc }}">
                                                    <img src="{{ $previewSrc }}" alt=""
                                                         style="width:150px;height:150px;object-fit:contain;">
                                                </a>
                                                <br>
                                                <select class="form-control" name="status"
                                                        onchange="changeImageStatus(this.options[this.selectedIndex].value, {{ $order['id'] }}, {{ $painterImg->id }}, '{{ route('changeOrderPainterImageStatus') }}')">
                                                    @if ($APainterImagesStatus)
                                                        @foreach ($APainterImagesStatus as $pis)
                                                            <option value="{{ $pis->id }}" @if ((int) $painterImg->status === (int) $pis->id) selected @endif>{{ $pis->title }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                                <br><span>{{ $painterImg->updated_at }}</span><br>
                                                <a href="{{ route('remove_painter_sketch_image', ['id' => $order['id'], 'row_id' => $painterImg->id]) }}"
                                                   class="btn btn-danger" type="submit">x</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @elseif ($legacyPainterSketchImages->isNotEmpty())
                                    @php
                                        $i = -1;
                                    @endphp

                                    <div style="text-align: center; padding-bottom:20px;">
                                        <select class="form-control" id="status" name="status" onchange="changePainterSketchImagesStatus(this.options[this.selectedIndex].value, {{ $order['id'] }})">
                                        @if ($APainterImagesStatus)
                                            @foreach ($APainterImagesStatus as $pis)
                                                <option value="{{ $pis->id }}" @if (isset($order['painter_sketch_images_status']) and $order['painter_sketch_images_status'] == $pis->id) selected @endif>{{ $pis->title }}</option>
                                            @endforeach
                                        @endif
                                        </select>
                                        <br><span>{{ $order['painter_sketch_images_status_date'] }}</span>
                                    </div>

                                    <ul class="admin__painter__imgs__list">
                                        @foreach ($legacyPainterSketchImages as $painter_img)
                                            @php
                                                $i++;
                                                $painterImgSrc = order_image_url($painter_img);
                                            @endphp
                                            <li class="painter__img__del">
                                                <a target="_blank" href="{{ $painterImgSrc }}">
                                                    <img src="{{ $painterImgSrc }}" alt=""
                                                         style="width:150px;height:150px;object-fit:contain;">
                                                </a><br>
                                                <a href="{{ route('remove_painter_sketch_image', ['id' => $order['id'], 'img_id' => $i]) }}"
                                                   class="btn btn-danger" type="submit">x</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                            </div>

                            <div class="panel-body">
                                <h3 style="text-align: center;">Картины художника</h3>
                                @php
                                    $dbPainterImages = collect($order['order_painter_images'] ?? [])->where('is_img_painter', 1)->values();
                                    $legacyPainterImages = collect(array_values(array_filter(array_map('trim', explode(',', trim((string) ($order['painter_images'] ?? ''), ','))))));
                                @endphp

                                @if ($dbPainterImages->isNotEmpty())
                                    <ul class="admin__painter__imgs__list">
                                        @foreach ($dbPainterImages as $painterImg)
                                            @php
                                                $previewPath = $painterImg->small_image ?: $painterImg->image;
                                                $previewSrc = order_image_url($previewPath);
                                                $fullSrc = order_image_url($painterImg->image);
                                            @endphp
                                            <li class="painter__img__del">
                                                <a target="_blank" href="{{ $fullSrc }}">
                                                    <img src="{{ $previewSrc }}" alt=""
                                                         style="width:150px;height:150px;object-fit:contain;">
                                                </a>
                                                <br>
                                                <select class="form-control" name="status"
                                                        onchange="changeImageStatus(this.options[this.selectedIndex].value, {{ $order['id'] }}, {{ $painterImg->id }}, '{{ route('changeOrderPainterImageStatus') }}')">
                                                    @if ($APainterImagesStatus)
                                                        @foreach ($APainterImagesStatus as $pis)
                                                            <option value="{{ $pis->id }}" @if ((int) $painterImg->status === (int) $pis->id) selected @endif>{{ $pis->title }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                                <br><span>{{ $painterImg->updated_at }}</span><br>
                                                <a href="{{ route('remove_painter_image', ['id' => $order['id'], 'img_id' => 0, 'row_id' => $painterImg->id]) }}"
                                                   class="btn btn-danger" type="submit">x</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @elseif ($legacyPainterImages->isNotEmpty())
                                    @php
                                        $i = -1;
                                    @endphp

                                    <div style="text-align: center; padding-bottom:20px;">
                                        <select class="form-control" id="status" name="status" onchange="changePainterImagesStatus(this.options[this.selectedIndex].value, {{ $order['id'] }})">
                                        @if ($APainterImagesStatus)
                                            @foreach ($APainterImagesStatus as $pis)
                                                <option value="{{ $pis->id }}" @if (isset($order['painter_images_status']) and $order['painter_images_status'] == $pis->id) selected @endif>{{ $pis->title }}</option>
                                            @endforeach
                                        @endif
                                        </select>
                                        <br><span>{{ $order['painter_images_status_date'] }}</span>
                                    </div>

                                    <ul class="admin__painter__imgs__list">
                                        @foreach ($legacyPainterImages as $painter_img)
                                            @php
                                                $i++;
                                                $painterImgSrc = order_image_url($painter_img);
                                            @endphp
                                            <li class="painter__img__del">
                                                <a target="_blank" href="{{ $painterImgSrc }}">
                                                    <img src="{{ $painterImgSrc }}" alt=""
                                                         style="width:150px;height:150px;object-fit:contain;">
                                                </a><br>
                                                <a href="{{ route('remove_painter_image', ['id' => $order['id'], 'img_id' => $i]) }}"
                                                   class="btn btn-danger" type="submit">x</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                            </div>
                            <div class="panel-body">
                                <h3 style="text-align: center;">Фото коммент и текст клиента</h3>
                                @php
                                    $user_comments = \App\Models\User::get_user_acc_comments($order['id']);
                                @endphp
                                <ul class="commentary commentary__bot commentary__bot__inline">
                                    <li>Комментарии<br> клиента к картинам:<br>{{ $order['client_comment'] }}</li>
                                    @if ($user_comments)
                                        <ul class="user__comments__list">
                                            @foreach ($user_comments as $u_comment)
                                                <li class="order_comment">
                                                            <span>{{ date('d.m.Y', strtotime($u_comment->created_at)) }} -
                                                            </span>{{ $u_comment->comment }}
                                                </li><br>
                                            @endforeach
                                        </ul>
                                    @endif
                                </ul>
                                <p>
                                    Фото - {{ $order['client_comment'] }}
                                </p>
                                <div class="photo_comment">
                                    @php
                                        $client_imgs = $order['client_images'];
                                        $client_imgs = trim($client_imgs, ',');
                                        $client_imgs_items = explode(',', $client_imgs);
                                    @endphp

                                    @if (is_array($client_imgs_items) && !empty($client_imgs_items) && $client_imgs_items[0] != '')
                                        <ul class="admin__painter__imgs__list">
                                            @php
                                                $j = -1;
                                                $j++;
                                            @endphp
                                            @foreach ($client_imgs_items as $img_item)
                                                <li>
                                                    @php
                                                        $clientImgSrc = order_image_url($img_item);
                                                    @endphp
                                                    <a href="{{ $clientImgSrc }}" target="_blank">
                                                        <img src="{{ $clientImgSrc }}" alt=""
                                                             style="width:150px;height:150px;object-fit:contain;">
                                                        <br>
                                                        <a href="{{ route('remove_user_image', ['id' => $order['id'], 'img_id' => $j]) }}"
                                                           class="btn btn-danger" type="submit">x</a>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-bordered">
                        <div class="panel-body">
                            <div class="form-group">
                                <h3 style="text-align: center;">Товары в заказе</h3><br>
                            </div>
                            @foreach ($order['items'] as $key=>$product)
                                @php
                                    if (!is_array($product) || is_string($product) || isset($product['totalPrice'])) {
                                        continue;
                                    }
                                @endphp

                                <form class="order-item-block" action="{{ route('update_order_item_price') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="index" value="{{ $key }}">
                                    <input type="hidden" name="order_id" value="{{ $order['id'] }}">
                                    <input type="hidden" name="item_index" value="{{ $key }}">
                                    <div class="form-group">
                                        @if(isset($product['name']))
                                            @if ($product['name'])
                                                <label>Имя:</label>
                                                <input style="margin-bottom: 10px;" class="form-control" type="text" name="name"
                                                       value="{{ $product['name'] }}">
                                            @endif
                                        @endif
                                                <label>Размер:</label>
                                                @if(isset($product['size_name']))
                                                <input style="margin-bottom: 10px;" class="form-control js__size_name" type="text" name="size_name"
                                                    value=@if(isset($product['size_name']))"{{ $product['size_name'] }}"@else "" @endif>
                                                @endif

                                                @if(isset($product['sizeId']))
                                                <input style="margin-bottom: 10px;" class="form-control js__size_id" type="text" name="sizeId"
                                                    value=@if(isset($product['sizeId']))"{{ $product['sizeId'] }}"@else "" @endif>
                                                @endif

                                                <label>Цена:</label>
                                                <input style="margin-bottom: 10px;" class="form-control js__price" type="text" name="price"
                                                       value=@if(isset($product['price']))"{{ $product['price'] }}"@else "0" @endif>

                                                <label>Комментарий к позиции:</label>
                                                @php
                                                    $itemComment = $product['userComment']
                                                        ?? ($product['user_comment'] ?? ($product['comment'] ?? ''));
                                                    $giftCode = strtoupper((string)($product['manual_gift_code'] ?? ''));
                                                    if (!in_array($giftCode, ['G0', 'G1', 'G2'], true)) {
                                                        $giftCode = 'G0';
                                                        $boxCandidates = [];
                                                        if (isset($product['boxIds'])) {
                                                            $boxIdsRaw = $product['boxIds'];
                                                            if (is_string($boxIdsRaw)) {
                                                                $decodedBoxIds = json_decode($boxIdsRaw, true);
                                                                if (is_array($decodedBoxIds)) {
                                                                    $boxIdsRaw = $decodedBoxIds;
                                                                }
                                                            }
                                                            if (is_array($boxIdsRaw)) {
                                                                foreach ($boxIdsRaw as $boxIdCandidate) {
                                                                    $boxCandidates[] = (int)$boxIdCandidate;
                                                                }
                                                            } elseif ($boxIdsRaw !== null) {
                                                                $boxCandidates[] = (int)$boxIdsRaw;
                                                            }
                                                        }
                                                        if (isset($product['compl_id'])) {
                                                            $boxCandidates[] = (int)$product['compl_id'];
                                                        }
                                                        foreach ($boxCandidates as $boxIdCandidate) {
                                                            if ($boxIdCandidate === 1) {
                                                                $giftCode = 'G2';
                                                                break;
                                                            }
                                                            if ($boxIdCandidate === 2) {
                                                                $giftCode = 'G1';
                                                            }
                                                        }
                                                    }

                                                    $canvasId = isset($product['manual_canvas_id']) ? (int)$product['manual_canvas_id'] : 0;
                                                    if ($canvasId < 1 || $canvasId > 5) {
                                                        $canvasId = isset($product['canvasId']) ? (int)$product['canvasId'] : 0;
                                                    }
                                                    if ($canvasId < 1 || $canvasId > 5) {
                                                        $canvasId = isset($product['holst_id']) ? (int)$product['holst_id'] : 0;
                                                    }
                                                    if ($canvasId < 1 || $canvasId > 5) {
                                                        $canvasId = 2;
                                                    }

                                                    $decorationId = isset($product['manual_decoration_id']) ? (int)$product['manual_decoration_id'] : 0;
                                                    if (!in_array($decorationId, [1, 2, 3, 5], true)) {
                                                        $legacyDecorationId = (int)($product['decorationId'] ?? ($product['decor_id'] ?? ($product['decorId'] ?? 0)));
                                                        if (in_array($legacyDecorationId, [1, 2, 3, 5], true)) {
                                                            $decorationId = $legacyDecorationId;
                                                        }
                                                    }
                                                    if (!in_array($decorationId, [1, 2, 3, 5], true)) {
                                                        $manualLacCode = strtoupper((string)($product['manual_lac_code'] ?? ''));
                                                        $manualBrushCode = strtoupper((string)($product['manual_brushstrokes_code'] ?? ''));
                                                        if ($manualLacCode === 'L2') {
                                                            $decorationId = 1;
                                                        } elseif ($manualBrushCode === 'P1') {
                                                            $decorationId = 2;
                                                        } elseif ($manualLacCode === 'L1') {
                                                            $decorationId = 3;
                                                        } else {
                                                            $decorationId = 5;
                                                        }
                                                    }

                                                    $orientationCode = strtoupper((string)($product['manual_orientation_code'] ?? ''));
                                                    if (!in_array($orientationCode, ['V0', 'V1', 'V2', 'V3', 'V4'], true)) {
                                                        $legacyFormId = (int)($product['formId'] ?? ($product['forma_id'] ?? ($product['form_id'] ?? 0)));
                                                        if ($legacyFormId >= 1 && $legacyFormId <= 4) {
                                                            $orientationCode = 'V' . $legacyFormId;
                                                        } else {
                                                            $orientationCode = 'V0';
                                                        }
                                                    }

                                                    $manualBagetCode = strtoupper((string)($product['manual_baget_code'] ?? ''));
                                                    if (!in_array($manualBagetCode, ['B0', 'B1', 'B2'], true)) {
                                                        $manualBagetCode = !empty($product['ram_id'])
                                                            ? \App\Models\CanvasRam::productionBagetCode($product['ram_id'])
                                                            : (!empty($product['is_manual_baget']) ? 'B1' : 'B0');
                                                    }
                                                    $termsRaw = trim((string)($product['terms'] ?? ''));
                                                    $termsHasExpress = (
                                                        stripos($termsRaw, 'Express') !== false
                                                        || stripos($termsRaw, 'Ekspress') !== false
                                                        || stripos($termsRaw, 'Ekspresowy') !== false
                                                        || stripos($termsRaw, 'Экспресс') !== false
                                                        || stripos($termsRaw, 'Kiirsaadetis') !== false
                                                        || stripos($termsRaw, 'Ekspres') !== false
                                                    );
                                                    $manualExpressChecked = !empty($product['is_manual_express'])
                                                        || (!empty($product['terms_price']) && (float)$product['terms_price'] > 0)
                                                        || $termsHasExpress;
                                                @endphp
                                                <textarea class="form-control" name="userComment" style="min-height: 80px; resize: vertical;">{{ $itemComment }}</textarea>

                                                <label style="margin-top: 10px; display:block;">Опции для названия файла:</label>
                                                <label style="margin-top: 8px; display:block;">Вид холста:</label>
                                                <select class="form-control" name="manual_canvas_id">
                                                    <option value="1" @if($canvasId === 1) selected @endif>Эконом (S)</option>
                                                    <option value="2" @if($canvasId === 2) selected @endif>Интерьерный (S)</option>
                                                    <option value="3" @if($canvasId === 3) selected @endif>Синтетический (S)</option>
                                                    <option value="4" @if($canvasId === 4) selected @endif>Хлопковый (C)</option>
                                                    <option value="5" @if($canvasId === 5) selected @endif>Глянцевый (G)</option>
                                                </select>
                                                <label style="margin-top: 8px; display:block;">Подарочная упаковка:</label>
                                                <select class="form-control" name="manual_gift_code">
                                                    <option value="G0" @if($giftCode === 'G0') selected @endif>Обычная упаковка (G0)</option>
                                                    <option value="G1" @if($giftCode === 'G1') selected @endif>Подарочная бумага (G1)</option>
                                                    <option value="G2" @if($giftCode === 'G2') selected @endif>Эксклюзивная упаковка (G2)</option>
                                                </select>

                                                <label style="margin-top: 8px; display:block;">Лак / мазки:</label>
                                                <select class="form-control" name="manual_decoration_id">
                                                    <option value="5" @if($decorationId === 5) selected @endif>Стандарт. Без дополнений (L0, P0)</option>
                                                    <option value="1" @if($decorationId === 1) selected @endif>Арт-гель (L2, P0)</option>
                                                    <option value="2" @if($decorationId === 2) selected @endif>Художественные мазки (L0, P1)</option>
                                                    <option value="3" @if($decorationId === 3) selected @endif>Даммарный лак (L1, P0)</option>
                                                </select>

                                                <label style="margin-top: 8px; display:block;">Ориентация:</label>
                                                <select class="form-control" name="manual_orientation_code">
                                                    <option value="V0" @if($orientationCode === 'V0') selected @endif>Авто по размеру (V0)</option>
                                                    <option value="V1" @if($orientationCode === 'V1') selected @endif>Вертикальная (V1)</option>
                                                    <option value="V2" @if($orientationCode === 'V2') selected @endif>Горизонтальная (V2)</option>
                                                    <option value="V3" @if($orientationCode === 'V3') selected @endif>Квадрат (V3)</option>
                                                    <option value="V4" @if($orientationCode === 'V4') selected @endif>Панорама (V4)</option>
                                                </select>

                                                <label style="margin-top: 8px; display:block;">Оформление:</label>
                                                <select class="form-control" name="manual_baget_code">
                                                    <option value="B0" @if($manualBagetCode === 'B0') selected @endif>Без рамки (B0)</option>
                                                    <option value="B1" @if($manualBagetCode === 'B1') selected @endif>Рамка (B1)</option>
                                                    <option value="B2" @if($manualBagetCode === 'B2') selected @endif>На бумаге, в рамке (B2)</option>
                                                </select>
                                                <label style="display:block;"><input type="checkbox" name="manual_express" value="1" @if($manualExpressChecked) checked @endif> Экспресс</label>

                                                @php
                                                    $origImages = $product['orig_images'] ?? [];
                                                    if (is_string($origImages)) {
                                                        $decodedOrig = json_decode($origImages, true);
                                                        if (is_array($decodedOrig)) {
                                                            $origImages = $decodedOrig;
                                                        } else {
                                                            $origImages = array_filter(array_map('trim', explode(',', $origImages)));
                                                        }
                                                    }
                                                    if (!is_array($origImages)) {
                                                        $origImages = [];
                                                    }
                                                    $origImages = array_values(array_filter($origImages, function ($v) {
                                                        return is_string($v) && trim($v) !== '';
                                                    }));
                                                @endphp
                                                @if (!empty($origImages))
                                                    <p style="margin-top: 10px;">Фото клиента:</p>
                                                    <div class="order-item-images">
                                                        @foreach ($origImages as $img)
                                                            @php
                                                                $imgSrc = order_image_url($img);
                                                            @endphp
                                                            <a class="order-item-image-link" href="{{ $imgSrc }}" target="_blank">
                                                                <img src="{{ $imgSrc }}" alt="">
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                @endif
                                    </div>

                                    <hr style="margin: 12px 0;">
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <p>Добавить фото к этой позиции:</p>
                                        <input type="file"
                                               accept=".png,.bmp,.jpg,.jpeg,.psd,.fig,.pdf,.heic,.heif"
                                               multiple
                                               class="js_painter_images"
                                               name="user_images[]"
                                               id="js_painter_images_{{ $order['id'] }}_{{ $key }}">
                                    </div>

                                    <div class="form-group" style="display:flex; gap:10px; flex-wrap:wrap; margin-top: 10px;">
                                        <button class="btn-primary btn primary" type="submit" formnovalidate>Обновить</button>
                                        <button class="btn-primary btn primary" type="submit" formaction="{{ route('add_client_images') }}">Добавить фото</button>
                                    </div>
                                </form>
                                {{-- <div class="orig__images__cover"> --}}
                                {{-- @isset($product['orig_images'])
                                    <span class="img__titles">Картинки</span>
                                    <div class="orig_img">
                                    @foreach ($product['orig_images'] as $img)
                                        <span class="org__img__single">
                                            <a class="admin__img_itm" href="{{ $img }}" target="_blank">
                                                <img src="{{ order_image_url($img) }}" alt="">
                                            </a>
                                            <div class="clear"></div>
                                            <form method="POST" action="{{ route('delete_order_image') }}">
                                                @csrf
                                                <input type="hidden" name="img_url" value="{{ $img }}">
                                                <input type="hidden" name="order_id" value="{{  $order['id'] }}">
                                                <button class="btn btn-danger" type="submit">Удалить</button>
                                            </form>
                                        </span>
                                    @endforeach
                                </div>
                            @endif --}}
                            @endforeach
                            {{-- </div> --}}
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <form action="{{ route('add_order_item') }}" enctype="multipart/form-data" method="POST">
                                    @csrf
                                    <input type="hidden" name="order_id" value="{{ $order['id'] }}">
                                    <h3>Добавить товар</h3><br>
                                    <div class="form-group">
                                        <p>Название:</p>
                                        <input class="form-control" type="text" name="name" value="">
                                    </div>
                                    <div class="form-group">
                                        <p>Цена:</p>
                                        <input class="form-control js__price" name="price" value="">
                                    </div>
                                    <div class="form-group">
                                        <p>Размер:</p>
                                        <input class="form-control" type="text" name="size" value="">
                                    </div>
                                    <div class="form-group">
                                        <p>Картинки:</p>
                                        <input class="form-control" type="file" multiple name="basket_images[]"
                                               value="" accept="image/*,image/heif,image/heic">
                                    </div>
                                    <div class="form-group">
                                        <p>Изготовление (к-во дней):</p>
                                        <input class="form-control" type="text" name="basket_terms" value="">
                                    </div>
                                    <div class="form-group">
                                        <p>Опции для названия файла:</p>
                                        <label style="margin-top: 8px; display:block;">Вид холста:</label>
                                        <select class="form-control" name="manual_canvas_id">
                                            <option value="1">Эконом (S)</option>
                                            <option value="2" selected>Интерьерный (S)</option>
                                            <option value="3">Синтетический (S)</option>
                                            <option value="4">Хлопковый (C)</option>
                                            <option value="5">Глянцевый (G)</option>
                                        </select><br>
                                        <label style="margin-top: 8px; display:block;">Подарочная упаковка:</label>
                                        <select class="form-control" name="manual_gift_code">
                                            <option value="G0" selected>Обычная упаковка (G0)</option>
                                            <option value="G1">Подарочная бумага (G1)</option>
                                            <option value="G2">Эксклюзивная упаковка (G2)</option>
                                        </select><br>
                                        <label style="margin-top: 8px; display:block;">Лак / мазки:</label>
                                        <select class="form-control" name="manual_decoration_id">
                                            <option value="5" selected>Стандарт. Без дополнений (L0, P0)</option>
                                            <option value="1">Арт-гель (L2, P0)</option>
                                            <option value="2">Художественные мазки (L0, P1)</option>
                                            <option value="3">Даммарный лак (L1, P0)</option>
                                        </select><br>
                                        <label style="margin-top: 8px; display:block;">Ориентация:</label>
                                        <select class="form-control" name="manual_orientation_code">
                                            <option value="V0" selected>Авто по размеру (V0)</option>
                                            <option value="V1">Вертикальная (V1)</option>
                                            <option value="V2">Горизонтальная (V2)</option>
                                            <option value="V3">Квадрат (V3)</option>
                                            <option value="V4">Панорама (V4)</option>
                                        </select><br>
                                        <label>Оформление:</label>
                                        <select class="form-control" name="manual_baget_code">
                                            <option value="B0" selected>Без рамки (B0)</option>
                                            <option value="B1">Рамка (B1)</option>
                                            <option value="B2">На бумаге, в рамке (B2)</option>
                                        </select><br>
                                        <label><input type="checkbox" name="manual_express" value="1"> Экспресс</label>
                                    </div>
                                    <div class="form-group">
                                        <p>Комментарий пользователя:</p>
                                        <textarea style="width:300px;height:150px;" class="form-control"
                                                  name="basket_comment"></textarea>
                                    </div>
                            </div>
                            <button class="btn-primary btn primary" type="submit">Добавить</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <iframe id="form_target" name="form_target" style="display:none"></iframe>
        <form id="my_form" action="{{ route('voyager.upload') }}" target="form_target" method="post"
              enctype="multipart/form-data" style="width:0px;height:0;overflow:hidden">
            {{ csrf_field() }}
            <input name="image" id="upload_file" type="file" onchange="$('#my_form').submit();this.value='';">
        </form>
    </div>
    <div class="modal fade" id="paymentRequestModal" tabindex="-1" role="dialog" aria-labelledby="paymentRequestModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.order_payment_requests.store', ['order' => $order['id']]) }}">
                    @csrf
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="paymentRequestModalLabel">Создать заявку на оплату</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="payment_request_amount">Сумма, EUR</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="payment_request_amount" name="amount" required>
                        </div>
                        <div class="form-group">
                            <label for="payment_request_purpose">Назначение</label>
                            <input type="text" class="form-control" id="payment_request_purpose" name="purpose" placeholder="Например: доплата за доставку">
                        </div>
                        <p style="margin-bottom: 0; color: #666;">После создания появится уникальная ссылка, которую можно скопировать и отправить клиенту.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary">Создать заявку</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="paymentRequestModal" tabindex="-1" role="dialog" aria-labelledby="paymentRequestModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.order_payment_requests.store', ['order' => $order['id']]) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title" id="paymentRequestModalLabel">Создать заявку на оплату</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="payment_request_amount">Сумма, EUR</label>
                            <input
                                type="number"
                                min="0.01"
                                max="999999.99"
                                step="0.01"
                                class="form-control"
                                id="payment_request_amount"
                                name="amount"
                                value="{{ old('amount') }}"
                                required>
                        </div>
                        <div class="form-group">
                            <label for="payment_request_purpose">Назначение платежа</label>
                            <input
                                type="text"
                                class="form-control"
                                id="payment_request_purpose"
                                name="purpose"
                                maxlength="255"
                                value="{{ old('purpose') }}"
                                placeholder="Например: доплата за доставку">
                        </div>
                        <p style="margin: 0; color: #777;">
                            После создания появится уникальная ссылка, которую можно сразу отправить клиенту.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary">Создать заявку</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('javascript')
    <script>
        $('document').ready(function () {


            $('.js__price').keyup(function () {
                var val = $(this).val();
                if (isNaN(val)) {
                    val = val.replace(/[^0-9\.]/g, '');
                    if (val.split('.').length > 2) {
                        val = val.replace(/\.+$/, "");
                    }
                }
                $(this).val(val);
            });


            function filterPickupOptionsByCountry() {
                var selectedCountry = ($('select[name="country"]').val() || '').toUpperCase();
                var $options = $('#pickup_workshop_id option');
                $options.each(function () {
                    if (!this.value) {
                        this.hidden = false;
                        return;
                    }
                    var raw = ($(this).data('country') || 'ALL').toString().toUpperCase();
                    var allowed = raw.split(',').map(function (code) {
                        return code.trim();
                    }).filter(function (code) {
                        return code.length > 0;
                    });
                    if (!allowed.length) {
                        allowed.push('ALL');
                    }
                    this.hidden = !!selectedCountry && allowed.indexOf('ALL') === -1 && allowed.indexOf(selectedCountry) === -1;
                });

                var $selected = $('#pickup_workshop_id option:selected');
                if ($selected.length && $selected[0].hidden) {
                    $('#pickup_workshop_id').val('');
                }
                if (!$('#pickup_workshop_id').val()) {
                    var firstVisible = $('#pickup_workshop_id option').filter(function () {
                        return this.value && !this.hidden;
                    }).first().val();
                    if (firstVisible) {
                        $('#pickup_workshop_id').val(firstVisible);
                    }
                }
            }

            function filterTownOptionsByCountry() {
                var selectedCountry = ($('select[name="country"]').val() || '').toUpperCase();
                var $options = $('#delivery_town_id option');
                $options.each(function () {
                    if (!this.value) {
                        this.hidden = false;
                        return;
                    }
                    var townCountry = ($(this).data('country') || '').toString().toUpperCase();
                    this.hidden = !!selectedCountry && !!townCountry && townCountry !== selectedCountry;
                });

                var $selected = $('#delivery_town_id option:selected');
                if ($selected.length && $selected[0].hidden) {
                    $('#delivery_town_id').val('');
                }
                if (!$('#delivery_town_id').val()) {
                    var firstVisible = $('#delivery_town_id option').filter(function () {
                        return this.value && !this.hidden;
                    }).first().val();
                    if (firstVisible) {
                        $('#delivery_town_id').val(firstVisible);
                    }
                }
            }

            function syncDeliveryAdminFields() {
                var method = $('#sposob').val() || '';
                var showPickup = method === 'pickup_at_viar_workshop' || method === 'pickup_Riga' || method === 'pickup_Daugavplis' || method === 'pickup_Daugavpils';
                var showTown = method === 'city_delivery';
                var hideCityForWorkshop = method === 'pickup_at_viar_workshop' || method === 'pickup_Riga' || method === 'pickup_Daugavplis' || method === 'pickup_Daugavpils';

                $('#pickup_workshop_container').toggle(showPickup);
                $('#delivery_town_container').toggle(showTown);
                $('#city_container').toggle(!hideCityForWorkshop);

                if (showPickup) {
                    filterPickupOptionsByCountry();
                    if (method === 'pickup_Riga') {
                        $('#pickup_workshop_id').val('1');
                    } else if (method === 'pickup_Daugavplis' || method === 'pickup_Daugavpils') {
                        $('#pickup_workshop_id').val('2');
                    }
                    var pickupText = $('#pickup_workshop_id option:selected').text().trim();
                    if (pickupText) {
                        $('input[name="address"]').val(pickupText);
                        if (hideCityForWorkshop) {
                            $('input[name="city"]').val('');
                        } else {
                            $('input[name="city"]').val(pickupText);
                        }
                    }
                } else {
                    $('#pickup_workshop_id').val('');
                }

                if (showTown) {
                    filterTownOptionsByCountry();
                    var townText = $('#delivery_town_id option:selected').text().trim();
                    if (townText) {
                        $('input[name="city"]').val(townText);
                    }
                } else {
                    $('#delivery_town_id').val('');
                }
            }

            $('#sposob').on('change', syncDeliveryAdminFields);
            $('select[name="country"]').on('change', syncDeliveryAdminFields);
            $('#pickup_workshop_id').on('change', function () {
                var pickupText = $('#pickup_workshop_id option:selected').text().trim();
                if (pickupText) {
                    $('input[name="address"]').val(pickupText);
                    if (($('#sposob').val() || '') === 'pickup_at_viar_workshop') {
                        $('input[name="city"]').val('');
                    } else {
                        $('input[name="city"]').val(pickupText);
                    }
                }
            });
            $('#delivery_town_id').on('change', function () {
                var townText = $('#delivery_town_id option:selected').text().trim();
                if (townText) {
                    $('input[name="city"]').val(townText);
                }
            });

            function applyPaymentStatusColor() {
                var $statusSelect = $('select[name="payment_status"]');
                if (!$statusSelect.length) {
                    return;
                }

                $statusSelect.removeClass('status-not-payed status-payed status-prepayment');
                var statusValue = $statusSelect.val();

                if (statusValue === 'not_payed') {
                    $statusSelect.addClass('status-not-payed');
                } else if (statusValue === 'payed') {
                    $statusSelect.addClass('status-payed');
                } else if (statusValue === 'prepayment') {
                    $statusSelect.addClass('status-prepayment');
                }
            }

            $('select[name="payment_status"]').on('change', applyPaymentStatusColor);

            $(document).on('click', '.js-copy-payment-request-link', function () {
                var $input = $(this).siblings('.js-payment-request-link').first();
                if (!$input.length) {
                    $input = $(this).closest('.payment-request-admin-item__link').find('.js-payment-request-link').first();
                }
                if (!$input.length) {
                    return;
                }

                $input.trigger('focus').trigger('select');
                document.execCommand('copy');
            });

            @if($errors->has('amount') || $errors->has('purpose'))
                $('#paymentRequestModal').modal('show');
            @endif

            applyPaymentStatusColor();
            syncDeliveryAdminFields();
        });

    function changeImageStatus(val, id, image_id, routeName) {
        $.ajax({
            type: 'post',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: routeName,
            data: {
                status_name: val,
                order_id: id,
                order_painter_image_id: image_id
            },
            success: function(response) {
                if (response) {
                    var data = jQuery.parseJSON(response);
                    if (data['info'] != 1) {
                        alert('Ошибка обновления статуса');
                    } else {
                        alert('Статус обновлен');
                    }
                }
            }
        });
    }

    function changePainterSketchImagesStatus(val, id) {
        $.ajax({
            type: 'post',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('changePainterSketchImagesStatus') }}',
            data: {
                status_name: val,
                order_id: id
            },
            success: function(response) {

                if (response) {
                    var data = jQuery.parseJSON(response);
                    if (data['info'] != 1) {
                        alert('Ошибка обновления статуса');
                    } else {
                        alert(data['success']);
                    }

                }
            }
        });
    }

    function changePainterImagesStatus(val, id) {
        $.ajax({
            type: 'post',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('changePainterImagesStatus') }}',
            data: {
                status_name: val,
                order_id: id
            },
            success: function(response) {

                if (response) {
                    var data = jQuery.parseJSON(response);
                    if (data['info'] != 1) {
                        alert('Ошибка обновления статуса');
                    } else {
                        alert(data['success']);
                    }

                }
            }
        });
    }

    </script>
@stop
