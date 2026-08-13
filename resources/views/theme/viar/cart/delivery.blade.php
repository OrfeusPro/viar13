<div class="vz-art cart-page-global">
    <div class="section-frame">
        <div class="cart-page-global__wrapper">
            <div class="cart-page-global__content">
                <div class="cart-data-page cart-delivery-page">
                    <div class="cart-data-page__wrapper cart-delivery-page__wrapper">
                        <div class="cart-delivery-page__content">
                            <div class="cart-delivery-page__select-country" id="country_block"  country="{{$country}}">
                                <span>@lang('cart_new.step_3_country_selection')</span>
                                <div class="select">
                                    <div class="select__wrapper">
                                        <div class="select__content">
                                            <div class="select__trigger empty">
                                                <div class="icon">
                                                    <svg> <use xlink:href="{{ asset(env('THEME') . 'img/cart/sprite.svg') }}#arrow-select"> </use> </svg>
                                                </div>
                                                <a href="">
													@php
														$cur_country['deliv_price'] = 0;
													@endphp
                                                    @foreach ($c_tels as $c_tel)
                                                        @if ($country)
                                                            @php
                                                                $sel_ct = $country;
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
                                                        <a href="#"
                                                            class="select__option
															@if ($country)
																@php
																$sel_ct = $country;
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

                                            <div class="icon"><img width="24" height="24" src="/images/icon/check-circle.svg" alt=""> </div>
                                            <div class="window-prompt">
                                                @lang('cart_new.step_3_delivery_in') <?php echo isset($town->translations[0]) ? $town->translations[0]->value : $town->city; ?>
                                            </div>
                                        </div>
                                        <span class="delivery-price"><?=$town->price?>€</span>
                                    </div>

                                </div>
                            </div>

                            <?php } ?>


                            <div class="cart-payments-page__item cart-courier  cart-delivery-item js-active" data-delivery_type="to_the_door">
                                <div class="cart-delivery-item__wrapper">
                                    <div class="cart-delivery-item__header js-cart-delivery-item">
                                        <div class="kvizz-radio js-checkbox">

                                            <div class="icon"><img width="24" height="24" src="/images/icon/check-circle.svg" alt=""> </div>
                                            <div class="window-prompt">
                                                @lang('cart_new.step_3_delivery_to_your_address_by_courier')
                                            </div>
                                        </div>
                                        <span class="delivery-price">{{$cur_country['deliv_price']}}€</span>
                                    </div>
                                    <div class="cart-delivery-item__content" style="display:block;">

                                    </div>
                                </div>
                            </div>










                            <div class="cart-payments-page__item cart-pickup  cart-delivery-item" data-delivery_type="venipak"
                                @if (!count($citys)) style="display: none;" @endif>
                                <div class="cart-delivery-item__wrapper">
                                    <div class="cart-delivery-item__header js-cart-delivery-item">
                                        <div class="kvizz-radio js-checkbox">

                                            <div class="icon"><img width="24" height="24" src="/images/icon/check-circle.svg" alt=""> </div>
                                            <div class="window-prompt">
                                                @lang('cart_new.step_3_delivery_to_pick-up_point')
                                            </div>
                                        </div>

                                        @if(isset($basket['coupon_type']) && $basket['coupon_type']=='free_delivery')
                                            <span class="delivery-price">0€</span>
                                        @else
                                            <span class="delivery-price">@if(isset($cur_country['delivery_venipak'])){{$cur_country['delivery_venipak']}}€ @else 0€ @endif</span>
                                        @endif

                                        {{-- <span class="delivery-price">@if(isset($cur_country['delivery_venipak'])){{$cur_country['delivery_venipak']}}€ @else 0€ @endif</span> --}}
                                    </div>
                                    <div class="cart-delivery-item__content">
                                        <div class="cart-delivery-point">
                                            <div class="cart-delivery-point__wrapper">
{{--                                                <a href="https://venipak.lv/produkti-un-pakalpojumi/pickup-sutijumu-punkti/" target="_blank" class="btn--orange cart-delivery-point__btn">--}}
{{--                                                    <svg>--}}
{{--                                                        <use--}}
{{--                                                            xlink:href="{{ asset(env('THEME') . 'img/cart/sprite.svg') }}#geo">--}}
{{--                                                        </use>--}}
{{--                                                    </svg>--}}
{{--                                                    <span>@lang('cart_new.step_3_show_nearest_pick-up_points')</span>--}}
{{--                                                </a>--}}
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

                                                                                @include(config('theme.resource') . 'cart.citys', ['citys' => $citys])

                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </label>
                                                    </div>

                                                </div>

                                                <div class="cart-delivery-point__pickup-point">
                                                    <div class="cart-delivery-point__pickup-point--title">
                                                        <span class="city-text" data-default='@lang('cart_new.step_3_all_city')'>@lang('cart_new.step_3_all_city')</span> (<span class="pickup-text">{{ count($warehouses) }}</span> @lang('cart_new.step_3_points'))
                                                    </div>
                                                    <div class="cart-delivery-point__pickup-point--wrapper"data-simplebar="">
{{--                                                        @include(config('theme.resource') . 'cart.warehouses', ['warehouses' => $warehouses])--}}
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
                                            <div class="icon"><img width="24" height="24" src="/images/icon/check-circle.svg" alt=""> </div>
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
                                                $currentPickupCountry = strtoupper($country ?? '');
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
