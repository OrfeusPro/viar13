@if (Auth::user()->role->name != 'painter')
<div class="cabinet-orderState">
	<div class="cabinet-ordersBlock">
		<div class="cabinet-ordersBlock_top">
			<p class="text-base">
				@lang("account_new.orders.total_amount_of_purchases")
				<span class="darkSpan">{{ $total_amount_of_purchases }} €

				</span>
			</p>
			{{-- <p class="text-base">
				Текущая скидка: <span class="activeSpan">-5%</span>
			</p> --}}
		</div>
	</div>
</div>
@endif

@foreach($orders as $key=>$order)
@php
	$order->delivery = json_decode($order->delivery, 1);
	$order->items = json_decode($order->items, 1);

	$painter_picture_images = $order->order_painter_images()->where('is_img_painter', 1)->get();
	$painter_sketch_images = $order->order_painter_images()->where('is_img_sketch', 1)->get();

	$statusClass = 'acting';
	$class = '';
	$statusName = "";
	$paymentStatusMap = [
		'not_payed' => __('account_new.payment_status_unpaid'),
		'prepayment' => __('account_new.payment_status_prepayment'),
		'payed' => __('account_new.payment_status_paid'),
	];
	$paymentStatus = $order->payment_status ?? 'not_payed';
	$paymentStatusLabel = $paymentStatusMap[$paymentStatus] ?? __('account_new.payment_status_unpaid');
	$canPayOnline = Auth::user()->role->name != 'painter'
		&& $paymentStatus === 'not_payed'
		&& $order->status !== 'completed';
	$trackingNumbers = collect(explode(',', (string) ($order->labels ?? '')))
		->map(function ($label) {
			return trim($label);
		})
		->filter()
		->values();

	if( $order->status == 'completed')
	{
		$statusName = __('account.index13');
		$statusClass = 'completed';
		$class = 'success-state';
	}
	if( $order->status == 'watching')
	{
		$statusName =  trans('gl.status_watching');
		$statusClass = 'acting';
		$class = '';
	}
	if( $order->status == 'sended')
	{
		$statusName = __('account_new.orders.sended');
		$statusClass = 'acting';
		$class = 'success-state';
	}

	if( $order->status == 'send_lubanas')
	{
		$statusName = __('account_new.orders.sended');
		$statusClass = 'acting';
		$class = 'success-state';
	}

	if( $order->status == 'in_production')
	{
		$statusName = __('account_new.orders.in_production');
		// $statusName = trans('account_new.orders.user_status.print');
		$statusClass = 'acting';
		$class = '';
	}

	if( $order->status == 'pegging')
	{
		$statusName = trans('gl.status_pending');
		$statusClass = 'acting';
		$class = '';
	}

	$i=0;
@endphp

<div class="{{ $statusClass }} @if($key==count($orders)-1) last @endif" style="margin-top:20px;">
    <div class="{{ $statusClass }}-content">

		<div>
		</div>

</div>
</div>


{{-- <input type="hidden" name="_token" value="@csrf"> --}}

<div class="cabinet-orderState {{ $class }}">
	<div class="cabinet-ordersBlock">

		<div class="cabinet-ordersBlock_body scroll-box">
			<div class="cabinet-scroll__nav">
				<div class="scroll-up">
					<svg width="19" height="13" viewBox="0 0 19 13" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path
							d="M17.8577 12.7766C17.7308 12.9255 17.5848 13 17.4198 13C17.2548 13 17.1089 12.9255 16.982 12.7766L9.5 3.99828L2.01804 12.7766C1.89112 12.9255 1.74516 13 1.58016 13C1.41516 13 1.2692 12.9255 1.14228 12.7766L0.190381 11.6598C0.0634605 11.5109 -7.7486e-07 11.3396 -7.7486e-07 11.146C-7.7486e-07 10.9525 0.0634605 10.7812 0.190381 10.6323L9.06212 0.223368C9.18904 0.0744559 9.335 0 9.5 0C9.665 0 9.81096 0.0744559 9.93788 0.223368L18.8096 10.6323C18.9365 10.7812 19 10.9525 19 11.146C19 11.3396 18.9365 11.5109 18.8096 11.6598L17.8577 12.7766Z"
							fill="#FA7846" />
					</svg>
				</div>
				<div class="scroll-down">
					<svg width="19" height="13" viewBox="0 0 19 13" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path
							d="M1.14229 0.223367C1.26921 0.0744551 1.41516 -1.13249e-06 1.58016 -1.13249e-06C1.74516 -1.13249e-06 1.89112 0.0744551 2.01804 0.223367L9.5 9.00172L16.982 0.223367C17.1089 0.0744551 17.2548 -1.13249e-06 17.4198 -1.13249e-06C17.5848 -1.13249e-06 17.7308 0.0744551 17.8577 0.223367L18.8096 1.34021C18.9365 1.48912 19 1.66037 19 1.85395C19 2.04754 18.9365 2.21879 18.8096 2.3677L9.93788 12.7766C9.81096 12.9255 9.665 13 9.5 13C9.335 13 9.18904 12.9255 9.06212 12.7766L0.19038 2.3677C0.0634597 2.21879 0 2.04754 0 1.85395C0 1.66037 0.0634597 1.48912 0.19038 1.34021L1.14229 0.223367Z"
							fill="#FA7846" />
					</svg>
				</div>
			</div>
			<div class="cabinet-scroll__container scroll-block">


				<div class="cabinet-orderItem">
					<div class="cabinet-orderItem__top grid-2">
						<div class="simple-block">
							<p class="text-base">
								@lang('account.index15')
								<span class="darkSpan"> # {{ $order->id }}
									@if(Auth::user()->role->name != 'painter') - {{ date( 'd.m.Y', strtotime($order->created_at)) }} @endif
								</span>
							</p>
							@if(Auth::user()->role->name != 'painter')
								<p class="text-base">
									@lang('account.index17') <span class="darkSpan">@lang('account.index18')</span>
								</p>
							@endif


							@if($order->painter_end_time && Auth::user()->role->name == 'painter')

							@php
								$currentDate = now(); // Текущая дата и время
								$painterEndTime = strtotime($order->painter_end_time); // Преобразование строки во временную метку

								// Рассчитываем разницу между текущей датой и временем $order->painter_end_time в секундах
								$timeDifference = $painterEndTime - $currentDate->timestamp;

								if ($timeDifference < 24 * 3600) { // 24 * 3600 секунд в одном дне
									$red = "style='color: red; font-weight: 600;'";
								} else {
									$red = '';
								}
							@endphp


							<p class="text-base">
								@lang('account_new.orders.execute_to'): <span class="activeSpan" {!! $red !!}>
									{{date( 'd.m.Y', strtotime($order->painter_end_time))}}
								</span>
							</p>
							@endif

						</div>
						<div class="simple-block">
							<div class="s-row">

								@if(Auth::user()->role->name != 'painter')
									@if($order->has_pdf == 1)
									<a target="_blank" class="download-account" href="/storage/pdf/{{ $order->id }}.pdf?ver={{ preg_replace('/\D/', '', $order->updated_at) }}">@lang('account.index19')</a>
									@else
									@endif
								@endif

								{{-- <a href="#" class="download-account">Скачать счёт</a> --}}
								<div class="cabinet-orderItem__status pendingStatus">
									<img src="{{ asset(config('theme.current') . '/images') }}/cabinet/work-process.svg" width="25"
										height="25" alt="">
									<span>{{ $statusName }}</span>
								</div>
							</div>
						</div>
					</div>
					<div class="cabinet-orderItem__inner grid-2 @if(Auth::user()->role->name == 'painter') orange_border_b @endif">

						@foreach($order->items as $product)
						@php
						if(!isset($product['sumPrice']))
						{
							continue;
						}
						$i++;
						@endphp

							<div class="co-block co-row">
								<div class="img">
									@include(env('THEME_RESOURCES') . 'account.basket_img')
								</div>
								<div class="simple-block">
									@include(env('THEME_RESOURCES') . 'account.all_basket_order_items')
									@if(Auth::user()->role->name != 'painter')
										<p class="text-base">
											@lang("cart_new.general_price") <span class="darkSpan">
												@isset($product['formatedPrice']){{ $product['formatedPrice'] }} @endisset
											</span>
										</p>
									@endif

									@if(isset($product['userComment']) && $i !== 1)
										@if ($product['userComment'])
										{{-- <br> --}}
											<p class="text-base">
												{{ trans('gl.comments') }}: <br>
												<span class="darkSpan">{{ $product['userComment'] }}</span>
											</p>
										@endif
									@endif
								</div>
							</div>

							@if($i == 1)
							<div class="co-block co-single">
								<div class="simple-block">
									@if(Auth::user()->role->name != 'painter')
										{{-- <p class="text-base">
											Доставка в Пик-Ап пункт: <span class="darkSpan">-
												5€</span>
										</p>
										<p class="text-base">
											<span class="darkSpan">Paku Skapis RIMI Tilta
												Tilta iela 32, 1005, Rīga, LV</span>

										</p> --}}
											{{-- <p>Телефон:{{ $order->delivery['phone'] }}</p> --}}
											{{-- <p>Страна: {{ $order->delivery['country'] }}</p> --}}


										@if (isset($order->delivery['sposob']))
										<p class="text-base">
											@lang("cart_new.delivery")
											@if ($order->delivery['sposob'] == 'to_the_door')
												<span class="darkSpan">@lang("mail.delivery_sposob_to_the_door")</span>
											@elseif($order->delivery['sposob'] == 'pickup_Riga')
												<span class="darkSpan">@lang("mail.delivery_sposob_pickup_riga")</span>
											@elseif($order->delivery['sposob'] == 'pickup_Daugavplis')
												<span class="darkSpan">@lang("mail.delivery_sposob_pickup_daugavplis")</span>
											@elseif($order->delivery['sposob'] == 'venipak')
												<span class="darkSpan">@lang("cart_new.step_3_delivery_to_pick-up_point")</span>
											@elseif($order->delivery['sposob'] == 'pickup_at_viar_workshop')
												<span class="darkSpan">@lang("cart_new.step_3_pick_up_at_viar_workshop")</span>
                                            @elseif($order->delivery['sposob'] == 'city_delivery')
                                            <p>@lang("cart_new.delivery_by") {{$order->delivery['city']}}</p>
											@endif
										</p>
										@endif

										@if(isset($order->delivery['address']) && $order->delivery['address'])
										<p class="text-base">
											@lang("account.index16") <span class="darkSpan">
												{{ $order->delivery['address'] }}
											</span>
										</p>
										@endif
										@if($trackingNumbers->isNotEmpty())
										<p class="text-base">
											@lang('account_new.tracking_number')
											<span class="darkSpan">{{ $trackingNumbers->implode(', ') }}</span>
										</p>
										@endif
										@php
											$recipientPhone = $order->delivery['phone'] ?? null;
											$payerPhone = $order->delivery['payer_phone'] ?? null;
											if (!$payerPhone && isset($order->user) && $order->user->phone) {
												$payerPhone = $order->user->phone;
											}
											$hasRecipientPhone = array_key_exists('payer_phone', $order->delivery ?? [])
												|| !empty($order->delivery['is_no_payer']);
											$showRecipientPhone = $recipientPhone
												&& $hasRecipientPhone
												&& ($recipientPhone !== $payerPhone || !empty($order->delivery['is_no_payer']));
										@endphp
										@if($showRecipientPhone)
										<p class="text-base">
											@lang("cart_new.step_2_recipient_phone") <span class="darkSpan">
												{{ $recipientPhone }}
											</span>
										</p>
										@endif

										<p class="text-base">
											@lang("cart_new.payment")
											@if ($order->payment == 'cash_in_office')
												<span class="darkSpan">@lang("cart_new.step_4_cash_upon_receipt_at_the_workshop")</span>
											@elseif($order->payment == 'on_delivery')
												<span class="darkSpan">@lang("cart_new.step_4_payment_to_the_courier")</span>
											@elseif($order->payment == 'transfer')
												<span class="darkSpan">@lang("cart_new.step_4_payment_by_bank_transfer")</span>
                                            @elseif($order->payment == 'prepayment')
                                                <span class="darkSpan">@lang("cart_new.prepayment")</span>
											@elseif($order->payment == 'online_paysera')
												<span class="darkSpan">@lang("cart_new.step_4_payment_by_card")</span>
											@elseif($order->payment == 'paypalOnetimePayment')
												<span class="darkSpan">PayPal</span>
											@elseif($order->payment == 'google_pay')
												<span class="darkSpan">Google Pay</span>
											@elseif($order->payment == 'apple_pay')
												<span class="darkSpan">Apple Pay</span>
											@elseif($order->payment == 'creditcart')
												<span class="darkSpan">@lang("cart_new.step_4_by_card_online")</span>
											@endif
										</p>

											{{-- <p>Индекс: {{ $order->delivery['postal_index'] }}</p> --}}

											{{-- @if (isset($order->delivery['when_send']))
												<p>Дата доставки: {{ $order->delivery['when_send'] }}</p>
											@endif --}}



												{{-- {{ dd($order) }} --}}
												@php
													$toFloat = static function ($value): float {
														$value = preg_replace('/[^0-9,\\.\\-]/', '', (string) $value);
														$value = str_replace(',', '.', (string) $value);
														return (float) $value;
													};

													$originalSubtotal = $toFloat($order['price'] ?? 0);
													$saleSubtotalRaw = $order['sale_price'] ?? null;
													$saleSubtotal = ($saleSubtotalRaw !== null && $saleSubtotalRaw !== '') ? $toFloat($saleSubtotalRaw) : null;

													$baseSubtotal = $originalSubtotal;
													if ($saleSubtotal !== null && $saleSubtotal > 0 && $saleSubtotal < $originalSubtotal) {
														$baseSubtotal = $saleSubtotal;
													}

													$discountedSubtotal = $baseSubtotal;

													$saleEur = $toFloat($order['sale_eur'] ?? 0);
													if ($saleEur > 0) {
														$discountedSubtotal -= $saleEur;
													}

													$salePercent = $toFloat($order['sale_percent'] ?? 0);
													if ($salePercent > 0) {
														$discountedSubtotal -= $discountedSubtotal * ($salePercent / 100);
													}

													if ($discountedSubtotal < 0) {
														$discountedSubtotal = 0;
													}

													$discountedSubtotal = round($discountedSubtotal, 2);

													$deliveryPrice = isset($order['delivery']['deliv_price']) ? $toFloat($order['delivery']['deliv_price']) : 0.0;
													$termsPrice = isset($order['items']['total_terms_price']) ? $toFloat($order['items']['total_terms_price']) : 0.0;
													$total_all_summ = round($discountedSubtotal + $deliveryPrice + $termsPrice, 2);
												@endphp


												<p class="text-base">
													@lang("cart_new.general_price")
													@if (abs($originalSubtotal - $discountedSubtotal) > 0.009)
														<span style="text-decoration: line-through;">{{ number_format($originalSubtotal, 2) }}</span>
														<span class="darkSpan">{{ number_format($discountedSubtotal, 2) }} €</span>
													@else
														<span class="darkSpan">{{ number_format($originalSubtotal, 2) }} €</span>
													@endif
												</p>


											@if (isset($order->items['total_terms_price']) && $order->items['total_terms_price'] > 0)
												<p class="text-base">
												@lang('gl.express'): <span class="darkSpan">{{ number_format($order->items['total_terms_price'], 2) }} €</span>
											@endif

											@if (isset($order->delivery['deliv_price']) && $order->delivery['deliv_price'] != "")
												<p class="text-base">
													@lang("cart_new.general_delivery") <span class="darkSpan">{{ number_format($order->delivery['deliv_price']) }} €</span>
												</p>
											@endisset

											<p class="text-base">
												@lang('account_new.payment_status')
												<span class="darkSpan">{{ $paymentStatusLabel }}</span>
											</p>

											@if($canPayOnline)
												<p class="text-base">
													<a class="download-account" href="{{ route('new_account.order_payment', ['order' => $order->id]) }}">
														@lang('account_new.pay_now')
													</a>
												</p>
											@endif


												<p class="text-base">
													@lang("cart_new.general_total_amount") <span class="darkSpan">
															@isset($total_all_summ){{ number_format($total_all_summ,2) }} € @endisset
													</span>
												</p>

										{{-- <hr> --}}
										{{-- @if (isset($order->delivery['is_coupon_30_40']))
											<p style="color:orange;font-weight: bold;">Использован купон:30x40</p>
										@endif

										@if (isset($order->delivery['is_coupon_40_60']))
											<p style="color:orange;font-weight: bold;">Использован купон:40x60</p>
										@endif

										@if (isset($order->delivery['is_coupon_dates']))
											<p style="color:orange;font-weight: bold;">Использован купон:2 даты</p>
										@endif

										@if (isset($order->delivery['has_invited_sale']))
											<p style="color:orange;font-weight: bold;">Использована скидка по приглашению</p>
										@endif

										@if (isset($order->delivery['is_1free']))
											<p style="color:orange;font-weight: bold;">Использована акция При заказе 3 картин - 1 в подарок </p>
										@endif --}}

									@endif

									@if(isset($product['userComment']))
										@if ($product['userComment'])
										{{-- <br> --}}
											<p class="text-base">
												{{ trans('gl.comments') }}: <br>
												<span class="darkSpan">{{ $product['userComment'] }}</span>
											</p>
										@endif
									@endif

									@if(isset($order->delivery['comment']) && $i == 1)
										@if ($order->delivery['comment'] && $order->delivery['comment'] != "null")
										{{-- <br> --}}
											<p class="text-base">
												{{ trans('gl.comments') }}: <br>
												<span class="darkSpan">{{ $order->delivery['comment'] }}</span>
											</p>
										@endif
									@endif

									@if($order->comment)
										<p class="text-base">
											{{ trans('gl.comments') }}: <br>
											<span class="darkSpan">{{ $order->comment }}</span>
										</p>
									@endif

									@if($order->admin_comment)
										<p class="text-base">
											{{ trans('gl.comments') }}: <br>
											<span class="darkSpan">{{ $order->admin_comment }}</span>
										</p>
									@endif

								</div>
							</div>
							@else
							<div class="co-block co-single">
								<div class="simple-block">
								</div>
							</div>
							@endif
						@endforeach
						<br>
					</div>


					@if(Auth::user()->role->name != 'painter')

						@if( $order->status == 'completed')

						@endif

						@if( $order->status == 'watching')

							@php
							$painter = App\Models\User::getPainterByOrderId($order->id);
							@endphp

							@if ($painter != '')
								<div class="cabinet-orderItem__info">
									<p>@lang("account_new.orders.user_status.watching")</p>
									<img src="{{ asset(config('theme.current') . '/images') }}/cabinet/icon-danger.svg" width="30" height="30" alt="">
								</div>
							@endif
						@endif

						@if( $order->status == 'sended')
							{{-- {{ __('account.index14') }} --}}
						@endif

						@if( $order->status == 'in_production')
							@php
								$painter = App\Models\User::getPainterByOrderId($order->id);
							@endphp

							@if ($painter != '')

								<div class="cabinet-orderItem__info">
									<p>@lang("account_new.orders.user_status.print_text")</p>
									<img src="{{ asset(config('theme.current') . '/images') }}/cabinet/icon-danger.svg" width="30" height="30" alt="">
								</div>
							@endif
						@endif



						@if( $order->status == 'pegging')
							@php
								$painter = App\Models\User::getPainterByOrderId($order->id);
							@endphp

								@if ($painter != '')

									@if(!$painter_picture_images->last() && !$painter_sketch_images->last())
										<div class="cabinet-orderItem__info">
											<p>@lang("account_new.orders.user_status.pegging")</p>
											<img src="{{ asset(config('theme.current') . '/images') }}/cabinet/icon-danger.svg" width="30" height="30" alt="">
										</div>
									@elseif($painter_picture_images->last()  && $painter_sketch_images->last())
										<div class="cabinet-orderItem__info">
											<p>@lang("account_new.orders.user_status.picture")</p>
											<img src="{{ asset(config('theme.current') . '/images') }}/cabinet/icon-danger.svg" width="30" height="30" alt="">
										</div>
									@elseif(!$painter_picture_images->last() && $painter_sketch_images->last())
										<div class="cabinet-orderItem__info">
											<p>@lang("account_new.orders.user_status.sketch")</p>
											<img src="{{ asset(config('theme.current') . '/images') }}/cabinet/icon-danger.svg" width="30" height="30" alt="">
										</div>
									@else

								@endif

							@endif
						@endif

					@endif

					@php
					$user_comments = \App\Models\User::get_user_comments_total($order->id);
					@endphp

					@if(!$user_comments->isEmpty() || $order->client_comment)
						{{-- @if(Auth::user()->role->name != 'user')
						<div class="ct-row">
							<p>
								@lang("account_new.orders.client_comments")
							</p>
						</div>
						<div class="co-block">
							<div class="simple-block" style="margin-top: -25px;background: #ffffff8a;border: 0.5px solid #d1d1d1;border-radius: 15px;padding: 15px 25px;">
								@if($order->client_comment)<p class="text-base" style="font-size: 14px; max-width: 100%;">{{ $order->client_comment }}</p>@endif
								@foreach($user_comments as $u_comment)
									<p class="text-base" style="font-size: 14px; max-width: 100%;">{{ date( 'd.m.Y', strtotime($u_comment->created_at)) }} <span class="darkSpan">{{ $u_comment->comment }}</span></p>
								@endforeach
							</div>
						</div>
						@endif --}}
					@endif

					{{-- чат Художника с Админом --}}
					@include(env('THEME_RESOURCES') . 'account.o_painter_chat')

					{{-- чат Пользователя с Админом --}}
					@include(env('THEME_RESOURCES') . 'account.o_client_chat')



					@if(Auth::user()->role->name == 'user')
						{{-- чат картины --}}
						@php
							$chat = \App\Models\User::get_user_acc_comments($order->id,"is_img_painter");
						@endphp

						@include(env('THEME_RESOURCES') . 'account.o_chat_img', [
							"imgs"=> $painter_picture_images,
							"css_js" => "js_hb_painter_$order->id",
							"css_js_send_image_form" => "js_painter_form_images_upd",
							"css_js_send_image_id" => "painter_imgs_order_$order->id",
							"css_js_send_image_name" => "painter_images",
							"painter_load_image_title" => __("account_new.orders.add_your_picture"),
							"chat_img_type" => "is_img_painter",
							"chat_route_admin_painter" => route('new_update_order_chat'),
							"chat_route_admin_user" => route('new_send_client_painter_comments'),
							"route_change_images_status" => route('changeOrderPainterImageStatus'),
							"block_title" => __("account_new.orders.picture_title"),
							"block_sub_title" => __("account_new.picture_sub_title"),
							"chat" => $chat,
							])

						{{-- чат наброски --}}
						@php
							$chat = \App\Models\User::get_user_acc_comments($order->id,"is_img_sketch");
						@endphp

						@include(env('THEME_RESOURCES') . 'account.o_chat_img', [
							"imgs"=> $painter_sketch_images,
							"css_js" => "js_hb_sketch_painter_$order->id",
							"css_js_send_image_form" => "js_painter_form_images_upd_sketch",
							"css_js_send_image_id" => "painter_sketch_imgs_order_$order->id",
							"css_js_send_image_name" => "painter_sketch_images",
							"painter_load_image_title" => __("account_new.orders.add_your_sketch"),
							"chat_img_type" => "is_img_sketch",
							"chat_route_admin_painter" => route('new_update_order_chat'),
							"chat_route_admin_user" => route('new_send_client_painter_comments'),
							"route_change_images_status" => route('changeOrderPainterImageStatus'),
							"block_title" => __("account_new.orders.sketch_title"),
							"block_sub_title" => __("account_new.sketch_sub_title"),
							"chat" => $chat,
							])
					@else
						{{-- чат наброски --}}
						@php
							$painter_sketch_images = $order->order_painter_images()->where('is_img_sketch', 1)->get();
							$chat = \App\Models\User::get_user_acc_comments($order->id,"is_img_sketch");
						@endphp

						@include(env('THEME_RESOURCES') . 'account.o_chat_img', [
							"imgs"=> $painter_sketch_images,
							"css_js" => "js_hb_sketch_painter_$order->id",
							"css_js_send_image_form" => "js_painter_form_images_upd_sketch",
							"css_js_send_image_id" => "painter_sketch_imgs_order_$order->id",
							"css_js_send_image_name" => "painter_sketch_images",
							"painter_load_image_title" => __("account_new.orders.add_your_sketch"),
							"chat_img_type" => "is_img_sketch",
							"chat_route_admin_painter" => route('new_send_admin_to_client_painter_comments'),
							"chat_route_admin_user" => route('new_send_client_painter_comments'),
							"route_change_images_status" => route('changeOrderPainterImageStatus'),
							"block_title" => __("account_new.orders.sketch_title"),
							"block_sub_title" => __("account_new.sketch_sub_title"),
							"chat" => $chat,
							])

						{{-- чат картины --}}
						@php
							$painter_picture_images = $order->order_painter_images()->where('is_img_painter', 1)->get();
							$chat = \App\Models\User::get_user_acc_comments($order->id,"is_img_painter");
						@endphp

						@include(env('THEME_RESOURCES') . 'account.o_chat_img', [
							"imgs"=> $painter_picture_images,
							"css_js" => "js_hb_painter_$order->id",
							"css_js_send_image_form" => "js_painter_form_images_upd",
							"css_js_send_image_id" => "painter_imgs_order_$order->id",
							"css_js_send_image_name" => "painter_images",
							"painter_load_image_title" => __("account_new.orders.add_your_picture"),
							"chat_img_type" => "is_img_painter",
							"chat_route_admin_painter" => route('new_send_admin_to_client_painter_comments'),
							"chat_route_admin_user" => route('new_send_client_painter_comments'),
							"route_change_images_status" => route('changeOrderPainterImageStatus'),
							"block_title" => __("account_new.orders.picture_title"),
							"block_sub_title" => __("account_new.picture_sub_title"),
							"chat" => $chat,
							])
					@endif

				</div>
			</div>
		</div>
	</div>
</div>

@endforeach

@if (!is_array($orders))
	<div class="text-center">
		{{ $orders->links('theme.viar.blog.paginate') }}
	</div>
@endif


@section('script')
<script>
	$(document).on('submit', '.js_send_user_add_images', function (e) {
		e.preventDefault();
		var th = $(this);

		// $('.js_spinner').jmspinner('large');
		th.find('.painter_btn').attr('disabled', true);

		let formData = new FormData();
		let order_id = th.find('[name="order_id"]').val();
		let user_comment = th.find('[name="user_comment"]').val();

		if (user_comment == null || user_comment == 'null') {
			user_comment = '';
		}

		formData.append('order_id', order_id);
		formData.append('client_comment', user_comment);

		var totalfiles = document.getElementById('js_painter_images_' + order_id).files.length;

		for (var index = 0; index < totalfiles; index++) {
			formData.append("client_images[]", document.getElementById('js_painter_images_' +
				order_id).files[index]);
		}

		$.ajax({
			type: 'post',
			dataType: 'json',
			processData: false,
			contentType: false,
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: '{{ route('new_send_client_painter_comments') }}',
			data: formData,
			success: function (response) {
				// $('.js_spinner').removeClass('spinner');
				th.find('.painter_btn').removeAttr('disabled');
				th.find('.js_u_comm_textarea').val('');
				if (response.status == 1 || response.status == true) {
					var d = new Date();
					var curr_date = d.getDate();
					var curr_month = d.getMonth() + 1;
					var curr_year = d.getFullYear();
					var curr_hours = d.getHours();
					var curr_minutes = d.getMinutes();
					var curr_seconds = d.getSeconds();
					curr_month = curr_month += '';
					if (curr_month.length == 1) {
						curr_month = '0' + curr_month;
					}

					// var date = curr_date + '.' + curr_month + '.' + curr_year;
					var date = curr_year + '-' + curr_month + '-' + curr_date + ' ' + curr_hours + ':' + curr_minutes + ':' + curr_seconds;

					if (response.comment) {
						th.find('.js_order_user_comments').append(`
						<div class="chat-group">
							<div class="chat-item">
								<div class="chat-item__row">
									<div class="chat-name">
										{{-- @if(Auth::user()->id==$u_comment->user_id) --}}
											@lang("account_new.orders.chat.you")
										{{-- @else --}}
											{{-- @lang("account_new.orders.chat.admin") --}}
										{{-- @endif --}}
									</div>
									<div class="chat-date">
										${date}
									</div>
								</div>
								<div class="chat-item__body">
									<p>
										${response.comment}
									</p>
								</div>
							</div>
						</div>
						`);
					}

					// var containerHeight = th.find('.chat-scroll__container .scroll-block').height();
					th.find('.chat-scroll__container .scroll-block').scrollTop(9999);

					// var containerHeight = th.find('.user_comment').height();


					if (response.images) {
						// th.next('.js_user_all_images').find(
						// 	'.user__painter__imgs__list').children().remove();
						// var cur_images = response.images.split(",");

						// for (let index = 0; index < cur_images.length; index++) {
						// 	const el = cur_images[index];
						// 	th.next('.js_user_all_images').find(
						// 		'.user__painter__imgs__list').append(`
						// 		<li>
						// 			<a href="${el}" target="_blank">
						// 				<img src="${el}" alt="" class="img__user_upl">
						// 			</a>
						// 		</li>
						// 	`);

						// }

						if (!response.comment) {
							window.location.reload();
						}
					}
				} else {
					alert('Error');
				}

				th.trigger('reset');
				document.getElementById("js_painter_images_" + order_id).value = "";
				// $('.js_spinner').jmspinner(false);
			},
			error: function (error) {
				// $('.js_spinner').removeClass('spinner');
				th.find('.painter_btn').removeAttr('disabled');
				th.trigger('reset');
				document.getElementById("js_painter_images_" + order_id).value = "";
				// $('.js_spinner').jmspinner(false);
				console.log(error);
			}
		});

	});


	$(document).on('submit', '.js_painter_form_images_upd', function (e) {
		e.preventDefault();

		var th = $(this);
		// th.find('.js_spinner').jmspinner('large');
		th.find('button').attr('disabled', true);

		let formData = new FormData();
		let order_id = th.find('[name="order_id"]').val();

		formData.append('order_id', order_id);

		var totalfiles = document.getElementById('painter_imgs_order_' + order_id).files.length;

		for (var index = 0; index < totalfiles; index++) {
			formData.append("painter_images[]", document.getElementById('painter_imgs_order_' +
				order_id).files[index]);
		}

		$.ajax({
			type: 'post',
			dataType: 'json',
			processData: false,
			contentType: false,
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: '{{ route('new_update_painter_order_images') }}',
			data: formData,
			success: function (response) {
				th.find('button').removeAttr('disabled');
				// th.find('.js_spinner').removeClass('spinner');
				if (response.status === 1) {
					if (response.images) {
						// $('.js_hb_painter_' + response.order_id).find('.painter_images_group').children().remove();
						// var cur_images = response.images.split(",");

						// for (let index = 0; index < cur_images.length; index++) {
						// 	const el = cur_images[index];
						// 	if(el){
						// 	$('.js_hb_painter_' + response.order_id).find('.painter_images_group')
						// 		.append(`
						// 			<div class="painter_img">
						// 				<a target="_blank"
						// 				href="${el}">
						// 					<img style="max-width:100%;max-height:100px;"
						// 					src="${el}" alt="">
						// 				</a>
						// 			</div>
						// 				`);
						// 	}
						// }
					}
					window.location.reload();
					// alert('Success');
				} else {
					alert('Error');
				}
				// th.find('.js_spinner').jmspinner(false);
			},
			error: function (error) {
				// th.find('.js_spinner').removeClass('spinner');
				th.find('button').removeAttr('disabled');
				var message = error.responseJSON && error.responseJSON.message
					? error.responseJSON.message
					: 'Error';
				alert(message);
			}
		});

	});


	$(document).on('submit', '.js_painter_form_images_upd_sketch', function (e) {
		e.preventDefault();

		var th = $(this);
		// th.find('.js_spinner').jmspinner('large');
		th.find('button').attr('disabled', true);

		let formData = new FormData();
		let order_id = th.find('[name="order_id"]').val();

		formData.append('order_id', order_id);

		var totalfiles = document.getElementById('painter_sketch_imgs_order_' + order_id).files.length;

		for (var index = 0; index < totalfiles; index++) {
			formData.append("painter_sketch_images[]", document.getElementById('painter_sketch_imgs_order_' + order_id).files[index]);
		}

		$.ajax({
			type: 'post',
			dataType: 'json',
			processData: false,
			contentType: false,
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: '{{ route('new_update_painter_sketch_order_images') }}',
			data: formData,
			success: function (response) {
				th.find('button').removeAttr('disabled');
				// th.find('.js_spinner').removeClass('spinner');
				if (response.status === 1) {
					if (response.images) {
						// $('.js_hb_sketch_painter_' + response.order_id).find('.painter_images_group').children().remove();
						// var cur_images = response.images.split(",");

						// for (let index = 0; index < cur_images.length; index++) {
						// 	const el = cur_images[index];
						// 	if(el)
						// 	{
						// 	$('.js_hb_sketch_painter_' + response.order_id).find('.painter_images_group')
						// 		.append(`
						// 			<div class="painter_img" style="text-align: center;">
						// 				<a target="_blank"
						// 				href="${el}">
						// 					<img style="max-width:100%;max-height:100px;"
						// 					src="${el}" alt="">
						// 				</a>
						// 			</div>
						// 				`);
						// 	}
						// }
					}
					window.location.reload();
					// alert('Success');
				} else {
					alert('Error');
				}
				// th.find('.js_spinner').jmspinner(false);
			},
			error: function (error) {
				// th.find('.js_spinner').removeClass('spinner');
				th.find('button').removeAttr('disabled');
				var message = error.responseJSON && error.responseJSON.message
					? error.responseJSON.message
					: 'Error';
				alert(message);
			}
		});

	});

	$(document).on('submit', '.send_message', function (e) {
		e.preventDefault();
		e.stopImmediatePropagation();

		var th = $(this);

		var order_id = $(this).data('id');
		var route = $(this).data('route');
		var user_comment = $(this).find("[name='user_comment']").val();
		var chat_img_type = $(this).find("[name='chat_img_type']").val();
		var order_painter_image_id = $(this).find("[name='order_painter_image_id']").val();

		var dataToSend = {
			order_id: order_id,
			msg: user_comment,
			order_painter_image_id: order_painter_image_id
		};

		dataToSend[chat_img_type] = 1;

		$.ajax({
			method: 'POST',
			url: route,
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			data: dataToSend,
			dataType: 'json',
			success: function (msg) {
				// $('.js_spinner').removeClass('spinner');
				th.find('.painter_btn').removeAttr('disabled');
				th.find('.js_u_comm_textarea').val('');
				if (msg.status == 1 || msg.status == true) {
					var d = new Date();
					var curr_date = d.getDate();
					var curr_month = d.getMonth() + 1;
					var curr_year = d.getFullYear();
					var curr_hours = d.getHours();
					var curr_minutes = d.getMinutes();
					var curr_seconds = d.getSeconds();
					curr_month = curr_month += '';
					if (curr_month.length == 1) {
						curr_month = '0' + curr_month;
					}

					$('#'+order_painter_image_id).removeClass('btn-red');
					$('#send_'+order_painter_image_id).removeClass('btn-red');

					// var date = curr_date + '.' + curr_month + '.' + curr_year;
					var date = curr_year + '-' + curr_month + '-' + curr_date + ' ' + curr_hours + ':' + curr_minutes + ':' + curr_seconds;

					th.find('.js_order_user_comments').append(`
					<div class="chat-group">
						<div class="chat-item">
							<div class="chat-item__row">
								<div class="chat-name">
									@lang("account_new.orders.chat.you")
								</div>
								<div class="chat-date">
									${date}
								</div>
							</div>
							<div class="chat-item__body">
								<p>
									${msg.comment}
								</p>
							</div>
						</div>
					</div>

					`);

					// var containerHeight = th.find('.chat-scroll__container .scroll-block').height();
					th.find('.chat-scroll__container .scroll-block').scrollTop(9999);
					th.find("[name='user_comment']").val("");
				}


				// $('.js_spinner').jmspinner(false);
			},
			error: function (jqXHR, exception) {
				// $('.js_spinner').jmspinner(false);

			}
		})

		return false
	});



	$(document).on('submit', '.js_painter_admin_chat', function (e) {
		e.preventDefault();
		e.stopImmediatePropagation();
		//$('.js_spinner').jmspinner('large');

		var th = $(this);

		var order_id = $(this).data('id');
		var user_comment = $(this).find("[name='user_comment']").val();

		$.ajax({
			method: 'POST',
			url: "{{ route('new_update_order_chat') }}",
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			data: {
				order_id: order_id,
				painter_msg: user_comment,
			},
			dataType: 'json',
			success: function (msg) {
				// $('.js_spinner').removeClass('spinner');
				th.find('.painter_btn').removeAttr('disabled');
				th.find('.js_u_comm_textarea').val('');
				if (msg.status == 1 || msg.status == true) {
					var d = new Date();
					var curr_date = d.getDate();
					var curr_month = d.getMonth() + 1;
					var curr_year = d.getFullYear();
					var curr_hours = d.getHours();
					var curr_minutes = d.getMinutes();
					var curr_seconds = d.getSeconds();
					curr_month = curr_month += '';
					if (curr_month.length == 1) {
						curr_month = '0' + curr_month;
					}

					// var date = curr_date + '.' + curr_month + '.' + curr_year;
					var date = curr_year + '-' + curr_month + '-' + curr_date + ' ' + curr_hours + ':' + curr_minutes + ':' + curr_seconds;

					th.find('.js_order_user_comments').append(`
					<div class="chat-group">
						<div class="chat-item">
							<div class="chat-item__row">
								<div class="chat-name">
									@lang("account_new.orders.chat.you")
								</div>
								<div class="chat-date">
									${date}
								</div>
							</div>
							<div class="chat-item__body">
								<p>
									${msg.comment}
								</p>
							</div>
						</div>
					</div>

					`);

					// var containerHeight = th.find('.chat-scroll__container .scroll-block').height();
					th.find('.chat-scroll__container .scroll-block').scrollTop(9999);
					th.find("[name='user_comment']").val("");
				}


				// $('.js_spinner').jmspinner(false);
			},
			error: function (jqXHR, exception) {
				// $('.js_spinner').jmspinner(false);

			}
		})

		return false
	});

	function changeImageStatus(val, id, image_id, routeName, check_comment = null) {
		$.ajax({
			type: 'post',
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: routeName,
			data: {
				status_name: val,
				order_id: id,
				order_painter_image_id: image_id,
				check_comment: check_comment
			},
			success: function(response) {
				if (response) {
					var data = jQuery.parseJSON(response);
					if (data['info'] != 1) {
						if (data['check_comment'] === false && data['info'] === false) {
							$('#'+image_id).addClass('btn-red');
							$('#send_'+image_id).addClass('btn-red');
						}

						// alert('Error status update');
					} else {
						// alert(data['success']);
						window.location.reload();
					}
				}
			},
			error: function(error) {
				var message = error.responseJSON && error.responseJSON.message
					? error.responseJSON.message
					: 'Error';
				alert(message);
			}
		});
	}

	function messageRead(element) {
		var chatId = element.getAttribute('data-chat-id');

		$.ajax({
			type: 'post',
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: "{{ route('new_message_read') }}",
			data: {
				chatId: chatId,
			},
			success: function(response) {
				if (response) {
					var data = jQuery.parseJSON(response);
					if (data['status'] != 1) {

					} else {
						element.classList.remove('active');
					}
				}
			},
			error: function(error) {
				var message = error.responseJSON && error.responseJSON.message
					? error.responseJSON.message
					: 'Error';
				alert(message);
			}
		});
	}

	// Отправка сообщения от художника клиенту (общий чат)
	$(document).on('submit', '.js_painter_client_chat', function (e) {
		e.preventDefault();
		e.stopImmediatePropagation();

		var th = $(this);
		var order_id = th.data('id');
		var user_comment = th.find("[name='user_comment']").val();

		$.ajax({
			method: 'POST',
			url: "{{ route('new_send_client_painter_comments') }}",
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			data: {
				order_id: order_id,
				client_comment: user_comment // как в send_message для чатів по изображению
			},
			dataType: 'json',
			success: function (msg) {
				th.find('.painter_btn').prop('disabled', false);
				th.find('.js_u_comm_textarea').val('');

				if (msg.status == 1 || msg.status === true) {
					var d = new Date();
					var curr_date = d.getDate();
					var curr_month = d.getMonth() + 1;
					var curr_year = d.getFullYear();
					var curr_hours = d.getHours();
					var curr_minutes = d.getMinutes();
					var curr_seconds = d.getSeconds();
					curr_month = (curr_month + '').padStart(2, '0');

					var date = curr_year + '-' + curr_month + '-' + curr_date + ' ' + curr_hours + ':' + curr_minutes + ':' + curr_seconds;

					th.find('.js_order_user_comments').append(`
						<div class="chat-group">
							<div class="chat-item">
								<div class="chat-item__row">
									<div class="chat-name">@lang("account_new.orders.chat.you")</div>
									<div class="chat-date">${date}</div>
								</div>
								<div class="chat-item__body">
									<p>${msg.comment ?? user_comment}</p>
								</div>
							</div>
						</div>
					`);

					th.find('.chat-scroll__container .scroll-block').scrollTop(9999);
					th.find("[name='user_comment']").val("");
				}
			},
			error: function () {
				th.find('.painter_btn').prop('disabled', false);
			}
		});
	});

</script>
@endsection
