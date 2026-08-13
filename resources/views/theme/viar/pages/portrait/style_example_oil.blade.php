<div class="about-procces category-pop">
	<div class="section-frame">
		<div class="category-pop__inner">
			<div class="ap-title__row">
				<h2 class="category-pop__title page-title">
					@lang('pages.portrait_oil.block_images.title')
				</h2>
			</div>
			<div class="category-pop--block">
				<div class="cp-tabs">
					<div class="cp-tabs-inner">
						<h3 class="cp-tab active tabs-js">
							{!! trans('portrait_royal.s_reason_cat_all') !!}
						</h3>
						@foreach ($list['list_category'] as $cat)
							<h3 class="cp-tab tabs-js" data-cat="{{ trim($cat) }}">
								@lang('pages.portrait_oil.block_images.' . trim($cat))
							</h3>
						@endforeach
					</div>
				</div>
				<div class="cp-blocks">

					<div class="cp-block">
						<div class="cp-block-inner portrait-list__slider">
							@foreach ($list['list'] as $image)
								<div class="/*cp-item*/ portrait-list__item" data-cat="{{ $image->category }}">
									<div class="/*cp-item-inner*/ oil_item portrait-list__img">
										{{-- <picture> --}}
											{{-- <source srcset="{{ format_webp("storage/".$image->src) }}" type="image/webp"> --}}
											{{-- <source srcset="{{ $image->src }}" type="image/webp"> --}}
											{{-- <source srcset="{{ $image->src }}" type="image/jpeg"> --}}
											{{-- <img width="433" height="583" src="{{ $image->src }}" alt="{{ $image->title }} {{ $image->title }}" loading="lazy"> --}}
										{{-- </picture> --}}
										<picture>
											<source media="(max-width: 576px)" srcset="{{ $image->src }}" type="image/webp">
											<source srcset="{{ $image->src }}">
											<img width="315" height="451" src="{{ $image->src }}" alt="">
										</picture>

										<a href="#" class="default-btn js-image-calc"
										onclick="$('input[name=obraz_img]').val('{{ $image->src }}'); $('input[name=obraz_title]').val('{{ $image->id }}'); $('.popup-dyn-image').attr('src','{{ $image->src }}')"
										data-name="{{ $loop->iteration }}"
										obg="@lang('cart_new.obraz') #{{ $image->id }}">{{ trans('portrait_royal.btn_order_portrait') }}</a>
									</div>
								</div>
							@endforeach
						</div>
					</div>
					{{-- @endforeach --}}

				</div>
			</div>
		</div>
		<div class="portrait-list__pagination">
			<div class="pagination-button prev">
				<svg width="10" height="16" viewBox="0 0 10 16" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path
						d="M9.23549 14.6145C9.33594 14.5141 9.38616 14.3986 9.38616 14.268C9.38616 14.1374 9.33594 14.0219 9.23549 13.9215L3.31417 8.00014L9.23549 2.07882C9.33594 1.97838 9.38616 1.86286 9.38616 1.73228C9.38616 1.6017 9.33594 1.48619 9.23549 1.38574L8.48214 0.632393C8.3817 0.531947 8.26618 0.481724 8.1356 0.481724C8.00502 0.481724 7.88951 0.531947 7.78906 0.632393L0.767857 7.6536C0.667411 7.75405 0.617188 7.86956 0.617188 8.00014C0.617188 8.13072 0.667411 8.24623 0.767857 8.34668L7.78906 15.3679C7.88951 15.4683 8.00502 15.5186 8.1356 15.5186C8.26618 15.5186 8.3817 15.4683 8.48214 15.3679L9.23549 14.6145Z"
						fill="#FC8C5F"></path>
				</svg>
			</div>
			<div class="pagination-button next">
				<svg width="10" height="16" viewBox="0 0 10 16" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path
						d="M0.764508 14.6145C0.664062 14.5141 0.613838 14.3986 0.613838 14.268C0.613838 14.1374 0.664062 14.0219 0.764508 13.9215L6.68583 8.00014L0.764508 2.07882C0.664062 1.97838 0.613838 1.86286 0.613838 1.73228C0.613838 1.6017 0.664062 1.48619 0.764508 1.38574L1.51786 0.632393C1.6183 0.531947 1.73382 0.481724 1.8644 0.481724C1.99498 0.481724 2.11049 0.531947 2.21094 0.632393L9.23214 7.6536C9.33259 7.75405 9.38281 7.86956 9.38281 8.00014C9.38281 8.13072 9.33259 8.24623 9.23214 8.34668L2.21094 15.3679C2.11049 15.4683 1.99498 15.5186 1.8644 15.5186C1.73382 15.5186 1.6183 15.4683 1.51786 15.3679L0.764508 14.6145Z"
						fill="white"></path>
				</svg>
			</div>
		</div>
	</div>
</div>