@include(env('THEME_RESOURCES') . 'account.breads', ['page' => $page])

@php
	$productionLabel = $orderData['terms_price'] > 0 ? __('gl.express') : __('gl.standart');
@endphp

<style>
	.cabinet-payment-page__layout {
		display: grid;
		grid-template-columns: minmax(0, 1fr) 340px;
		gap: 24px;
		align-items: start;
	}

	.cabinet-content.cabinet-payment-page__content {
		background: #fff9f3;
		border-radius: 8px;
		padding: 35px 30px 60px;
		position: relative;
	}

	.cabinet-payment-page__main {
		min-width: 0;
	}

	.cabinet-payment-page__order-card {
		padding: 24px;
		margin-bottom: 24px;
	}

	.cabinet-payment-page__order-head {
		display: grid;
		grid-template-columns: minmax(0, 1fr) auto;
		gap: 24px;
		align-items: start;
	}

	.cabinet-payment-page__order-total {
		text-align: right;
	}

	.cabinet-payment-page__order-total span {
		display: block;
		margin-bottom: 8px;
		color: #848484;
		font-weight: 500;
		font-size: 16px;
		line-height: 19px;
	}

	.cabinet-payment-page__order-total strong {
		color: #1e2533;
		font-weight: 600;
		font-size: 28px;
		line-height: 34px;
	}

	.cabinet-payment-page__methods-title {
		color: #1e2533;
		font-weight: 600;
		font-size: 32px;
		line-height: 38px;
		margin-bottom: 24px;
	}

	.cabinet-payment-page__methods {
		margin-bottom: 0;
	}

	.cabinet-payment-page__methods .cart-payments-page__item {
		min-height: 104px;
		margin-bottom: 16px !important;
		padding-left: 18px;
		padding-right: 24px;
		background: #f9f1ea;
		border: 1px solid #d6d6d6;
	}

	.cabinet-payment-page__methods .cart-payments-page__item:hover {
		border-color: #fa7846;
	}

	.cabinet-payment-page__method-button {
		width: 100%;
		border: 0;
		padding: 0;
		background: transparent;
		text-align: left;
		display: flex;
		align-items: center;
		justify-content: space-between;
		cursor: pointer;
	}

	.cabinet-payment-page__methods .cart-payments-page__item .window-prompt {
		font-size: 18px;
		line-height: 24px;
	}

	.cabinet-payment-page__methods .cart-payments-page__item--img {
		width: 150px;
		flex-shrink: 0;
	}

	.cabinet-payment-page__sidebar {
		min-width: 0;
	}

	.cabinet-payment-page__sidebar .cart-page-sidebar {
		padding: 28px 26px;
		border-radius: 8px;
		background: #f9f1ea;
	}

	.cabinet-payment-page__sidebar .cart-page-sidebar__total-amount span {
		font-size: 22px;
		line-height: 28px;
	}

	.cabinet-payment-page__sidebar .cart-page-sidebar__total-amount strong {
		font-size: 28px;
		line-height: 34px;
	}

	.cabinet-payment-page__sidebar .cart-page-sidebar__amount-goods span,
	.cabinet-payment-page__sidebar .cart-page-sidebar__delivery span,
	.cabinet-payment-page__sidebar .cart-page-sidebar__amount-goods strong,
	.cabinet-payment-page__sidebar .cart-page-sidebar__delivery strong {
		font-size: 18px;
		line-height: 22px;
	}

	.cabinet-payment-page__back-desktop {
		margin-top: 24px;
	}

	.cabinet-payment-page__back-mobile {
		display: none;
		margin-top: 24px;
	}

	@media (max-width: 1200px) {
		.cabinet-payment-page__layout {
			grid-template-columns: 1fr;
		}

		.cabinet-payment-page__sidebar {
			order: 2;
		}

		.cabinet-payment-page__main {
			order: 1;
		}

		.cabinet-payment-page__back-desktop {
			display: none;
		}

		.cabinet-payment-page__back-mobile {
			display: block;
		}
	}

	@media (max-width: 768px) {
		.cabinet-payment-page__order-card {
			padding: 20px;
			margin-bottom: 20px;
		}

		.cabinet-payment-page__order-head {
			grid-template-columns: 1fr;
			gap: 14px;
		}

		.cabinet-payment-page__order-total {
			text-align: left;
		}

		.cabinet-payment-page__methods-title {
			font-size: 24px;
			line-height: 30px;
			margin-bottom: 18px;
		}

		.cabinet-payment-page__methods .cart-payments-page__item {
			min-height: 88px;
			padding-right: 18px;
		}

		.cabinet-payment-page__methods .cart-payments-page__item--img {
			width: 110px;
		}

		.cabinet-payment-page__sidebar .cart-page-sidebar {
			position: static;
		}
	}

	@media (max-width: 576px) {
		.cabinet-payment-page__methods .cart-payments-page__item .window-prompt {
			font-size: 16px;
			line-height: 20px;
		}

		.cabinet-payment-page__methods .cart-payments-page__item--img {
			width: 88px;
		}
	}
</style>

<div class="main-cabinet">
	<div class="section-frame">
		<div class="main-cabinet__inner">
			<div class="cabinet-top__block">
				<div class="page-title cabinet-title">
					{!! $page['title'] !!}
				</div>
				<p>
					@lang('account_new.pay_order_hint')
				</p>
			</div>
			<div class="cabinet-container">
				@include(env('THEME_RESOURCES') . 'account.menu')

				<div class="cabinet-content cabinet-payment-page__content">
					<div class="cabinet-payment-page__layout">
						<div class="cabinet-payment-page__main">
							@if ($errors->any())
								<div class="cabinet-orderItem__info" style="margin-bottom: 20px; max-width: 100%;">
									<p>{{ $errors->first('payment') }}</p>
									<img src="{{ asset(config('theme.current') . '/images') }}/cabinet/icon-danger.svg" width="30" height="30" alt="">
								</div>
							@endif

							<div class="cabinet-orderState cabinet-payment-page__order-card">
								<div class="cabinet-payment-page__order-head">
									<div class="simple-block">
										<p class="text-base">
											@lang('account.index15')
											<span class="darkSpan"># {{ $order->id }} - {{ date('d.m.Y', strtotime($order->created_at)) }}</span>
										</p>
										<p class="text-base">
											@lang('account_new.payment_status')
											<span class="darkSpan">{{ $orderData['payment_status_label'] }}</span>
										</p>
									</div>
									<div class="cabinet-payment-page__order-total">
										<span>@lang('cart_new.general_total_amount')</span>
										<strong>{{ number_format($orderData['total'], 2) }} €</strong>
									</div>
								</div>
							</div>

							<div class="cabinet-payment-page__methods-title">
								@lang('cart_new.step_4_select_a_payment_method')
							</div>

							<div class="cart-payments-page__list cabinet-payment-page__methods">
								@foreach($paymentMethods as $paymentKey => $methodData)
									<form method="POST" action="{{ route('new_account.order_payment.start', ['order' => $order->id]) }}" class="cabinet-payment-page__method-form">
										@csrf
										<input type="hidden" name="payment" value="{{ $paymentKey }}">
										<button type="submit" class="cart-payments-page__item cabinet-payment-page__method-button">
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

							<div class="cart-data-page__bottom tablet cabinet-payment-page__back-mobile">
								<a href="{{ route('new_account.orders') }}" rel="nofollow" class="cart-data-page__btn-back">
									@lang('account_new.back_to_orders')
								</a>
							</div>
						</div>

						<div class="cabinet-payment-page__sidebar">
							<div class="cart-page-sidebar">
								<div class="cart-page-sidebar__total-amount">
									<span>@lang('cart_new.general_total_amount')</span>
									<strong>{{ number_format($orderData['total'], 2) }} €</strong>
								</div>

								<div class="cart-page-sidebar__amount-goods">
									<span>@lang('cart_new.general_amount_of_goods')</span>
									<strong>1 x {{ number_format($orderData['discounted_subtotal'], 2) }} €</strong>
								</div>

								<div class="cart-page-sidebar__delivery">
									<span>@lang('cart_new.general_delivery')</span>
									<strong>{{ number_format($orderData['delivery_price'], 2) }} €</strong>
								</div>

								<div class="cart-page-sidebar__delivery manufacturing">
									<span>@lang('cart_new.general_production')</span>
									<strong>{{ $productionLabel }} {{ number_format($orderData['terms_price'], 2) }} €</strong>
								</div>

								<div class="cart-data-page__bottom cabinet-payment-page__back-desktop">
									<a href="{{ route('new_account.orders') }}" rel="nofollow" class="cart-data-page__btn-back">
										@lang('account_new.back_to_orders')
									</a>
								</div>
							</div>
						</div>
					</div>

					<div class="cabinet-content__icon">
						<img src="{{ asset(config('theme.current') . '/images')}}/cabinet/credit-card.svg" width="82" height="82" alt="Viar Cabinet Order Payment">
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@include(config('theme.resource') . 'pages.gallery.zpart_viarcanvas_is')
