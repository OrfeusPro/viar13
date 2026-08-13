<div class="vz-art cart-page-global">
    <div class="section-frame">

        @include(env('THEME_RESOURCES') . '.cart.bread', ['step' => 3])

        <div class="cart-page-global__wrapper">
            <div class="cart-page-global__content">

                @if ($onlyGiftCardOnline)
                <div class="cart-delivery-page__content">
                    <div class="cart-delivery-page__title">
                        @lang('cart_new.step_3_choose_a_shipping_method')
                    </div>

                    <div class="cart-delivery-page__select-country" id="country_block" country="{{$user['country']}}">
                        <span>@lang('cart_new.step_3_country_selection')</span>
                        <div class="select">
                            <div class="select__wrapper">
                                <div class="select__content">
                                    <div class="select__trigger empty">
                                        <div class="icon">
                                            <svg>
                                                <use
                                                    xlink:href="{{ asset(env('THEME') . 'img/cart/sprite.svg') }}#arrow-select">
                                                </use>
                                            </svg>
                                        </div>
                                        <a href="">
                                            @php
                                                $cur_country['deliv_price'] = 0;
                                                if($user['country'] == 'RU' || $user['country'] == 'UA')
                                                {
                                                    $user['country'] = "LV";
                                                }

                                            @endphp

                                            @foreach ($c_tels as $c_tel)
                                                @if ($user['country'])
                                                    @php
                                                        $sel_ct = $user['country'];
                                                        if ($sel_ct == $c_tel['country_code']) {
                                                            echo $c_tel['country_name'];
                                                            $cur_country = $c_tel;
                                                        }
                                                    @endphp
                                                @endif
                                            @endforeach


                                        </a>
                                    </div>
                                    <div class="select__dropdown">
                                        <div class="select__options  " data-simplebar="">
                                            @foreach ($c_tels as $c_tel)
                                                @if($c_tel['country_code'] == 'RU' || $c_tel['country_code'] == 'UA') @continue @endif

                                                <a href="#"
                                                    class="select__option
                                                    @if ($user['country'])
                                                        @php
                                                        $sel_ct = $user['country'];
                                                        if ($sel_ct == $c_tel['country_code']) {
                                                            echo ' selected ';
                                                        }
                                                        @endphp
                                                    @else
                                                    @php
                                                        $sel_ct = strtoupper(session()->get('locale'));

                                                        if ($sel_ct == $c_tel['country_code']) {
                                                            echo ' selected ';
                                                        }
                                                    @endphp @endif"
                                                    data-value="{{ $c_tel['country_code'] }}"
                                                    value="{{ $c_tel['country_code'] }}"
                                                    data-id="{{ $c_tel['id'] }}"
                                                    data-high_price={{ $c_tel['high_price'] }}
                                                    data-price="{{ $c_tel['deliv_price'] }}">
                                                    {{ $c_tel['country_name'] }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="cart-payments-page__item cart-email cart-delivery-item js-active"
                        data-delivery_type="email">
                        <div class="cart-delivery-item__wrapper">
                            <div class="cart-delivery-item__header js-cart-delivery-item">
                                <div class="kvizz-radio js-checkbox">
                                    <div class="check check-border"></div>
                                    <span class="jcf-radio jcf-unchecked">
                                        <input name="payments" type="radio" value="email">
                                    </span>
                                    <div class="window-prompt">
                                        @lang('cart_new.step_3_send_to_email')
                                    </div>
                                </div>

                                <span class="delivery-price" style="display:none;">0€</span>
                                <span class="delivery-price2">{{ Auth::user()->email ?? '' }}</span>
                            </div>
                            {{-- <div class="cart-delivery-item__content" style="display:block;">
                                <label class="cart-data-page__input">
                                    <span>@lang('cart_new.step_3_email_address')</span>
                                    <input type="email" name="email_delivery" class="input-grey"
                                        value="{{ Auth::user()->email ?? '' }}" required>
                                </label>
                            </div> --}}
                        </div>
                    </div>
                    {{-- Скрываем все остальные варианты --}}
                    @push('cartDeliveryStyles')
                        <style>
                            .cart-delivery-page__wrapper .cart-delivery-item:not(.cart-email) {
                                display: none !important;
                            }
                        </style>
                    @endpush
                </div>
                @else






                <div class="cart-data-page cart-delivery-page">
                    <div class="cart-data-page__wrapper cart-delivery-page__wrapper">
                        <div class="cart-delivery-page__content">
                            <div class="cart-delivery-page__title">
                                @lang('cart_new.step_3_choose_a_shipping_method')
                            </div>
                            <div class="cart-delivery-page__select-country" id="country_block" country="{{$user['country']}}">
                                <span>@lang('cart_new.step_3_country_selection')</span>
                                <div class="select">
                                    <div class="select__wrapper">
                                        <div class="select__content">
                                            <div class="select__trigger empty">
                                                <div class="icon">
                                                    <svg>
                                                        <use
                                                            xlink:href="{{ asset(env('THEME') . 'img/cart/sprite.svg') }}#arrow-select">
                                                        </use>
                                                    </svg>
                                                </div>
                                                <a href="">
                                                    {{-- @foreach ($c_tels as $c_tel)
													@php
													//  dd(session()->get('locale'));
													@endphp
														@if (\Session::has('basket_country'))
															@php
																$sel_ct = session()->get('basket_country');
																if ($sel_ct == $c_tel['country_code']) {
																echo $c_tel['country_name'];
																}
															@endphp
														@else
															@php
																$sel_ct = strtoupper(session()->get('locale'));
																// dd($sel_ct, $c_tel['country_code']);
																if ($sel_ct == $c_tel['country_code']) {
																	echo $c_tel['country_name'];
																}
															@endphp
														@endif

													@endforeach --}}
													@php
														$cur_country['deliv_price'] = 0;
                                                        if($user['country'] == 'RU' || $user['country'] == 'UA')
                                                        {
                                                            $user['country'] = "LV";
                                                        }

													@endphp

                                                    @foreach ($c_tels as $c_tel)
                                                        @if ($user['country'])
                                                            @php
                                                                $sel_ct = $user['country'];
                                                                if ($sel_ct == $c_tel['country_code']) {
                                                                    echo $c_tel['country_name'];
																	$cur_country = $c_tel;
                                                                }
                                                            @endphp
                                                        @endif
                                                    @endforeach


                                                </a>
                                            </div>
                                            <div class="select__dropdown">
                                                <div class="select__options  " data-simplebar="">
                                                    @foreach ($c_tels as $c_tel)
                                                        @if($c_tel['country_code'] == 'RU' || $c_tel['country_code'] == 'UA') @continue @endif

                                                        <a href="#"
                                                            class="select__option
															@if ($user['country'])
																@php
																$sel_ct = $user['country'];
																if ($sel_ct == $c_tel['country_code']) {
																	echo ' selected ';
																}
																@endphp
															@else
															@php
																$sel_ct = strtoupper(session()->get('locale'));

																if ($sel_ct == $c_tel['country_code']) {
																	echo ' selected ';
																}
															@endphp @endif"
                                                            data-value="{{ $c_tel['country_code'] }}"
                                                            value="{{ $c_tel['country_code'] }}"
                                                            data-id="{{ $c_tel['id'] }}"
                                                            data-high_price={{ $c_tel['high_price'] }}
                                                            data-price="{{ $c_tel['deliv_price'] }}">
                                                            {{ $c_tel['country_name'] }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php foreach ($sale_towns as $town) { ?>
                            <div class="cart-payments-page__item  town_delivery-<?=$town->country?> town_delivery cart-delivery-item" data-delivery_type="city_delivery" data-town_id="<?= $town->id ?>" sale-city="<?php echo isset($town->translations[0]) ? $town->translations[0]->value : $town->city; ?>" style="display: none;"  >
                                <div class="cart-delivery-item__wrapper">
                                    <div class="cart-delivery-item__header js-cart-delivery-item">
                                        <div class="kvizz-radio js-checkbox">
                                            <div class="check check-border"></div>
                                            <span class="jcf-radio jcf-unchecked">
                                                <input name="payments" type="radio">
                                            </span>
                                            <div class="window-prompt">
                                                @lang('cart_new.step_3_delivery_in') <?php echo isset($town->translations[0]) ? $town->translations[0]->value : $town->city; ?>
                                            </div>
                                        </div>

                                        @if(isset($basket['coupon_type']) && $basket['coupon_type']=='free_delivery')
                                            <span class="delivery-price">0€</span>
                                        @else
                                            <span class="delivery-price"><?=$town->price?>€</span>
                                        @endif
                                    </div>
                                    <div class="cart-delivery-item__content" style="display:none;">
                                        <div class="cart-delivery-courier">
                                            <label for="" class="cart-data-page__input name">
                                                <span>@lang('cart_new.step_3_pickup_address')</span>
                                                <input type="text" name="address" class="input-grey" required>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                            <div class="cart-payments-page__item cart-courier  cart-delivery-item @if (!count($citys)) js-active @endif" data-delivery_type="to_the_door">
                                <div class="cart-delivery-item__wrapper">
                                    <div class="cart-delivery-item__header js-cart-delivery-item">
                                        <div class="kvizz-radio js-checkbox">
                                            <div class="check check-border"></div>
                                            <span class="jcf-radio jcf-unchecked">
                                                <input name="payments" type="radio">
                                            </span>
                                            <div class="window-prompt">
                                                @lang('cart_new.step_3_delivery_to_your_address_by_courier')
                                            </div>
                                        </div>
                                        @if(isset($basket['coupon_type']) && $basket['coupon_type']=='free_delivery')
                                            <span class="delivery-price">0€</span>
                                        @else
                                            <span class="delivery-price">{{$cur_country['deliv_price']}}€</span>
                                        @endif
                                    </div>
                                    <div class="cart-delivery-item__content" style="display:block;">
                                        <div class="cart-delivery-courier">
                                            <div class="cart-delivery-courier__row">
                                                <label for="" class="cart-data-page__input city">
                                                    <span>@lang('cart_new.step_3_city')</span>
                                                    <input type="text" class="input-grey" required>
                                                </label>
                                                <label for="" class="cart-data-page__input index">
                                                    <span>@lang('cart_new.step_3_index')</span>
                                                    <input type="text" class="input-grey" required>
                                                </label>
                                            </div>
                                            <label for="" class="cart-data-page__input name">
                                                <span>@lang('cart_new.step_3_courier_address')</span>
                                                <input type="text" id="address_delivery" name="address_delivery" class="input-grey" required>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="cart-payments-page__item cart-pickup cart-delivery-item @if (!count($citys)) "data-delivery_type="venipak"  style="display: none;" @else js-active" data-delivery_type="venipak"  @endif >

                                <div class="cart-delivery-item__wrapper">
                                    <div class="cart-delivery-item__header js-cart-delivery-item">
                                        <div class="kvizz-radio js-checkbox">
                                            <div class="check check-border"></div>
                                            <span class="jcf-radio jcf-unchecked">
                                                <input name="payments" type="radio">
                                            </span>
                                            <div class="window-prompt">
                                                @lang('cart_new.step_3_delivery_to_pick-up_point')
                                            </div>
                                        </div>

                                        @if(isset($basket['coupon_type']) && $basket['coupon_type']=='free_delivery')
                                            <span class="delivery-price">0€</span>
                                        @else
                                            <span class="delivery-price">@if(isset($cur_country['delivery_venipak'])){{$cur_country['delivery_venipak']}}€ @else 0€ @endif</span>
                                        @endif
                                    </div>
                                    <div class="cart-delivery-item__content" @if (count($citys)) style="display:block;" @endif >
                                        <div class="cart-delivery-point">
                                            <div class="cart-delivery-point__wrapper">
                                                <div class="cart-delivery-point__row">
                                                    <div class="cart-delivery-point__col">
                                                        <label for="" class="cart-data-page__input city">
                                                            <span>@lang('cart_new.step_3_city_selection')</span>
                                                            <div class="select">
                                                                <div class="select__wrapper">
                                                                    <div class="select__content">
                                                                        <div class="select__trigger empty">
                                                                            <div class="icon">
                                                                                <svg>
                                                                                    <use
                                                                                        xlink:href="{{ asset(env('THEME') . 'img/cart/sprite.svg') }}#arrow-select">
                                                                                    </use>
                                                                                </svg>
                                                                            </div>
                                                                            <a class="all-city-cart" data-default="@lang('cart_new.step_3_all_city')" href="">@lang('cart_new.step_3_all_city')</a>
                                                                        </div>
                                                                        <div class="select__dropdown">
                                                                            <div class="select__options"
                                                                                data-simplebar="">

                                                                                @include(env('THEME_RESOURCES') . '.cart.citys', ['citys' => $citys])

                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </label>
                                                    </div>
                                                    <div class="cart-delivery-point__col">
                                                        <label for="" class="cart-data-page__input point">
                                                            <span>@lang('cart_new.step_3_selecting_a_pick-up_point')</span>
                                                            <input type="text" class="input-grey" required>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="cart-delivery-point__pickup-point">
                                                    <div class="cart-delivery-point__pickup-point--title">
                                                        <span class="city-text" data-default='@lang('cart_new.step_3_all_city')'>@lang('cart_new.step_3_all_city')</span> (<span class="pickup-text">{{ count($warehouses) }}</span> @lang('cart_new.step_3_points'))
                                                    </div>
                                                    <div class="cart-delivery-point__pickup-point--wrapper"
                                                        data-simplebar="">

                                                        @include(env('THEME_RESOURCES') . '.cart.warehouses', ['warehouses' => $warehouses])

                                                    </div>
                                                    <a href="#" class="wind-open">@lang('cart_new.step_3_expand')</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="cart-payments-page__item cart-master  cart-delivery-item" data-delivery_type="pickup_at_viar_workshop" style="/*opacity: 0.4;pointer-events: none;*/">
                                <div class="cart-delivery-item__wrapper">
                                    <div class="cart-delivery-item__header js-cart-delivery-item">
                                        <div class="kvizz-radio js-checkbox">
                                            <div class="check check-border"></div>
                                            <span class="jcf-radio jcf-unchecked">
                                                <input name="payments" type="radio">
                                            </span>
                                            <div class="window-prompt">
                                                @lang('cart_new.step_3_pick_up_at_viar_workshop')
                                            </div>
                                        </div>
                                        <span class="delivery-price"> 0€</span>
                                    </div>
                                    <div class="cart-delivery-item__content">
                                        <div class="cart-delivery-page__select-country">
                                            <span>@lang('cart_new.step_3_city')</span>
                                            @php
                                                $currentPickupCountry = strtoupper($user['country'] ?? '');
                                                $pickupCountryMatches = static function ($rawCountries, $country) {
                                                    $country = strtoupper(trim((string) $country));
                                                    if ($country === '') {
                                                        return true;
                                                    }

                                                    $codes = collect(explode(',', (string) ($rawCountries ?? 'ALL')))
                                                        ->map(function ($value) {
                                                            return strtoupper(trim($value));
                                                        })
                                                        ->filter(function ($value) {
                                                            return $value !== '';
                                                        })
                                                        ->unique()
                                                        ->values();

                                                    if ($codes->isEmpty()) {
                                                        return true;
                                                    }

                                                    return $codes->contains('ALL') || $codes->contains($country);
                                                };

                                                $defaultViarPickup = null;
                                                foreach ($DeliveryPickupAtViarWorkshop as $viar) {
                                                    if ($pickupCountryMatches($viar->country_code ?? 'ALL', $currentPickupCountry)) {
                                                        $defaultViarPickup = $viar;
                                                        break;
                                                    }
                                                }
                                            @endphp
                                            <div class="select">
                                                <div class="select__wrapper">
                                                    <div class="select__content">
                                                        <div class="select__trigger empty">
                                                            <div class="icon">
                                                                <svg>
                                                                    <use
                                                                        xlink:href="{{ asset(env('THEME') . 'img/cart/sprite.svg') }}#arrow-select">
                                                                    </use>
                                                                </svg>
                                                            </div>
                                                            @if ($defaultViarPickup)
                                                                <a href="">{{ $defaultViarPickup->title }}</a>
                                                            @endif
                                                        </div>
                                                        <div class="select__dropdown">

															<div class="select__options  " data-simplebar="">
                                                                @php $selectedSet = false; @endphp
																@foreach ($DeliveryPickupAtViarWorkshop as $viar)
                                                                    @php
                                                                        $countryCode = strtoupper(trim((string)($viar->country_code ?? 'ALL')));
                                                                        $isVisible = $pickupCountryMatches($viar->country_code ?? 'ALL', $currentPickupCountry);
                                                                        $isSelected = !$selectedSet && $isVisible;
                                                                        if ($isSelected) {
                                                                            $selectedSet = true;
                                                                        }
                                                                    @endphp
                                                                	<a href="#"
                                                                       class="select__option @if($isSelected) selected @endif"
                                                                       data-value="{{ $viar->id }}"
                                                                       data-country="{{ $countryCode }}"
                                                                       @if(!$isVisible) style="display: none;" @endif>
                                                                        {{ $viar->title }}
                                                                    </a>
																@endforeach

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="cart-delivery-comments">
                                <div class="cart-delivery-comments__header js-cart-delivery-comments__header">
                                    <div class="cart-delivery-comments__arrow">
                                        <svg>
                                            <use
                                                xlink:href="{{ asset(env('THEME') . 'img/cart/sprite.svg') }}#arrow-select">
                                            </use>
                                        </svg>
                                    </div>
                                    <div class="cart-delivery-comments__title   ">
                                        <span>@lang('cart_new.step_3_your_comment_on_the_order')</span>
                                        <img src="{{ asset(env('THEME') . 'img/cart/comment.svg') }}" alt="">
                                    </div>
                                </div>
                                <div class="cart-delivery-comments__wrapper">
                                    <div class="cart-delivery-comments__content">
                                        <span>@lang('cart_new.step_3_details')</span>
                                        <textarea placeholder="@lang('cart_new.step_3_your_comment')" class="input-grey"></textarea>
                                        <label class="cart-delivery-comments__add-photo">
                                            <svg>
                                                <use
                                                    xlink:href="{{ asset(env('THEME') . 'img/cart/sprite.svg') }}#add-photo">
                                                </use>
                                            </svg>
                                            <input type="file">
                                            <span>@lang('cart_new.step_3_add_photo_example')</span>
                                            <span class="cart-comments--complete">@lang('cart_new.general_added')</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="cart-delivery-calendar">
                                <label for="" class="cart-data-page__input">
                                    <span>@lang('cart_new.step_3_desired_delivery_date')</span>
                                    <input type="text" class="input-grey" data-toggle="datepicker"
                                        placeholder="DD/MM/YYYY" autocomplete="off">
                                </label>
                                <div class="cart-delivery-calendar__warning"
                                    style="color: #d33; font-size: 14px; margin-top: 8px; display: none;" hidden>
                                    @lang('cart_new.step_3_cart_express_warning')
                                </div>
                            </div>
                        </div>
                        <div class="cart-data-page__bottom">
                            <a href="{{ route('cart.step2') }}" rel="nofollow" class="cart-data-page__btn-back">
                                @lang('cart_new.general_return')
                            </a>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            @include(env('THEME_RESOURCES') . '.cart.sidebar', [
                'basket' => $basket,
                'btn' => trans('cart_new.step_3_go_to_the_payment'),
                'btn_class' => 'cart_send_delivery',
                'action' => route('cart.step4'),
                'step' => 3,
            ])

            {{-- <div class="cart-page-global__sidebar data-page">
				<div class="cart-page-sidebar">
					<div class="cart-page-sidebar__wrapper">
						<div class="cart-page-sidebar__total-amount discount-full">
							<span>
								@lang("cart_new.general_total_amount")
							</span>
							<strong>
								250€
							</strong>
						</div>
						<div class="cart-page-sidebar__total-amount discount">
							<span>
								С 10% скидкой от купона:
							</span>
							<strong>
								235€
							</strong>
						</div>
						<div class="cart-page-sidebar__amount-goods">
							<span>@lang("cart_new.general_amount_of_goods")</span>
							<strong>1 x 125</strong>
						</div>
						<div class="cart-page-sidebar__amount-goods">
							<span> </span>
							<strong>1 x 125</strong>
						</div>
						<div class="cart-page-sidebar__delivery">
							<span>@lang("cart_new.general_delivery")</span>
							<strong>---</strong>
						</div>
						<div class="cart-page-sidebar__delivery manufacturing">
							<span>@lang("cart_new.general_production")</span>
							<strong>Экспресс 5€ </strong>
						</div>
						<div class="cart-page-sidebar__btn-checkout">
							<button class="btn--orange">
								@lang("cart_new.step_3_go_to_the_payment")
							</button>
						</div>
					</div>
				</div>
			</div> --}}
            <div class="cart-data-page__bottom tablet">
                <a href="{{ route('cart.step2') }}" rel="nofollow" class="cart-data-page__btn-back">
                    @lang('cart_new.general_return')
                </a>
            </div>
        </div>
    </div>
</div>


<script>
    document.querySelector('.cart-pickup .js-cart-delivery-item').click();

    (function () {
        var calendars = document.querySelectorAll('.cart-delivery-calendar');
        if (!calendars.length) {
            return;
        }

        var bindCalendar = function (calendar) {
            var deliveryInput = calendar.querySelector('[data-toggle="datepicker"]');
            var warning = calendar.querySelector('.cart-delivery-calendar__warning');

            if (!deliveryInput || !warning) {
                return;
            }

            var toggleWarning = function () {
                var hasDate = deliveryInput.value.trim().length > 0;
                warning.style.display = hasDate ? 'block' : 'none';
                warning.hidden = !hasDate;
            };

            deliveryInput.addEventListener('change', toggleWarning);
            deliveryInput.addEventListener('input', toggleWarning);
            deliveryInput.addEventListener('blur', function () {
                toggleWarning();
            });
            deliveryInput.addEventListener('focus', toggleWarning);
            deliveryInput.addEventListener('click', toggleWarning);

            var handleDatepickerPick = function (event) {
                if (!event.target || !event.target.closest('.datepicker-panel')) {
                    return;
                }
                setTimeout(toggleWarning, 0);
            };

            document.addEventListener('mousedown', handleDatepickerPick, true);
            document.addEventListener('click', handleDatepickerPick, true);
            document.addEventListener('touchstart', handleDatepickerPick, true);

            if (window.jQuery) {
                window.jQuery(deliveryInput).on('pick.datepicker change.datepicker', toggleWarning);
            }

            toggleWarning();
        };

        for (var i = 0; i < calendars.length; i += 1) {
            bindCalendar(calendars[i]);
        }
    })();
</script>
