@php $is_gift_card = false @endphp
@if (!empty($basket))
	@php

		$logstring = var_export($basket, true);
		$file = __DIR__ . '/output_sidebar.txt';
		file_put_contents($file, $logstring . "\n", FILE_APPEND);

		$total_item_counts = 0;
		$term = '';
		$term_price = 0;
		$total_term_price = 0;
	@endphp

	@foreach ($basket as $basketIndex => $product)
		@if(isset($product['pid']) && $product['pid'] == 5)
			@php $is_gift_card = true @endphp
		@endif

		@if (!isset($product['sumPrice']))
			@continue
		@endif
		@php
			$total_item_counts = $total_item_counts + $product['count'];
			if (isset($product['terms_price'])) {
			    $term_price = (float) $term_price + (float) $product['terms_price'];
			    $total_term_price = $total_term_price + (float) $product['terms_price'];
			}

		@endphp
	@endforeach
@else
	@php $total_item_counts = 0; @endphp
@endif

<div id="cart-sidebar">
	<div class="cart-page-global__sidebar">
		<div class="cart-page-sidebar">

            @if ($step == 1 && isset($recommendedItems) && !empty($recommendedItems))
                @php
                    $showRecommendations = false;

                    $basketProducts = array_filter($basket, function($item, $key) {
                        return is_numeric($key) && isset($item['sumPrice']);
                    }, ARRAY_FILTER_USE_BOTH);

                    if (count($basketProducts) == 1) {
                            $showRecommendations = true;
                    }
                @endphp

                @if ($showRecommendations)
                    @include(config('theme.resource') . '.cart.recommendations', ['recommendedItems' => $recommendedItems])
                @endif
            @endif

            <div class="cart-page-sidebar__wrapper">

				<div class="cart-page-sidebar__total-amount">
					<span>
						@lang('cart_new.general_total_amount')
					</span>
					<strong>
						@php
							$total_price = 0;
							$sale_price = 0;
							if (isset($basket['totalPrice'])) {
							    $total_price = (float) $basket['totalPrice'] + (float) $term_price . ' €';
							} else {
							    $total_price = $basket['totalPrice'] . ' €';
							}

							if(isset($basket['sale_price']))
							{
								$total_price = (float)$basket['sale_price'] + (float) $total_term_price . ' €';
								$sale_price = $basket['sale_price'];
								$sale_price = number_format((float)$sale_price, 2, '.', '') . ' €';
							}

							if (isset($cart_delivery['price'])) {
							    $total_price = (float) $total_price + (float) $cart_delivery['price'] . ' €';
							}


							$total_price = number_format((float)$total_price,2, '.', '') . ' €';
						@endphp


						@if(isset($basket['sale_price']))
							{{ $total_price }}
						@else
							{{ $total_price }}
						@endif

					</strong>
				</div>
				<div class="cart-page-sidebar__amount-goods">
					<span>@lang('cart_new.general_amount_of_goods')</span>


					@foreach ($basket as $basketIndex => $product)
						@if (!isset($product['sumPrice']))
							@continue
						@endif

						@if ($product['count'] && $product['price'])
							<strong>{{ $product['count'] }} x {{ str_replace('.00', '', $product['price']) }}€</strong>
						@endif
					@break
				@endforeach
			</div>

			@php
				$i = 0;
				// $term = '';
				$term_price = '';
			@endphp
			@foreach ($basket as $basketIndex => $product)
				@if (!isset($product['sumPrice']))
					@continue
				@endif


				@php
					$i++;
				@endphp

				@if ($product['count'] && $product['price'] && $i != 1)
					<div class="cart-page-sidebar__amount-goods">
						<span> </span>
						<strong>{{ $product['count'] }} x {{ str_replace('.00', '', $product['price']) }}€</strong>
					</div>
				@endif
			@endforeach

			@if ($basket['coupon_id'] > 0)
				<div class="cart-page-sidebar__amount-goods clear_coupon">
					<span>@lang('mail.one_free_text2')</span>
					<strong> {{ $basket['coupon_val'] }} </strong>
				</div>
			@endif

			@if ($basket['coupon_id'] == -1)
				<div class="cart-page-sidebar__amount-goods clear_coupon">
					<span>Такого купона нет</span>
					<strong> </strong>
				</div>
			@endif

			@if ($basket['coupon_id'] == -2)
				<div class="cart-page-sidebar__amount-goods clear_coupon">
					<span>Ошибка купона</span>
					<strong> </strong>
				</div>
			@endif


			@if (isset($basket['sale_price']) && $total_price != $sale_price)
				<div class="cart-page-sidebar__amount-goods">
					<span>
						@lang('cart.total')
					</span>
					<strong>
							{{ $sale_price }}
					</strong>
				</div>
			@endif

			@if (isset($cart_delivery['delivery_type']))
				<div class="cart-page-sidebar__delivery">

					<span>@lang('cart_new.general_delivery')</span>
					<strong>
						@if (isset($basket['coupon_type']) && $basket['coupon_type'] == 'free_delivery')
							@lang('cart_new.free_delivery')<br>
                        @else
                            {{ $cart_delivery['price'] . ' €' }}
                        @endif


{{--						@if ($cart_delivery['delivery_type'] == 'to_the_door')--}}
{{--							@lang('cart_new.step_3_delivery_to_your_address_by_courier') {{ $cart_delivery['price'] }}--}}
{{--						@elseif ($cart_delivery['delivery_type'] == 'pickup_at_viar_workshop')--}}
{{--							@lang('cart_new.step_3_pick_up_at_viar_workshop') {{ $cart_delivery['price'] }}--}}
{{--						@elseif ($cart_delivery['delivery_type'] == 'venipak')--}}
{{--							@lang('cart_new.step_3_delivery_to_pick-up_point') {{ $cart_delivery['price'] }}--}}
{{--						@elseif ($cart_delivery['delivery_type'] == 'city_delivery')--}}
{{--							@lang('cart_new.step_3_delivery_in') {{ $cart_delivery['city'] }} {{ $cart_delivery['price'] }}--}}
{{--						@endif--}}

					</strong>
				</div>
			@else
				<div class="cart-page-sidebar__delivery delivering"
					@if (isset($basket['coupon_type']) && $basket['coupon_type'] == 'free_delivery') data-coupon="@lang('cart_new.free_delivery')" @else data-coupon="" @endif>
					<span>@lang('cart_new.general_delivery')</span>
					@if (isset($basket['coupon_type']) && $basket['coupon_type'] == 'free_delivery')
						<strong>@lang('cart_new.free_delivery')</strong>
					@else
						<strong>---</strong>
					@endif
				</div>
			@endif



			<div class="cart-page-sidebar__delivery manufacturing">
				<span>@lang('cart_new.general_production')</span>
				<strong>
					@if ($total_term_price)
						@lang('gl.express')
					@else
						@lang('gl.standart')
					@endif {{ $total_term_price . ' €' }}
				</strong>
			</div>



			@if ($step == 1)
				@if ($basket['spend_bonus'] == false)
					@if ($basket['coupon_id'] <= 0)
						<div class="cart-page-sidebar__certificate">
							@if(!$is_gift_card)
								<span>
									<strong>
										@lang('cart_new.ganeral_coupon_or_certificate')
									</strong>
								</span>

								<input name="coupon" type="text" class="input-grey" placeholder="@lang('cart_new.general_enter_code')">

								@auth
									<a href="" class="cart-page-sidebar__certificate--btn">
										@lang('cart_new.general_apply')
									</a>
								@endauth

								@guest
									<a href="" class="js-popup-login" style="margin-top: 6px;
									font-weight: 400;
									font-size: 15px;
									line-height: 18px;
									color: #fa7846;
									border-bottom: 1px solid #fa7846;
									transition: 0.3s linear;
									float: right;
									margin-bottom: 30px;">
										@lang('cart_new.general_apply')
									</a>
								@endguest
							@endif

						</div>
					@endif
					@if ($basket['coupon_id'] > 0)
						<div class="cart-page-sidebar__certificate">
							<span>
								<b>@lang('cart_new.ganeral_coupon_applyed')</b>
							</span>
						</div>
					@endif

				@endif


				@if ($basket['coupon_id'] <= 0 && !$is_gift_card)
					<div class="cart-bonuses">
						@guest
							<span style="margin-top: 10px; color: #fa7947;">
								<b>@lang('cart_new.general_login_for_bonus')</b>
							</span> <a href="" class="js-popup-login"> @lang('cart_new.general_login_link')</a>
						@endguest
						@auth
							<span style="margin-top: 10px">
								@lang('cart_new.general_your_bonus') {{ $basket['bonus'] }}
							</span>

							<a href="" class= "cart-bonuses-btn">
								@lang('cart_new.general_apply')
							</a>

						@endauth
					</div>
				@endif
			@endif
			<div class="cart-page-sidebar__btn-checkout">
				<button class="btn--orange {{ $btn_class }}" data-action="{{ $action }}">
					{{ $btn }}
				</button>
			</div>
			@if ($step == 1)
				<div class="kviz-politics">
					<div class="cart-checkbox">
						<input type="checkbox" checked>
						<svg class="vz-art kviz-input__icon">
							<use xlink:href="{{ asset(env('THEME') . '/sprite.svg') }}#log"></use>
						</svg>
					</div>
					<svg>
						<use xlink:href="{{ asset(env('THEME') . '/sprite.svg') }}#lock"></use>
					</svg>
					<p>
						{!! storefront_html(trans('cart_new.create_acc_policy_text')) !!}

					</p>
				</div>
			@endif
			@if ($step == 4)
				<div class="cart-payments-sidebar__personal-data">
					<label class="cart-payments-page__item js-cart-payments-page__personal-dataa js-active">
						<div class="kvizz-radio js-checkbox">
							<div class="check check-border"></div>
							<span class="jcf-radio jcf-unchecked">
								<input name="personal-data" type="checkbox">
							</span>
							<div class="window-prompt">
								@lang('cart_new.step_4_agreement')
							</div>
						</div>
					</label>
					<div class="cart-payments-sidebar__personal-data--text">
						@lang('cart_new.step_4_personal-data-text1') <a href="{{ storefront_url('/condition') }}" target="_blank">@lang('cart_new.step_4_personal-data-text2')</a>
					</div>
				</div>
			@endif

			@if ($total_item_counts > 0 && $step == 1)
				<div class="cart-page-sidebar__make">
					<div class="cart-page-sidebar__make--title">
						<span>@lang('cart_new.general_production')</span>
						<img src="{{ asset(env('THEME') . 'img/cart/clock-orange.svg') }}" alt="">
					</div>

					@php
						$term_class = false;
						if (!$total_term_price) {
						    $term_class = 1;
						}
						if ($total_term_price) {
						    $term_class = 2;
						}
					@endphp

					<div
						class="cart-page-sidebar__make--item @if ($term_class == 1) js-active @endif js-cart-page-sidebar__make--item">
						<span data-method="standart">
							@lang('gl.standart')
						</span>
						{{-- <strong>0€</strong> --}}
					</div>
					<div
						class="cart-page-sidebar__make--item @if ($term_class == 2) js-active @endif js-cart-page-sidebar__make--item">
						<span data-method="express">
							@lang('gl.express')
							<br>
							<div style="font-size:12px">@lang('cart_new.additional_fee')</div>
						</span>
						{{-- <strong>5€</strong> --}}
					</div>
				</div>
			@endif
		</div>
	</div>
</div>
</div>
