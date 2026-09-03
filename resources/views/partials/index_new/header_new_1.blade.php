<header class="vz-art vz-header header_1">
	<div class="vz-art section-frame">
		<div class="vz-art header-bar">
			<a href="{{ route('home') }}" class="vz-art logo">
				<img loading="lazy" src="{{ asset('img/icons/logo.svg') }}" width="170" height="68" alt="@lang('settings.site_name')" class="img-svg">
			</a>
			<ul class="vz-art header-social">
				<li>
					<a href="https://www.facebook.com/viarcanvas/" target="_blank" rel="noopener">
						<i class="fa-facebook"></i>
					</a>
				</li>
				<li>
					<a href="https://www.instagram.com/viarcanvas/" target="_blank" rel="noopener">
						<i class="fa-instagram"></i>
					</a>
				</li>
			</ul>
			<div class="header-new__wrapper">
				<nav class="vz-art header-menu header-item_pc">
					<ul>
						<li>
							<a href="#" class="vz-art js-drop-newmenu" data-menu="0">
								<span>{{ trans('header_footer_new.header_col1_name') }}</span>
								<i class="fa-arrow-down"></i>
							</a>
						</li>
						<li>
							<a href="#" class="vz-art js-drop-newmenu" data-menu="1">
								<span>{{ trans('header_footer_new.header_col2_name') }}</span>
								<i class="fa-arrow-down"></i>
							</a>
						</li>
						<li>
							<a href="#" class="vz-art js-drop-newmenu" data-menu="2">
								<span>{{ trans('header_footer_new.header_col3_name') }}</span>
								<i class="fa-arrow-down"></i>
							</a>
						</li>
						<li>
							{{-- <a href="{{ route('blog') }}">
								<span>{{ trans('header_footer_new.header_col4_name') }}</span>
							</a> --}}
							<a class="mainl" href="{{ storefront_url('/page/contacts') }}">
								<span>{{ trans('header_footer_new.contacts') }}</span>
							</a>
						</li>
						<li>
							<a class="mainl" href="{{ route('sizesprices') }}">
								<span>{{ trans('header_footer_new.prices') }}</span>
							</a>
						</li>
                        <li>
                            <a class="mainl" href="{{ route('new_stocks') }}">
                                <span>{{ trans('header_footer_new.stocks') }}</span>
                            </a>
                        </li>
					</ul>
				</nav>
				<div class="vz-art header-button">
					<a href="{{ route('account.index') }}"
						class="acc__link vz-art header-user @if (!Auth::check()) js-popup-login @endif"
						aria-label="user link">
						<i class="fa-user"></i>
					</a>
					<a href="{{ route('cart.index') }}" rel="nofollow" class="vz-art header-cart" aria-label="cart link">
						<i class="fa-cart"></i>
					</a>
				</div>


				<div class="vz-art language header-item_pc _spollers _one _esc">
					<p class="_spoller "><img class="lozad" loading="lazy"
							src="{{ asset('images/flag/' . app()->getLocale() . '.svg') }}"> {{ ucfirst(app()->getLocale()) }} <i
							class="fa-arrow-down"></i>
					</p>

					<ul class="ver1_langs">
						@foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
							@php
								$cur_url = isset($localized_urls[$localeCode]) ? $localized_urls[$localeCode] : LaravelLocalization::getLocalizedURL($localeCode, null, [], true);
								$cur_url_mod = strtok($cur_url, '?');
							@endphp
							<li>
								<a href="{{ $cur_url_mod }}">
									<img class="lozad" loading="lazy" src="{{ asset('images/flag/' . $localeCode . '.svg') }}">
									{{ $localeCode }}
								</a>
							</li>
						@endforeach
					</ul>
				</div>
				<div class="vz-art burger burger-open">
					<span></span>
					<span></span>
					<span></span>
				</div>
				<div class="header-new__drops">
					<div class="header-new__drop header-new-drop ">
						<div class="header-new-drop__wrapper">
							<div class="header-new-drop__list">
								@php
									$menu_def_img = '';
								@endphp

								@if (isset($menu_items1) && $menu_items1)
									@foreach ($menu_items1 as $item)
										@if ($item->is_show)
											@php
												if (isset($item['png']) && $item['png'] && !$menu_def_img) {
												$menu_def_img = Voyager::image($item['png']);

												}
										@endphp <a href="{{ storefront_url($item->getTranslatedAttribute('link')) }}" class="header-new-drop__item"
												@if (isset($item['png']) && $item['png']) data-img="{{ Voyager::image($item['png']) }}" @endif>
												{{ $item->getTranslatedAttribute('title') }}
											</a>
										@endif
									@endforeach
								@endif

							</div>
                            @if ($menu_def_img)
                                <div class="header-new-drop__img">
                                    <div class="header-new-drop__img--wrapper">
                                        <img class="js-img-drop lozad" src="{{ $menu_def_img }}" loading="lazy" alt="">
                                    </div>
                                </div>
                            @endif
						</div>
					</div>
					<div class="header-new__drop header-new-drop ">
						<div class="header-new-drop__wrapper">
							<div class="header-new-drop__list">
								@php
									$menu_def_img = '';
								@endphp
								@if (isset($menu_items2) && $menu_items2)
									@foreach ($menu_items2 as $item)
										@if ($item->is_show)
											@php
												if (isset($item['png']) && $item['png'] && !$menu_def_img) {
												$menu_def_img = Voyager::image($item['png']);
												}
											@endphp
											<a href="{{ storefront_url($item->getTranslatedAttribute('link')) }}" class="header-new-drop__item"
												@if (isset($item['png']) && $item['png']) data-img="{{ Voyager::image($item['png']) }}" @endif>
												{{ $item->getTranslatedAttribute('title') }}
											</a>
										@endif
									@endforeach
								@endif

							</div>
                            @if ($menu_def_img)
                                <div class="header-new-drop__img">
                                    <div class="header-new-drop__img--wrapper">
                                        <img class="js-img-drop lozad" src="{{ $menu_def_img }}" loading="lazy" alt="">
                                    </div>
                                </div>
                            @endif
						</div>
					</div>
					<div class="header-new__drop header-new-drop ">
						<div class="header-new-drop__wrapper">
							<div class="header-new-drop__list">
								@php
									$menu_def_img = '';
								@endphp
								@if (isset($menu_items3) && $menu_items3)
									@foreach ($menu_items3 as $item)
										@if ($item->is_show)
											@php
												if (isset($item['png']) && $item['png'] && !$menu_def_img) {
												$menu_def_img = Voyager::image($item['png']);
												}
											@endphp
											<a href="{{ storefront_url($item->getTranslatedAttribute('link')) }}" class="header-new-drop__item"
												@if (isset($item['png']) && $item['png']) data-img="{{ Voyager::image($item['png']) }}" @endif>
												{{ $item->getTranslatedAttribute('title') }}
											</a>
										@endif
									@endforeach
								@endif

							</div>
                            @if ($menu_def_img)
                                <div class="header-new-drop__img">
                                    <div class="header-new-drop__img--wrapper">
                                        <img class="js-img-drop lozad" src="{{ $menu_def_img }}" loading="lazy" alt="">
                                    </div>
                                </div>
                            @endif
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</header>
