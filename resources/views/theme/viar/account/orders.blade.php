@include(env('THEME_RESOURCES') . 'account.breads', ['page' => $page])

<div class="main-cabinet">
	<div class="section-frame">
		<div class="main-cabinet__inner">
			<div class="cabinet-top__block">
				<div class="page-title cabinet-title">
					{!! $page['title'] !!}
				</div>
				<p>
					@if(isset($count_not_read_messages) && $count_not_read_messages)
						@if($count_not_read_messages == 1) 
							{!! str_replace("###",$count_not_read_messages,__("account_new.orders.count_massage")) !!}
						@else
							{!! str_replace("###",$count_not_read_messages,__("account_new.orders.count_massages")) !!}
						@endif
						<br>
					@endif

					@if(Route::getCurrentRoute()->getName() == "new_account.paid")
						{{-- Оплата заказов поделена на <b class="activeSpan">2 переода</b>! --}}
						@lang("account_new.orders.count_paid_title") <b class="activeSpan">( {{ count($orders) }} )</b>!
					@elseif(Route::getCurrentRoute()->getName() == "new_account.orders")
						@lang("account_new.orders.title")
					@elseif(Route::getCurrentRoute()->getName() == "new_account.orders_success")
						@lang('account_new.orders_success.count') <b class="activeSpan">( {{ count($orders) }} )</b>!
					@else
					
					@endif
					{{-- {{ Route::getCurrentRoute()->getName() }} --}}
					<a class="activeSpan" href="{{ route('logout') }}"
						onclick="event.preventDefault(); document.getElementById('logout-form').submit();">@lang('account_new.btn.logout')</a>
				</p>
			</div>
			<div class="cabinet-container">

				@include(env('THEME_RESOURCES') . 'account.menu')

				<div class="cabinet-content">

				@if(Auth::user()->role->name == 'painter' && Route::getCurrentRoute()->getName() == "new_account.orders")
					@php
						$painterOrdersSorted = collect($painter_deadline_orders ?? [])
							->filter(function ($order) {
								if (empty($order->painter_end_time)) {
									return false;
								}

								$status = (string) ($order->status ?? '');
								return !\Illuminate\Support\Str::contains($status, ['production', 'производ', 'Производ']);
							})
							->sortBy(function ($order) {
                                $deadline = strtotime($order->painter_end_time);
                                $urgencyRank = !empty($order->painter_is_urgent) ? 0 : 1;
                                return sprintf('%d_%012d', $urgencyRank, $deadline);
							});
					@endphp

					@if($painterOrdersSorted->count())
						<div class="cabinet-orderState" style="margin-bottom: 20px;">
							<div class="cabinet-ordersBlock">
								<div class="cabinet-ordersBlock_top">
									<p class="text-base">
										@lang('account_new.orders.execute_to')
									</p>
								</div>
								<div class="cabinet-ordersBlock_body" style="padding: 10px 20px;">
									@foreach($painterOrdersSorted as $deadlineOrder)
										@php
											$deadlineTs = strtotime($deadlineOrder->painter_end_time);
											$diffSeconds = $deadlineTs - now()->timestamp;

											$deadlineStyle = '';
											if (!empty($deadlineOrder->painter_is_urgent)) {
												$deadlineStyle = "style='background: #63ffcb; padding-left: 5px; padding-right: 5px; font-weight: 600;'";
											} elseif ($diffSeconds < 0) {
												$deadlineStyle = "style='color: red; font-weight: 700;'";
											} elseif ($diffSeconds < 24 * 3600) {
												$deadlineStyle = "style='color: red; font-weight: 600;'";
											} elseif ($diffSeconds < 3 * 24 * 3600) {
												$deadlineStyle = "style='color: #FA7846; font-weight: 600;'";
											}
										@endphp

										<p class="text-base" style="margin: 0 0 6px;">
											<span class="darkSpan"># {{ $deadlineOrder->id }}</span>
											<span {!! $deadlineStyle !!}>— {{ date('d.m.Y', $deadlineTs) }}</span>
										</p>
									@endforeach
								</div>
							</div>
						</div>
					@endif
				@endif

					@include(env('THEME_RESOURCES') . 'account.order')

					<div class="cabinet-content__icon">
						<img src="{{ asset(config('theme.current') . '/images')}}/cabinet/free-icon-order-delivery.svg" width="82" height="82"
							alt="Viar Cabinet Peding Orders">
					</div>


				</div>
			</div>
		</div>
	</div>
</div>


@include(config('theme.resource') . 'account.special_offers')
@include(config('theme.resource') . 'pages.gallery.zpart_viarcanvas_is')
