@extends('layots.common')

@section('title', $data['meta_title'])

@section('og_tags')
    <meta property="og:title" content="{{ $data['meta_title'] }}" />
    <meta property="og:image" content="{{ \URL::to('/') }}/img/logo.png" />
@endsection

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('css/basket.css') }}">

@endsection

@section('content')
    {{ Breadcrumbs::render('basket') }}

    <script>
        var prod_seo = [];
        $(document).ready(function() {
            $('.basket-item').each(function(index, value) {
                let el_cat = $(this).data('cat');
                let el_name = $(this).find('.js_item__name__orig').text();
                let el_price = $(this).find('.js_item__price').text();
                el_price = el_price.replace(" €", "");
                el_price = parseFloat(el_price);
                el_price = el_price.toFixed(2);

                let el_count = $(this).find('.js_prod_count').val();

                prod_seo.push({
                    'name': el_name,
                    'category': el_cat,
                    'price': el_price,
                    'quantity': el_count
                });
            });
            console.log('prod_seo', prod_seo);

            window.dataLayer = window.dataLayer || [];
            dataLayer.push({
                'pageType': 'cart_page',
                'ecommerce': {
                    'currencyCode': 'EUR',
                    'checkout': {
                        'actionField': {
                            'step': 1
                        },
                        'products': prod_seo
                    }
                },
                'event': 'EE-event',
                'EE-event-category': 'Enhanced Ecommerce',
                'EE-event-action': 'CartPage',
                'EE-event-non-interaction': 'False'
            });

        });

    </script>

    @if (\Session::has('error'))
        <script>
            $(document).ready(function() {
                $('.js-popup-login').trigger('click');
            });
        </script>
    @endif

    @if (\Session::has('def_error'))
        <div class="basket">
            <div class="basket-content">
                <br>
                <span style="color:red;">{{ \Session::get('def_error') }} </span>
            </div>
        </div>
    @endif

    @if (!empty($basket))
        <section class="basket">
            @if ($empty)
                <section style="min-height: 439px;" class="basket">
                    <div class="basket-content">
                        <div class="title">
                            <h2>@lang('basket.empty_basket')</h2>
                        </div>
                    </div>
                </section>
            @else
                <div class="title">
                    <h2>{{ __('cart.tittle') }}</h2>
                </div>
            @endif
            <div class="basket-content">
                @php
                    $items_count = 0;
                @endphp
                @foreach ($basket as $basketIndex => $product)
                    @if (!isset($product['sumPrice'])) @continue @endif
                    @php
                        $items_count++;
                    @endphp
                    <div class="basket-item clearfix js_cart_prod" @isset($product['pid']) @if ($product['pid'] != 'undefined' && $product['pid']!=1 && $product['pid']!=2) data-cat="{{ App\Models\GalleryItem::getCatIdByProductId($product['pid']) }}" @else
                                    @isset($product['is_oil_portrait']) data-cat="oil" @endisset @endif @endisset>

                        <div class="img">
                            @include('partials.basket_img')
                        </div>

                        <div class="text">
                            <ul>
                                @include('partials.all_basket_order_items')
                            </ul>
                        </div>
                        <div class="sum">
                            <div class="clearfix">
                                @if (isset($product['count']))
                                    <input class="js_prod_count" type="text" data-index="{{ $basketIndex }}"
                                        placeholder="{{ $product['count'] }}" value="{{ $product['count'] }}">
                                @endif
                                @if (isset($product['sumFormatedPrice']))
                                    <strong class="js_item__price">{{ $product['sumFormatedPrice'] }}</strong>
                                @endif
                            </div>

                            @if (isset($product['formatedPrice']))
                                <span>= 1x{{ $product['formatedPrice'] }}</span>
                            @endif

                            <a href="javascript:void(0)"
                                onclick="deleteFromBasket(this, '{{ $basketIndex }}'); ">{{ __('cart.delete_btn') }}</a>
                            @if (isset($product['is_active_30_40']))
                                <br>
                                <p class="coup_30_40">{!! $data['coup_app_text'] !!}</p>
                            @endif
                            @if (!empty($product['has_special_label']))
                                <br>
                                <p style="color: #fc7a0f; font-size: 12px; margin-top: 5px;">
                                    @if ($product['label_type'] == 'super_deal')
                                        ⚠️ SUPER DEAL: {{ __('cart.not_sale') }}
                                    @elseif ($product['label_type'] == 'hit')
                                        ⚠️ HIT: {{ __('cart.not_sale') }}
                                    @elseif ($product['label_type'] == 'top')
                                        ⚠️ TOP: {{ __('cart.not_sale') }}
                                    @elseif ($product['label_type'] == 'recommendation')
                                        ⚠️ Recommendation: {{ __('cart.not_sale') }}
                                    @else
                                        ⚠️ {{ __('cart.this_product_not_sale') }}
                                    @endif
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach

                @if (!$empty)
                    <div class="cart_total__bot">
                        @auth
                            <div class="bonus-share clearfix">
                                <div class="bonus clearfix">
                                    <h5>{{ __('cart.bonuses_desc_tittle') }}</h5>
                                    <p>{{ __('cart.bonuses_desc') }}</p>
                                    <a href="{{ route('stocks.index') }}">{{ __('cart.bonuses_btn') }}</a>
                                </div>
                                <div class="coupon">
                                    <p>{{ __('cart.bonuses_inp_desc') }}:</p>
                                    <form class="clearfix js_coupon_form">
                                        <input required type="text" name="user_coupon" class="js_coupon">
                                        <button type="submit">{{ __('cart.bonuses_submit') }}</button>
                                    </form>
                                </div>
                            </div>

                            @if (Auth::user()->bonuses > 0)
                                <div class="bon__items @if (Auth::user()->active_coupon == null) bon__end_flow @endif
                                    " data-bons="{{ Auth::user()->bonuses }}">
                            @endif

                            @if (Auth::user()->active_coupon != null)
                                @php
                                    $nom = \App\Models\User::getCouponNominal(Auth::user()->active_coupon);
                                @endphp

                                @if ($nom != '')
                                    <div class="pay clearfix">
                                        <div class="pay-content">
                                            <div class="pay-sum">
                                                <h6>{{ $data['coup_sale'] }}
                                                    @php
                                                        if (session()->has('is_coupon_def')) {
                                                            echo session()->get('is_coupon_def') . ' €';
                                                        } elseif ($nom) {
                                                            echo ' - ' . $nom . ' €';
                                                        }
                                                    @endphp
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif

                            @if (session()->has('is_coupon_30_40'))
                                <div class="coupons__grid coupon_30_40">
                                    <div class="pay clearfix">
                                        <div class="pay-content">
                                            <div class="pay-sum">
                                                <h6>{{ $data['coup_30_40_title'] }}</h6>
                                                <span>{{ $data['coup_30_40_text'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if (Auth::user()->is_facebook_sale == 1)
                                <div class="coupons__grid coupon_print">
                                    <div class="pay clearfix">
                                        <div class="pay-content">
                                            <div class="pay-sum">
                                                <h6>{{ $data['send_us_print_text_vac'] }}</h6>
                                                <span>{{ __('cart.summ_to_pay') }}:
                                                    -{{ $stocks['facebook_sale'] }}%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if (Auth::user()->is_active_friend_inv == 1)
                                <div class="coupons__grid coupon_friend">
                                    <div class="pay clearfix">
                                        <div class="pay-content">
                                            <div class="pay-sum">
                                                <h6>{{ $data['user_sale_text'] }}</h6>
                                                <span>{{ __('cart.summ_to_pay') }}: -{{ $friend_sale_count }} €</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if (Auth::user()->has_sale_20eur == 1)
                                <div class="coupons__grid coupon_20eur">
                                    <div class="pay clearfix">
                                        <div class="pay-content">
                                            <div class="pay-sum">
                                                <h6>{{ $data['sale_for_prev_order'] }}</h6>
                                                <span>{{ __('cart.summ_to_pay') }}: -20 €</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if (Auth::user()->is_coupon_dates != null)
                                <div class="coupons__grid coupon_dates">
                                    <div class="pay clearfix">
                                        <div class="pay-content">
                                            <div class="pay-sum">
                                                <h6>{{ $data['coup_on_2_dates'] }}</h6>
                                                <span>{{ __('cart.summ_to_pay') }}: -
                                                    {{ session()->get('sale_dated') }}%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="pay clearfix">
                                <div class="pay-content">
                                    <div class="pay-sum">
                                        <span>{{ __('cart.summ_to_pay') }}:</span>
                                        <strong>{{ $items_count }} {{ __('cart.prod_on_summ') }}</strong>
                                        <ul>
                                            @if (isset($basket['saved_price']))
                                                @if ($basket['saved_price'] != $basket['totalPrice'])
                                                    <li><strike>{{ $basket['saved_price'] }}€</strike></li>
                                                    <li>{{ $basket['totalPrice'] }} €</li>
                                                @else
                                                    <li></li>
                                                    <li> {{ $basket['totalPrice'] }} €</li>
                                                @endif
                                            @else
                                                <li></li>
                                                <li>{{ $basket['totalPrice'] }} €</li>
                                            @endisset
                                    </ul>
                                </div>
                                <i>{{ __('cart.with_out_delivery') }}</i>
                            </div>
                        </div>

                    @endauth
            @endif
        </div>
        </div>

    </section>

    @if (!$empty)
        <section class="checkout">
            <div class="title">
                <h2>{{ __('cart.ordering') }}</h2>
            </div>
            <div class="checkout-content">
                <form id="checkout-form" enctype="multipart/form-data" method="post"
                    action="{{ route('make_order') }}">
                    {{ csrf_field() }}
                    <div class="checkout-form clearfix">
                        <div class="checkout-item">
                            <div class="item">
                                <h4>1. {{ __('cart.ordering_user_data') }}</h4>
                                <div class="input">
                                    <label>E-mail</label>
                                    <input type="text" name="email" placeholder="" required @if (Auth::user()) value="{{ Auth::user()->email }}" @endif>
                                </div>
                                <div class="fio">
                                    <label>{{ __('cart.name') }}</label>
                                    <div class="clearfix">
                                        <input type="text" name="name" placeholder="" required @if (Auth::user()) value="{{ Auth::user()->first_name }}" @endif>
                                        <input type="text" name="last_name" placeholder="" required @if (Auth::user()) value="{{ Auth::user()->last_name }}" @endif>
                                    </div>
                                </div>
                                <div class="tel">
                                    <label>{{ __('cart.phone') }} </label>
                                    <div>
                                        <div class="select">
                                            <select class="js_select_cc"
                                                data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false, "fakeDropInBody": false, "useCustomScroll": false}'>
                                                <option selected value="" data-tel="">&nbsp;&nbsp;&nbsp;</option>
                                                @foreach ($c_tels as $c_tel)
                                                    <option data-price="{{ $c_tel['deliv_price'] }}"
                                                        data-tel="{{ $c_tel['phone_code'] }}">
                                                        {{ $c_tel['country_code'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <input type="tel" name="phone" class="js_phone_cc" required
                                            {{-- class="checkout-tel" --}} required @if (Auth::user()) value="{{ Auth::user()->phone }}" @else value="+" @endif placeholder="">
                                    </div>
                                    <p>
                                        <a class="js_toggle_user"
                                            href="javascript:void(0)">{{ __('cart.phone_desc_href') }}</a>,
                                        {{ __('cart.phone_desc') }}
                                    </p>

                                    {{-- receiver --}}
                                    <div class="js_user_data" style="display:none;">
                                        <br>
                                        <p>{{ $data['receiver_data'] }}</p>
                                        <div class="fio__rec">
                                            <div class="clearfix cf_data">
                                                <input class="un" type="text" name="name_rec"
                                                    placeholder="{{ __('cart.name') }}">
                                                <input class="uln" type="text" name="last_name_rec"
                                                    placeholder="{{ $data['deliv_price'] }}">
                                            </div>
                                        </div>
                                        <label class="not_text">{{ __('cart.phone') }} </label>
                                        <div class="fir_rec2">

                                            <div>
                                                <div class="select">
                                                    <select class="js_select_cc"
                                                        data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false, "fakeDropInBody": false, "useCustomScroll": false}'>
                                                        <option selected value="" data-tel="">&nbsp;&nbsp;&nbsp;
                                                        </option>
                                                        @foreach ($c_tels as $c_tel)
                                                            <option data-id="{{ $c_tel['id'] }}"
                                                                data-price="{{ $c_tel['deliv_price'] }}"
                                                                data-tel="{{ $c_tel['phone_code'] }}">
                                                                {{ $c_tel['country_code'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <input type="tel" name="phone_rec" class="js_phone_cc"
                                                    {{-- class="checkout-tel" --}} @if (Auth::user()) value="{{ Auth::user()->phone }}" @else value="+" @endif placeholder="">
                                            </div>

                                        </div>

                                        <div class="fir_rec3">
                                            <div class="dom clearfix">
                                                <input type="text" name="address_rec"
                                                    placeholder="{{ $data['user_add_place'] }}">
                                                <input type="text" name="postal_index_rec" maxlength="8"
                                                    placeholder="{{ $data['usr_code_place'] }}">
                                            </div>
                                        </div>
                                    </div>
                                    {{-- endreceiver --}}
                                </div>
                            </div>
                            <div class="item">
                                <h4>2. {{ __('cart.delivery') }}</h4>
                                <div class="input-select">
                                    <label>{{ __('cart.delivery_chose_country') }}</label>
                                    <select name="country" class="js_country"
                                        data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false, "fakeDropInBody": false, "useCustomScroll": false}'>
                                        @foreach ($c_tels as $c_tel)
                                            <option @if (\Session::has('basket_country')) @php
                                                $sel_ct = session()->get('basket_country');
                                                if ($sel_ct == $c_tel['country_code']) {
                                                    echo ' selected ';
                                                }
                                            @endphp @endif
                                                value="{{ $c_tel['country_code'] }}" data-id="{{ $c_tel['id'] }}"
                                                data-high_price={{ $c_tel['high_price'] }}
                                                data-price="{{ $c_tel['deliv_price'] }}">
                                                {{ $c_tel['country_name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="input-checkbox js_check_home_dev__block">
                                    <label for="q">1. {{ __('cart.delivery_at_home') }}</label>
                                    <input checked class="js_check_home_dev" id="q" name="delivery" value="to_the_door"
                                        type="radio">
                                </div>
                                <div class="dom clearfix g__user_data">
                                    <input type="text" name="city" class="js_cur_city" @if (Auth::user()) value="{{ Auth::user()->city }}" @endif
                                        placeholder="{{ $data['user_city'] }}">

                                    <input type="text" name="address" @if (Auth::user()) value="{{ Auth::user()->address }}" @endif
                                        placeholder="{{ $data['user_add_place'] }}">

                                    <input type="text" name="postal_index" maxlength="8" minlength="4" @if (Auth::user()) value="{{ Auth::user()->postal_index }}" @endif placeholder="{{ $data['usr_code_place'] }}">
                                </div>
                                {{-- <div class="input-checkbox js_check_riga">
                                    <label>2. {{ __('cart.selfdelivery_Riga') }}</label>
                                    <input name="delivery" value="pickup_Riga" type="radio">
                                </div> --}}
                                <div class="input-checkbox js_check_dau">
                                    <label>2. {{ __('cart.selfdelivery_Daugavpils') }}</label>
                                    <input name="delivery" value="pickup_Daugavplis" type="radio">
                                </div>
                            </div>
                        </div>
                        <div class="checkout-item">
                            <div class="item">
                                <h4>3. {{ __('cart.payment_tittle') }}</h4>
                                <div class="input-checkbox js_office__inp js_no_select">
                                    <label>1. {{ __('cart.payment_cash') }}</label>
                                    <input class="js_office" name="payment" value="cash_in_office" type="radio">
                                </div>
                                <div class="input-checkbox js_on_dev_inp">
                                    <label>2. <span
                                            class="js_on_dev_text">{{ __('cart.payment_on_delivery') }}</span></label>
                                    <input class="js_on_dev" name="payment" value="on_delivery" type="radio">
                                </div>
                                <div class="input-checkbox js_on_trans">
                                    <label>3. <span
                                            class="js_per_text">{{ __('cart.payment_online') }}</span></label>
                                    <input checked class="js_trans" name="payment" value="transfer" type="radio">
                                </div>
                            </div>
                            <div class="item">
                                <h4>4. {{ __('cart.comment') }}</h4>
                                <div class="textarea">
                                    <p>{{ __('cart.comment_desc') }}</p>
                                    <textarea name="comment" required
                                        placeholder="{{ __('cart.comment_placeholder') }}"></textarea>
                                    <input id="real_file_input" data-desc="{{ trans('gl.load_btn_text') }}"
                                        name="photo" accept="image/*,image/heif,image/heic" type="file">
                                </div>
                                @php
                                    $date_form_now = Carbon::now();
                                @endphp
                                <br>
                                <p>
                                <p class="text__sm">{{ trans('gl.jel_dost_thanks') }}</p>
                                <div class="date__picker">
                                    <input name="when_send" class="date__inp" id="date_pick_del"
                                        placeholder="MM/DD/YYYY">
                                </div>
                                </p>
                            </div>
                        </div>

                        <div class="ur_checkers">
                            <div class="input-checkbox">
                                <input type="checkbox" name="ur_name" class="js_ur">{{ $data['ur_lico'] }}
                            </div>

                            <div class="ur_info" style="display:none;">

                                <div class="input">
                                    <input type="text" name="ur_name_l" placeholder="{{ $data['ur_name_l'] }}">
                                </div>

                                <div class="input">
                                    <input type="text" name="ur_reg_num" placeholder="{{ $data['ur_reg_num'] }}">
                                </div>

                                <div class="input">
                                    <input type="text" name="ur_legal_addr" placeholder="{{ $data['ur_addr'] }}">
                                </div>

                                <div class="input">
                                    <input type="text" name="ur_pnr_nr" placeholder="{{ $data['ur_pnr_nr'] }}">
                                </div>

                                <div class="input">
                                    <input type="text" name="ur_bank_name" placeholder="{{ $data['ur_bank_name'] }}">
                                </div>

                                <div class="input">
                                    <input type="text" name="ur_bank_code" placeholder="{{ $data['ur_bank_code'] }}">
                                </div>

                                <div class="input">
                                    <input type="text" name="ur_bank_acc_code"
                                        placeholder="{{ $data['ur_bank_acc_code'] }}">
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="total">
                        <h5 data-total="{{ __('cart.total') }}:" id="totalPrice"><i>{{ __('cart.total') }}:</i>

                            @isset($basket['saved_price'])
                                @if ($basket['saved_price'] != $basket['totalPrice'])
                                    <strike><small>{{ $basket['saved_price'] }} €</small></strike>
                                @endif
                            @endisset
                            <span class="js_toto_price" @isset($basket['saved_price']) style="color:#fc7a0f;" @endisset>
                                {{ $basket['formatedTotalPrice'] }}
                            </span>
                            <span class="add_del_price">{{ $data['deliv_price'] }} <span>4</span>€</span>
                            <input type="hidden" name="deliv_price" value="4">
                        </h5>
                        <button type="button"
                            class="js__sbm__btn"><span>{{ __('cart.order_confirm') }}</span></button>
                        <div class="js_preloader_status" style="display: none">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin: auto; background: none; display: block; shape-rendering: auto;" width="126px" height="126px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
                                <path d="M29 50A21 21 0 0 0 71 50A21 22.7 0 0 1 29 50" fill="#fa7846" stroke="none">
                                    <animateTransform attributeName="transform" type="rotate" dur="1s" repeatCount="indefinite" keyTimes="0;1" values="0 50 50.85;360 50 50.85"></animateTransform>
                                </path></svg>
                        </div>
                        <p><input type="checkbox" name="agreement" checked>
                            {!! $data['agg_text'] !!}
                        </p>
                    </div>
                </form>
            </div>
        </section>
    @endif

@endif

@include('partials.basket.scripts_styles')

<script>
window.initRecommendationsSlider = function() {
    if (typeof jQuery === 'undefined' || typeof jQuery.fn.slick === 'undefined') {
        console.log('⚠️ jQuery або Slick ще не завантажені');
        return false;
    }

    var $slider = jQuery('.cart-recommendations__slider');

    if (!$slider.length) {
        return false;
    }

    if ($slider.hasClass('slick-initialized')) {
        $slider.slick('unslick');
    }

    $slider.slick({
        infinite: false,
        slidesToShow: 1,
        slidesToScroll: 1,
        dots: true,
        arrows: true,
        prevArrow: '<button type="button" class="slick-prev cart-recommendations__nav-prev"><svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 1L1 7L7 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>',
        nextArrow: '<button type="button" class="slick-next cart-recommendations__nav-next"><svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 1L7 7L1 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>',
        adaptiveHeight: true,
        speed: 400,
        cssEase: 'ease-in-out'
    });

    return true;
};

(function tryInitSlider() {
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.slick !== 'undefined') {
        jQuery(document).ready(function($) {
            window.initRecommendationsSlider();

            var cartSidebar = document.getElementById('cart-sidebar');
            if (cartSidebar) {
                var observer = new MutationObserver(function() {
                    if (document.querySelector('.cart-recommendations__slider')) {
                        setTimeout(function() {
                            window.initRecommendationsSlider();
                        }, 200);
                    }
                });

                observer.observe(cartSidebar, {
                    childList: true,
                    subtree: true
                });
            }
        });
    } else {
        setTimeout(tryInitSlider, 100);
    }
})();
</script>

@endsection
