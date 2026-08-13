@php
    $paymentRequests = collect($order['payment_requests'] ?? []);
    $showCreatedAlert = session('payment_request_created_order_id') == $order['id'];
    $showValidationErrors = old('context_order_id') == $order['id'] && ($errors->has('amount') || $errors->has('purpose'));
@endphp

<div class="payment-request-quick">
    <div class="payment-request-quick__title">Заявка на оплату</div>

    @if($showCreatedAlert)
        <div class="alert alert-success" style="margin-bottom: 10px; padding: 8px;">
            Создана {{ session('payment_request_created_number') }}
        </div>
    @endif

    @if($showValidationErrors)
        <div class="alert alert-danger" style="margin-bottom: 10px; padding: 8px;">
            @if($errors->has('amount'))
                <div>{{ $errors->first('amount') }}</div>
            @endif
            @if($errors->has('purpose'))
                <div>{{ $errors->first('purpose') }}</div>
            @endif
        </div>
    @endif

    <button
        type="button"
        class="btn btn-sm btn-primary payment-request-quick__create js-open-payment-request-modal"
        data-order-id="{{ $order['id'] }}"
        data-action="{{ route('admin.order_payment_requests.store', ['order' => $order['id']]) }}">
        Создать платеж
    </button>

    @if($paymentRequests->count() > 0)
        @foreach($paymentRequests as $paymentRequest)
            <div class="payment-request-quick__item">
                <div><strong>{{ $paymentRequest->public_number }}</strong></div>
                <div>{{ number_format((float) $paymentRequest->amount, 2) }} {{ $paymentRequest->currency }}</div>
                @if($paymentRequest->purpose)
                    <div>{{ $paymentRequest->purpose }}</div>
                @endif
                @if($paymentRequest->selected_payment_method)
                    <div>
                        @switch($paymentRequest->selected_payment_method)
                            @case('online_paysera')
                                Метод: Онлайн банкинг
                                @break
                            @case('creditcart')
                                Метод: Картой онлайн
                                @break
                            @case('google_pay')
                                Метод: Google Pay
                                @break
                            @case('apple_pay')
                                Метод: Apple Pay
                                @break
                            @case('paypalOnetimePayment')
                                Метод: PayPal
                                @break
                            @case('transfer')
                                Метод: Оплата перечислением
                                @break
                            @default
                                Метод: {{ $paymentRequest->selected_payment_method }}
                        @endswitch
                    </div>
                @endif
                <div class="payment-request-quick__status {{ $paymentRequest->status === 'paid' ? 'paid' : 'pending' }}">
                    {{ $paymentRequest->status === 'paid' ? 'Оплачено' : 'Ожидает оплаты' }}
                </div>
                <div class="payment-request-quick__actions">
                    <button
                        type="button"
                        class="btn btn-xs btn-default js-copy-payment-request-link"
                        data-copy-link="{{ $paymentRequest->publicUrl() }}">
                        Скопировать ссылку
                    </button>
                </div>
            </div>
        @endforeach
    @endif
</div>
