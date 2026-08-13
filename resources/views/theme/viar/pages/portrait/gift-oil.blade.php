<section class="portrait-gift__section">
	<div class="section-inner">
		<div class="portrait-gift__grid">
			<div class="portrait-content" style="padding-left: 137px;">
				<div class="portrait-content__inner">
					<div class="page-title">
						@lang('pages.portrait_oil.oil-gift.t11')

					</div>
					<p>
						@lang('pages.portrait_oil.oil-gift.t12')
					</p>
					<br>
					<p>
						@lang('pages.portrait_oil.oil-gift.t13')
					</p>
					<div class="default-btn js-fast_order--royal" data-gift="1">
						{{ trans('portrait_royal.btn_order_portrait') }}</div>
					<div class="absolute-elements">
						<div class="portrait-gift">
							<img loading="lazy" width="149" height="138" src="{{ ver_asset('images/collage/gift.svg') }}"
								alt="Viar Image">
							<p>{!! trans('portrait_royal.s_gift_note') !!}</p>
						</div>
					</div>
				</div>
			</div>
			<div class="portrait-img">
				<picture>
					<source srcset="{{ ver_asset(env('THEME') . 'images/oil/gift_1.webp') }}" type="image/webp">
					<img width="800" height="451" src="{{ ver_asset(env('THEME') . 'images/oil/gift_1.png') }}" alt="">
				</picture>
			</div>
		</div>
	</div>
</section>
