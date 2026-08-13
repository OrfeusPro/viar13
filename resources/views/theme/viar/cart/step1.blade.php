<div class="vz-art cart-page-global">
	<div class="section-frame">

		@include(env('THEME_RESOURCES') . '.cart.bread', ['step' => 1])

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

			<div class="cart-page-global__wrapper">
				<div class="cart-page-global__content">
					<div class="cart-page-global__title">
						@lang("cart_new.general_your_order") <span id="total_item_counts">{{ $total_item_counts }}</span> @lang("cart_new.general_pcs")
					</div>

					@php $items_count = 0; @endphp

					@foreach ($basket as $basketIndex => $product)
						@if (!isset($product['sumPrice']))
							@continue
						@endif
						@php $items_count++; @endphp

						<div id="cart_item_{{ $basketIndex }}" class="cart-page-item" item-id='{{ $basketIndex }}'>
							<div style="float: right; padding-left:10px;"><a href="javascript:void(0)" class="cart_item_delete"
									data-id='{{ $basketIndex }}'>X</a></div>
							<div class="cart-page-item__wrapper">
								<div class="cart-page-item__content">
									<div class="cart-page-item__left">
										<div class="cart-page-item__left--img">
											@if(isset($product['pid']) && $product['pid'] != 5)
												<div class="cart-page-item__download-photo">
													@lang("cart_new.general_uploaded_photos")
												</div>
											@endif
											<div class="cart-page-item__photo">
												@include(env('THEME_RESOURCES') . '.cart.cart_item_image')
											</div>
										</div>
										<div class="cart-page-item__left--content">
											@include(env('THEME_RESOURCES') . '.cart.cart_items')
										</div>
									</div>
									<div class="cart-page-item__pricing">
										<div class="cart-page-item__price">
											<span>
												@lang("cart_new.general_quantity")
											</span>
											<div class="count">
												<a href="" class="count__btn minus" data-id="{{ $basketIndex }}" data-min="1" data-type="minus">
													-
												</a>
												<div class="count__number">
													@isset($product['count'])
														{{ $product['count'] }}
													@endisset
												</div>
												<a href="" class="count__btn plus" data-id="{{ $basketIndex }}" data-min="1" data-type="plus">
													+
												</a>
											</div>
										</div>
										<div class="cart-page-item__price">
											<span>
												@lang("cart_new.general_price")
											</span>
											<strong>
												@if (isset($product['price']))
													{{ str_replace('.00', '', $product['price']) }}€
												@endif
											</strong>
										</div>
										<div class="cart-page-item__price">
											<span>
												@lang("cart_new.general_together")
											</span>
											<strong id="cart_price_{{ $basketIndex }}">
												@if (isset($product['sumPrice']))
													{{ $product['sumPrice'] }}€
												@endif
											</strong>
										</div>
									</div>
								</div>

								@isset($product['orig_images'])
									<div class="cart-page-item__bottom">
										@if(count($product['orig_images'])>1)
										@foreach ($product['orig_images'] as $key_img=>$img)
											@if($key_img>0)
											<div class="cart-page-item__photo" id="cart_img_{{ $basketIndex }}_{{ $key_img }}">
												<img src="{{ asset($img) }}">

												<div class="cart-page-item__photo--btns">
													<a href="#" class="cart-page-item__photo--btn cart-page-item-edit" data-type="upload" data-cartid="{{ $basketIndex }}" data-cartimgid="{{ $key_img }}" data-curid="cart_img_{{ $basketIndex }}_{{ $key_img }}" data-img="{{ $img }}">
														@lang("cart_new.general_change")
														<input type="file" accept="image/*,image/heif,image/heic">
													</a>
													<a href="#" class="cart-page-item__photo--btn cart-page-item-remove" data-type="delete" data-cartid="{{ $basketIndex }}" data-cartimgid="{{ $key_img }}" data-curid="cart_img_{{ $basketIndex }}_{{ $key_img }}" data-img="{{ $img }}">
														@lang("cart_new.general_delete")
													</a>
												</div>
											</div>
											@endif
										@endforeach
										@endif

									</div>
								@endisset

							</div>
						</div>
					@endforeach
					<div class="cart-page-global__bottom">
						<a href="{{ route('home') }}"
							class="cart-page-global__btn-global">@lang("cart_new.step_1_add_more_products")</a>
					</div>
				</div>

				@include(env('THEME_RESOURCES') . '.cart.sidebar', [
				    'basket' => $basket,
				    'btn' => trans('cart_new.general_checkout'),
					'btn_class' => 'cart_send_products',
				    'action' => route('cart.step2'),
				    'step' => 1,
				])

			</div>
		@endif
	</div>
</div>
