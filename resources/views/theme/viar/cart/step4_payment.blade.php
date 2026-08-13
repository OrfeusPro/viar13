<div class="vz-art cart-page-global">
	<div class="section-frame">

		@include(env('THEME_RESOURCES') . '.cart.bread', ['step' => 4])

		<div class="cart-page-global__wrapper">
			<div class="cart-page-global__content">
				<div class="cart-data-page cart-payments-page">
					<div class="cart-data-page__wrapper cart-payments-page__wrapper">
						<div class="cart-payments-page__content">
							<div class="cart-payments-page__title">
								@lang('cart_new.step_4_select_a_payment_method')
							</div>
							<div class="cart-payments-page__list">

								@if($cart_delivery['delivery_type'] == 'pickup_at_viar_workshop' && $cart_delivery['country']=='LV')
								<!-- <label data-type='cash_in_office' class="cart-payments-page__item js-cart-payments-page__item">
									<div class="kvizz-radio js-checkbox">
										<div class="check check-border"></div>
										<span class="jcf-radio jcf-unchecked">
											<input name="payments" type="radio">
										</span>
										<div class="window-prompt">
											@lang('cart_new.step_4_cash_upon_receipt_at_the_workshop')
										</div>
									</div>
								</label> -->
								@endif

								@if($cart_delivery['delivery_type'] == 'to_the_door' && ($cart_delivery['country']=='LV' || $cart_delivery['country']=='LT' || $cart_delivery['country']=='EE'))
								@isset($c_tels['high_price'])
								<label data-type='on_delivery' class="cart-payments-page__item js-cart-payments-page__item">
									<div class="kvizz-radio js-checkbox">
										<div class="check check-border"></div>
										<span class="jcf-radio jcf-unchecked">
											<input name="payments" type="radio">
										</span>
										<div class="window-prompt">
											@lang('cart_new.step_4_payment_to_the_courier') +{{ $c_tels['high_price'] }}€
										</div>
									</div>
								</label>
								@endisset
								@endif
                                    @foreach(\App\Http\Controllers\Libwebtopay\WebToPay::PAYSERA_METHODS_MAP as $keyMethod => $methodData)
                                        <label data-type='{{ $keyMethod }}' class="cart-payments-page__item js-cart-payments-page__item">
                                            <div class="kvizz-radio js-checkbox">
                                                <div class="check check-border"></div>
                                                <span class="jcf-radio jcf-unchecked">
											<input name="payments" type="radio">
										</span>
                                                <div class="window-prompt">
                                                    {{ $methodData['title_translate'] ? __($methodData['title']) : $methodData['title'] }}
                                                </div>
                                            </div>
                                            @if($methodData['img'])
                                            <div class="cart-payments-page__item--img">
                                                <img src="{{ asset(env('THEME') . $methodData['img']) }}" alt="">
                                            </div>
                                            @endif
                                        </label>
                                    @endforeach
                                    {{-- @if(Auth::user()->email === 'info@asoft.com.ua') --}}
                                    <label data-type='paypalOnetimePayment' class="cart-payments-page__item js-cart-payments-page__item">
                                        <div class="kvizz-radio js-checkbox">
                                            <div class="check check-border"></div>
                                            <span class="jcf-radio jcf-unchecked">
											<input name="payments" type="radio">
										</span>
                                            <div class="window-prompt">
                                                PayPal
                                            </div>
                                        </div>
										<div class="cart-payments-page__item--img">
                                            <img src="{{ asset(env('THEME') . 'img/icons/PayPal.svg') }}" alt="">
                                        </div>
                                    </label>
                                    {{-- @endif --}}
								<label data-type='transfer' class="cart-payments-page__item js-cart-payments-page__item">
									<div class="kvizz-radio js-checkbox">
										<div class="check check-border"></div>
										<span class="jcf-radio jcf-unchecked">
											<input name="payments" type="radio">
										</span>
										<div class="window-prompt">
											@lang('cart_new.step_4_payment_by_bank_transfer')
										</div>
									</div>
								</label>

                                    <label data-type='prepayment' class="cart-payments-page__item js-cart-payments-page__item">
                                        <div class="kvizz-radio js-checkbox">
                                            <div class="check check-border"></div>
                                            <span class="jcf-radio jcf-unchecked">
											<input name="payments" type="radio">
										</span>
                                            <div class="window-prompt">

                                                @lang('cart_new.prepayment')
                                            </div>
                                        </div>
                                        <div class="cart-payments-page__item--img">
                                            <img src="{{ asset(env('THEME') . 'img/cart/prepaid2.png') }}" alt="">
                                        </div>
                                    </label>

							</div>
						</div>
						<div class="cart-data-page__bottom">
							<a href="{{ route('cart.step3') }}" rel="nofollow" class="cart-data-page__btn-back">
								@lang('cart_new.general_return')
							</a>
						</div>
					</div>
				</div>
			</div>

			@if (!empty($basket))
				@php $total_item_counts = 0; @endphp
				@foreach ($basket as $basketIndex => $product)
					@if (!isset($product['sumPrice']))
						@continue
					@endif
					@php
						$total_item_counts = $total_item_counts + $product['count'];
					@endphp
				@endforeach
			@else
				@php $total_item_counts = 0; @endphp
			@endif

			@include(env('THEME_RESOURCES') . '.cart.sidebar', [
			    'basket' => $basket,
			    'btn' => trans('cart_new.step_4_pay'),
			    'btn_class' => 'cart_send_pay',
			    'action' => route('save_order_and_pay'),
			    'step' => 4,
			])

			{{-- <div class="cart-page-global__sidebar data-page">
				<div class="cart-page-sidebar cart-payments-sidebar">
					<div class="cart-page-sidebar__wrapper cart-payments-sidebar__wrapper">
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
								@lang("cart_new.step_4_pay")
							</button>
						</div>
						<div class="cart-payments-sidebar__personal-data">
							<label class="cart-payments-page__item js-cart-payments-page__personal-dataa">
								<div class="kvizz-radio js-checkbox">
									<div class="check check-border"></div>
									<span class="jcf-radio jcf-unchecked">
										<input name="personal-data" type="checkbox">
									</span>
									<div class="window-prompt">
										@lang("cart_new.step_4_agreement")
									</div>
								</div>
							</label>
							<div class="cart-payments-sidebar__personal-data--text">
								@lang("cart_new.step_4_personal-data-text1") <a href="">@lang("cart_new.step_4_personal-data-text2")</a>
							</div>
						</div>
					</div>
				</div>
			</div> --}}
			<div class="cart-data-page__bottom tablet">
				<a href="{{ route('cart.step3') }}" rel="nofollow" class="cart-data-page__btn-back">
					@lang('cart_new.general_return')
				</a>
			</div>
		</div>
	</div>
</div>
