
{{-- dropdown menu --}}
<div class="vz-art menu">
    <div class="vz-art menu-frame" data-menu="1">
        <div class="vz-art section-frame">
            <nav class="vz-art menu-frame__content">
                <ul class="vz-art menu-link">
                    @if (isset($menu_items1) && $menu_items1)
                        @foreach ($menu_items1 as $item)
							@if($item->is_show)
								<li>
					<a href="{{ storefront_url($item->getTranslatedAttribute('link')) }}" data-link="{{ $loop->iteration }}">
										<span>{{ $item->getTranslatedAttribute('title') }}</span>
										<i class="fa-arrow-next"></i>
									</a>
								</li>
							@endif
                        @endforeach
                    @endif
                </ul>
                @if (isset($menu_items1) && $menu_items1)
                    @foreach ($menu_items1 as $item)
						@if($item->is_show)
							<div class="vz-art menu-list" data-link="{{ $loop->iteration }}">
								@if ($item['images'])
									@foreach (json_decode($item['images']) as $img)
										<a href="#" class="vz-art menu-item">
                                            @php
                                                $homeNewMenuImageSources = image_picture_sources($img, true);
                                            @endphp
											<picture>
                                                @if(!empty($homeNewMenuImageSources['src_webp']))
                                                    <source srcset="{{ $homeNewMenuImageSources['src_webp'] }}" type="image/webp">
                                                @endif
                                                @if(!empty($homeNewMenuImageSources['src']) && !empty($homeNewMenuImageSources['type']))
                                                    <source srcset="{{ $homeNewMenuImageSources['src'] }}" type="{{ $homeNewMenuImageSources['type'] }}">
                                                @endif
												<img class="lozad" src="{{ $homeNewMenuImageSources['src'] }}" loading="lazy" alt="">
											</picture>
										</a>
									@endforeach
								@endif
                                @if(isset($item['png']) && $item['png'])
                                <a href="#" class="vz-art menu-item">
                                    @php
                                        $homeNewMenuPngSources = image_picture_sources($item['png'], true);
                                    @endphp
                                    <picture>
                                        @if(!empty($homeNewMenuPngSources['src_webp']))
                                            <source srcset="{{ $homeNewMenuPngSources['src_webp'] }}" type="image/webp">
                                        @endif
                                        @if(!empty($homeNewMenuPngSources['src']) && !empty($homeNewMenuPngSources['type']))
                                            <source srcset="{{ $homeNewMenuPngSources['src'] }}" type="{{ $homeNewMenuPngSources['type'] }}">
                                        @endif
                                        <img class="lozad" src="{{ $homeNewMenuPngSources['src'] }}" loading="lazy" alt="">
                                    </picture>
                                </a>
                                @endif
							</div>
                        @endif
                    @endforeach
                @endif
            </nav>
        </div>

    </div>
    <div class="vz-art menu-frame" data-menu="2">
        <div class="vz-art section-frame">
            <nav class="vz-art menu-frame__content">
                <ul class="vz-art menu-link">
                    @if (isset($menu_items2) && $menu_items2)
                        @foreach ($menu_items2 as $item)
							@if($item->is_show)
								<li>
					<a href="{{ storefront_url($item->getTranslatedAttribute('link')) }}" data-link="{{ $loop->iteration }}">
										<span>{{ $item->getTranslatedAttribute('title') }}</span>
										<i class="fa-arrow-next"></i>
									</a>
								</li>
							@endif
                        @endforeach
                    @endif
                </ul>
                @if (isset($menu_items2) && $menu_items2)
                    @foreach ($menu_items2 as $item)
						@if($item->is_show)
							<div class="vz-art menu-list" data-link="{{ $loop->iteration }}">
								@if ($item['images'])
									@foreach (json_decode($item['images']) as $img)
										<a href="#" class="vz-art menu-item">
                                            @php
                                                $homeNewMenuImageSources = image_picture_sources($img, true);
                                            @endphp
											<picture>
                                                @if(!empty($homeNewMenuImageSources['src_webp']))
                                                    <source srcset="{{ $homeNewMenuImageSources['src_webp'] }}" type="image/webp">
                                                @endif
                                                @if(!empty($homeNewMenuImageSources['src']) && !empty($homeNewMenuImageSources['type']))
                                                    <source srcset="{{ $homeNewMenuImageSources['src'] }}" type="{{ $homeNewMenuImageSources['type'] }}">
                                                @endif
												<img class="lozad" src="{{ $homeNewMenuImageSources['src'] }}" loading="lazy" alt="">
											</picture>
										</a>
									@endforeach
								@endif
							</div>
                        @endif
                    @endforeach
                @endif
            </nav>
        </div>
    </div>
    <div class="vz-art menu-frame" data-menu="3">
        <div class="vz-art section-frame">
            <nav class="vz-art menu-frame__content">
                <ul class="vz-art menu-link">
                    @if (isset($menu_items3) && $menu_items3)
                        @foreach ($menu_items3 as $item)
							@if($item->is_show)
								<li>
					<a href="{{ storefront_url($item->getTranslatedAttribute('link')) }}" data-link="{{ $loop->iteration }}">
										<span>{{ $item->getTranslatedAttribute('title') }}</span>
										<i class="fa-arrow-next"></i>
									</a>
								</li>
							@endif
                        @endforeach
                    @endif
                </ul>
                @if (isset($menu_items3) && $menu_items3)
                    @foreach ($menu_items3 as $item)
						@if($item->is_show)
							<div class="vz-art menu-list" data-link="{{ $loop->iteration }}">
								@if ($item['images'])
									@foreach (json_decode($item['images']) as $img)
										<a href="#" class="vz-art menu-item">
                                            @php
                                                $homeNewMenuImageSources = image_picture_sources($img, true);
                                            @endphp
											<picture>
                                                @if(!empty($homeNewMenuImageSources['src_webp']))
                                                    <source srcset="{{ $homeNewMenuImageSources['src_webp'] }}" type="image/webp">
                                                @endif
                                                @if(!empty($homeNewMenuImageSources['src']) && !empty($homeNewMenuImageSources['type']))
                                                    <source srcset="{{ $homeNewMenuImageSources['src'] }}" type="{{ $homeNewMenuImageSources['type'] }}">
                                                @endif
												<img class="lozad" src="{{ $homeNewMenuImageSources['src'] }}" loading="lazy" alt="">
											</picture>
										</a>
									@endforeach
								@endif
							</div>
                        @endif
                    @endforeach
                @endif
            </nav>
        </div>
    </div>
</div>

{{-- enddropdown --}}

<div class="vz-art burge-menu">
    <div class="vz-art header-bar header-bar_burger">
        <a href="{{ route('home') }}" class="vz-art logo" aria-label="logo link">
            <img loading="lazy" src="{{ asset('images/logo.svg') }}" alt="@lang('settings.site_name')">
        </a>
        <div class="header-button">
            <a href="#" class="vz-art header-user" aria-label="user link">
                <i class="fa-user"></i>
            </a>
            <a href="#" class="vz-art header-cart" aria-label="cart link">
                <i class="fa-cart"></i>
            </a>
        </div>
        <div class="vz-art burger burger-close">
            <i class="fa-close"></i>
        </div>
    </div>
    <div class="vz-art burge-menu__list">
        <div class="vz-art burge-menu__item">
            <div class="h3_old">{{ trans('header_footer_new.header_col1_name') }}<i class="fa-arrow-down"></i>
            </div>
            @if (isset($menu_items1) && $menu_items1)
                <ul>
                    @foreach ($menu_items1 as $item)
						@if($item->is_show)
							<li>
								<a href="{{ storefront_url($item->getTranslatedAttribute('link')) }}">{{ $item->getTranslatedAttribute('title') }}</a>
							</li>
						@endif
                    @endforeach
                </ul>
            @endif
        </div>
        <div class="vz-art burge-menu__item">
            <div class="h3_old">{{ trans('header_footer_new.header_col2_name') }} <i class="fa-arrow-down"></i>
            </div>
            @if (isset($menu_items2) && $menu_items2)
                <ul>
                    @foreach ($menu_items2 as $item)
						@if($item->is_show)
							<li>
								<a href="{{ storefront_url($item->getTranslatedAttribute('link')) }}">{{ $item->getTranslatedAttribute('title') }}</a>
							</li>
						@endif
                    @endforeach
                </ul>
            @endif
        </div>
{{--        <div class="vz-art burge-menu__item">--}}
{{--            <div class="h3_old">{{ trans('header_footer_new.header_col3_name') }}1 <i class="fa-arrow-down"></i>--}}
{{--            </div>--}}
{{--            @if (isset($menu_items3) && $menu_items3)--}}
{{--                <ul>--}}
{{--                    @foreach ($menu_items3 as $item)--}}
{{--						@if($item->is_show)--}}
{{--							<li>--}}
{{--								<a href="{{ $item->getTranslatedAttribute('link') }}">{{ $item->getTranslatedAttribute('title') }}</a>--}}
{{--							</li>--}}
{{--						@endif--}}
{{--                    @endforeach--}}
{{--                </ul>--}}
{{--            @endif--}}
{{--        </div>--}}


        <div class="vz-art burge-menu__item">
		{{--<div><a href="{{ route('blog') }}">{{ trans('header_footer_new.header_col4_name') }}</a></div>--}}
            <div class="h3_old"><a class="mainl" href="{{ storefront_url('/page/contacts') }}">{{ trans('header_footer_new.contacts') }}</a></div>
        </div>
        <div class="vz-art burge-menu__item">
            <div class="h3_old"><a class="mainl" href="{{ route('sizesprices') }}">{{ trans('header_footer_new.prices') }}</a></div>
        </div>
        <div class="vz-art burge-menu__item">
            <div class="h3_old"><a class="mainl" href="{{ route('new_stocks') }}">{{ trans('header_footer_new.stocks') }}</a></div>
        </div>
    </div>

    {{-- <div class="vz-art language header-item_pc _spollers _one _esc new_lang">
                <p class="_spoller "><img class="lozad" loading="lazy" src="{{ asset('images/flag/' . app()->getLocale() . '.svg') }}"> {{ ucfirst(app()->getLocale()) }} <i class="fa-arrow-down"></i>
                </p>

                <ul class="ver1_langs">
                    @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                        @php
                            $cur_url = isset($localized_urls[$localeCode]) ? $localized_urls[$localeCode] : LaravelLocalization::getLocalizedURL($localeCode, null, [], true);
                            $cur_url_mod = strtok($cur_url, '?');
                        @endphp
                        <li>
                            <a rel="alternate" hreflang="{{ $localeCode }}" href="{{ $cur_url_mod }}">
                                <img class="lozad" src="{{ asset('images/flag/' . $localeCode . '.svg') }}" loading="lazy"> {{ $localeCode }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div> --}}
</div>
