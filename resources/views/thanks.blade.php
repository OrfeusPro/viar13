@extends('layots.common')

@section('title', $data['thanks_purch'])

@section('og_tags')
    <meta property="og:title" content="{{ $data['thanks_purch'] }}" />
    <meta property="og:image" content="{{ \URL::to('/') }}/img/logo.png" />
@endsection

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('css/thank-purchase.css') }}">
@endsection

@section('content')
    {{ Breadcrumbs::render('thanks') }}
    <style>
        .product-item.clearfix {
            height: auto !important;
        }
        .check {
            width: auto;
            height: auto;
            display: unset;
            border: unset;
            background: unset;
        }
    </style>

    <section class="thank-purchase">
        <div class="title">
            <h2>@lang('thanks.thanks')</h2>
        </div>
        @auth

            <div class="thank-purchase-content">
                <div class="product clearfix">
                    @php
                        $last_order = (array) $last_order;
                        $last_order['items'] = json_decode($last_order['items'], true);
                        $last_order['delivery'] = json_decode($last_order['delivery'], true);
                    @endphp

                    @foreach ($last_order['items'] as $product)
                        <div class="product-item clearfix">
                            <div class="img">
                                @include('partials.basket_img')
                            </div>
                            <div class="text">
                                @isset($product['name'])
                                    <h3>
                                        {!! $product['name'] !!}
                                    </h3>
                                @endisset
                                <ul>
                                    @include('partials.all_basket_order_items')
                                </ul>
                                @isset($product['sumFormatedPrice'])
                                    <strong>
                                        {{ $product['sumFormatedPrice'] }}</strong>
                                @endisset
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="check">
                    <h4>{!! $data['we_check_order'] !!}</h4>
                    <div class="clearfix">
                        <div class="check-item">
                            <div>
                                <i>{!! $data['addr_dost'] !!}</i>
                                <span>{{ $user_addr }}</span>
                            </div>
                            @if ($order_date != '')
                                <div>
                                    <i>
                                        {{ trans('gl.jel_dost_thanks') }}
                                    </i>
                                    <span>{{ $order_date }} </span>
                                </div>
                            @endif
                        </div>
                        <div class="check-item">
                            <h5><i>{!! $data['itog_st'] !!}</i>
                                @if ($last_order['sale_price'] && $last_order['sale_price'] != null)
                                    @php
                                        if (isset($last_order['delivery']['deliv_price'])) {
                                            $last_order['sale_price'] = number_format($last_order['sale_price'], 2, '.', '');
                                            $last_order['delivery']['deliv_price'] = number_format($last_order['delivery']['deliv_price']);
                                            $tot_price = $last_order['sale_price'] + intval($last_order['delivery']['deliv_price']);
                                            $tot_price = number_format($tot_price, 2, '.', '');
                                        }
                                    @endphp
                                    <span class="js_thx" style="color:#e1751c;display:block;">{{ $tot_price }} €</span>
                                @else
                                    {{ $last_order['price'] }} €
                                @endif
                            </h5>
                            <a href="{{ route('account.index') }}"><span>{!! $data['lk_text'] !!}</span></a>
                            <p>{!! $data['lk_text2'] !!}</p>
                        </div>
                        <div class="check-item">
                            <div>
                                <i>{!! $data['spos_opl'] !!}</i>
                                <span>
                                    @if ($last_order['payment'] == 'cash_in_office')
                                        1. {{ __('cart.payment_on_delivery') }}
                                    @endif
                                    @if ($last_order['payment'] == 'on_delivery')
                                        1. {{ __('cart.payment_on_delivery') }}
                                    @endif
                                    @if ($last_order['payment'] == 'transfer')
                                        1. {{ __('cart.payment_online') }}
                                    @endif
                                        @if ($last_order['payment'] == 'prepayment')
                                            1. {{ __('cart.prepayment') }}
                                        @endif
                                    @if ($last_order['payment'] == 'online_paysera')
                                        1. {{ __('cart_new.step_4_payment_by_card') }}
                                    @endif
                                    @if ($last_order['payment'] == 'paypalOnetimePayment')
                                        1. PayPal
                                    @endif
                                    @if ($last_order['payment'] == 'google_pay')
                                        1. Google Pay
                                    @endif
                                    @if ($last_order['payment'] == 'apple_pay')
                                        1. Apple Pay
                                    @endif
                                    @if ($last_order['payment'] == 'creditcart')
                                        1. {{ __('cart_new.step_4_by_card_online') }}
                                    @endif
                                </span>
                            </div>
                            <div>
                                <i>{!! $data['contact_pol_nr'] !!}</i>
                                <span>{{ $user_phone }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endauth
    </section>

    <script src="{{ asset('js/thank-purchase.min.js') }}"></script>
@endsection
