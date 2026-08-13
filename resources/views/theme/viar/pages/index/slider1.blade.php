<section class="portraits hb_slider_main">
	<div class="portraits-wrap">
		<div class="portraits-slider">
			@if ($home_slides)
				@foreach ($home_slides as $slide)
					<article class="portraits-slide @if (isset($slide['is_white']) && $slide['is_white']) mslidewhite @endif">
						<picture>
							@if ($webpSrc = image_webp_url('storage/' . $slide[App::getLocale() . '_mob']))
								<source media="(max-width: 700px)" srcset="{{ $webpSrc }}" type="image/webp">
							@endif
							<source media="(max-width: 700px)" srcset="{{ Voyager::image($slide[App::getLocale() . '_mob']) }}">
							@if ($webpSrc = image_webp_url('storage/' . $slide[App::getLocale()]))
								<source srcset="{{ $webpSrc }}" type="image/webp">
							@endif
							<img class="portraits-bg lozad" src="{{ Voyager::image($slide[App::getLocale()]) }}" alt="">
						</picture>

						<div class="section-frame">
							<div class="portraits-info">
								<div class="portraits-heading">
									@if ($loop->first)
										<h1 class="portraits-title" @if (isset($slide['is_white']) && $slide['is_white']) style="color: white;" @endif>
											{!! trans('homepage_new.slider_title') !!}
										</h1>
									@else
										<div class="portraits-title" @if (isset($slide['is_white']) && $slide['is_white']) style="color: white;" @endif>
											{!! trans('homepage_new.slider_title') !!}
										</div>
									@endif
									<p @if (isset($slide['is_white']) && $slide['is_white']) style="color: white;" @endif>{!! trans('homepage_new.slider_desc') !!}</p>
								</div>
								<div class="portraits-btn">
									<a href="{{ route('all_styles') }}" data-catid="1">{!! trans('homepage_new.slider_btn1_title') !!}</a>
									{{-- <a href="#" class="js-examples" data-catid="1">{!! trans('homepage_new.slider_btn1_title') !!}</a> --}}
									{{-- <a href="{{ route('all_styles') }}" class="portraits-btn__style">{!! trans('homepage_new.slider_btn2_title') !!}</a> --}}
								</div>
								<div class="portraits-gift">
									<picture>
										<source srcset="{{ asset('images/gift.webp') }}" type="image/webp">
										<source srcset="{{ asset('images/gift.png') }}">
										<img src="{{ asset('images/gift.png') }}" class="gift-photo" alt="img" loading="lazy" width="85"
											height="51">
									</picture>
									<svg class="gift-photo_mob">
										<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#gift') }}"></use>
									</svg>
									<p class="hb_text-right_pc" @if (isset($slide['is_white']) && $slide['is_white']) style="color: white;" @endif>
										{!! trans('homepage_new.slider_image_text') !!}</p>
									<p class="hb_text-right_mob" @if (isset($slide['is_white']) && $slide['is_white']) style="color: white;" @endif>
										{!! trans('homepage_new.slider_image_text_mob') !!}</p>
									<svg class="portraits-gift__arrow">
										<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#arrow') }}"></use>
									</svg>
								</div>
							</div>
						</div>
					</article>
				@endforeach
			@endif
		</div>

		<div class="portraits-arrow">
			<a href="#" class="portraits-prev" aria-label="portrait prev">
				<i class="fa-arrow-prev"></i>
			</a>
			<a href="#" class="portraits-next" aria-label="portrait next">
				<i class="fa-arrow-next"></i>
			</a>
		</div>
	</div>

	<div class="section-frame frame-dots">
		<div class="portraits-dots"></div>
	</div>
</section>

@if($creepingLine && !$creepingLine->isEmpty())
	<div class="marquee-container mb-6">
		<div class="marquee-inner">
			@foreach ($creepingLine as $line)
				<span class="marquee-text">{!! $line->text !!}</span>
			@endforeach
	
			@foreach ($creepingLine as $line)
				<span class="marquee-text">{!! $line->text !!}</span>
			@endforeach

		</div>
	</div>
@else
	<div class="ellipse notmobile">
		<a href="#services" class="anchor ellipse-arrow ellipse-arrow_white" aria-label="anchor link">
			<i class="fa-arrow-down"></i>
		</a>
		<img src="{{ asset('images/icon/ellipse-whete.svg') }}" alt="img" loading="eager">
	</div>
@endif
