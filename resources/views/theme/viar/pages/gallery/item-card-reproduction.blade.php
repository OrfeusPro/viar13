<div class="br-object">
	<div class="section-frame">
		<div class="breadcrumbs breadcrumbs__block">
			<div>
				<a href="{{ route('home') }}" class="breadcrumbs__link breadcrumbs__link_main">
					<span>@lang('account.index1')</span>
				</a>
			</div>
			<svg xmlns="http://www.w3.org/2000/svg" width="6" height="11" viewBox="0 0 6 11" fill="none"
				class="img-svg breadcrumbs__arrow replaced-svg">
				<path
					d="M0.24775 1.4341C0.180786 1.50106 0.147304 1.57807 0.147304 1.66513C0.147304 1.75218 0.180786 1.82919 0.24775 1.89615L4.1953 5.8437L0.247751 9.79124C0.180787 9.85821 0.147304 9.93522 0.147304 10.0223C0.147304 10.1093 0.180787 10.1863 0.247751 10.2533L0.749984 10.7555C0.816948 10.8225 0.893957 10.856 0.98101 10.856C1.06806 10.856 1.14507 10.8225 1.21204 10.7555L5.89284 6.07473C5.9598 6.00776 5.99329 5.93075 5.99329 5.8437C5.99329 5.75664 5.9598 5.67964 5.89284 5.61267L1.21204 0.931868C1.14507 0.864903 1.06806 0.831421 0.981009 0.831421C0.893956 0.831421 0.816947 0.864903 0.749983 0.931868L0.24775 1.4341Z"
					fill="#FA7846"></path>
			</svg>
			<div>
				<a href="{{ route('hb.gallery.index') }}" class="breadcrumbs__link breadcrumbs__link_main">
					<span>{{ trans('breadcrumbs.gallery') }}</span>
				</a>
			</div>
			<svg xmlns="http://www.w3.org/2000/svg" width="6" height="11" viewBox="0 0 6 11" fill="none"
				class="img-svg breadcrumbs__arrow replaced-svg">
				<path
					d="M0.24775 1.4341C0.180786 1.50106 0.147304 1.57807 0.147304 1.66513C0.147304 1.75218 0.180786 1.82919 0.24775 1.89615L4.1953 5.8437L0.247751 9.79124C0.180787 9.85821 0.147304 9.93522 0.147304 10.0223C0.147304 10.1093 0.180787 10.1863 0.247751 10.2533L0.749984 10.7555C0.816948 10.8225 0.893957 10.856 0.98101 10.856C1.06806 10.856 1.14507 10.8225 1.21204 10.7555L5.89284 6.07473C5.9598 6.00776 5.99329 5.93075 5.99329 5.8437C5.99329 5.75664 5.9598 5.67964 5.89284 5.61267L1.21204 0.931868C1.14507 0.864903 1.06806 0.831421 0.981009 0.831421C0.893956 0.831421 0.816947 0.864903 0.749983 0.931868L0.24775 1.4341Z"
					fill="#FA7846"></path>
			</svg>
			<div>
				<a href="{{ route('hb.gallery.module', $page->url) }}" class="breadcrumbs__link breadcrumbs__link_main">
					<span>{{ $page->name }}</span>
				</a>
			</div>
			<svg xmlns="http://www.w3.org/2000/svg" width="6" height="11" viewBox="0 0 6 11" fill="none"
				class="img-svg breadcrumbs__arrow replaced-svg">
				<path
					d="M0.24775 1.4341C0.180786 1.50106 0.147304 1.57807 0.147304 1.66513C0.147304 1.75218 0.180786 1.82919 0.24775 1.89615L4.1953 5.8437L0.247751 9.79124C0.180787 9.85821 0.147304 9.93522 0.147304 10.0223C0.147304 10.1093 0.180787 10.1863 0.247751 10.2533L0.749984 10.7555C0.816948 10.8225 0.893957 10.856 0.98101 10.856C1.06806 10.856 1.14507 10.8225 1.21204 10.7555L5.89284 6.07473C5.9598 6.00776 5.99329 5.93075 5.99329 5.8437C5.99329 5.75664 5.9598 5.67964 5.89284 5.61267L1.21204 0.931868C1.14507 0.864903 1.06806 0.831421 0.981009 0.831421C0.893956 0.831421 0.816947 0.864903 0.749983 0.931868L0.24775 1.4341Z"
					fill="#FA7846"></path>
			</svg>
			<div>
				<a href="{{ url()->current() }}" class="breadcrumbs__link" aria-current="page">
                    <span>{{ $item->name }}</span>
                </a>
			</div>
		</div>

	</div>
</div>

<div class="mcard-main">
    {{-- Повідомлення про знижку з рекомендацій --}}
    <div class="section-frame">
        @include((config('theme.resource') ?: 'theme.viar.') . '.partials.recommendation-discount-notification')
    </div>

    <div class="section-frame">
		<form class="rcard-main__inner" action="#">
			<input type="hidden" name="pid" value="{{ $item->id }}">
			<div class="rcard-mode testtest">
				<div class="rcm-block rc-design" data-block="1">
					<div class="rcard-sl">
						<div class="rcard-navSlider-wrapper rcardd-swiper" thumbsSlider="">
							<div class="mcard-navSlider swiper-wrapper">

								@php $images = json_decode($item->images, true); @endphp
								@if ($images)
									@foreach ($images as $image)
										@php
											$src = '/storage/' . $image;
											$imageAlt = trans('homepage_new.image') . ' ' . $item->name . ($loop->iteration > 1 ? ' ' . $loop->iteration : '');
										@endphp

										<div class="mc-navSLide swiper-slide">
											<picture type="testtest">
                                                @if ($webpSrc = image_webp_url($src))
                                                    <source loading="lazy" srcset="{{ $webpSrc }}" type="image/webp" class="lozad" >
                                                @endif
												<source loading="lazy" srcset="https://viarcanvas.com{{ $src }}" type="image/jpeg" class="lozad">
												<img loading="lazy" class="lozad" width="85" height="85" src="https://viarcanvas.com/{{ $src }}" @altAttrs($item, 'images', $image, null, $imageAlt)>
											</picture>
										</div>
									@endforeach
								@endif

							</div>
						</div>
						<div class="swiper-button swiper-prev">
							<svg width="14" height="9" viewBox="0 0 14 9" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M1.33366 8L7.00032 2L12.667 8" stroke="#1E2533" stroke-width="2" />
							</svg>
						</div>
						<div class="swiper-button swiper-next">
							<svg width="14" height="9" viewBox="0 0 14 9" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M12.6663 1L6.99967 7L1.33301 1" stroke="white" stroke-width="2" />
							</svg>
						</div>
					</div>
					<div class="rcard-mainSlider-wrapper">
						<div class="mc-zoom">
							<svg width="50" height="50" viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg">
								<circle cx="25" cy="25" r="25" fill="#FA7846" />
								<g clip-path="url(#clip0_316_355)">
									<path
										d="M41 37.8511L32.1883 29.0393C33.6274 27.3399 34.5 25.1463 34.5 22.75C34.5 17.3735 30.1264 13 24.75 13C19.3736 13 15 17.3735 15 22.75C15 28.1265 19.3735 32.5 24.75 32.5C27.1463 32.5 29.3399 31.6274 31.0393 30.1883L39.8511 39L41 37.8511ZM24.75 30.875C20.2702 30.875 16.625 27.2299 16.625 22.75C16.625 18.2702 20.2702 14.625 24.75 14.625C29.2299 14.625 32.875 18.2702 32.875 22.75C32.875 27.2299 29.2299 30.875 24.75 30.875Z"
										fill="white" />
									<rect width="10.4429" height="1.14688" transform="matrix(-2.0104e-05 -1 1 2.0104e-05 24.1289 27.8779)"
										fill="white" />
									<rect width="10.4429" height="1.09709" transform="matrix(-1 -2.03991e-05 -2.05255e-05 -1 29.8799 23.0928)"
										fill="white" />
								</g>
								<defs>
									<clipPath id="clip0_316_355">
										<rect width="25" height="25" fill="white" transform="translate(15 13)" />
									</clipPath>
								</defs>
							</svg>
						</div>
						<div class="mcard-mainSlider swiper-wrapper">

							@php $images = json_decode($item->images, true); @endphp
							@if ($images)
								@foreach ($images as $image)
									@php
										$src = '/storage/' . $image;
										$imageAlt = trans('homepage_new.image') . ' ' . $item->name . ($loop->iteration > 1 ? ' ' . $loop->iteration : '');
									@endphp

                                    @if ($supportsWebp)
                                        <a href="{{ image_webp_url($src) ?: image_original_url($src) }}" aria-label="mcards-images link" class="mc-mainSlide swiper-slide" data-fancybox="mcards-images">
                                        @else
                                     <a href="https://viarcanvas.com{{ $src }}" aria-label="mcards-images link" class="mc-mainSlide swiper-slide" data-fancybox="mcards-images">
                                            @endif

                                        <picture>
											@if ($webpSrc = image_webp_url($src))
												<source  srcset="{{ $webpSrc }}" type="image/webp">
											@endif
											<source  srcset="https://viarcanvas.com{{ $src }}" type="image/jpeg">
											<img width="545" height="500" src="https://viarcanvas.com{{ $src }}" @altAttrs($item, 'images', $image, null, $imageAlt)>
										</picture>
									</a>
								@endforeach
							@endif

						</div>
						<div class="swiper-button swiper-prev">
							<svg width="12" height="19" viewBox="0 0 12 19" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M11 18L2 9.5L11 1" stroke="#1E2533" stroke-width="2" />
							</svg>
						</div>
						<div class="swiper-button swiper-next">
							<svg width="12" height="19" viewBox="0 0 12 19" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M0.999999 0.999999L10 9.5L1 18" stroke="white" stroke-width="2" />
							</svg>
						</div>
					</div>
				</div>
				<div class="rcm-block rc-view hidden-block" data-block="2">
					<div class="rcard-sl">
						<div class="rcard-navSlider-wrapper rcardv-swiper" thumbsSlider="">
							<div class="mcard-navSlider swiper-wrapper">


								@php $images = json_decode($item->images, true); @endphp
								@if ($images)
									@foreach ($images as $image)
										@php
											$src = '/storage/' . $image;
											$imageAlt = trans('homepage_new.image') . ' ' . $item->name . ($loop->iteration > 1 ? ' ' . $loop->iteration : '');
										@endphp

										<div class="mc-navSLide swiper-slide active"
										data-portrait="https://viarcanvas.com{{ $src }}" data-slider="{{ $loop->iteration }} }}">
											<picture>
												@if ($webpSrc = image_webp_url($src))
													<source srcset="{{ $webpSrc }}" type="image/webp">
												@endif
												<source srcset="https://viarcanvas.com{{ $src }}" type="image/jpeg">
												<img width="85" height="95" src="https://viarcanvas.com/{{ $src }}"
												@altAttrs($item, 'images', $image, null, $imageAlt) loading="lazy">
											</picture>
										</div>
									@endforeach
								@endif

							</div>
						</div>
						<div class="swiper-button swiper-prev">
							<svg width="14" height="9" viewBox="0 0 14 9" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M1.33366 8L7.00032 2L12.667 8" stroke="#1E2533" stroke-width="2" />
							</svg>
						</div>
						<div class="swiper-button swiper-next">
							<svg width="14" height="9" viewBox="0 0 14 9" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M12.6663 1L6.99967 7L1.33301 1" stroke="white" stroke-width="2" />
							</svg>
						</div>
					</div>
					<div class="rcard-interio" id="interior_container">
						<div id="rIntZoom" class="mc-zoom">
							<svg width="50" height="50" viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg">
								<circle cx="25" cy="25" r="25" fill="#FA7846" />
								<g clip-path="url(#clip0_316_355)">
									<path
										d="M41 37.8511L32.1883 29.0393C33.6274 27.3399 34.5 25.1463 34.5 22.75C34.5 17.3735 30.1264 13 24.75 13C19.3736 13 15 17.3735 15 22.75C15 28.1265 19.3735 32.5 24.75 32.5C27.1463 32.5 29.3399 31.6274 31.0393 30.1883L39.8511 39L41 37.8511ZM24.75 30.875C20.2702 30.875 16.625 27.2299 16.625 22.75C16.625 18.2702 20.2702 14.625 24.75 14.625C29.2299 14.625 32.875 18.2702 32.875 22.75C32.875 27.2299 29.2299 30.875 24.75 30.875Z"
										fill="white" />
									<rect width="10.4429" height="1.14688" transform="matrix(-2.0104e-05 -1 1 2.0104e-05 24.1289 27.8779)"
										fill="white" />
									<rect width="10.4429" height="1.09709" transform="matrix(-1 -2.03991e-05 -2.05255e-05 -1 29.8799 23.0928)"
										fill="white" />
								</g>
								<defs>
									<clipPath id="clip0_316_355">
										<rect width="25" height="25" fill="white" transform="translate(15 13)" />
									</clipPath>
								</defs>
							</svg>
						</div>
						<canvas id="canvas_interior"> </canvas>
					</div>

				</div>
			</div>
			<div class="rcard-content">
				<div class="rcard-tabs">
					<div class="rcard-tab active" data-id="1">
						@lang("canvas.form_your_pic")
					</div>
					<div class="rcard-tab" data-id="2">
						@lang("canvas.form_interiour")
					</div>
				</div>
				<div class="rcc-design rcc-block" data-block="1">
					<h1 class="mcard-title" data-name="{{ $item->name }}" data-pid="{{ $item->id }}">
						{{ $item->name }}
					</h1>
					<div class="mcard-info">
						<div class="mcard-code">
							@lang('gallery.product_code'): {{ $item->id }}
						</div>
						<div class="mcard-avail">
							<svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
								<g clip-path="url(#clip0_214_3)">
									<path
										d="M14.8315 2.21218C14.6062 1.98689 14.2409 1.98689 14.0156 2.21218L4.66372 11.5641L0.985349 7.88568C0.760084 7.66036 0.394781 7.66036 0.169458 7.88568C-0.0558351 8.11097 -0.0558351 8.47625 0.169458 8.70157L4.25582 12.7879C4.48103 13.0131 4.84645 13.0132 5.07171 12.7879L14.8315 3.02807C15.0568 2.80275 15.0568 2.43748 14.8315 2.21218Z"
										fill="#1F9750"></path>
								</g>
								<defs>
									<clipPath id="clip0_214_3">
										<rect width="15" height="15" fill="white"></rect>
									</clipPath>
								</defs>
							</svg>
							<span>@lang('cart_new.in_stock')</span>
						</div>
						<div class="mcard-rate">
							<div class="rate-inner">
								<svg width="15" height="14" viewBox="0 0 15 14" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path
										d="M7.5 0L9.18386 5.18237H14.6329L10.2245 8.38525L11.9084 13.5676L7.5 10.3647L3.09161 13.5676L4.77547 8.38525L0.367076 5.18237H5.81614L7.5 0Z"
										fill="#FC8C5F"></path>
								</svg>
								<svg width="15" height="14" viewBox="0 0 15 14" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path
										d="M7.5 0L9.18386 5.18237H14.6329L10.2245 8.38525L11.9084 13.5676L7.5 10.3647L3.09161 13.5676L4.77547 8.38525L0.367076 5.18237H5.81614L7.5 0Z"
										fill="#FC8C5F"></path>
								</svg>
								<svg width="15" height="14" viewBox="0 0 15 14" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path
										d="M7.5 0L9.18386 5.18237H14.6329L10.2245 8.38525L11.9084 13.5676L7.5 10.3647L3.09161 13.5676L4.77547 8.38525L0.367076 5.18237H5.81614L7.5 0Z"
										fill="#FC8C5F"></path>
								</svg>
								<svg width="15" height="14" viewBox="0 0 15 14" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path
										d="M7.5 0L9.18386 5.18237H14.6329L10.2245 8.38525L11.9084 13.5676L7.5 10.3647L3.09161 13.5676L4.77547 8.38525L0.367076 5.18237H5.81614L7.5 0Z"
										fill="#FC8C5F"></path>
								</svg>
								<svg width="15" height="14" viewBox="0 0 15 14" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path
										d="M7.5 0L9.18386 5.18237H14.6329L10.2245 8.38525L11.9084 13.5676L7.5 10.3647L3.09161 13.5676L4.77547 8.38525L0.367076 5.18237H5.81614L7.5 0Z"
										fill="#FC8C5F"></path>
								</svg>
							</div>
							<span>@lang('gl.our_w_title')</span>
						</div>
					</div>
					<div class="rcard-row">
						<div class="rcard-type">
							<div class="rcard-type-block">
								<p>@lang('gallery.execution_type'):</p>
								<div class="rcard-type__inner">
									<div class="rcard-type-item rcard-type__set gallery-js-type active" data-price="0" data-execution="0" data-text="@lang('gallery.reproduction_type_1')">
										<picture>
											 <source srcset="{{ asset(env('THEME') . 'images') }}/cardreproduction/5.webp" type="image/webp">
											<source srcset="{{ asset(env('THEME') . 'images') }}/cardreproduction/5.jpg" type="image/jpeg">
											<img width="75" height="94" src="{{ asset(env('THEME') . 'images') }}/cardreproduction/5.jpg"
												alt="">
										</picture>
										<span>@lang("gallery.reproduction_type_1")</span>
									</div>
									<div class="rcard-type-item rcard-type__set gallery-js-type"  data-price="0" data-execution="1" data-text="@lang('gallery.reproduction_type_1') @lang('gallery.reproduction_type_2')">
										<picture>
											 <source srcset="{{ asset(env('THEME') . 'images') }}/cardreproduction/3.webp" type="image/webp">
											<source srcset="{{ asset(env('THEME') . 'images') }}/cardreproduction/3.jpg" type="image/jpeg">
											<img width="75" height="94" src="{{ asset(env('THEME') . 'images') }}/cardreproduction/3.jpg"
												alt="">
										</picture>
										<span>@lang("gallery.reproduction_type_2")</span>
									</div>
								</div>
							</div>
							<div class="rcard-type-block">
								<p>@lang("gallery.option"):</p>
								<div class="rcard-type__inner">
									<div class="rcard-type-item rcard-type__trigger gallery-js-type" data-frame="1" data-price="100">
										<picture>
											 <source srcset="{{ asset(env('THEME') . 'images') }}/cardreproduction/4.webp" type="image/webp">
											<source srcset="{{ asset(env('THEME') . 'images') }}/cardreproduction/4.jpg" type="image/jpeg">
											<img width="93" height="96" src="{{ asset(env('THEME') . 'images') }}/cardreproduction/4.jpg"
												alt="Viar" loading="lazy">
										</picture>
										<span>@lang("gallery.reproduction_type_3")</span>
									</div>
								</div>
							</div>
						</div>
                        @if($item->allow_client_photo_upload)
                            <div class="gallery-client-upload">
                                <div class="accordion-title h2_old open">
                                    <span>{!! trans('portrait_buy_form.step1_title') !!}</span>
                                    <span class="tab-icon"></span>
                                </div>
                                <div class="accordion-content">
                                    <p class="vz-art kviz-input__title">{!! trans('portrait_buy_form.step1_desc') !!}</p>
                                    <div class="file-save file-save__popup gallery-client-upload__label">
                                        <div class="abs-close gallery-photo-clear">X</div>
                                        <div class="file-save__item js-file-preview">
                                            <svg>
                                                <use xlink:href="{{ asset(env('THEME').'sprite.svg#save') }}"></use>
                                            </svg>
                                            <div class="file-save__title">
                                                <p>{{ trans('homepage_new.load_photo') }}</p>
                                                <span>{{ trans('homepage_new.pree_to_add_photo') }}</span>
                                            </div>
                                        </div>
                                        <div class="file-save__item js-file-upload">
                                            <svg>
                                                <use xlink:href="{{ asset(env('THEME').'sprite.svg#picture') }}"></use>
                                            </svg>
                                            <div class="file-save__title">
                                                <p></p>
                                                <span></span>
                                            </div>
                                        </div>
                                        <div class="file-save__item js-file-multiple">
                                            <svg>
                                                <use xlink:href="{{ asset(env('THEME').'sprite.svg#check') }}"></use>
                                            </svg>
                                            <div class="file-save__title">
                                                <p class="file-title_green">{{ trans('portrait.form_files_loaded') }}</p>
                                            </div>
                                        </div>
                                        <input id="gallery_client_photo" class="file-input gallery-client-photo-input" type="file" accept=".png,.bmp,.jpg,.jpeg,.psd,.fig,.pdf,.heic,.heif" aria-label="gallery client photo">
                                    </div>
                                </div>
                            </div>
                        @endif
						<div class="rcard-size">
							<p>@lang('cart.size'):</p>
							<div class="mcard-size js-mcard-size mcard-size--open">
								<a>
									<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path fill-rule="evenodd" clip-rule="evenodd"
											d="M0.466667 0H0V0.466667V5.13333H0.933333V1.5933L4.80335 5.46331L5.46331 4.80335L1.5933 0.933333H5.13333V0H0.466667ZM13.5333 0H14V0.466667V5.13333H13.0667V1.5933L9.19665 5.46331L8.53669 4.80335L12.4067 0.933333H8.86667V0H13.5333ZM14 14H13.5333H8.86667V13.0667H12.4067L8.53669 9.19665L9.19665 8.53669L13.0667 12.4067V8.86667H14V13.5333V14ZM0.466667 14H0V13.5333V8.86667H0.933333V12.4067L4.80335 8.53669L5.46331 9.19665L1.5933 13.0667H5.13333V14H0.466667Z"
											fill="#FA7846" />
									</svg>
									{{-- first price --}}
									@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.custom_sizes_calc_cat', ['first_size' => true])

									<svg width="13" height="8" viewBox="0 0 13 8" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path
											d="M12.2184 0.137457C12.1316 0.0458193 12.0317 4.23244e-08 11.9188 4.72591e-08C11.8059 5.21938e-08 11.7061 0.0458193 11.6192 0.137457L6.5 5.53952L1.38076 0.137458C1.29392 0.0458198 1.19405 5.16054e-07 1.08116 5.20989e-07C0.968269 5.25924e-07 0.868403 0.0458198 0.781562 0.137458L0.130261 0.824744C0.0434205 0.916381 -3.05028e-07 1.02176 -2.99821e-07 1.14089C-2.94614e-07 1.26002 0.0434205 1.36541 0.130261 1.45705L6.2004 7.86254C6.28724 7.95418 6.38711 8 6.5 8C6.61289 8 6.71276 7.95418 6.7996 7.86254L12.8697 1.45705C12.9566 1.36541 13 1.26002 13 1.14089C13 1.02176 12.9566 0.916381 12.8697 0.824743L12.2184 0.137457Z"
											fill="#FC8C5F" />
									</svg>
								</a>
								<div class="filter-item--wrapper">
									<p>@lang('gallery.select_size'):</p>

									<ul class="c-sizeCheck gallery-js-size">

										@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.custom_sizes_calc_cat')

									</ul>
								</div>

							</div>
						</div>
					</div>
					<div class="rcard-frame">
						<p>@lang("gl.rama")</p>
						<div class="rcard-frame__inner">
							<a href="#" class="frame-js">
								<span>@lang("gallery.baguettes_and_frames")</span>
								<picture>
									 <source srcset="{{ asset(env('THEME') . 'images') }}/cardreproduction/6.webp" type="image/webp">
									<source srcset="{{ asset(env('THEME') . 'images') }}/cardreproduction/6.jpg" type="image/jpeg">
									<img width="166" height="73" src="{{ asset(env('THEME') . 'images') }}/cardreproduction/6.jpg"
										alt="Viar" loading="lazy">
								</picture>
								<svg width="13" height="8" viewBox="0 0 13 8" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path
										d="M12.2184 0.137457C12.1316 0.0458193 12.0317 4.23244e-08 11.9188 4.72591e-08C11.8059 5.21938e-08 11.7061 0.0458193 11.6192 0.137457L6.5 5.53952L1.38076 0.137458C1.29392 0.0458198 1.19405 5.16054e-07 1.08116 5.20989e-07C0.968269 5.25924e-07 0.868403 0.0458198 0.781562 0.137458L0.130261 0.824744C0.0434205 0.916381 -3.05028e-07 1.02176 -2.99821e-07 1.14089C-2.94614e-07 1.26002 0.0434205 1.36541 0.130261 1.45705L6.2004 7.86254C6.28724 7.95418 6.38711 8 6.5 8C6.61289 8 6.71276 7.95418 6.7996 7.86254L12.8697 1.45705C12.9566 1.36541 13 1.26002 13 1.14089C13 1.02176 12.9566 0.916381 12.8697 0.824743L12.2184 0.137457Z"
										fill="#FC8C5F" />
								</svg>
							</a>
							<div class="frame-wrapper" data-add-label="@lang('pages.modular-generator.generate.step5.add')" data-remove-label="@lang('cart_new.general_delete')">
								<div class="rc-filter-box">
									<div class="filter-item mc-js-filter">
										<a href="#">
											<span>@lang("gallery.color")</span>
											<svg width="13" height="8" viewBox="0 0 13 8" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path
													d="M12.2184 0.137457C12.1316 0.0458193 12.0317 4.23244e-08 11.9188 4.72591e-08C11.8059 5.21938e-08 11.7061 0.0458193 11.6192 0.137457L6.5 5.53952L1.38076 0.137458C1.29392 0.0458198 1.19405 5.16054e-07 1.08116 5.20989e-07C0.968269 5.25924e-07 0.868403 0.0458198 0.781562 0.137458L0.130261 0.824744C0.0434205 0.916381 -3.05028e-07 1.02176 -2.99821e-07 1.14089C-2.94614e-07 1.26002 0.0434205 1.36541 0.130261 1.45705L6.2004 7.86254C6.28724 7.95418 6.38711 8 6.5 8C6.61289 8 6.71276 7.95418 6.7996 7.86254L12.8697 1.45705C12.9566 1.36541 13 1.26002 13 1.14089C13 1.02176 12.9566 0.916381 12.8697 0.824743L12.2184 0.137457Z"
													fill="#FC8C5F"></path>
											</svg>
										</a>
										<div class="filter-item--wrapper filter-color">
											<p>@lang("gallery.select_color"):</p>
											<ul class="color-grid" id="ram_colors">
												@foreach($def_inter_rams_color as $ram_color)
													<li data-ramcolorid="{{$ram_color->id}}" style="background: {{$ram_color->color}};"></li>
												@endforeach
											</ul>
										</div>
									</div>
									<div class="filter-item mc-js-filter">
										<a href="#">
											<span>@lang("gallery.material")</span>
											<svg width="13" height="8" viewBox="0 0 13 8" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path
													d="M12.2184 0.137457C12.1316 0.0458193 12.0317 4.23244e-08 11.9188 4.72591e-08C11.8059 5.21938e-08 11.7061 0.0458193 11.6192 0.137457L6.5 5.53952L1.38076 0.137458C1.29392 0.0458198 1.19405 5.16054e-07 1.08116 5.20989e-07C0.968269 5.25924e-07 0.868403 0.0458198 0.781562 0.137458L0.130261 0.824744C0.0434205 0.916381 -3.05028e-07 1.02176 -2.99821e-07 1.14089C-2.94614e-07 1.26002 0.0434205 1.36541 0.130261 1.45705L6.2004 7.86254C6.28724 7.95418 6.38711 8 6.5 8C6.61289 8 6.71276 7.95418 6.7996 7.86254L12.8697 1.45705C12.9566 1.36541 13 1.26002 13 1.14089C13 1.02176 12.9566 0.916381 12.8697 0.824743L12.2184 0.137457Z"
													fill="#FC8C5F"></path>
											</svg>
										</a>
										<div class="filter-item--wrapper">
											<p>@lang("gallery.select_material")</p>
											<ul class="filter-material" id="ram_materials">

												@foreach($def_inter_rams_material as $ram_material)
												<li data-rammaterialid="{{$ram_material->id}}">
													<svg width="15" height="15" viewBox="0 0 15 15" fill="none"
														xmlns="http://www.w3.org/2000/svg">
														<g class="clip0_309_246">
															<path
																d="M14.8315 2.21218C14.6062 1.98689 14.2409 1.98689 14.0156 2.21218L4.66372 11.5641L0.985349 7.88568C0.760084 7.66036 0.394781 7.66036 0.169458 7.88568C-0.0558351 8.11097 -0.0558351 8.47625 0.169458 8.70157L4.25582 12.7879C4.48103 13.0131 4.84645 13.0132 5.07171 12.7879L14.8315 3.02807C15.0568 2.80275 15.0568 2.43748 14.8315 2.21218Z"
																fill="#FA7846"></path>
														</g>
														<defs>
															<clipPath class="clip0_309_246">
																<rect width="15" height="15" fill="white"></rect>
															</clipPath>
														</defs>
													</svg>
													<span>{{$ram_material->name}}</span>
												</li>
												@endforeach
											</ul>
										</div>
									</div>
								</div>
								<div class="rc-row">
									<div class="rc-f-selected-container">
										{{--
										<div class="mc-f-selected">
											<div>
												Бронзовый
											</div>
											<svg width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
												<rect width="10.4429" height="0.870241"
													transform="matrix(0.707093 -0.707121 0.707093 0.707121 0.00146484 7.38428)" fill="#1E2533"></rect>
												<rect width="10.4429" height="0.870241"
													transform="matrix(-0.707092 -0.707121 0.707092 -0.707121 7.38428 8)" fill="#1E2533"></rect>
											</svg>
										</div>
										<div class="mc-f-selected">
											<div>
												Дерево
											</div>
											<svg width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
												<rect width="10.4429" height="0.870241"
													transform="matrix(0.707093 -0.707121 0.707093 0.707121 0.00146484 7.38428)" fill="#1E2533"></rect>
												<rect width="10.4429" height="0.870241"
													transform="matrix(-0.707092 -0.707121 0.707092 -0.707121 7.38428 8)" fill="#1E2533"></rect>
											</svg>
										</div>
										--}}
									</div>
									<div class="mc-search">
										<div class="mc-input">
											<input id="ram_search" data-route="{{ route('hb.gallery.ram_search')}}" type="search" name="search_id" placeholder="@lang('gallery.search_sku')">
										</div>
										<button>
											<svg width="18" height="18" viewBox="0 0 18 18" fill="none"
												xmlns="http://www.w3.org/2000/svg">
												<g clip-path="url(#clip0_461_7169)">
													<path
														d="M18 17.2046L11.8996 11.1042C12.8959 9.92765 13.5 8.40895 13.5 6.75001C13.5 3.02783 10.4722 0 6.75001 0C3.02786 0 0 3.02783 0 6.75001C0 10.4722 3.02783 13.5 6.75001 13.5C8.40895 13.5 9.92765 12.8959 11.1042 11.8996L17.2046 18L18 17.2046ZM6.75001 12.375C3.64857 12.375 1.12501 9.85145 1.12501 6.75001C1.12501 3.64857 3.64857 1.12501 6.75001 1.12501C9.85145 1.12501 12.375 3.64857 12.375 6.75001C12.375 9.85145 9.85145 12.375 6.75001 12.375Z"
														fill="white" />
												</g>
												<defs>
													<clipPath id="clip0_461_7169">
														<rect width="18" height="18" fill="white" />
													</clipPath>
												</defs>
											</svg>
										</button>
									</div>
								</div>
								<div class="frame-block">
									<div class="frames-list">

										@foreach ($def_inter_rams as $ram)
                                            @php $ramSrc = '/storage/' . ltrim($ram->img, '/'); @endphp


											<div class="frame-item"
                                                 data-code="{{ $ram->id }}"
                                                 data-price="{{ $ram->price }}"
                                                 data-price-text="{{ $ram->price ? $ram->price . ' €' : '' }}"
                                                 data-material="{{ translated_value(optional($ram->material->first()), 'name') }}"
                                                 data-color="{{ translated_value(optional($ram->color->first()), 'name') }}"
                                                 data-width="{{ $ram->width ? $ram->width . ' cm' : '' }}"
                                                 data-height="{{ $ram->height ? $ram->height . ' cm' : '' }}"
                                                 data-src="{{ asset($ramSrc) }}"
                                                 data-ramprice="{{ $ram->price }}">
												<picture>
                                                    @if ($webpSrc = image_webp_url($ramSrc))
                                                        <source srcset="{{ $webpSrc }}" type="image/webp">
                                                    @endif
													<source srcset="{{ asset($ramSrc) }}" type="image/jpeg">
													<img width="150" height="150" src="{{ asset($ramSrc) }}"
														alt="Viar" loading="lazy">
												</picture>
												<div class="fi-info">
													<p>
														@lang("gallery.code"):
														<span>{{ $ram->id }}</span>
													</p>
													<b>+{{ $ram->price }} €</b>
												</div>
												<div class="frame-info">
													<ul>
														<li>
															<svg width="8" height="8" viewBox="0 0 8 8" fill="none"
																xmlns="http://www.w3.org/2000/svg">
																<g clip-path="url(#clip0_261_1144)">
																	<path
																		d="M7.90987 1.17996C7.78973 1.0598 7.5949 1.0598 7.47473 1.17996L2.48706 6.16764L0.525259 4.20583C0.405118 4.08565 0.210289 4.08565 0.0901174 4.20583C-0.0300391 4.32598 -0.0300391 4.5208 0.0901174 4.64097L2.26951 6.82036C2.38962 6.94047 2.58451 6.9405 2.70465 6.82036L7.90987 1.6151C8.03002 1.49493 8.03002 1.30012 7.90987 1.17996Z"
																		fill="#1F9750" />
																</g>
																<defs>
																	<clipPath id="clip0_261_1144">
																		<rect width="8" height="8" fill="white" />
																	</clipPath>
																</defs>
															</svg>
															<span>@lang('cart_new.in_stock')</span>
														</li>
														<li>
															<span>@lang("gallery.code"):</span>
															<span>{{ $ram->id }}</span>
														</li>
														@isset($ram->material->first()->name)
														<li>
															<span>@lang("gallery.material"):</span>
															<span>{{ translated_value($ram->material->first(), 'name') }}</span>
														</li>
														@endisset
														@isset($ram->color->first()->name)
														<li>
															<span>@lang("gallery.shade"):</span>
															<span>{{ translated_value($ram->color->first(), 'name') }}</span>
														</li>
														@endisset
														<li>
															<span>@lang("modular_pictures.index28"):</span>
															<span>{{ $ram->width }} cm</span>
														</li>
														<li>
															<span>@lang("modular_pictures.index30"):</span>
															<span>{{ $ram->height }} cm</span>
														</li>
														<li>
															<span>@lang("gallery.ram_total_price"):</span>
															<span>{{ $ram->price }} €</span>
														</li>
													</ul>
												</div>
													<div class="frame-icons">
														<span class="frame-info-zoom">
															<svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
																<circle cx="14" cy="14" r="14" fill="#FA7846" />
																<g clip-path="url(#clip0_877_8)">
																	<path d="M22.6 21.9106L17.313 16.6236C18.1764 15.604 18.7 14.2878 18.7 12.85C18.7 9.62412 16.0759 7 12.85 7C9.62414 7 7 9.62412 7 12.85C7 16.0759 9.62412 18.7 12.85 18.7C14.2878 18.7 15.604 18.1764 16.6236 17.313L21.9106 22.6L22.6 21.9106ZM12.85 17.725C10.1621 17.725 7.97501 15.5379 7.97501 12.85C7.97501 10.1621 10.1621 7.97501 12.85 7.97501C15.5379 7.97501 17.725 10.1621 17.725 12.85C17.725 15.5379 15.5379 17.725 12.85 17.725Z" fill="white" />
																	<rect width="6.26573" height="0.68813" transform="matrix(-2.0104e-05 -1 1 2.0104e-05 12.4766 15.9268)" fill="white" />
																	<rect width="6.26574" height="0.658255" transform="matrix(-1 -2.03991e-05 -2.05255e-05 -1 15.9277 13.0557)" fill="white" />
																</g>
																<defs>
																	<clipPath id="clip0_877_8">
																		<rect width="15" height="15" fill="white" transform="translate(7 7)" />
																	</clipPath>
																</defs>
															</svg>
														</span>
														<span class="frame-info-trigger">
															<svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
																<circle cx="14" cy="14" r="14" fill="#FA7846" />
																<text x="14" y="19" text-anchor="middle" font-size="14" fill="#fff" font-family="Arial" font-weight="700">i</text>
															</svg>
														</span>
													</div>
												<a href="#" class="mf-popup frame-info-modal">
													@lang("gl.read_more")
												</a>
												<button type="button" class="frame-select-btn">@lang('pages.modular-generator.generate.step5.add')</button>
												</div>
											@endforeach

										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				<div class="rcc-view rcc-block hidden-block" data-block="2">
					<div class="rcc-view__inner">
						<div class="interior-select">
							<p>@lang('gallery.rep_inter'):</p>
							<div class="interior-item__slider_block">
								<div class="interior-item__slider">
									<div class="interiorSlider-wrapper swiper-wrapper">
										<div data-slider="0" data-size="250,300"
											data-interior="{{ asset(env('THEME') . 'images') }}/canvas/interio1.jpg"
											class="interiorSlide swiper-slide active">
											<div class="selected-icon">
												<svg width="12" height="9" viewBox="0 0 12 9" fill="none"
													xmlns="http://www.w3.org/2000/svg">
													<path
														d="M4.90391 8.96884C4.643 8.96884 4.3911 8.87105 4.19842 8.69487L0.337368 5.16245C-0.086973 4.77427 -0.114713 4.11735 0.275141 3.69409C0.665745 3.27307 1.32475 3.2447 1.74984 3.63213L4.83643 6.45688L10.1827 0.582689C10.5695 0.157186 11.23 0.125087 11.6574 0.510279C12.084 0.895472 12.117 1.55239 11.7293 1.97789L5.67762 8.62844C5.49019 8.83298 5.23003 8.9554 4.95189 8.96884C4.93539 8.96884 4.91965 8.96884 4.90391 8.96884Z"
														fill="white"></path>
												</svg>
											</div>
											<picture>
												 <source srcset="{{ asset(env('THEME') . 'images') }}/mcard/interio1.webp" type="image/webp">
												<source srcset="{{ asset(env('THEME') . 'images') }}/mcard/interio1.jpg" type="image/jpeg">
												<img src="{{ asset(env('THEME') . 'images') }}/mcard/interio1.jpg" loading="lazy" alt="Viar">
											</picture>
										</div>
										<div data-slider="1" class="interiorSlide swiper-slide"
											data-interior="{{ asset(env('THEME') . 'images') }}/canvas/interio2.jpg" data-size="300,400">
											<div class="selected-icon">
												<svg width="12" height="9" viewBox="0 0 12 9" fill="none"
													xmlns="http://www.w3.org/2000/svg">
													<path
														d="M4.90391 8.96884C4.643 8.96884 4.3911 8.87105 4.19842 8.69487L0.337368 5.16245C-0.086973 4.77427 -0.114713 4.11735 0.275141 3.69409C0.665745 3.27307 1.32475 3.2447 1.74984 3.63213L4.83643 6.45688L10.1827 0.582689C10.5695 0.157186 11.23 0.125087 11.6574 0.510279C12.084 0.895472 12.117 1.55239 11.7293 1.97789L5.67762 8.62844C5.49019 8.83298 5.23003 8.9554 4.95189 8.96884C4.93539 8.96884 4.91965 8.96884 4.90391 8.96884Z"
														fill="white"></path>
												</svg>
											</div>
											<picture>
												 <source srcset="{{ asset(env('THEME') . 'images') }}/mcard/interio2.webp" type="image/webp">
												<source srcset="{{ asset(env('THEME') . 'images') }}/mcard/interio2.jpg" type="image/jpeg">
												<img width="162" height="122" src="{{ asset(env('THEME') . 'images') }}/mcard/interio2.jpg"
													alt="Viar" loading="lazy">
											</picture>
										</div>
										<div data-slider="2" class="interiorSlide swiper-slide">
											<div class="selected-icon">
												<svg width="12" height="9" viewBox="0 0 12 9" fill="none"
													xmlns="http://www.w3.org/2000/svg">
													<path
														d="M4.90391 8.96884C4.643 8.96884 4.3911 8.87105 4.19842 8.69487L0.337368 5.16245C-0.086973 4.77427 -0.114713 4.11735 0.275141 3.69409C0.665745 3.27307 1.32475 3.2447 1.74984 3.63213L4.83643 6.45688L10.1827 0.582689C10.5695 0.157186 11.23 0.125087 11.6574 0.510279C12.084 0.895472 12.117 1.55239 11.7293 1.97789L5.67762 8.62844C5.49019 8.83298 5.23003 8.9554 4.95189 8.96884C4.93539 8.96884 4.91965 8.96884 4.90391 8.96884Z"
														fill="white"></path>
												</svg>
											</div>
											<picture>
												 <source srcset="{{ asset(env('THEME') . 'images') }}/mcard/interio3.webp" type="image/webp">
												<source srcset="{{ asset(env('THEME') . 'images') }}/mcard/interio3.jpg" type="image/jpeg">
												<img width="162" height="122" src="{{ asset(env('THEME') . 'images') }}/mcard/interio3.jpg"
													alt=" Viar" loading="lazy">
											</picture>
										</div>
										<div data-slider="3" class="interiorSlide swiper-slide">
											<div class="selected-icon">
												<svg width="12" height="9" viewBox="0 0 12 9" fill="none"
													xmlns="http://www.w3.org/2000/svg">
													<path
														d="M4.90391 8.96884C4.643 8.96884 4.3911 8.87105 4.19842 8.69487L0.337368 5.16245C-0.086973 4.77427 -0.114713 4.11735 0.275141 3.69409C0.665745 3.27307 1.32475 3.2447 1.74984 3.63213L4.83643 6.45688L10.1827 0.582689C10.5695 0.157186 11.23 0.125087 11.6574 0.510279C12.084 0.895472 12.117 1.55239 11.7293 1.97789L5.67762 8.62844C5.49019 8.83298 5.23003 8.9554 4.95189 8.96884C4.93539 8.96884 4.91965 8.96884 4.90391 8.96884Z"
														fill="white"></path>
												</svg>
											</div>
											<picture>
												 <source srcset="{{ asset(env('THEME') . 'images') }}/mcard/interio1.webp" type="image/webp">
												<source srcset="{{ asset(env('THEME') . 'images') }}/mcard/interio1.jpg" type="image/jpeg">
												<img width="162" height="122" src="{{ asset(env('THEME') . 'images') }}/mcard/interio1.jpg"
													loading="lazy" alt="Viar">
											</picture>
										</div>
										<div data-slider="4" class="interiorSlide swiper-slide">
											<div class="selected-icon">
												<svg width="12" height="9" viewBox="0 0 12 9" fill="none"
													xmlns="http://www.w3.org/2000/svg">
													<path
														d="M4.90391 8.96884C4.643 8.96884 4.3911 8.87105 4.19842 8.69487L0.337368 5.16245C-0.086973 4.77427 -0.114713 4.11735 0.275141 3.69409C0.665745 3.27307 1.32475 3.2447 1.74984 3.63213L4.83643 6.45688L10.1827 0.582689C10.5695 0.157186 11.23 0.125087 11.6574 0.510279C12.084 0.895472 12.117 1.55239 11.7293 1.97789L5.67762 8.62844C5.49019 8.83298 5.23003 8.9554 4.95189 8.96884C4.93539 8.96884 4.91965 8.96884 4.90391 8.96884Z"
														fill="white"></path>
												</svg>
											</div>
											<picture>
												 <source srcset="{{ asset(env('THEME') . 'images') }}/mcard/interio2.webp" type="image/webp">
												<source srcset="{{ asset(env('THEME') . 'images') }}/mcard/interio2.jpg" type="image/jpeg">
												<img width="162" height="122" src="{{ asset(env('THEME') . 'images') }}/mcard/interio2.jpg"
													alt="Viar" loading="lazy">
											</picture>
										</div>
										<div data-slider="5" class="interiorSlide swiper-slide">
											<div class="selected-icon">
												<svg width="12" height="9" viewBox="0 0 12 9" fill="none"
													xmlns="http://www.w3.org/2000/svg">
													<path
														d="M4.90391 8.96884C4.643 8.96884 4.3911 8.87105 4.19842 8.69487L0.337368 5.16245C-0.086973 4.77427 -0.114713 4.11735 0.275141 3.69409C0.665745 3.27307 1.32475 3.2447 1.74984 3.63213L4.83643 6.45688L10.1827 0.582689C10.5695 0.157186 11.23 0.125087 11.6574 0.510279C12.084 0.895472 12.117 1.55239 11.7293 1.97789L5.67762 8.62844C5.49019 8.83298 5.23003 8.9554 4.95189 8.96884C4.93539 8.96884 4.91965 8.96884 4.90391 8.96884Z"
														fill="white"></path>
												</svg>
											</div>
											<picture>
												 <source srcset="{{ asset(env('THEME') . 'images') }}/mcard/interio3.webp" type="image/webp">
												<source srcset="{{ asset(env('THEME') . 'images') }}/mcard/interio3.jpg" type="image/jpeg">
												<img width="162" height="122" src="{{ asset(env('THEME') . 'images') }}/mcard/interio3.jpg"
													alt="Viar" loading="lazy">
											</picture>
										</div>
									</div>
									<div class="swiper-button swiper-prev">
										<svg width="15" height="23" viewBox="0 0 15 23" fill="none"
											xmlns="http://www.w3.org/2000/svg">
											<path d="M13 21L3 11.5L13 2" stroke="#FA7846" stroke-width="3" />
										</svg>
									</div>
									<div class="swiper-button swiper-next">
										<svg width="15" height="23" viewBox="0 0 15 23" fill="none"
											xmlns="http://www.w3.org/2000/svg">
											<path d="M2 2L12 11.5L2 21" stroke="#FA7846" stroke-width="3" />
										</svg>
									</div>
								</div>
							</div>
						</div>
						<div class="rcc-size">
							<p>
								@lang('gallery.rep_rsten'):
							</p>
							<div class="range__box" style="display: block;">
								<input class="main__input--wall" type="number" placeholder="250 @lang('collage_new.sm')" min="250" max="300"
									name="wall_size" />
								<div class="range_box">
									<input type="range" class="zoomRange range-input" min="250" max="300" step="1" />
									<div class="changeSm">
										<div>
											<span class="js_s1">250</span> @lang('collage_new.sm')
										</div>
										<div>
											<span class="js_s2">300</span> @lang('collage_new.sm')
										</div>
									</div>
								</div>
							</div>
							<div class="range__box">
								<input class="main__input--wall" type="number" placeholder="300 @lang('collage_new.sm')" min="300" max="400"
									name="wall_size" />
								<div class="range_box">
									<input type="range" class="zoomRange range-input" min="300" max="400" step="1" />
									<div class="changeSm">
										<div>
											<span class="js_s1">300</span> @lang('collage_new.sm')
										</div>
										<div>
											<span class="js_s2">400</span> @lang('collage_new.sm')
										</div>
									</div>
								</div>
							</div>
							<div class="range__box">
								<input class="main__input--wall" type="number" placeholder="350 @lang('collage_new.sm')" min="350" max="400"
									name="wall_size" />
								<div class="range_box">
									<input type="range" class="zoomRange range-input" min="350" max="400" step="1" />
									<div class="changeSm">
										<div>
											<span class="js_s1">350</span> @lang('collage_new.sm')
										</div>
										<div>
											<span class="js_s2">400</span> @lang('collage_new.sm')
										</div>
									</div>
								</div>
							</div>
							<div class="range__box">
								<input class="main__input--wall" type="number" placeholder="250 @lang('collage_new.sm')" min="250" max="300"
									name="wall_size" />
								<div class="range_box">
									<input type="range" class="zoomRange range-input" min="250" max="300" step="1" />
									<div class="changeSm">
										<div>
											<span class="js_s1">250</span> @lang('collage_new.sm')
										</div>
										<div>
											<span class="js_s2">300</span> @lang('collage_new.sm')
										</div>
									</div>
								</div>
							</div>
							<div class="range__box">
								<input class="main__input--wall" type="number" placeholder="350 @lang('collage_new.sm')" min="350" max="400"
									name="wall_size" />
								<div class="range_box">
									<input type="range" class="zoomRange range-input" min="350" max="400" step="1" />
									<div class="changeSm">
										<div>
											<span class="js_s1">350</span> @lang('collage_new.sm')
										</div>
										<div>
											<span class="js_s2">400</span> @lang('collage_new.sm')
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="mcard-rowb" id="buy">
					<div class="mcard-price__block">
						<p>@lang("gallery.total"):</p>
						<div class="mcard-price">
							@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.custom_sizes_calc_cat', ['current_price' => true])
						</div>
					</div>
					<button class="mm-btn"
						data-recommendation-discount="{{ $recommendationDiscount ?? '' }}"
						data-item-id="{{ $item->id }}">
					@lang('portrait_buy_form.final_add_to_bask')
				</button>
				</div>
			</div>
			<div class="subcontent">
				<div class="subcats">
					<p>@lang("gallery.subcategories"):</p>
					<div class="subcats-inner">

						@foreach ($item->cats as $tag_item)
							<span><a href="{{ route('hb.gallery.category', ["type"=> $tag_item->getType(), "category"=> $tag_item->url]) }}">{{ translated_value($tag_item, 'name', $tag_item->name) }}</a></span>
						@endforeach

						@foreach ($item->get_style as $tag_item)
							<span><a href="{{ route('hb.gallery.style', ['alias' => $tag_item->alias]) }}">{{ translated_value($tag_item, 'name', $tag_item->name) }}</a></span>
						@endforeach

						@foreach ($item->get_age as $tag_item)
							<span><a href="{{ route('hb.gallery.age', ['alias' => $tag_item->alias]) }}">{{ translated_value($tag_item, 'name', $tag_item->name) }}</a></span>
						@endforeach

						@foreach ($item->get_genre as $tag_item)
							<span><a href="{{ route('hb.gallery.genre', ['alias' => $tag_item->alias]) }}">{{ translated_value($tag_item, 'name', $tag_item->name) }}</a></span>
						@endforeach

					</div>
				</div>
				<div class="limits">
					<p>@lang("gallery.ordering_steps_t1_5"):</p>
					<div class="mcard-rowm-inner">
						<div class="mcard-size js-mcard-size">
							<a>
								<svg width="15" height="15" viewBox="0 0 15 15" fill="none"
									xmlns="http://www.w3.org/2000/svg">
									<rect x="0.5" y="0.5" width="14" height="14" rx="7" fill="#FFFAF3"
										stroke="#FA7846" />
									<rect x="2.72656" y="2.72754" width="9.54545" height="9.54545" rx="4.77273"
										fill="#FC8C5F" />
								</svg>
								<span>
									{!! $AProductionTime->standart_text !!} <span>{{ $AProductionTime->standart_price }}€</span>
								</span>
								<svg width="13" height="8" viewBox="0 0 13 8" fill="none"
									xmlns="http://www.w3.org/2000/svg">
									<path
										d="M12.2184 0.137457C12.1316 0.0458193 12.0317 4.23244e-08 11.9188 4.72591e-08C11.8059 5.21938e-08 11.7061 0.0458193 11.6192 0.137457L6.5 5.53952L1.38076 0.137458C1.29392 0.0458198 1.19405 5.16054e-07 1.08116 5.20989e-07C0.968269 5.25924e-07 0.868403 0.0458198 0.781562 0.137458L0.130261 0.824744C0.0434205 0.916381 -3.05028e-07 1.02176 -2.99821e-07 1.14089C-2.94614e-07 1.26002 0.0434205 1.36541 0.130261 1.45705L6.2004 7.86254C6.28724 7.95418 6.38711 8 6.5 8C6.61289 8 6.71276 7.95418 6.7996 7.86254L12.8697 1.45705C12.9566 1.36541 13 1.26002 13 1.14089C13 1.02176 12.9566 0.916381 12.8697 0.824743L12.2184 0.137457Z"
										fill="#FC8C5F"></path>
								</svg>
							</a>
							<div class="filter-item--wrapper">
								<ul class="c-sizeCheck">
									<li class="active gallery-js-data" data-price="0" data-text="{!! $AProductionTime->standart_text !!}">
										<div class="checkbox-item">
											<label>
												<input type="checkbox" name="activities" checked="" class="js_time">
												<span class="radiomark"></span>
												<p>{!! $AProductionTime->standart_text !!} <span>{{ $AProductionTime->standart_price }}€</span></p>
											</label>
										</div>
									</li>
									<li class="gallery-js-data" data-price="{{ $AProductionTime->express_price }}" data-text="{!! $AProductionTime->express_text !!}">
										<div class="checkbox-item">
											<label>
												<input type="checkbox" name="activities" class="js_time">
												<span class="radiomark"></span>
												<p>{!! $AProductionTime->express_text !!} <span>{{ $AProductionTime->express_price }}€</span></p>
											</label>
										</div>
									</li>
								</ul>
							</div>
						</div>
						<div class="mcard-add">
							<a href="#" class="mc-deliver">
								<svg width="28" height="15" viewBox="0 0 28 15" fill="none"
									xmlns="http://www.w3.org/2000/svg">
									<path
										d="M5.30462 0.942134C5.19524 0.976314 4.98196 1.15698 4.9054 1.27417C4.86712 1.33276 4.82884 1.6892 4.81243 2.12866L4.78509 2.88549L3.71868 2.91479C3.13352 2.92944 2.64681 2.94409 2.63587 2.94897C2.62493 2.95385 2.58665 3.02709 2.54837 3.11499C2.44446 3.32983 2.51009 3.50561 2.73977 3.60815C2.90931 3.68627 3.13899 3.69116 5.67649 3.68139L8.43274 3.66674L8.56399 3.53002C8.72259 3.36401 8.73352 3.20776 8.5804 3.03686L8.47102 2.90991H7.07649H5.68743V2.29956V1.6892H12.9335H20.1796L20.1687 6.7771L20.1523 11.8699L16.964 11.8845C14.4429 11.8943 13.7757 11.8845 13.7538 11.8357C13.5023 11.1814 13.2726 10.8689 12.8241 10.5662C12.1132 10.0828 11.096 10.0095 10.2757 10.3855C9.77805 10.6101 9.32415 11.1033 9.12181 11.6257L9.02884 11.8699L7.36087 11.8845L5.68743 11.8943V10.7712V9.64819H6.42571C7.0929 9.64819 7.17493 9.63842 7.27337 9.55053C7.43743 9.40405 7.43743 9.14526 7.2679 8.98901C7.16399 8.89135 7.08743 8.89135 4.2054 8.89135H1.24681L1.11556 9.02807C0.956963 9.19409 0.946025 9.35034 1.09915 9.52124L1.20853 9.64819H3.01321H4.81243V11.0349C4.81243 12.324 4.8179 12.4265 4.91634 12.5339L5.02024 12.6511L7.03274 12.6755L9.03977 12.6999L9.1054 12.8953C9.35696 13.657 10.1062 14.2429 11.0195 14.3894C12.1187 14.5701 13.3491 13.9011 13.6609 12.9587L13.7538 12.6755H17.7241H21.689L21.7601 12.9197C21.9132 13.447 22.4218 13.9646 23.0398 14.2136C24.3304 14.7458 25.8288 14.1697 26.2991 12.9685L26.403 12.6999L26.9171 12.6755C27.4859 12.6462 27.6554 12.5828 27.8413 12.3191C27.9452 12.1726 27.9507 12.0896 27.9343 10.7224C27.9179 9.30639 27.9179 9.2771 27.7812 8.98901C27.7046 8.82788 27.0921 7.81713 26.4249 6.74292C25.1452 4.68725 24.9812 4.47729 24.5765 4.37963C24.4452 4.34545 23.7234 4.32592 22.7116 4.32592H21.0601L21.0437 2.84643C21.0273 1.39135 21.0273 1.37182 20.9015 1.21069C20.639 0.88354 21.2241 0.907954 12.9226 0.912837C8.77727 0.912837 5.34837 0.927485 5.30462 0.942134ZM24.4234 5.19995C24.6038 5.38549 26.9116 9.14526 26.9882 9.37475C27.0484 9.5603 27.0702 9.89721 27.0702 10.7566V11.8992L26.7366 11.8845L26.4085 11.8699L26.3155 11.6257C25.8999 10.532 24.664 9.92651 23.4335 10.2097C22.6406 10.3953 21.9679 10.9763 21.7382 11.6746C21.6726 11.8699 21.6726 11.8699 21.3609 11.8845L21.0546 11.8992V8.50073V5.10717H22.6952C24.232 5.10717 24.3413 5.11206 24.4234 5.19995ZM11.8616 10.9958C12.2007 11.0837 12.5835 11.3767 12.7531 11.6697C12.9116 11.9431 12.9445 12.4021 12.8296 12.7097C12.7093 13.032 12.414 13.3298 12.064 13.491C11.7249 13.6472 11.1507 13.6667 10.8116 13.5349C10.4945 13.4128 10.1445 13.0955 10.0077 12.8123C9.86009 12.4998 9.87102 11.9773 10.0351 11.6746C10.2648 11.2595 10.839 10.9275 11.3531 10.9226C11.4734 10.9177 11.7031 10.9519 11.8616 10.9958ZM24.571 11.0154C25.6101 11.3621 25.878 12.6121 25.0577 13.2712C24.7405 13.5251 24.4835 13.6179 24.0624 13.6179C23.2148 13.6228 22.5968 13.1101 22.5421 12.363C22.5148 11.9285 22.646 11.616 22.9906 11.3132C23.4171 10.9373 24.0077 10.8249 24.571 11.0154Z"
										fill="#FA7846"></path>
									<path
										d="M5.05918 4.53125C4.82402 4.65332 4.81855 4.69239 4.81309 5.92285V7.10938H4.15684C3.52793 7.10938 3.48965 7.11426 3.36387 7.23145C3.1834 7.3877 3.1834 7.6123 3.36387 7.76855L3.49512 7.89062H7.50918C11.4904 7.89062 11.5287 7.89062 11.6709 7.78809C11.8732 7.64648 11.8732 7.35352 11.6709 7.21191C11.5287 7.10938 11.474 7.10938 8.6084 7.10938H5.68809V5.93262V4.75586L5.52949 4.61426C5.35996 4.46289 5.23418 4.44336 5.05918 4.53125Z"
										fill="#FA7846"></path>
									<path
										d="M0.120585 5.1364C-0.0653521 5.25358 -0.0489458 5.60026 0.147929 5.74187C0.290117 5.83952 0.333867 5.8444 1.95809 5.82976L3.62059 5.81511L3.72449 5.69792C3.86668 5.54167 3.86121 5.29753 3.70262 5.16569C3.57684 5.05827 3.54402 5.05827 1.90887 5.05827C0.49246 5.05827 0.224492 5.06804 0.120585 5.1364Z"
										fill="#FA7846"></path>
								</svg>
								<span>@lang('gallery.photo_item_block_about_delivery')</span>
							</a>
							<a href="#" class="mc-payment">
								<svg width="20" height="20" viewBox="0 0 20 20" fill="none"
									xmlns="http://www.w3.org/2000/svg">
									<path
										d="M1.73828 3.36282C1.44141 3.40579 0.9375 3.64798 0.695312 3.86282C0.46875 4.06204 0.246094 4.39407 0.117188 4.72611L0.0195312 4.98001V9.99954V15.0191L0.117188 15.273C0.246094 15.605 0.46875 15.937 0.695312 16.1363C0.976562 16.3824 1.44531 16.5933 1.81641 16.6441C2.02344 16.6675 4.82812 16.6792 10.2148 16.6714L18.3008 16.6597L18.5547 16.5699C18.8711 16.4527 19.2891 16.1792 19.4727 15.9644C19.6523 15.7574 19.8125 15.4683 19.9062 15.1949C19.9766 14.9878 19.9805 14.7613 19.9805 9.99954C19.9805 5.23782 19.9766 5.01126 19.9062 4.80423C19.6836 4.14017 19.2148 3.67142 18.5469 3.42923L18.3008 3.33939L10.1367 3.33548C5.58984 3.33157 1.86719 3.34329 1.73828 3.36282ZM18.375 4.26126C18.8477 4.43704 19.1289 4.86673 19.168 5.46829L19.1914 5.81986H10H0.808594L0.828125 5.47611C0.859375 4.97611 1.02734 4.63236 1.35547 4.4097C1.70312 4.16751 1.08594 4.18314 9.98828 4.18314C18.1484 4.17923 18.1602 4.17923 18.375 4.26126ZM19.1406 7.49954V8.31986H10H0.859375V7.49954V6.67923H10H19.1406V7.49954ZM19.1719 12.0191L19.1602 14.8628L19.0586 15.0816C18.9297 15.3511 18.7109 15.5777 18.4531 15.7066L18.2617 15.8003H10H1.73828L1.54688 15.7066C1.28906 15.5777 1.07031 15.3511 0.941406 15.0816L0.839844 14.8628L0.828125 12.0191L0.816406 9.17923H10H19.1836L19.1719 12.0191Z"
										fill="#FA7846"></path>
									<path
										d="M15.1441 10.8594C14.7456 10.9609 14.3941 11.2656 14.2573 11.6289C14.1831 11.832 14.1753 11.918 14.187 12.5547C14.1988 13.2109 14.2066 13.2773 14.2925 13.4531C14.4214 13.7109 14.648 13.9297 14.9175 14.0586C15.1284 14.1563 15.1636 14.1602 15.8003 14.1602C16.3433 14.1602 16.5034 14.1484 16.6714 14.0898C16.9448 13.9922 17.2652 13.6953 17.3863 13.4297C17.4761 13.2344 17.48 13.1758 17.48 12.5C17.48 11.8242 17.4761 11.7656 17.3863 11.5703C17.273 11.3164 16.9448 11.0117 16.6909 10.918C16.4722 10.8359 15.398 10.7969 15.1441 10.8594ZM16.48 11.7422C16.6323 11.8516 16.6792 12.0273 16.6792 12.5C16.6792 13.2617 16.6167 13.3203 15.8511 13.3203C15.0347 13.3203 14.9995 13.2891 14.9995 12.5C14.9995 11.9492 15.0386 11.8086 15.2105 11.7227C15.2573 11.7031 15.5386 11.6836 15.8394 11.6836C16.2886 11.6797 16.4058 11.6914 16.48 11.7422Z"
										fill="#FA7846"></path>
									<path
										d="M2.70707 11.7258C2.48442 11.8351 2.44535 12.2023 2.64067 12.3859L2.74223 12.4797L5.36332 12.4914C7.73832 12.5031 8.00004 12.4953 8.10942 12.4406C8.3477 12.3156 8.39848 11.9836 8.20707 11.7922L8.09379 11.6789L5.44145 11.6828C3.69145 11.6828 2.76567 11.6984 2.70707 11.7258Z"
										fill="#FA7846"></path>
									<path
										d="M2.74609 13.3789C2.61719 13.4336 2.5 13.6094 2.5 13.75C2.5 13.8945 2.61719 14.0664 2.75391 14.125C2.94922 14.207 7.86719 14.207 8.0625 14.125C8.23047 14.0547 8.32031 13.9258 8.32031 13.75C8.32031 13.5742 8.23047 13.4453 8.0625 13.375C7.87109 13.293 2.92969 13.2969 2.74609 13.3789Z"
										fill="#FA7846"></path>
								</svg>
								<span> @lang('gallery.photo_item_block_about_pay')</span>
							</a>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>

@if($creepingLine && !$creepingLine->isEmpty())
<div class="marquee-container">
    <div class="marquee-inner">
        @foreach ($creepingLine as $line)
            <span class="marquee-text">{!! $line->text !!}</span>
        @endforeach

        @foreach ($creepingLine as $line)
            <span class="marquee-text">{!! $line->text !!}</span>
        @endforeach

    </div>
</div>
@endif

@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.item-card_part-about')
@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.item-card_part-order-stage')


<div class="new-painters">
	<div class="section-frame">
		<div class="new-painters__inner">
			<h2 class="paintners-title page-title">
                @if (Route::currentRouteName() == 'hb.gallery.item.single')
{{--                    @if ($h2_titles->other_work_h2!='')--}}
{{--                        {!! $h2_titles->other_work_h2 !!}--}}
{{--                    @else--}}
{{--                        @lang('gallery.rep_other_works')--}}
{{--                    @endif--}}
                @if ($type==4)
                    {{$artist_name}}  @lang('gallery.other_works_title')
                    @else
                        @lang('gallery.rep_other_works')
                    @endif
                @elseif (Route::currentRouteName() == 'home')
                    @lang('gallery.rep_other_works')
                @else
                    @lang('gallery.rep_other_works')
                @endif

            </h2>
			<div class="cats-slider">
				<div class="cats-slider__wrapper swiper-wrapper">

					@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.reproduction_one_item', ['items' => $similar_paintings, 'type' => 'reproduction', 'slide' => true])

				</div>
				<div class="swiper-button swiper-prev">
					<svg width="12" height="19" viewBox="0 0 12 19" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M11 18L2 9.5L11 1" stroke="#1E2533" stroke-width="2" />
					</svg>
				</div>
				<div class="swiper-button swiper-next">
					<svg width="12" height="19" viewBox="0 0 12 19" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M0.999999 0.999999L10 9.5L1 18" stroke="white" stroke-width="2" />
					</svg>
				</div>
			</div>
		</div>
	</div>
</div>


@foreach ($item->get_age as $tag_item)
<div class="pop-painters">
	<div class="section-frame">
		<div class="pop-painters__inner">
			<div class="paintners-title page-title">
				{{ translated_value($tag_item, 'name', $tag_item->name) }}
			</div>
			<div class="pp-list">
				<div class="pp-list-wrapper swiper-wrapper">
					<div class="cp-item swiper-slide">
						<div class="cp-item-inner">
							<div class="cp-item-inner-b">
								<a href="#" class="mm-btn">Смотреть</a>
							</div>
							<picture>
								 <source srcset="{{ asset(env('THEME') . 'images') }}/gallery/7.webp" type="image/webp">
								<source srcset="{{ asset(env('THEME') . 'images') }}/gallery/7.jpg" type="image/jpeg">
								<img width="433" height="583" src="{{ asset(env('THEME') . 'images') }}/gallery/7.jpg"
									alt="ViarCanvas" loading="lazy">
							</picture>
						</div>
						<p>Пейзаж</p>
					</div>
					<div class="cp-item swiper-slide">
						<div class="cp-item-inner">
							<div class="cp-item-inner-b">
								<a href="#" class="mm-btn">Смотреть</a>
							</div>
							<picture>
								 <source srcset="{{ asset(env('THEME') . 'images') }}/gallery/8.webp" type="image/webp">
								<source srcset="{{ asset(env('THEME') . 'images') }}/gallery/8.jpg" type="image/jpeg">
								<img width="433" height="583" src="{{ asset(env('THEME') . 'images') }}/gallery/8.jpg"
									alt="ViarCanvas" loading="lazy">
							</picture>
						</div>
						<p>Абстракция</p>
					</div>
					<div class="cp-item swiper-slide">
						<div class="cp-item-inner">
							<div class="cp-item-inner-b">
								<a href="#" class="mm-btn">Смотреть</a>
							</div>
							<picture>
								 <source srcset="{{ asset(env('THEME') . 'images') }}/gallery/7.webp" type="image/webp">
								<source srcset="{{ asset(env('THEME') . 'images') }}/gallery/7.jpg" type="image/jpeg">
								<img width="433" height="583" src="{{ asset(env('THEME') . 'images') }}/gallery/7.jpg"
									alt="ViarCanvas" loading="lazy">
							</picture>
						</div>
						<p>Пейзаж</p>
					</div>
					<div class="cp-item swiper-slide">
						<div class="cp-item-inner">
							<div class="cp-item-inner-b">
								<a href="#" class="mm-btn">Смотреть</a>
							</div>
							<picture>
								 <source srcset="{{ asset(env('THEME') . 'images') }}/gallery/8.webp" type="image/webp">
								<source srcset="{{ asset(env('THEME') . 'images') }}/gallery/8.jpg" type="image/jpeg">
								<img width="433" height="583" src="{{ asset(env('THEME') . 'images') }}/gallery/8.jpg"
									alt="ViarCanvas" loading="lazy">
							</picture>
						</div>
						<p>Абстракция</p>
					</div>
				</div>
				<div class="swiper-button swiper-prev">
					<svg width="12" height="19" viewBox="0 0 12 19" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M11 18L2 9.5L11 1" stroke="#1E2533" stroke-width="2" />
					</svg>
				</div>
				<div class="swiper-button swiper-next">
					<svg width="12" height="19" viewBox="0 0 12 19" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M0.999999 0.999999L10 9.5L1 18" stroke="white" stroke-width="2" />
					</svg>
				</div>
			</div>
			<a href="#" class="mm-btn">Смотреть все</a>
		</div>
	</div>
</div>
@break
@endforeach



@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.zpart_trybuy')

@if($viewedProducts)
<div class="similar">
	<div class="section-frame">
		<div class="similar-inner">
			<div class="similar-title page-title">
				@lang('gallery.rep_viewed_goods')
			</div>
			<div class="similar-slider">
				<div class="similar-slider--wrapper swiper-wrapper">

					@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.reproduction_one_item', ['items' => $viewedProducts, 'type' => 'reproduction', 'slide' => true])

				</div>
				<div class="swiper-button swiper-prev">
					<svg width="12" height="19" viewBox="0 0 12 19" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M11 18L2 9.5L11 1" stroke="#1E2533" stroke-width="2" />
					</svg>
				</div>
				<div class="swiper-button swiper-next">
					<svg width="12" height="19" viewBox="0 0 12 19" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M0.999999 0.999999L10 9.5L1 18" stroke="white" stroke-width="2" />
					</svg>
				</div>
			</div>
		</div>
	</div>
</div>
@endif

<style>
.mcard-size--open .filter-item--wrapper {
    display: block !important;
    position: static;
    opacity: 1;
    visibility: visible;
    transform: none;
    box-shadow: none;
	background: #FFFFFF;
    box-shadow: 4px 4px 20px rgba(30, 37, 51, 0.08);
	margin-top: 15px;
}

.mcard-size--open > a {
    pointer-events: none;
}

.frame-select-btn {
    display: block;
    width: 100%;
    margin-top: 10px;
    padding: 10px 12px;
    background: linear-gradient(90deg, #fe915f 0%, #f86f46 100%);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-weight: 700;
    text-align: center;
    cursor: pointer;
    transition: opacity 0.2s ease;
}

.frame-select-btn:hover {
    opacity: 0.9;
}

.frame-select-btn--remove {
    background: #fff;
    color: #fa7846;
    border: 1px solid #fa7846;
}

.frame-select-btn--remove:hover {
    background: #ffe9df;
}

.frame-info{
	top: 10px;
	right: 10px;
}

.faq{padding: 60px 0px 0px 0px}

</style>
@include(config('theme.resource') . 'pages.index.faq9')

@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.zpart_viarcanvas_is')
