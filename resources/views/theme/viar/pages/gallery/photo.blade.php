<div class="br-object">
    <div class="section-frame">
        <div class="breadcrumbs breadcrumbs__block">
            <span class="breadcrumbs__item">
                <a href="{{ route('home') }}" class="breadcrumbs__link breadcrumbs__link_main">
                    <span>@lang('account.index1')</span>
                </a>
            </span>
            <svg xmlns="http://www.w3.org/2000/svg" width="6" height="11" viewBox="0 0 6 11" fill="none"
                class="img-svg breadcrumbs__arrow replaced-svg">
                <path
                    d="M0.24775 1.4341C0.180786 1.50106 0.147304 1.57807 0.147304 1.66513C0.147304 1.75218 0.180786 1.82919 0.24775 1.89615L4.1953 5.8437L0.247751 9.79124C0.180787 9.85821 0.147304 9.93522 0.147304 10.0223C0.147304 10.1093 0.180787 10.1863 0.247751 10.2533L0.749984 10.7555C0.816948 10.8225 0.893957 10.856 0.98101 10.856C1.06806 10.856 1.14507 10.8225 1.21204 10.7555L5.89284 6.07473C5.9598 6.00776 5.99329 5.93075 5.99329 5.8437C5.99329 5.75664 5.9598 5.67964 5.89284 5.61267L1.21204 0.931868C1.14507 0.864903 1.06806 0.831421 0.981009 0.831421C0.893956 0.831421 0.816947 0.864903 0.749983 0.931868L0.24775 1.4341Z"
                    fill="#FA7846"></path>
            </svg>
            <span class="breadcrumbs__item">
                <a href="{{ route('hb.gallery.index') }}" class="breadcrumbs__link breadcrumbs__link_main">
                    <span>{{ trans('breadcrumbs.gallery') }}</span>
                </a>
            </span>
            <svg xmlns="http://www.w3.org/2000/svg" width="6" height="11" viewBox="0 0 6 11" fill="none"
                class="img-svg breadcrumbs__arrow replaced-svg">
                <path
                    d="M0.24775 1.4341C0.180786 1.50106 0.147304 1.57807 0.147304 1.66513C0.147304 1.75218 0.180786 1.82919 0.24775 1.89615L4.1953 5.8437L0.247751 9.79124C0.180787 9.85821 0.147304 9.93522 0.147304 10.0223C0.147304 10.1093 0.180787 10.1863 0.247751 10.2533L0.749984 10.7555C0.816948 10.8225 0.893957 10.856 0.98101 10.856C1.06806 10.856 1.14507 10.8225 1.21204 10.7555L5.89284 6.07473C5.9598 6.00776 5.99329 5.93075 5.99329 5.8437C5.99329 5.75664 5.9598 5.67964 5.89284 5.61267L1.21204 0.931868C1.14507 0.864903 1.06806 0.831421 0.981009 0.831421C0.893956 0.831421 0.816947 0.864903 0.749983 0.931868L0.24775 1.4341Z"
                    fill="#FA7846"></path>
            </svg>
            <span class="breadcrumbs__item">
                <a href="{{ route('hb.gallery.module', $page->url) }}" class="breadcrumbs__link breadcrumbs__link_main">
                    <span>{{ $page->name }}</span>
                </a>
            </span>
            @if(!empty($category))
            <svg xmlns="http://www.w3.org/2000/svg" width="6" height="11" viewBox="0 0 6 11" fill="none"
                class="img-svg breadcrumbs__arrow replaced-svg">
                <path
                    d="M0.24775 1.4341C0.180786 1.50106 0.147304 1.57807 0.147304 1.66513C0.147304 1.75218 0.180786 1.82919 0.24775 1.89615L4.1953 5.8437L0.247751 9.79124C0.180787 9.85821 0.147304 9.93522 0.147304 10.0223C0.147304 10.1093 0.180787 10.1863 0.247751 10.2533L0.749984 10.7555C0.816948 10.8225 0.893957 10.856 0.98101 10.856C1.06806 10.856 1.14507 10.8225 1.21204 10.7555L5.89284 6.07473C5.9598 6.00776 5.99329 5.93075 5.99329 5.8437C5.99329 5.75664 5.9598 5.67964 5.89284 5.61267L1.21204 0.931868C1.14507 0.864903 1.06806 0.831421 0.981009 0.831421C0.893956 0.831421 0.816947 0.864903 0.749983 0.931868L0.24775 1.4341Z"
                    fill="#FA7846"></path>
            </svg>
            <span class="breadcrumbs__item">
                <a href="{{ route('hb.gallery.category', [$page->url, $category->url]) }}" class="breadcrumbs__link">
                    <span>{{ translated_value($category, 'name', $category->name) }}</span>
                </a>
            </span>
            @endif
        </div>

    </div>
</div>


<div class="photo-main">
    <div class="section-frame">
        <div class="photo-main__inner">


            @foreach ($top_slides as $item)

                <div class="photo-content">

                    @if ($loop->first)
                        <h1 class="photo-main__title">
                            {!! $item['sub_cat_title'] !!}
                        </h1>
                    @else
                        <div class="photo-main__title">
                            {!! $item['sub_cat_title'] !!}
                        </div>
                    @endif


                    {!! render_content_images($item['sub_cat_text']) !!}
                </div>
                <div class="photo-slider__wrapper">
                    <div class="photo-slider">
                        <div class="photo-slider-wrapper swiper-wrapper">

                            @php $images = json_decode($item->sub_cat_image, true); @endphp
                            @if ($images)
                                @foreach ($images as $image)
                                    @php $src = '/storage/' . $image; @endphp

                                    <div class="photo-slider-item swiper-slide">
                                        <picture>
                                            @if($webpSrc = image_webp_url($src))
                                                <source srcset="{{ $webpSrc }}" type="image/webp">
                                            @endif
                                            <source srcset="https://viarcanvas.com{{ $src }}" type="image/jpeg">
                                            <img width="430" height="450" src="https://viarcanvas.com{{ $src }}" alt="ViarCanvas">
                                        </picture>
                                    </div>

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

            @endforeach



        </div>
    </div>
</div>

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
@endif

{{--
<div class="photo-main">
    <div class="section-frame">
        <div class="photo-main__inner">
            <div class="photo-content">
                <div class="photo-main__title">
                    {!! translated_value($category, 'name', $category->name) !!}
                </div>
                {!! translated_value($category, 'description', $category->description) !!}
            </div>

            <div class="photo-slider__wrapper">
                <div class="photo-slider">
                    <div class="photo-slider-wrapper swiper-wrapper">

                        @php $images = json_decode($category->images_slider, true); @endphp
                        @if ($images)
                            @foreach ($images as $image)
                                @php $src = '/storage/' . $image; @endphp

                                <div class="photo-slider-item swiper-slide">
                                    <picture>
                                        <source srcset="https://viarcanvas.com{{ $src }}" type="image/jpeg">
                <img width="430" height="450" src="https://viarcanvas.com{{ $src }}" alt="{!! translated_value($category, 'name', $category->name) !!}">
                                    </picture>
                                </div>
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

        </div>
    </div>
</div>
--}}


<h2 class="bs-title page-title" >
    @lang("gallery.photo_gallery_title")
</h2>
@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.zpart_catalog', ['category' => $category])

<div class="rp-why photo-rp-why">
    <div class="section-frame">
        <div class="rp-why__inner">
            <div class="rp-why__main">
                <h2 class="rpw-subtitle">
                    @lang("gallery.photo_b2_t1")
                </h2>
                <div class="rpw-title">
                    @lang("gallery.photo_b2_t2")
                </div>
                <p>
                    @lang("gallery.photo_b2_t3")
                </p>
            </div>
            <div class="rp-why-content">
                <p>
                    @lang("gallery.photo_b2_t4")
                </p>
                <ul>
                    <li>
                        <p class="prw-li">
                            @lang("gallery.photo_b2_t5")

                        </p>
                        <p>
                            @lang("gallery.photo_b2_t6")
                        </p>
                    </li>
                    <li>
                        <p class="prw-li">
                            @lang("gallery.photo_b2_t7")

                        </p>
                        <p>
                            @lang("gallery.photo_b2_t8")
                        </p>
                    </li>
                    <li>
                        <p class="prw-li">
                            @lang("gallery.photo_b2_t9")
                        </p>
                        <p>
                            @lang("gallery.photo_b2_t10")
                        </p>
                    </li>
                </ul>
            </div>
            <picture>
                {{-- <source srcset="{{ asset(env('THEME').'images') }}/reproduction/5min.webp" media="(max-width: 970px)" type="image/webp"> --}}
                <source srcset="{{ asset(env('THEME') . 'images') }}/reproduction/5min.jpg" media="(max-width: 970px)"
                    type="image/jpeg">
                {{-- <source srcset="{{ asset(env('THEME').'images') }}/reproduction/5.webp" type="image/webp"> --}}
                <source srcset="{{ asset(env('THEME') . 'images') }}/reproduction/5.jpg" type="image/jpeg">
                <img width="1399" height="65" src="{{ asset(env('THEME') . 'images') }}/reproduction/5.jpg"
                    alt="Viar" loading="lazy">
            </picture>
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
<h2 class="bs-title page-title" >
    @lang('gallery.photo_footer_title')
</h2> <br>
