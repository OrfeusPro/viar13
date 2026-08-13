<header class="header">
    <div class="container header__container">
        <a href="{{ route('home') }}" class="header__logo">
            <img loading="lazy" src="{{ asset('img/icons/logo.svg') }}" alt="" class="img-svg lozad">
        </a>
        <ul class="header__social">
            <li>
                <a href="https://www.facebook.com/viarcanvas/" target="_blank" rel="noopener">
                    <img loading="lazy" src="{{ asset('img/icons/facebook.svg') }}" alt="" class="img-svg lozad">
                </a>
            </li>
            <li>
                <a href="https://www.instagram.com/viarcanvas/" target="_blank" rel="noopener">
                    <img loading="lazy" src="{{ asset('img/icons/instagram.svg') }}" alt="" class="img-svg lozad">
                </a>
            </li>
        </ul>
        <nav class="header__nav">
            <ul class="header__list">
                <li>
                    <div class="header__list-item">
                        <div class="header__link">
                            <span>{{ trans('header_footer_new.header_col1_name') }}</span>
                            <img loading="lazy" src="{{ asset('img/icons/angle-down.svg') }}" alt="" class="img-svg lozad">
                        </div>
                        <div class="menu">
                            <div class="menu__block">
                                <div class="menu__product-tabs">
                                    @if ($menu_items1)
                                        @foreach ($menu_items1 as $item)
											@if($item->is_show)
											   <div class="menu__product-tab @if ($loop->first) menu__product-tab-first menu__product-tab--activeTab @else menu__product-tab-second @endif">
													<a href="{{ $item['link'] }}">{{ $item['title'] }}</a>
													<img loading="lazy" src="{{ asset('img/icons/angle-arrow.svg') }}" alt=""
														class="img-svg lozad">
												</div>
											@endif
                                        @endforeach
                                    @endif
                                    {{-- <div class="menu__product-tab menu__product-tab-second">
                                        <span>Фото коллажи</span>
                                        <img loading="lazy" src="img/icons/angle-arrow.svg" alt="" class="img-svg lozad">
                                    </div> --}}
                                </div>
                                <div class="menu__product-content">
                                    @if ($menu_items1)
                                        @foreach ($menu_items1 as $item)
											@if($item->is_show)
												<div class="menu__contentItem">
													@if ($item['images'])
														@foreach (json_decode($item['images']) as $img)
															<a href="#">
                                                                @php
                                                                    $menuProductImageSources = image_picture_sources($img, true);
                                                                @endphp
                                                                <picture>
                                                                    @if(!empty($menuProductImageSources['src_webp']))
                                                                        <source srcset="{{ $menuProductImageSources['src_webp'] }}" type="image/webp">
                                                                    @endif
                                                                    @if(!empty($menuProductImageSources['src']) && !empty($menuProductImageSources['type']))
                                                                        <source srcset="{{ $menuProductImageSources['src'] }}" type="{{ $menuProductImageSources['type'] }}">
                                                                    @endif
                                                                    <img loading="lazy" src="{{ $menuProductImageSources['src'] }}" alt="">
                                                                </picture>
															</a>
														@endforeach
													@endif
												</div>
											@endif
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                </li>
                <li>
                    <div class="header__list-item">
                        <div class="header__link">
                            <span>{{ trans('header_footer_new.header_col2_name') }}</span>
                            <img loading="lazy" src="{{ asset('img/icons/angle-down.svg') }}" alt="" class="img-svg lozad">
                        </div>
                        <div class="menu">
                            <div class="menu__block">
                                <div class="menu__product-tabs">
                                    @if ($menu_items2)
                                        @foreach ($menu_items2 as $item)
											@if($item->is_show)
												<div class="menu__product-tab @if ($loop->first) menu__product-tab-first menu__product-tab--activeTab @endif">
													<a href="{{ $item['link'] }}">{{ $item['title'] }}</a>
													<img loading="lazy" src="{{ asset('img/icons/angle-arrow.svg') }}" alt=""
														class="img-svg lozad">
												</div>
											@endif
                                        @endforeach
                                    @endif
                                </div>
                                <div class="menu__product-content">
                                    @if ($menu_items2)
                                        @foreach ($menu_items2 as $item)
											@if($item->is_show)
												<div class="menu__contentItem">
													@if ($item['images'])
														@foreach (json_decode($item['images']) as $img)
															<a href="#">
                                                                @php
                                                                    $menuProductImageSources = image_picture_sources($img, true);
                                                                @endphp
                                                                <picture>
                                                                    @if(!empty($menuProductImageSources['src_webp']))
                                                                        <source srcset="{{ $menuProductImageSources['src_webp'] }}" type="image/webp">
                                                                    @endif
                                                                    @if(!empty($menuProductImageSources['src']) && !empty($menuProductImageSources['type']))
                                                                        <source srcset="{{ $menuProductImageSources['src'] }}" type="{{ $menuProductImageSources['type'] }}">
                                                                    @endif
                                                                    <img loading="lazy" src="{{ $menuProductImageSources['src'] }}" alt="">
                                                                </picture>
															</a>
														@endforeach
													@endif
												</div>
											@endif
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                </li>
                <li>
                    <div class="header__list-item">
                        <div class="header__link">
                            <span>{{ trans('header_footer_new.header_col3_name') }}</span>
                            <img loading="lazy" src="{{ asset('img/icons/angle-down.svg') }}" alt="" class="img-svg lozad">
                        </div>
                        <div class="menu">
                            <div class="menu__block">
                                <div class="menu__product-tabs">
                                    @if ($menu_items3)
                                        @foreach ($menu_items3 as $item)
											@if($item->is_show)
												<div class="menu__product-tab @if ($loop->first) menu__product-tab-first menu__product-tab--activeTab @endif">
													<a href="{{ $item['link'] }}">{{ $item['title'] }}</a>
													<img loading="lazy" src="{{ asset('img/icons/angle-arrow.svg') }}" alt=""
														class="img-svg lozad">
												</div>
											@endif
                                        @endforeach
                                    @endif
                                </div>
                                <div class="menu__product-content">
                                    @if ($menu_items3)
                                        @foreach ($menu_items3 as $item)
											@if($item->is_show)
												<div class="menu__contentItem">
													@if ($item['images'])
														@foreach (json_decode($item['images']) as $img)
															<a href="#">
                                                                @php
                                                                    $menuProductImageSources = image_picture_sources($img, true);
                                                                @endphp
                                                                <picture>
                                                                    @if(!empty($menuProductImageSources['src_webp']))
                                                                        <source srcset="{{ $menuProductImageSources['src_webp'] }}" type="image/webp">
                                                                    @endif
                                                                    @if(!empty($menuProductImageSources['src']) && !empty($menuProductImageSources['type']))
                                                                        <source srcset="{{ $menuProductImageSources['src'] }}" type="{{ $menuProductImageSources['type'] }}">
                                                                    @endif
                                                                    <img loading="lazy" class="lozad" src="{{ $menuProductImageSources['src'] }}" alt="">
                                                                </picture>
															</a>
														@endforeach
													@endif
												</div>
											@endif
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                </li>
                <li>
                    <a href="{{ route('blog') }}">
                        <div class="header__link">
                            <span>{{ trans('header_footer_new.header_col4_name') }}</span>
                        </div>
                    </a>
                </li>
            </ul>
        </nav>
        <ul class="header__buttons">
            <li>
                @if (!Auth::check())
                    <a href="javascript:void(0)" class="office off">
                        <div class="header__btn">
                            <img loading="lazy" src="{{ asset('img/icons/user.svg') }}" alt="" class="img-svg lozad">
                        </div>
                    </a>
                @else
                    <a href="{{ route('account.index') }}"
                        class="office">
                        <div class="header__btn">
                            <img loading="lazy" src="{{ asset('img/icons/user.svg') }}" alt="" class="img-svg lozad">
                        </div>
                    </a>
                @endif

            </li>
            <li>
                <a href="{{ route('cart.index') }}" rel="nofollow">
                    <div class="header__btn">
                        <img loading="lazy" src="{{ asset('img/icons/cart.svg') }}" alt="" class="img-svg lozad">
                    </div>
                </a>
            </li>
        </ul>

        <div class="language header-item_pc _spollers _one _esc">
            <p class="_spoller ">
                <img loading="lazy" class="lozad" src="{{ asset('images/flag/' . app()->getLocale() . '.svg') }}"
                    alt="{{ ucfirst(app()->getLocale()) }}"> {{ ucfirst(app()->getLocale()) }} <i
                    class="fa-arrow-down"></i>
            </p>
            <ul>
                @foreach ($locales as $loc)
                    <li>
                        @if(Route::is('home') )
                            <a href="/{{ $loc['prefix'] }}">
                                <img loading="lazy" class="lozad" src="{{ asset('images/flag/' . $loc['prefix'] . '.svg') }}"
                                    alt="{{ $loc['prefix'] }}"> {{ $loc['prefix'] }}
                            </a>
                        @else
                            <a href="/{{ $loc['prefix'] }}/{{ collect(request()->segments())->last() }}">
                                <img loading="lazy" class="lozad" src="{{ asset('images/flag/' . $loc['prefix'] . '.svg') }}"
                                    alt="{{ $loc['prefix'] }}"> {{ $loc['prefix'] }}
                            </a>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>

        <button class="header__humburger">
            <img loading="lazy" src="{{ asset('img/icons/burger.svg') }}" alt="" class="img-svg lozad">
        </button>
        <button class="header__close">
            <img loading="lazy" src="{{ asset('img/icons/cancel.svg') }}" alt="" class="img-svg lozad">
        </button>
    </div>
</header>
