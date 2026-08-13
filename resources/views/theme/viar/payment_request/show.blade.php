@php
    $flashStatus = session('payment_request_flash');
    $isPaid = $paymentRequest->isPaid();
@endphp

<style>
    .payment-request-breads {
        position: relative;
        z-index: 1;
        padding-top: 120px;
        margin-bottom: 18px;
    }

    .payment-request-breads__inner {
        max-width: 1180px;
        margin: 0 auto;
    }

    .payment-request-breads .breadcrumbs__block {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }

    .payment-request-page {
        position: relative;
        z-index: 1;
        padding: 0 0 60px;
    }

    .payment-request-page__wrapper {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 360px;
        gap: 24px;
        align-items: start;
        max-width: 1180px;
        margin: 0 auto;
    }

    .payment-request-page__main {
        background: #fff9f3;
        border-radius: 12px;
        padding: 28px;
    }

    .payment-request-page__title {
        font-weight: 600;
        font-size: 32px;
        line-height: 38px;
        color: #1e2533;
        margin-bottom: 12px;
    }

    .payment-request-page__hint {
        font-size: 16px;
        line-height: 22px;
        color: #848484;
        margin-bottom: 24px;
    }

    .payment-request-page__notice {
        border-radius: 8px;
        padding: 16px 18px;
        margin-bottom: 20px;
        font-size: 15px;
        line-height: 21px;
    }

    .payment-request-page__notice.success {
        background: #eff9f0;
        border: 1px solid #2ba92b;
        color: #1d6f1d;
    }

    .payment-request-page__notice.warning {
        background: #fff5ec;
        border: 1px solid #fa7846;
        color: #9b4c21;
    }

    .payment-request-page__meta {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px 24px;
        margin-bottom: 24px;
    }

    .payment-request-page__meta-item span {
        display: block;
        margin-bottom: 6px;
        color: #848484;
        font-size: 14px;
        line-height: 18px;
    }

    .payment-request-page__meta-item strong {
        color: #1e2533;
        font-weight: 600;
        font-size: 18px;
        line-height: 22px;
    }

    .payment-request-page__methods .cart-payments-page__item {
        min-height: 98px;
        margin-bottom: 14px !important;
        padding-left: 18px;
        padding-right: 22px;
        background: #f9f1ea;
        border: 1px solid #d6d6d6;
        border-radius: 8px;
    }

    .payment-request-page__method-button {
        width: 100%;
        border: 0;
        padding: 0;
        background: transparent;
        display: flex;
        align-items: center;
        justify-content: space-between;
        text-align: left;
        cursor: pointer;
    }

    .payment-request-page__sidebar .cart-page-sidebar {
        background: #f9f1ea;
        border-radius: 12px;
        padding: 28px 26px;
    }

    .payment-request-page__sidebar .cart-page-sidebar__total-amount {
        padding-bottom: 10px;
    }

    .payment-request-page__sidebar .cart-page-sidebar__total-amount span {
        font-size: 22px;
        line-height: 28px;
    }

    .payment-request-page__sidebar .cart-page-sidebar__total-amount strong {
        font-size: 30px;
        line-height: 36px;
    }

    @media (max-width: 1100px) {
        .payment-request-breads {
            padding-top: 100px;
        }

        .payment-request-page__wrapper {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .payment-request-breads {
            padding-top: 80px;
            margin-bottom: 14px;
        }

        .payment-request-page__main {
            padding: 20px;
        }

        .payment-request-page__title {
            font-size: 24px;
            line-height: 30px;
        }

        .payment-request-page__meta {
            grid-template-columns: 1fr;
        }

        .payment-request-page__methods .cart-payments-page__item--img {
            width: 92px;
        }
    }
</style>

<div class="payment-request-breads">
    <div class="section-frame">
        <div class="payment-request-breads__inner">
            <div class="breadcrumbs breadcrumbs__block" itemscope itemtype="https://schema.org/BreadcrumbList">
                <div itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                    <a href="{{ route('home') }}" class="breadcrumbs__link breadcrumbs__link_main" itemprop="item">
                        <span itemprop="name">@lang('account.index1')</span>
                    </a>
                    <meta itemprop="position" content="1" />
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" width="6" height="11" viewBox="0 0 6 11" fill="none"
                    class="img-svg breadcrumbs__arrow replaced-svg">
                    <path
                        d="M0.24775 1.4341C0.180786 1.50106 0.147304 1.57807 0.147304 1.66513C0.147304 1.75218 0.180786 1.82919 0.24775 1.89615L4.1953 5.8437L0.247751 9.79124C0.180787 9.85821 0.147304 9.93522 0.147304 10.0223C0.147304 10.1093 0.180787 10.1863 0.247751 10.2533L0.749984 10.7555C0.816948 10.8225 0.893957 10.856 0.98101 10.856C1.06806 10.856 1.14507 10.8225 1.21204 10.7555L5.89284 6.07473C5.9598 6.00776 5.99329 5.93075 5.99329 5.8437C5.99329 5.75664 5.9598 5.67964 5.89284 5.61267L1.21204 0.931868C1.14507 0.864903 1.06806 0.831421 0.981009 0.831421C0.893956 0.831421 0.816947 0.864903 0.749983 0.931868L0.24775 1.4341Z"
                        fill="#FA7846"></path>
                </svg>
                <div itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                    <a href="#" class="breadcrumbs__link" itemprop="item">
                        <span itemprop="name">{{ $page['title'] }}</span>
                    </a>
                    <meta itemprop="position" content="2" />
                </div>
            </div>
        </div>
    </div>
</div>

<div class="payment-request-page">
    <div class="section-frame">
        <div class="payment-request-page__wrapper">
            <div class="payment-request-page__main">
                <div class="payment-request-page__title">@lang('account_new.payment_request_title')</div>
                <p class="payment-request-page__hint">@lang('account_new.payment_request_hint')</p>

                @if($flashStatus === 'paid' || $isPaid)
                    <div class="payment-request-page__notice success">
                        @lang('account_new.payment_request_success_message')
                    </div>
                @elseif($flashStatus === 'cancelled')
                    <div class="payment-request-page__notice warning">
                        @lang('account_new.payment_request_cancelled_message')
                    </div>
                @elseif($flashStatus === 'error')
                    <div class="payment-request-page__notice warning">
                        @lang('account_new.payment_request_invalid_message')
                    </div>
                @endif

                <div class="payment-request-page__meta">
                    <div class="payment-request-page__meta-item">
                        <span>@lang('account_new.payment_request_number')</span>
                        <strong>{{ $paymentRequest->public_number }}</strong>
                    </div>
                    <div class="payment-request-page__meta-item">
                        <span>@lang('account_new.payment_request_order_number')</span>
                        <strong>#{{ $paymentRequest->order_id }}</strong>
                    </div>
                    <div class="payment-request-page__meta-item">
                        <span>@lang('account_new.payment_status')</span>
                        <strong>
                            @if($isPaid)
                                @lang('account_new.payment_request_status_paid')
                            @else
                                @lang('account_new.payment_request_status_pending')
                            @endif
                        </strong>
                    </div>
                </div>

                @unless($isPaid)
                    <div class="cart-payments-page__list payment-request-page__methods">
                        @foreach($paymentMethods as $paymentKey => $methodData)
                            <form method="POST" action="{{ route('payment_request.start', ['token' => $paymentRequest->token]) }}">
                                @csrf
                                <input type="hidden" name="payment" value="{{ $paymentKey }}">
                                <button type="submit" class="cart-payments-page__item payment-request-page__method-button">
                                    <div class="kvizz-radio js-checkbox">
                                        <div class="check check-border"></div>
                                        <div class="window-prompt">
                                            {{ $methodData['title_translate'] ? __($methodData['title']) : $methodData['title'] }}
                                        </div>
                                    </div>
                                    @if(!empty($methodData['img']))
                                        <div class="cart-payments-page__item--img">
                                            <img src="{{ asset(env('THEME') . $methodData['img']) }}" alt="">
                                        </div>
                                    @endif
                                </button>
                            </form>
                        @endforeach
                    </div>
                @endunless
            </div>

            <div class="payment-request-page__sidebar">
                <div class="cart-page-sidebar">
                    <div class="cart-page-sidebar__total-amount">
                        <span>@lang('account_new.payment_request_amount')</span>
                        <strong>{{ number_format((float) $paymentRequest->amount, 2) }} €</strong>
                    </div>

                    @if($paymentRequest->selected_payment_method)
                        <div class="cart-page-sidebar__amount-goods">
                            <span>@lang('cart_new.payment')</span>
                            <strong>
                                @switch($paymentRequest->selected_payment_method)
                                    @case('online_paysera')
                                        {{ __('cart_new.step_4_payment_by_card') }}
                                        @break
                                    @case('creditcart')
                                        {{ __('cart_new.step_4_by_card_online') }}
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
                                        {{ __('cart_new.step_4_payment_by_bank_transfer') }}
                                        @break
                                    @default
                                        {{ $paymentRequest->selected_payment_method }}
                                @endswitch
                            </strong>
                        </div>
                    @endif

                    @if($paymentRequest->customer_email)
                        <div class="cart-page-sidebar__delivery manufacturing">
                            <span>Email</span>
                            <strong>{{ $paymentRequest->customer_email }}</strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
