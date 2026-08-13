<div class="ellipse">
	<img src="https://viarcanvas.com/images/icon/ellipse-whete.svg" alt="img" loading="eager" width="1374"
		height="99">
</div>
<div class="order-stage">
	<div class="section-frame">
		<div class="order-stage__inner">
			<h2 class="order-stage__title page-title">
                @if (Route::currentRouteName() == 'hb.gallery.item.single')
{{--                    @if ($h2_titles->zakaz_title_h2!='')--}}
{{--                    {!! $h2_titles->zakaz_title_h2 !!}--}}
{{--                    @else--}}
                    @if ($type==4)
                    @lang('gallery.zakaz_title') {{$painting_name}}?
                    @else
                        @lang('gallery.ordering_steps')
                    @endif
{{--                    @lang('gallery.ordering_steps')--}}
{{--                    @endif--}}
                @elseif (Route::currentRouteName() == 'home')
                    @lang('gallery.ordering_steps')
                @else
                    @lang('gallery.ordering_steps')
                @endif
			</h2>
			<p>@lang('gallery.ordering_steps_t1_1')</p>
			<div class="order-stage__items">
				<div class="order-stage__item">
					<div class="os-img">
						<picture>
                            <source srcset="{{ asset(env('THEME').'images') }}/mcard/11.webp" type="image/webp">
							<source srcset="{{ asset(env('THEME') . 'images') }}/mcard/11.jpg" type="image/jpeg">
							<img width="242" height="242" src="{{ asset(env('THEME') . 'images') }}/mcard/11.jpg" loading="lazy"
								alt="">
						</picture>
					</div>
					<div class="os-title">
						@lang('gallery.ordering_steps_t1_2')
					</div>
					<p>
						@lang('gallery.ordering_steps_t1_3')
					</p>
				</div>
				<div class="order-stage__item">
					<div class="os-img">
						<picture>
							 <source srcset="{{ asset(env('THEME').'images') }}/mcard/12.webp" type="image/webp">
							<source srcset="{{ asset(env('THEME') . 'images') }}/mcard/12.jpg" type="image/jpeg">
							<img width="242" height="242" src="{{ asset(env('THEME') . 'images') }}/mcard/12.jpg" loading="lazy"
								alt="">
						</picture>
					</div>
					<div class="os-title">
						@lang('gallery.photo_item_block_about_pay')
					</div>
					<p>
						@lang('gallery.ordering_steps_t1_4')
					</p>
				</div>
				<div class="order-stage__item">
					<div class="os-img">
						<picture>
							 <source srcset="{{ asset(env('THEME').'images') }}/mcard/13.webp" type="image/webp">
							<source srcset="{{ asset(env('THEME') . 'images') }}/mcard/13.jpg" type="image/jpeg">
							<img width="242" height="242" src="{{ asset(env('THEME') . 'images') }}/mcard/13.jpg" loading="lazy"
								alt="">
						</picture>
					</div>
					<div class="os-title">
						@lang('gallery.ordering_steps_t1_5')
					</div>
					<p>
						@lang('gallery.ordering_steps_t1_6')
					</p>
				</div>
				<div class="order-stage__item">
					<div class="os-img">
						<picture>
							 <source srcset="{{ asset(env('THEME').'images') }}/mcard/14.webp" type="image/webp">
							<source srcset="{{ asset(env('THEME') . 'images') }}/mcard/14.jpg" type="image/jpeg">
							<img width="242" height="242" src="{{ asset(env('THEME') . 'images') }}/mcard/14.jpg" alt="">
						</picture>
					</div>
					<div class="os-title">
						@lang('gallery.ordering_steps_t1_7')
					</div>
					<p>
						@lang('gallery.ordering_steps_t1_8')
					</p>
				</div>
			</div>
		</div>
	</div>
</div>
