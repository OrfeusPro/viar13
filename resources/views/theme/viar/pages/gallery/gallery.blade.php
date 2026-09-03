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
                <a href="{{ route('hb.gallery.index') }}" class="breadcrumbs__link">
                    <span>{{ trans('breadcrumbs.gallery') }}</span>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="gallery-main">
    <div class="section-frame">
        <div class="gs-slider">
            @foreach ($top_slides as $item)
            @php
                $slidePath = trim((string) parse_url((string) $item['link'], PHP_URL_PATH), '/');
                $slideType = strtolower((string) basename($slidePath));
                $slideLink = in_array($slideType, ['photo', 'module', 'reproduction'], true)
                    ? route('hb.gallery.module', ['type' => $slideType])
                    : storefront_url($item['link']);
            @endphp
            <div class="gallery-main__inner">
                <div class="gallery-main__content">

                    @if ($loop->first)
                        <h1 class="gallery-main__title page-title">

                            {!! $item['title'] !!}

                        </h1>
                    @else
                        <div class="gallery-main__title page-title">

                            {!! $item['title'] !!}

                        </div>
                    @endif


                    <hr>
                    <p>
                        {!! $item['text'] !!}
                    </p>
                </div>
                <div class="gallery-main__slider">
                    <div class="gs-inner">
                        <div class="g-m-slider">
                            <picture>
                                @if ($webpSrc = image_webp_url("storage/".$item->image))
                                    <source srcset="{{ $webpSrc }}" type="image/webp">
                                @endif
                                <source srcset="{{ Voyager::image($item->image) }}" type="image/jpeg">
                                <img width="575" height="530" src="{{ Voyager::image($item['image']) }}"
                                    @altAttrs($item, 'image', $item->image, null, 'ViarCanvas')>
                            </picture>
                            <a href="{{ $slideLink }}" class="g-btn">@lang('gallery.go_to_category')</a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

@if($creepingLine && !$creepingLine->isEmpty())
</div>
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
@endif
{{-- <div class="category-main">
    <div class="section-frame">
        <div class="category-main__inner">
            <div class="category-main__title-row">
                <div class="category-main__title page-title">
                    @lang('gallery.main_cat')
                </div>
                <p>@lang('gallery.main_cat_text')</p>
            </div>
            <div class="g-category-slider ccs-slider">
                <div class="g-category-slide">
                    <picture>
                        <source srcset="{{ asset(env('THEME') . 'images') }}/gallery/4.jpg" type="image/jpeg">
                        <img width="433" height="583" src="{{ asset(env('THEME') . 'images') }}/gallery/4.jpg"
                            alt="ViarCanvas">
                    </picture>
                    <div class="g-c-slide__inner">
                        <div class="gcs-title">
                            {!! $gallery['modc_title'] !!}
                        </div>
                            {!! $gallery['modc_text'] !!}
                        <a href="{{ route('hb.gallery.module', 'module') }}">{{ $gallery['modc_link_text'] }}</a>
                    </div>
                </div>
                <div class="g-category-slide">
                    <picture>
                        <source srcset="{{ asset(env('THEME') . 'images') }}/gallery/5.jpg" type="image/jpeg">
                        <img width="433" height="583" src="{{ asset(env('THEME') . 'images') }}/gallery/5.jpg"
                            alt="ViarCanvas">
                    </picture>
                    <div class="g-c-slide__inner">
                        <div class="gcs-title">
                            {!! $gallery['fotoc_title'] !!}
                        </div>
                        {!! $gallery['fotoc_text'] !!}
                        <a href="{{ route('hb.gallery.module', 'photo') }}">{{ $gallery['fotoc_link_text'] }}</a>
                    </div>
                </div>
                <div class="g-category-slide">
                    <picture>
                        <source srcset="{{ asset(env('THEME') . 'images') }}/gallery/6.jpg" type="image/jpeg">
                        <img width="433" height="583" src="{{ asset(env('THEME') . 'images') }}/gallery/6.jpg"
                            alt="ViarCanvas" loading="lazy">
                    </picture>
                    <div class="g-c-slide__inner">
                        <div class="gcs-title">
                            {!! $gallery['repr_title'] !!}
                        </div>
                        {!! $gallery['repr_text'] !!}
                        <a href="{{ route('hb.gallery.module', 'reproduction') }}">{{ $gallery['repr_link_text'] }}</a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div> --}}

<div class="category-main">
    <div class="section-frame">
        <div class="category-main__inner">
            <div class="category-main__title-row">
                <h2 class="category-main__title page-title">
                    @lang('gallery.main_cat')
                </h2>
                <p>@lang('gallery.main_cat_text')</p>
            </div>
            <div class="g-category-slider ccs-slider">

                    @foreach ($top_slides as $item)

                        @if($loop->iteration === 1)
                            <div class="g-category-slide">
                                <picture>
                                    @if ($webpSrc = image_webp_url("storage/".$item->main_category_image))
                                        <source srcset="{{ $webpSrc }}" type="image/webp">
                                    @endif
                                    <source srcset="{{ Voyager::image($item->main_category_image) }}" type="image/jpeg">
                                    <img width="433" height="583" src="{{ Voyager::image($item->main_category_image) }}"
                                        alt="ViarCanvas" loading="lazy">
                                </picture>
                                <div class="g-c-slide__inner">
                                    <div class="gcs-title">
                                        {!! $item['sub_cat_title'] !!}
                                    </div>
                                    {!! $item['main_category_text'] !!}
                                    <a href="{{ route('hb.gallery.module', 'photo') }}">@lang('gallery.go_to_category')</a>
                                </div>
                            </div>
                        @endif

                        @if($loop->iteration === 2)
                            <div class="g-category-slide">
                                <picture>
                                    @if ($webpSrc = image_webp_url("storage/".$item->main_category_image))
                                        <source srcset="{{ $webpSrc }}" type="image/webp">
                                    @endif
                                    <source srcset="{{ Voyager::image($item->main_category_image) }}" type="image/jpeg">
                                    <img width="433" height="583" src="{{ Voyager::image($item->main_category_image) }}"
                                        alt="ViarCanvas" loading="lazy">
                                </picture>
                                <div class="g-c-slide__inner">
                                    <div class="gcs-title">
                                        {!! $item['sub_cat_title'] !!}
                                    </div>
                                    {!! $item['main_category_text'] !!}
                                    <a href="{{ route('hb.gallery.module', 'module') }}">@lang('gallery.go_to_category')</a>
                                </div>
                            </div>
                        @endif

                        @if($loop->iteration === 3)
                            <div class="g-category-slide">
                                <picture>
                                    @if ($webpSrc = image_webp_url("storage/".$item->main_category_image))
                                        <source srcset="{{ $webpSrc }}" type="image/webp">
                                    @endif
                                    <source srcset="{{ Voyager::image($item->main_category_image) }}" type="image/jpeg">
                                    <img width="433" height="583" src="{{ Voyager::image($item->main_category_image) }}"
                                        alt="ViarCanvas" loading="lazy">
                                </picture>
                                <div class="g-c-slide__inner">
                                    <div class="gcs-title">
                                        {!! $item['sub_cat_title'] !!}
                                    </div>
                                    {!! $item['main_category_text'] !!}
                                    <a href="{{ route('hb.gallery.module', 'reproduction') }}">@lang('gallery.go_to_category')</a>
                                </div>
                            </div>
                        @endif
                    @endforeach


            </div>
        </div>
    </div>
</div>

@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.zpart_popular')

<div class="ellipse">
    <img alt="img" src="https://viarcanvas.com/theme/viar/images/icon/ellipse-whete.svg"
        decoding="async" loading="eager" height="99" width="1374">
</div>

@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.zpart_bestseller')
@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.zpart_trybuy')


<style>.faq{padding: 0px}</style>
@include(config('theme.resource') . 'pages.index.faq9')
@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.zpart_viarcanvas_is')

<h2 class="page-title">@lang('gallery.footer_h2')</h2>
