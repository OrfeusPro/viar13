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
                <a href="{{ route('hb.gallery.module', $page->url) }}" class="breadcrumbs__link">
                    <span>{{ $page->name }}</span>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="reproduction">

    <div class="reproduction-main">
        <div class="section-frame">
            <div class="reproduction-inner">

                @foreach ($top_slides as $item)

                    <div class="reproduction-content">
                        @if ($loop->first)
                            <h1 class="reproduction-title">
                                {!! $item['sub_cat_title'] !!}
                            </h1>
                        @else
                            <div class="reproduction-title">
                                {!! $item['sub_cat_title'] !!}
                            </div>
                        @endif

                        {!! render_content_images($item['sub_cat_text']) !!}
                    </div>

                    @php $images = json_decode($item->sub_cat_image, true); @endphp
                    @if ($images)
                        @foreach ($images as $image)
                            @php $src = '/storage/' . $image; @endphp

                            <picture>
                                @if($webpSrc = image_webp_url($src))
                                    <source media="(max-width: 525px)" srcset="{{ $webpSrc }}" type="image/webp">
                                @endif
                                <source media="(max-width: 525px)" srcset="https://viarcanvas.com{{ $src }}" type="image/jpeg">
                                @if($webpSrc = image_webp_url($src))
                                    <source srcset="{{ $webpSrc }}" type="image/webp">
                                @endif
                                <source srcset="https://viarcanvas.com{{ $src }}" type="image/jpeg">
                                <img width="1350" height="381" src="https://viarcanvas.com{{ $src }}" alt="">
                            </picture>

                        @endforeach
                    @endif
                    <div></div>

                @endforeach

            </div>
        </div>
    </div>

    @if(isset($creepingLine) && $creepingLine && !$creepingLine->isEmpty())
    <div class="marquee-container mb-6" style="margin-top: 30px;">
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
    {{--
    <div class="reproduction-main">
        <div class="section-frame">
            <div class="reproduction-inner">
                <div class="reproduction-content">
                    <div class="reproduction-title">
                        {!! translated_value($category, 'name', $category->name) !!}
                    </div>
                        {!! translated_value($category, 'description', $category->description) !!}
                </div>
                <picture>
                    <source media="(max-width: 525px)" srcset="{{ asset(env('THEME') . 'images') }}/reproduction/1Min.jpg"
                        type="image/jpeg">
                    <source srcset="{{ asset(env('THEME') . 'images') }}/reproduction/1.jpg" type="image/jpeg">
                    <img width="1350" height="381" src="{{ asset(env('THEME') . 'images') }}/reproduction/1.jpg"
                        alt="">
                </picture>
                <div></div>
            </div>
        </div>
    </div>
    --}}

    @include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.reproduction_tabs')
</div>
<h2 class="bs-title page-title" >
    @lang("gallery.reproduction_gallery_title")
</h2>

<div class="rp-selected">
    <div class="section-frame">
        <div class="rp-selected__inner">
            @if($category)
            <div class="rp-s-title__row">
                <div class="rp-selected__title page-title">
                    {{ translated_value($category, 'name', $category->name) }}
                </div>
                <p> {{ translated_value($category, 'description', $category->description) }}</p>
            </div>
            @endif
            <div class="mc-top-row">
                <div class="mc-filter-box">
                    <div class="filter-item mc-js-filter">
                        <a href="#">
                            <span>@lang('cart.size')</span>
                            <svg width="13" height="8" viewBox="0 0 13 8" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M12.2184 0.137457C12.1316 0.0458193 12.0317 4.23244e-08 11.9188 4.72591e-08C11.8059 5.21938e-08 11.7061 0.0458193 11.6192 0.137457L6.5 5.53952L1.38076 0.137458C1.29392 0.0458198 1.19405 5.16054e-07 1.08116 5.20989e-07C0.968269 5.25924e-07 0.868403 0.0458198 0.781562 0.137458L0.130261 0.824744C0.0434205 0.916381 -3.05028e-07 1.02176 -2.99821e-07 1.14089C-2.94614e-07 1.26002 0.0434205 1.36541 0.130261 1.45705L6.2004 7.86254C6.28724 7.95418 6.38711 8 6.5 8C6.61289 8 6.71276 7.95418 6.7996 7.86254L12.8697 1.45705C12.9566 1.36541 13 1.26002 13 1.14089C13 1.02176 12.9566 0.916381 12.8697 0.824743L12.2184 0.137457Z"
                                    fill="#FC8C5F" />
                            </svg>
                        </a>
                        <div class="filter-item--wrapper filter-sizes">
                            <p>@lang('gallery.select_size'):</p>

                            @php
                                $list_sizes = [
                                    "30x40",
                                    "40x60",
                                    "50x70",
                                    "55x80",
                                    "60x90",
                                    "70x100",
                                    "80x120",
                                    "95x140"
                                ]
                            @endphp
                            <ul class="c-sizeCheck">
                                @foreach ($list_sizes as $item_size)
                                    <li @if($item_size."_over" == \Request::get('size') ?? "") class="active" @endif  @if($item_size."_smaller" == \Request::get('size') ?? "") class="active" @endif>
                                        <div class="checkbox-item">
                                            <label>
                                                <input type="checkbox" name="activities" @if($item_size."_over" == \Request::get('size') ?? "") checked @endif  @if($item_size."_smaller" == \Request::get('size') ?? "") checked @endif>
                                                <span class="checkmark"></span>
                                                <p>{{ explode("x",$item_size)[0] }}cm x {{ explode("x",$item_size)[1] }}cm</p>
                                            </label>
                                        </div>
                                        <div class="checkbox-settings">
                                            @if($category)
                                                <p @if($item_size."_over" == \Request::get('size') ?? "") class="active" @endif><a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'color'=> \Request::get('color') ?? "", 'size'=> $item_size."_over", 'order'=> \Request::get('order') ?? "" ]) }}">@lang("gallery.size_over")</a></p>
                                                <p @if($item_size."_smaller" == \Request::get('size') ?? "") class="active" @endif><a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'color'=> \Request::get('color') ?? "", 'size'=> $item_size."_smaller", 'order'=> \Request::get('order') ?? "" ]) }}">@lang("gallery.size_smaller")</a></p>
                                            @else
                                                <p @if($item_size."_over" == \Request::get('size') ?? "") class="active" @endif><a href="{{ route('hb.gallery.module', [$page->url, 'color'=> \Request::get('color') ?? "", 'size'=> $item_size."_over", 'order'=> \Request::get('order') ?? "" ]) }}">@lang("gallery.size_over")</a></p>
                                                <p @if($item_size."_smaller" == \Request::get('size') ?? "") class="active" @endif><a href="{{ route('hb.gallery.module', [$page->url, 'color'=> \Request::get('color') ?? "", 'size'=> $item_size."_smaller", 'order'=> \Request::get('order') ?? "" ]) }}">@lang("gallery.size_smaller")</a></p>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach

                            </ul>
                        </div>
                    </div>
                    <div class="filter-item mc-js-filter">
                        <a href="#">
                            <span>@lang('gallery.color')</span>
                            <svg width="13" height="8" viewBox="0 0 13 8" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M12.2184 0.137457C12.1316 0.0458193 12.0317 4.23244e-08 11.9188 4.72591e-08C11.8059 5.21938e-08 11.7061 0.0458193 11.6192 0.137457L6.5 5.53952L1.38076 0.137458C1.29392 0.0458198 1.19405 5.16054e-07 1.08116 5.20989e-07C0.968269 5.25924e-07 0.868403 0.0458198 0.781562 0.137458L0.130261 0.824744C0.0434205 0.916381 -3.05028e-07 1.02176 -2.99821e-07 1.14089C-2.94614e-07 1.26002 0.0434205 1.36541 0.130261 1.45705L6.2004 7.86254C6.28724 7.95418 6.38711 8 6.5 8C6.61289 8 6.71276 7.95418 6.7996 7.86254L12.8697 1.45705C12.9566 1.36541 13 1.26002 13 1.14089C13 1.02176 12.9566 0.916381 12.8697 0.824743L12.2184 0.137457Z"
                                    fill="#FC8C5F" />
                            </svg>
                        </a>
                        <div class="filter-item--wrapper filter-color">
                            <p>@lang('gallery.select_color'):</p>
                            <ul class="color-grid color">
                                @foreach ($colors as $color)
                                    @if($category)
                                        <li><a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'color'=> $color->id, 'size'=> \Request::get('size') ?? "", 'order'=> \Request::get('order') ?? "" ]) }}" name="{{ $color->id }}"><div style="background: {{ $color->color }}; height: 29px; border: 1px solid #f3f3f3"></div></a></li>
                                    @else
                                        <li><a href="{{ route('hb.gallery.module', [$page->url, 'color'=> $color->id, 'size'=> \Request::get('size') ?? "", 'order'=> \Request::get('order') ?? "" ]) }}" name="{{ $color->id }}"><div style="background: {{ $color->color }}; height: 29px; border: 1px solid #f3f3f3"></div></a></li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class="filter-item mb-filter mc-js-filter">
                        <a href="#">
                            <span>@lang("gallery.filters")</span>
                            <svg width="13" height="8" viewBox="0 0 13 8" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M12.2184 0.137457C12.1316 0.0458193 12.0317 4.23244e-08 11.9188 4.72591e-08C11.8059 5.21938e-08 11.7061 0.0458193 11.6192 0.137457L6.5 5.53952L1.38076 0.137458C1.29392 0.0458198 1.19405 5.16054e-07 1.08116 5.20989e-07C0.968269 5.25924e-07 0.868403 0.0458198 0.781562 0.137458L0.130261 0.824744C0.0434205 0.916381 -3.05028e-07 1.02176 -2.99821e-07 1.14089C-2.94614e-07 1.26002 0.0434205 1.36541 0.130261 1.45705L6.2004 7.86254C6.28724 7.95418 6.38711 8 6.5 8C6.61289 8 6.71276 7.95418 6.7996 7.86254L12.8697 1.45705C12.9566 1.36541 13 1.26002 13 1.14089C13 1.02176 12.9566 0.916381 12.8697 0.824743L12.2184 0.137457Z"
                                    fill="#FC8C5F" />
                            </svg>
                        </a>
                        <div class="filter-item--wrapper filter-color">
                            <div class="module-catalog__inner">
                                <ul>
                                    <li class="mc-js-list">
                                        <a href="#">
                                            <span>@lang('gallery.select_size')</span>
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M9.13746 3.78156C9.04582 3.8684 9 3.96827 9 4.08116C9 4.19406 9.04582 4.29392 9.13746 4.38076L14.5395 9.5L9.13746 14.6192C9.04582 14.7061 9 14.8059 9 14.9188C9 15.0317 9.04582 15.1316 9.13746 15.2184L9.82474 15.8697C9.91638 15.9566 10.0218 16 10.1409 16C10.26 16 10.3654 15.9566 10.457 15.8697L16.8625 9.7996C16.9542 9.71276 17 9.61289 17 9.5C17 9.38711 16.9542 9.28724 16.8625 9.2004L10.457 3.13026C10.3654 3.04342 10.26 3 10.1409 3C10.0218 3 9.91638 3.04342 9.82474 3.13026L9.13746 3.78156Z"
                                                    fill="#1E2533" />
                                            </svg>
                                        </a>
                                        <ul class="mc-subcat np-subcat">
                                            <ul class="c-sizeCheck">
                                                @foreach ($list_sizes as $item_size)
                                                <li @if($item_size."_over" == \Request::get('size') ?? "") class="active" @endif  @if($item_size."_smaller" == \Request::get('size') ?? "") class="active" @endif>
                                                    <div class="checkbox-item">
                                                        <label>
                                                            <input type="checkbox" name="activities" @if($item_size."_over" == \Request::get('size') ?? "") checked @endif  @if($item_size."_smaller" == \Request::get('size') ?? "") checked @endif>
                                                            <span class="checkmark"></span>
                                                            <p>{{ explode("x",$item_size)[0] }}cm x {{ explode("x",$item_size)[1] }}cm</p>
                                                        </label>
                                                    </div>
                                                    <div class="checkbox-settings">
                                                        @if($category)
                                                            <p @if($item_size."_smaller" == \Request::get('size') ?? "") class="active" @endif><a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'color'=> \Request::get('color') ?? "", 'size'=> $item_size."_smaller", 'order'=> \Request::get('order') ?? "" ]) }}">-</a></p>/
                                                            <p @if($item_size."_over" == \Request::get('size') ?? "") class="active" @endif><a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'color'=> \Request::get('color') ?? "", 'size'=> $item_size."_over", 'order'=> \Request::get('order') ?? "" ]) }}">+</a></p>
                                                        @else
                                                            <p @if($item_size."_smaller" == \Request::get('size') ?? "") class="active" @endif><a href="{{ route('hb.gallery.module', [$page->url, 'color'=> \Request::get('color') ?? "", 'size'=> $item_size."_smaller", 'order'=> \Request::get('order') ?? "" ]) }}">-</a></p>/
                                                            <p @if($item_size."_over" == \Request::get('size') ?? "") class="active" @endif><a href="{{ route('hb.gallery.module', [$page->url, 'color'=> \Request::get('color') ?? "", 'size'=> $item_size."_over", 'order'=> \Request::get('order') ?? "" ]) }}">+</a></p>
                                                        @endif
                                                    </div>
                                                </li>
                                                @endforeach

                                            </ul>
                                        </ul>
                                    </li>
                                    <li class="mc-js-list">
                                        <a href="#">
                                            <span>@lang('gallery.color')</span>
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M9.13746 3.78156C9.04582 3.8684 9 3.96827 9 4.08116C9 4.19406 9.04582 4.29392 9.13746 4.38076L14.5395 9.5L9.13746 14.6192C9.04582 14.7061 9 14.8059 9 14.9188C9 15.0317 9.04582 15.1316 9.13746 15.2184L9.82474 15.8697C9.91638 15.9566 10.0218 16 10.1409 16C10.26 16 10.3654 15.9566 10.457 15.8697L16.8625 9.7996C16.9542 9.71276 17 9.61289 17 9.5C17 9.38711 16.9542 9.28724 16.8625 9.2004L10.457 3.13026C10.3654 3.04342 10.26 3 10.1409 3C10.0218 3 9.91638 3.04342 9.82474 3.13026L9.13746 3.78156Z"
                                                    fill="#1E2533" />
                                            </svg>
                                        </a>
                                        <ul class="mc-subcat np-subcat">
                                            <ul class="color-grid">
                                                    @foreach ($colors as $color)
                                                        @if($category)
                                                            <li style="padding-right: 0px;"><a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'color'=> $color->id, 'size'=> \Request::get('size') ?? "", 'order'=> \Request::get('order') ?? "" ]) }}" name="{{ $color->id }}"><div style="background: {{ $color->color }}; height: 29px; border: 1px solid #f3f3f3; width: 100%;"></div></a></li>
                                                        @else
                                                            <li style="padding-right: 0px;"><a href="{{ route('hb.gallery.module', [$page->url, 'color'=> $color->id, 'size'=> \Request::get('size') ?? "", 'order'=> \Request::get('order') ?? "" ]) }}" name="{{ $color->id }}"><div style="background: {{ $color->color }}; height: 29px; border: 1px solid #f3f3f3:width: 100%;"></div></a></li>
                                                        @endif
                                                    @endforeach
                                            </ul>
                                        </ul>
                                    </li>
                                    <li class="mc-js-list">
                                        <a href="#">
                                            <span>@lang("gallery.forma")</span>
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M9.13746 3.78156C9.04582 3.8684 9 3.96827 9 4.08116C9 4.19406 9.04582 4.29392 9.13746 4.38076L14.5395 9.5L9.13746 14.6192C9.04582 14.7061 9 14.8059 9 14.9188C9 15.0317 9.04582 15.1316 9.13746 15.2184L9.82474 15.8697C9.91638 15.9566 10.0218 16 10.1409 16C10.26 16 10.3654 15.9566 10.457 15.8697L16.8625 9.7996C16.9542 9.71276 17 9.61289 17 9.5C17 9.38711 16.9542 9.28724 16.8625 9.2004L10.457 3.13026C10.3654 3.04342 10.26 3 10.1409 3C10.0218 3 9.91638 3.04342 9.82474 3.13026L9.13746 3.78156Z"
                                                    fill="#1E2533" />
                                            </svg>
                                        </a>
                                        <ul class="mc-subcat np-subcat">
                                            <div class="forms-grid">
                                                <div class="active">
                                                    <img src="{{ asset(env('THEME') . 'images') }}/reproduction/1.svg" alt="">
                                                </div>
                                                <div>
                                                    <img src="{{ asset(env('THEME') . 'images') }}/reproduction/2.svg" alt="">
                                                </div>
                                                <div>
                                                    <img src="{{ asset(env('THEME') . 'images') }}/reproduction/3.svg" alt="">
                                                </div>
                                                <div>
                                                    <img src="{{ asset(env('THEME') . 'images') }}/reproduction/4.svg" alt="">
                                                </div>
                                                <div>
                                                    <img src="{{ asset(env('THEME') . 'images') }}/reproduction/5.svg" alt="">
                                                </div>
                                                <div>
                                                    <img src="{{ asset(env('THEME') . 'images') }}/reproduction/6.svg" alt="">
                                                </div>
                                            </div>
                                        </ul>
                                    </li>
                                </ul>
                            </div>

                        </div>
                    </div>
                    <div class="filter-item mb-filter mc-js-filter pop-sort">
                        <a href="#">
                            <span id="isort2">
                                @if(\Request::get('order') == 'cheap')
                                    @lang("gallery.sort_by_price_low")
                                @elseif(\Request::get('order') == 'expensive')
                                    @lang("gallery.sort_by_price_high")
                                @elseif(\Request::get('order') == 'new')
                                    @lang("gallery.sort_by_new")
                                @else
                                    @lang("gallery.sort_by_popular")
                                @endif
                            </span>
                            <svg width="13" height="8" viewBox="0 0 13 8" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M12.2184 0.137457C12.1316 0.0458193 12.0317 4.23244e-08 11.9188 4.72591e-08C11.8059 5.21938e-08 11.7061 0.0458193 11.6192 0.137457L6.5 5.53952L1.38076 0.137458C1.29392 0.0458198 1.19405 5.16054e-07 1.08116 5.20989e-07C0.968269 5.25924e-07 0.868403 0.0458198 0.781562 0.137458L0.130261 0.824744C0.0434205 0.916381 -3.05028e-07 1.02176 -2.99821e-07 1.14089C-2.94614e-07 1.26002 0.0434205 1.36541 0.130261 1.45705L6.2004 7.86254C6.28724 7.95418 6.38711 8 6.5 8C6.61289 8 6.71276 7.95418 6.7996 7.86254L12.8697 1.45705C12.9566 1.36541 13 1.26002 13 1.14089C13 1.02176 12.9566 0.916381 12.8697 0.824743L12.2184 0.137457Z"
                                    fill="#FC8C5F" />
                            </svg>
                        </a>
                        <div class="filter-item--wrapper filter-sort">
                            <ul class="sort-it">
                                <li @if(\Request::get('order') == '') class="active" @endif>
                                    @if($category)
                                        <a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'search'=> \Request::get('search') ?? "", 'color'=> \Request::get('color') ?? "", 'size'=> \Request::get('size') ?? "", 'order'=> "" ]) }}">
                                    @else
                                        <a href="{{ route('hb.gallery.module', [$page->url, 'search'=> \Request::get('search') ?? "", 'color'=> \Request::get('color') ?? "", 'size'=> \Request::get('size') ?? "", 'order'=> ""]) }}">
                                    @endif
                                        <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <g class="clip0_309_246">
                                                <path
                                                    d="M14.8315 2.21218C14.6062 1.98689 14.2409 1.98689 14.0156 2.21218L4.66372 11.5641L0.985349 7.88568C0.760084 7.66036 0.394781 7.66036 0.169458 7.88568C-0.0558351 8.11097 -0.0558351 8.47625 0.169458 8.70157L4.25582 12.7879C4.48103 13.0131 4.84645 13.0132 5.07171 12.7879L14.8315 3.02807C15.0568 2.80275 15.0568 2.43748 14.8315 2.21218Z"
                                                    fill="#FA7846" />
                                            </g>
                                            <defs>
                                                <clipPath class="clip0_309_246">
                                                    <rect width="15" height="15" fill="white" />
                                                </clipPath>
                                            </defs>
                                        </svg>
                                        <span>@lang("gallery.sort_by_popular")</span>
                                    </a>
                                </li>
                                <li @if(\Request::get('order') == 'new') class="active" @endif>
                                    @if($category)
                                        <a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'search'=> \Request::get('search') ?? "", 'color'=> \Request::get('color') ?? "", 'size'=> \Request::get('size') ?? "", 'order'=> "new" ]) }}">
                                    @else
                                        <a href="{{ route('hb.gallery.module', [$page->url, 'search'=> \Request::get('search') ?? "", 'color'=> \Request::get('color') ?? "", 'size'=> \Request::get('size') ?? "", 'order'=> "new"]) }}">
                                    @endif
                                        <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <g class="clip0_309_246">
                                                <path
                                                    d="M14.8315 2.21218C14.6062 1.98689 14.2409 1.98689 14.0156 2.21218L4.66372 11.5641L0.985349 7.88568C0.760084 7.66036 0.394781 7.66036 0.169458 7.88568C-0.0558351 8.11097 -0.0558351 8.47625 0.169458 8.70157L4.25582 12.7879C4.48103 13.0131 4.84645 13.0132 5.07171 12.7879L14.8315 3.02807C15.0568 2.80275 15.0568 2.43748 14.8315 2.21218Z"
                                                    fill="#FA7846" />
                                            </g>
                                            <defs>
                                                <clipPath class="clip0_309_246">
                                                    <rect width="15" height="15" fill="white" />
                                                </clipPath>
                                            </defs>
                                        </svg>
                                        <span>@lang("gallery.sort_by_new")</span>
                                    </a>
                                </li>
                                <li @if(\Request::get('order') == 'cheap') class="active" @endif>
                                    @if($category)
                                        <a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'search'=> \Request::get('search') ?? "", 'color'=> \Request::get('color') ?? "", 'size'=> \Request::get('size') ?? "", 'order'=> "cheap" ]) }}">
                                    @else
                                        <a href="{{ route('hb.gallery.module', [$page->url, 'search'=> \Request::get('search') ?? "", 'color'=> \Request::get('color') ?? "", 'size'=> \Request::get('size') ?? "", 'order'=> "cheap"]) }}">
                                    @endif
                                        <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <g class="clip0_309_246">
                                                <path
                                                    d="M14.8315 2.21218C14.6062 1.98689 14.2409 1.98689 14.0156 2.21218L4.66372 11.5641L0.985349 7.88568C0.760084 7.66036 0.394781 7.66036 0.169458 7.88568C-0.0558351 8.11097 -0.0558351 8.47625 0.169458 8.70157L4.25582 12.7879C4.48103 13.0131 4.84645 13.0132 5.07171 12.7879L14.8315 3.02807C15.0568 2.80275 15.0568 2.43748 14.8315 2.21218Z"
                                                    fill="#FA7846" />
                                            </g>
                                            <defs>
                                                <clipPath class="clip0_309_246">
                                                    <rect width="15" height="15" fill="white" />
                                                </clipPath>
                                            </defs>
                                        </svg>
                                        <span>@lang("gallery.sort_by_price_low")</span>
                                    </a>
                                </li>
                                <li @if(\Request::get('order') == 'expensive') class="active" @endif>
                                    @if($category)
                                        <a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'search'=> \Request::get('search') ?? "", 'color'=> \Request::get('color') ?? "", 'size'=> \Request::get('size') ?? "", 'order'=> "expensive" ]) }}">
                                    @else
                                        <a href="{{ route('hb.gallery.module', [$page->url, 'search'=> \Request::get('search') ?? "", 'color'=> \Request::get('color') ?? "", 'size'=> \Request::get('size') ?? "", 'order'=> "expensive"]) }}">
                                    @endif
                                        <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <g class="clip0_309_246">
                                                <path
                                                    d="M14.8315 2.21218C14.6062 1.98689 14.2409 1.98689 14.0156 2.21218L4.66372 11.5641L0.985349 7.88568C0.760084 7.66036 0.394781 7.66036 0.169458 7.88568C-0.0558351 8.11097 -0.0558351 8.47625 0.169458 8.70157L4.25582 12.7879C4.48103 13.0131 4.84645 13.0132 5.07171 12.7879L14.8315 3.02807C15.0568 2.80275 15.0568 2.43748 14.8315 2.21218Z"
                                                    fill="#FA7846" />
                                            </g>
                                            <defs>
                                                <clipPath class="clip0_309_246">
                                                    <rect width="15" height="15" fill="white" />
                                                </clipPath>
                                            </defs>
                                        </svg>
                                        <span>@lang("gallery.sort_by_price_high")</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="mc-next-row">

                    <div class="filter-popular">
                        <p>@lang("gallery.sort_by"):</p>
                        <div class="pop-sort filter-item mc-js-filter">
                            <a href="#">
                                <span id="isort">
                                    @if(\Request::get('order') == 'cheap')
                                        @lang("gallery.sort_by_price_low")
                                    @elseif(\Request::get('order') == 'expensive')
                                        @lang("gallery.sort_by_price_high")
                                    @elseif(\Request::get('order') == 'new')
                                        @lang("gallery.sort_by_new")
                                    @else
                                        @lang("gallery.sort_by_popular")
                                    @endif
                                </span>
                                <svg width="13" height="8" viewBox="0 0 13 8" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M12.2184 0.137457C12.1316 0.0458193 12.0317 4.23244e-08 11.9188 4.72591e-08C11.8059 5.21938e-08 11.7061 0.0458193 11.6192 0.137457L6.5 5.53952L1.38076 0.137458C1.29392 0.0458198 1.19405 5.16054e-07 1.08116 5.20989e-07C0.968269 5.25924e-07 0.868403 0.0458198 0.781562 0.137458L0.130261 0.824744C0.0434205 0.916381 -3.05028e-07 1.02176 -2.99821e-07 1.14089C-2.94614e-07 1.26002 0.0434205 1.36541 0.130261 1.45705L6.2004 7.86254C6.28724 7.95418 6.38711 8 6.5 8C6.61289 8 6.71276 7.95418 6.7996 7.86254L12.8697 1.45705C12.9566 1.36541 13 1.26002 13 1.14089C13 1.02176 12.9566 0.916381 12.8697 0.824743L12.2184 0.137457Z"
                                        fill="#FC8C5F" />
                                </svg>
                            </a>
                            <div class="filter-item--wrapper filter-sort">
                                <p>@lang("gallery.sort_by"):</p>
                                <ul class="sort-it">
                                    <li @if(\Request::get('order') == '') class="active" @endif>
                                        @if($category)
                                            <a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'search'=> \Request::get('search') ?? "", 'color'=> \Request::get('color') ?? "", 'size'=> \Request::get('size') ?? "", 'order'=> "" ]) }}">
                                        @else
                                            <a href="{{ route('hb.gallery.module', [$page->url, 'search'=> \Request::get('search') ?? "", 'color'=> \Request::get('color') ?? "", 'size'=> \Request::get('size') ?? "", 'order'=> ""]) }}">
                                        @endif
                                            <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <g class="clip0_309_246">
                                                    <path
                                                        d="M14.8315 2.21218C14.6062 1.98689 14.2409 1.98689 14.0156 2.21218L4.66372 11.5641L0.985349 7.88568C0.760084 7.66036 0.394781 7.66036 0.169458 7.88568C-0.0558351 8.11097 -0.0558351 8.47625 0.169458 8.70157L4.25582 12.7879C4.48103 13.0131 4.84645 13.0132 5.07171 12.7879L14.8315 3.02807C15.0568 2.80275 15.0568 2.43748 14.8315 2.21218Z"
                                                        fill="#FA7846" />
                                                </g>
                                                <defs>
                                                    <clipPath class="clip0_309_246">
                                                        <rect width="15" height="15" fill="white" />
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                            <span>@lang("gallery.sort_by_popular")</span>
                                        </a>
                                    </li>
                                    <li @if(\Request::get('order') == 'new') class="active" @endif>
                                        @if($category)
                                            <a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'search'=> \Request::get('search') ?? "", 'color'=> \Request::get('color') ?? "", 'size'=> \Request::get('size') ?? "", 'order'=> "new" ]) }}">
                                        @else
                                            <a href="{{ route('hb.gallery.module', [$page->url, 'search'=> \Request::get('search') ?? "", 'color'=> \Request::get('color') ?? "", 'size'=> \Request::get('size') ?? "", 'order'=> "new"]) }}">
                                        @endif
                                            <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <g class="clip0_309_246">
                                                    <path
                                                        d="M14.8315 2.21218C14.6062 1.98689 14.2409 1.98689 14.0156 2.21218L4.66372 11.5641L0.985349 7.88568C0.760084 7.66036 0.394781 7.66036 0.169458 7.88568C-0.0558351 8.11097 -0.0558351 8.47625 0.169458 8.70157L4.25582 12.7879C4.48103 13.0131 4.84645 13.0132 5.07171 12.7879L14.8315 3.02807C15.0568 2.80275 15.0568 2.43748 14.8315 2.21218Z"
                                                        fill="#FA7846" />
                                                </g>
                                                <defs>
                                                    <clipPath class="clip0_309_246">
                                                        <rect width="15" height="15" fill="white" />
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                            <span>@lang("gallery.sort_by_new")</span>
                                        </a>
                                    </li>
                                    <li @if(\Request::get('order') == 'cheap') class="active" @endif>
                                        @if($category)
                                            <a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'search'=> \Request::get('search') ?? "", 'color'=> \Request::get('color') ?? "", 'size'=> \Request::get('size') ?? "", 'order'=> "cheap" ]) }}">
                                        @else
                                            <a href="{{ route('hb.gallery.module', [$page->url, 'search'=> \Request::get('search') ?? "", 'color'=> \Request::get('color') ?? "", 'size'=> \Request::get('size') ?? "", 'order'=> "cheap"]) }}">
                                        @endif
                                            <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <g class="clip0_309_246">
                                                    <path
                                                        d="M14.8315 2.21218C14.6062 1.98689 14.2409 1.98689 14.0156 2.21218L4.66372 11.5641L0.985349 7.88568C0.760084 7.66036 0.394781 7.66036 0.169458 7.88568C-0.0558351 8.11097 -0.0558351 8.47625 0.169458 8.70157L4.25582 12.7879C4.48103 13.0131 4.84645 13.0132 5.07171 12.7879L14.8315 3.02807C15.0568 2.80275 15.0568 2.43748 14.8315 2.21218Z"
                                                        fill="#FA7846" />
                                                </g>
                                                <defs>
                                                    <clipPath class="clip0_309_246">
                                                        <rect width="15" height="15" fill="white" />
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                            <span>@lang("gallery.sort_by_price_low")</span>
                                        </a>
                                    </li>
                                    <li @if(\Request::get('order') == 'expensive') class="active" @endif>
                                        @if($category)
                                            <a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'search'=> \Request::get('search') ?? "", 'color'=> \Request::get('color') ?? "", 'size'=> \Request::get('size') ?? "", 'order'=> "expensive" ]) }}">
                                        @else
                                            <a href="{{ route('hb.gallery.module', [$page->url, 'search'=> \Request::get('search') ?? "", 'color'=> \Request::get('color') ?? "", 'size'=> \Request::get('size') ?? "", 'order'=> "expensive"]) }}">
                                        @endif
                                            <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <g class="clip0_309_246">
                                                    <path
                                                        d="M14.8315 2.21218C14.6062 1.98689 14.2409 1.98689 14.0156 2.21218L4.66372 11.5641L0.985349 7.88568C0.760084 7.66036 0.394781 7.66036 0.169458 7.88568C-0.0558351 8.11097 -0.0558351 8.47625 0.169458 8.70157L4.25582 12.7879C4.48103 13.0131 4.84645 13.0132 5.07171 12.7879L14.8315 3.02807C15.0568 2.80275 15.0568 2.43748 14.8315 2.21218Z"
                                                        fill="#FA7846" />
                                                </g>
                                                <defs>
                                                    <clipPath class="clip0_309_246">
                                                        <rect width="15" height="15" fill="white" />
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                            <span>@lang("gallery.sort_by_price_high")</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            </div>



            <div class="mc-f-selected-container">

                @if(\Request::get('color'))
                    @foreach ($colors as $color)
                        @if(\Request::get('color') == $color->id)
                            @if($category)
                                {{-- <li><a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'color'=> $color->id, 'size'=> \Request::get('size') ?? "" ]) }}" name="{{ $color->id }}"><div style="background: {{ $color->color }}; height: 29px; border: 1px solid #f3f3f3"></div></a></li> --}}
                                <div class="mc-f-selected">
                                    <div  style="background: {{ $color->color }}; height: 29px; border: 1px solid #f3f3f3; width: 87%;">

                                    </div>
                                    <a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'size'=> \Request::get('size') ?? "", 'order'=> \Request::get('order') ?? "" ]) }}">
                                    <svg width="8" height="8" viewBox="0 0 8 8" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <rect width="10.4429" height="0.870241"
                                            transform="matrix(0.707093 -0.707121 0.707093 0.707121 0.00146484 7.38428)" fill="#1E2533" />
                                        <rect width="10.4429" height="0.870241"
                                            transform="matrix(-0.707092 -0.707121 0.707092 -0.707121 7.38428 8)" fill="#1E2533" />
                                    </svg>
                                    </a>
                                </div>
                            @else

                                <div class="mc-f-selected">
                                    <div  style="background: {{ $color->color }}; height: 29px; border: 1px solid #f3f3f3; width: 87%;">

                                    </div>
                                    <a href="{{ route('hb.gallery.module', [$page->url, 'size'=> \Request::get('size') ?? "", 'order'=> \Request::get('order') ?? "" ]) }}">
                                    <svg width="8" height="8" viewBox="0 0 8 8" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <rect width="10.4429" height="0.870241"
                                            transform="matrix(0.707093 -0.707121 0.707093 0.707121 0.00146484 7.38428)" fill="#1E2533" />
                                        <rect width="10.4429" height="0.870241"
                                            transform="matrix(-0.707092 -0.707121 0.707092 -0.707121 7.38428 8)" fill="#1E2533" />
                                    </svg>
                                    </a>
                                </div>

                                {{-- <li><a href="{{ route('hb.gallery.module', [$page->url, 'color'=> $color->id, 'size'=> \Request::get('size') ?? "" ]) }}" name="{{ $color->id }}"><div style="background: {{ $color->color }}; height: 29px; border: 1px solid #f3f3f3"></div></a></li> --}}
                            @endif
                        @endif
                    @endforeach
                @endif

                @if(\Request::get('size'))
                    @if($category)
                        <div class="mc-f-selected">
                            <div>
                                <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M3.41667 3.125H3.125V3.41667V6.33333H3.70833V4.12081L6.1271 6.53957L6.53957 6.1271L4.12081 3.70833H6.33333V3.125H3.41667ZM11.5833 3.125H11.875V3.41667V6.33333H11.2917V4.12081L8.8729 6.53957L8.46043 6.1271L10.8792 3.70833H8.66667V3.125H11.5833ZM11.875 11.875H11.5833H8.66667V11.2917H10.8792L8.46043 8.8729L8.8729 8.46043L11.2917 10.8792V8.66667H11.875V11.5833V11.875ZM3.41667 11.875H3.125V11.5833V8.66667H3.70833V10.8792L6.1271 8.46043L6.53957 8.8729L4.12081 11.2917H6.33333V11.875H3.41667Z"
                                        fill="#FA7846" />
                                </svg>
                                <span>{{ explode("x",\Request::get('size'))[0]}}x{{ explode("_", explode("x",\Request::get('size'))[1])[0]}} cm</span>
                            </div>
                            <a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'color'=> \Request::get('color') ?? "", 'order'=> \Request::get('order') ?? "" ]) }}">
                            <svg width="8" height="8" viewBox="0 0 8 8" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <rect width="10.4429" height="0.870241"
                                    transform="matrix(0.707093 -0.707121 0.707093 0.707121 0.00146484 7.38428)" fill="#1E2533" />
                                <rect width="10.4429" height="0.870241"
                                    transform="matrix(-0.707092 -0.707121 0.707092 -0.707121 7.38428 8)" fill="#1E2533" />
                            </svg>
                            </a>
                        </div>
                    @else

                        <div class="mc-f-selected">
                            <div>
                                <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M3.41667 3.125H3.125V3.41667V6.33333H3.70833V4.12081L6.1271 6.53957L6.53957 6.1271L4.12081 3.70833H6.33333V3.125H3.41667ZM11.5833 3.125H11.875V3.41667V6.33333H11.2917V4.12081L8.8729 6.53957L8.46043 6.1271L10.8792 3.70833H8.66667V3.125H11.5833ZM11.875 11.875H11.5833H8.66667V11.2917H10.8792L8.46043 8.8729L8.8729 8.46043L11.2917 10.8792V8.66667H11.875V11.5833V11.875ZM3.41667 11.875H3.125V11.5833V8.66667H3.70833V10.8792L6.1271 8.46043L6.53957 8.8729L4.12081 11.2917H6.33333V11.875H3.41667Z"
                                        fill="#FA7846" />
                                </svg>
                                <span>{{ explode("x",\Request::get('size'))[0]}}x{{ explode("_", explode("x",\Request::get('size'))[1])[0]}} cm</span>
                            </div>
                            <a href="{{ route('hb.gallery.module', [$page->url, 'color'=> \Request::get('color') ?? "", 'order'=> \Request::get('order') ?? "" ]) }}">
                            <svg width="8" height="8" viewBox="0 0 8 8" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <rect width="10.4429" height="0.870241"
                                    transform="matrix(0.707093 -0.707121 0.707093 0.707121 0.00146484 7.38428)" fill="#1E2533" />
                                <rect width="10.4429" height="0.870241"
                                    transform="matrix(-0.707092 -0.707121 0.707092 -0.707121 7.38428 8)" fill="#1E2533" />
                            </svg>
                            </a>
                        </div>
                        {{-- <li><a href="{{ route('hb.gallery.module', [$page->url, 'color'=> $color->id, 'size'=> \Request::get('size') ?? "" ]) }}" name="{{ $color->id }}"><div style="background: {{ $color->color }}; height: 29px; border: 1px solid #f3f3f3"></div></a></li> --}}
                    @endif
                @endif
            </div>
            <p class="mc-info">@lang("gallery.found"): <span>{{ $items->total() }}</span></p>
            <div class="rp-grid">
                @include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.reproduction_one_item', ['items' => $items, 'type' => $type])
            </div>

            <div class="blog__list-nav">
                <a href="#">
                    {{-- Смотреть больше --}}
                </a>

                @if (!is_array($items))
                    {{ $items->links('theme.viar.blog.paginate') }}
                @endif
            </div>

        </div>
    </div>
</div>

<div class="rp-why">
    <div class="section-frame">
        <div class="rp-why__inner">
            <div class="rp-why__main">
                <h2 class="rpw-subtitle">
                    @lang("gallery.reproduction_b2_t1")
                </h2>
                <div class="rpw-title">
                    @lang("gallery.reproduction_b2_t2")

                </div>
                <p>
                    @lang("gallery.reproduction_b2_t3")

                </p>
            </div>
            <div class="rp-why-content">
                <ul>
                    <li>
                        <p class="prw-li">
                            @lang("gallery.reproduction_b2_t4")

                        </p>
                        <p>@lang("gallery.reproduction_b2_t5")

                        </p>
                    </li>
                    <li>
                        <p class="prw-li">
                            @lang("gallery.reproduction_b2_t6")
                            </p>
                        <p>
                            @lang("gallery.reproduction_b2_t7")
                        </p>
                    </li>
                    <li>
                        <p class="prw-li">
                            @lang("gallery.reproduction_b2_t8")
                        </p>
                        <p>
                            @lang("gallery.reproduction_b2_t9")
                        </p>
                    </li>
                </ul>
            </div>
            <picture>
                <source srcset="{{ asset(env('THEME').'images') }}/reproduction/2min.webp" media="(max-width: 970px)" type="image/webp">
                <source srcset="{{ asset(env('THEME') . 'images') }}/reproduction/2min.jpg" media="(max-width: 970px)" type="image/jpeg">
                <source srcset="{{ asset(env('THEME').'images') }}/reproduction/2.webp" type="image/webp">
                <source srcset="{{ asset(env('THEME') . 'images') }}/reproduction/2.jpg" type="image/jpeg">
                <img width="1399" height="65" src="{{ asset(env('THEME') . 'images') }}/reproduction/2.jpg"
                    alt="Viar" loading="lazy">
            </picture>
        </div>
    </div>
</div>



@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.zpart_popular_painters')
@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.zpart_new_painters')
@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.zpart_bestseller')
@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.zpart_trybuy')

<style>.faq{padding: 0px}</style>
@include(config('theme.resource') . 'pages.index.faq9')
@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.zpart_viarcanvas_is')
<h2 class="bs-title page-title" >
    @lang('gallery.reproduction_footer_title')
</h2> <br>
