@extends('layots.common')

@section('title',  $meta_item->meta_title)
@section('meta_desc', $meta_item->meta_description)

@section('og_tags')
    <meta property="og:title" content="{{ $meta_item->meta_title }}" />
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/main.min.css') }}" onload="this.media='all'">
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/gallery/gallery.css') }}" onload="this.media='all'">
    <link rel="stylesheet" type="text/css" href="{{ ver_asset('css/delivery.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ ver_asset('css/gallery.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ ver_asset(env('THEME').'style/gallery/gallery-media.css') }}" />
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/media.css') }}" media="all" />
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/overall/overall.css') }}" media="all" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/simplebar/5.3.0/simplebar.min.js" integrity="sha512-AS9rZZDdb+y4W2lcmkNGwf4swm6607XJYpNST1mkNBUfBBka8btA6mgRmhoFQ9Umy8Nj/fg5444+SglLHbowuA==" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/simplebar/5.3.0/simplebar.min.css" integrity="sha512-uZTwaYYhJLFXaXYm1jdNiH6JZ1wLCTVnarJza7iZ1OKQmvi6prtk85NMvicoSobylP5K4FCdGEc4vk1AYT8b9Q==" crossorigin="anonymous" />
    <style>
        h3:after {
            content: '';
            z-index: -1 !important;
        }
        .g-viar-title.page-title {
            text-align: start !important;
        }
        .delivery-section__intro {
            max-width: 980px;
            margin: 18px auto 34px;
            color: #6d7481;
            font-size: 18px;
            line-height: 1.7;
            text-align: center;
        }
        .delivery-section__intro p + p {
            margin-top: 12px;
        }
        .delivery-section__intro ul {
            margin: 16px auto 0;
            padding-left: 22px;
            text-align: left;
        }
        .delivery-section__intro li + li {
            margin-top: 8px;
        }
        @media screen and (max-width: 43.75em) {
            .delivery-section__intro {
                margin: 14px auto 26px;
                font-size: 15px;
                line-height: 1.6;
            }
        }
    </style>
@endsection

@section('content')
    <main id="top">
        <div class="br-object">
            <div class="section-frame">
                <div class="breadcrumbs">
                    <div class="breadcrumbs__block">
                        <a href="https://viarcanvas.com" class="breadcrumbs__link breadcrumbs__link_main">@lang('breadcrumbs.home')</a>
                        <svg xmlns="http://www.w3.org/2000/svg" width="6" height="11" viewBox="0 0 6 11" fill="none"
                             class="img-svg breadcrumbs__arrow replaced-svg">
                            <path
                                d="M0.24775 1.4341C0.180786 1.50106 0.147304 1.57807 0.147304 1.66513C0.147304 1.75218 0.180786 1.82919 0.24775 1.89615L4.1953 5.8437L0.247751 9.79124C0.180787 9.85821 0.147304 9.93522 0.147304 10.0223C0.147304 10.1093 0.180787 10.1863 0.247751 10.2533L0.749984 10.7555C0.816948 10.8225 0.893957 10.856 0.98101 10.856C1.06806 10.856 1.14507 10.8225 1.21204 10.7555L5.89284 6.07473C5.9598 6.00776 5.99329 5.93075 5.99329 5.8437C5.99329 5.75664 5.9598 5.67964 5.89284 5.61267L1.21204 0.931868C1.14507 0.864903 1.06806 0.831421 0.981009 0.831421C0.893956 0.831421 0.816947 0.864903 0.749983 0.931868L0.24775 1.4341Z"
                                fill="#FA7846"></path>
                        </svg>
                        <a href="{{ route('delivery_page') }}" class="breadcrumbs__link ">@lang('pages.delivery_title')</a>
                    </div>
                </div>
            </div>
        </div>
        @php
            /** @var \Illuminate\Support\Collection|\App\Models\SiteImage[] $siteImages */
            $siteImages = $siteImages ?? \App\Models\SiteImage::where('is_show', true)->get();

            $pair = function(string $deskKey, string $mobKey, string $deskDef, string $mobDef) use ($siteImages) {
                return [
                    'desk' => site_image($deskKey, $deskDef, ['collection' => $siteImages]),
                    'mob'  => site_image($mobKey,  $mobDef, ['collection' => $siteImages]),
                ];
            };

            $deliveryImages = [
                'b1'  => $pair('delivery_block_1_1', 'delivery_block_1_1_mob', 'images/delivery/b1.webp', 'images/delivery/b1Min.webp'),
                'a1'  => $pair('delivery_block_1_info', 'delivery_block_1_info_mob', 'images/delivery/a1.webp', 'images/delivery/a1Min.webp'),
                'i1'  => $pair('delivery_block_2_1', 'delivery_block_2_1_mob', 'images/delivery/i1.webp', 'images/delivery/i1Min.webp'),
                'i2'  => $pair('delivery_block_2_2', 'delivery_block_2_2_mob', 'images/delivery/i2.webp', 'images/delivery/i2Min.webp'),
                'i3'  => $pair('delivery_block_2_3', 'delivery_block_2_3_mob', 'images/delivery/i3.webp', 'images/delivery/i3Min.webp'),
                'a2'  => $pair('delivery_block_2_info', 'delivery_block_2_info_mob', 'images/delivery/a2.webp', 'images/delivery/a2Min.webp'),
                'b2'  => $pair('delivery_block_3_1', 'delivery_block_3_1_mob', 'images/delivery/b2.webp', 'images/delivery/b2Min.webp'),
                'a3'  => $pair('delivery_block_3_info', 'delivery_block_3_info_mob', 'images/delivery/a3.webp', 'images/delivery/a3Min.webp'),
                'i4'  => $pair('delivery_block_4_1', 'delivery_block_4_1_mob', 'images/delivery/i4.webp', 'images/delivery/i4Min.webp'),
                'i5'  => $pair('delivery_block_4_2', 'delivery_block_4_2_mob', 'images/delivery/i5.webp', 'images/delivery/i5Min.webp'),
                'i6'  => $pair('delivery_block_4_3', 'delivery_block_4_3_mob', 'images/delivery/i6.webp', 'images/delivery/i6Min.webp'),
                'a4'  => $pair('delivery_block_4_info', 'delivery_block_4_info_mob', 'images/delivery/a4.webp', 'images/delivery/a4Min.webp'),
            ];
        @endphp
        <section class="delivery-section">
            <div class="section-frame">
                <div class="section-inner">
                    <h1 class="page-title"><span class="orange">@lang('pages.delivery_title')</span></h1>
                    <div class="delivery-section__intro">{!! trans('pages.delivery_text') !!}</div>
                </div>
                <div class="delivery-section__inner">
                    <div class="delivery-section__block px-2">
                        <div class="title-m-row">
                            <div class="m-icon">
                                <picture>
                                    @if(!empty($deliveryImages['b1']['mob']['src_webp']))
                                        <source media="(max-width: 576px)" srcset="{{ $deliveryImages['b1']['mob']['src_webp'] }}" type="image/webp">
                                    @endif
                                    @if(!empty($deliveryImages['b1']['mob']['type']))
                                        <source media="(max-width: 576px)" srcset="{{ $deliveryImages['b1']['mob']['src'] }}" type="{{ $deliveryImages['b1']['mob']['type'] }}">
                                    @endif
                                    @if(!empty($deliveryImages['b1']['desk']['src_webp']))
                                        <source srcset="{{ $deliveryImages['b1']['desk']['src_webp'] }}" type="image/webp">
                                    @endif
                                    @if(!empty($deliveryImages['b1']['desk']['type']))
                                        <source srcset="{{ $deliveryImages['b1']['desk']['src'] }}" type="{{ $deliveryImages['b1']['desk']['type'] }}">
                                    @endif
                                    <img width="200" height="200" src="{{ $deliveryImages['b1']['desk']['src'] }}" alt="{{ $deliveryImages['b1']['desk']['alt'] ?? '' }}" title="{{ $deliveryImages['b1']['desk']['title'] ?? '' }}">
                                </picture>
                            </div>
                            <div class="title-block">

                                <div class="title-block">
                                    <div class="title-block__top"> <span>1.</span> <h2 style="display: inline-block" class="title-block__top"> @lang('pages.delivery_first_title') </h2> </div>
                                    <div class="text">  @lang('pages.delivery_first_text') </div> </div>
{{--                                @lang('pages.delivery_first_block_text')--}}

                            </div>
                        </div>
                        <div class="deadlines-grid">
                            <div class="deadlines-grid__item">
                                @lang('pages.delivery_first_block_box1')

                            </div>
                            <div class="deadlines-grid__item">
                                @lang('pages.delivery_first_block_box2')

                            </div>
                            <div class="deadlines-grid__item">
                                @lang('pages.delivery_first_block_box3')

                            </div>
                            <div class="deadlines-grid__item">
                                @lang('pages.delivery_first_block_box4')

                            </div>
                        </div>
                        <div class="block-object">
                            <picture>
                                @if(!empty($deliveryImages['a1']['mob']['src_webp']))
                                    <source media="(max-width: 576px)" srcset="{{ $deliveryImages['a1']['mob']['src_webp'] }}" type="image/webp">
                                @endif
                                @if(!empty($deliveryImages['a1']['mob']['type']))
                                    <source media="(max-width: 576px)" srcset="{{ $deliveryImages['a1']['mob']['src'] }}" type="{{ $deliveryImages['a1']['mob']['type'] }}">
                                @endif
                                @if(!empty($deliveryImages['a1']['desk']['src_webp']))
                                    <source srcset="{{ $deliveryImages['a1']['desk']['src_webp'] }}" type="image/webp">
                                @endif
                                @if(!empty($deliveryImages['a1']['desk']['type']))
                                    <source srcset="{{ $deliveryImages['a1']['desk']['src'] }}" type="{{ $deliveryImages['a1']['desk']['type'] }}">
                                @endif
                                <img width="236" height="224" src="{{ $deliveryImages['a1']['desk']['src'] }}" alt="{{ $deliveryImages['a1']['desk']['alt'] ?? '' }}" title="{{ $deliveryImages['a1']['desk']['title'] ?? '' }}">
                            </picture>
                        </div>
                    </div>
                    <div class="delivery-section__block px-1">
                        <div class="half-grid mob-reverse">
                            <div class="list-steps">
                                <div class="list-step">
                                    <div class="img">
                                        <picture>
                                            @if(!empty($deliveryImages['i1']['mob']['src_webp']))
                                                <source media="(max-width: 576px)" srcset="{{ $deliveryImages['i1']['mob']['src_webp'] }}" type="image/webp">
                                            @endif
                                            @if(!empty($deliveryImages['i1']['mob']['type']))
                                                <source media="(max-width: 576px)" srcset="{{ $deliveryImages['i1']['mob']['src'] }}" type="{{ $deliveryImages['i1']['mob']['type'] }}">
                                            @endif
                                            @if(!empty($deliveryImages['i1']['desk']['src_webp']))
                                                <source srcset="{{ $deliveryImages['i1']['desk']['src_webp'] }}" type="image/webp">
                                            @endif
                                            @if(!empty($deliveryImages['i1']['desk']['type']))
                                                <source srcset="{{ $deliveryImages['i1']['desk']['src'] }}" type="{{ $deliveryImages['i1']['desk']['type'] }}">
                                            @endif
                                            <img width="200" height="200" src="{{ $deliveryImages['i1']['desk']['src'] }}" alt="{{ $deliveryImages['i1']['desk']['alt'] ?? '' }}" title="{{ $deliveryImages['i1']['desk']['title'] ?? '' }}">
                                        </picture>
                                    </div>
                                    <div class="icon"><img width="24" height="24" src="/images/icon/check-circle.svg" alt=""> </div>
                                    @lang('pages.delivery_second_block_box1')
                                </div>
                                <div class="list-step">
                                    <div class="img">
                                        <picture>
                                            @if(!empty($deliveryImages['i2']['mob']['src_webp']))
                                                <source media="(max-width: 576px)" srcset="{{ $deliveryImages['i2']['mob']['src_webp'] }}" type="image/webp">
                                            @endif
                                            @if(!empty($deliveryImages['i2']['mob']['type']))
                                                <source media="(max-width: 576px)" srcset="{{ $deliveryImages['i2']['mob']['src'] }}" type="{{ $deliveryImages['i2']['mob']['type'] }}">
                                            @endif
                                            @if(!empty($deliveryImages['i2']['desk']['src_webp']))
                                                <source srcset="{{ $deliveryImages['i2']['desk']['src_webp'] }}" type="image/webp">
                                            @endif
                                            @if(!empty($deliveryImages['i2']['desk']['type']))
                                                <source srcset="{{ $deliveryImages['i2']['desk']['src'] }}" type="{{ $deliveryImages['i2']['desk']['type'] }}">
                                            @endif
                                            <img width="200" height="200" src="{{ $deliveryImages['i2']['desk']['src'] }}" alt="{{ $deliveryImages['i2']['desk']['alt'] ?? '' }}" title="{{ $deliveryImages['i2']['desk']['title'] ?? '' }}">
                                        </picture>
                                    </div>
                                    <div class="icon"><img width="24" height="24" src="/images/icon/check-circle.svg" alt=""> </div>
                                    @lang('pages.delivery_second_block_box2')

                                </div>
                                <div class="list-step">
                                    <div class="img">
                                        <picture>
                                            @if(!empty($deliveryImages['i3']['mob']['src_webp']))
                                                <source media="(max-width: 576px)" srcset="{{ $deliveryImages['i3']['mob']['src_webp'] }}" type="image/webp">
                                            @endif
                                            @if(!empty($deliveryImages['i3']['mob']['type']))
                                                <source media="(max-width: 576px)" srcset="{{ $deliveryImages['i3']['mob']['src'] }}" type="{{ $deliveryImages['i3']['mob']['type'] }}">
                                            @endif
                                            @if(!empty($deliveryImages['i3']['desk']['src_webp']))
                                                <source srcset="{{ $deliveryImages['i3']['desk']['src_webp'] }}" type="image/webp">
                                            @endif
                                            @if(!empty($deliveryImages['i3']['desk']['type']))
                                                <source srcset="{{ $deliveryImages['i3']['desk']['src'] }}" type="{{ $deliveryImages['i3']['desk']['type'] }}">
                                            @endif
                                            <img width="200" height="200" src="{{ $deliveryImages['i3']['desk']['src'] }}" alt="{{ $deliveryImages['i3']['desk']['alt'] ?? '' }}" title="{{ $deliveryImages['i3']['desk']['title'] ?? '' }}">
                                        </picture>
                                    </div>
                                    <div class="icon"><img width="24" height="24" src="/images/icon/check-circle.svg" alt=""> </div>
                                    @lang('pages.delivery_second_block_box3')
                                </div>
                            </div>
                            <div class="content-col">
                                <div class="title-block">
{{--                                    @lang('pages.delivery_second_block_text')--}}

                                    <div class="title-block__top" > <span>2.</span>   <h2 class="title-block__top " style="display: inline-block">@lang('pages.delivery_second_title')</h2> </div>
                                    <div class="text">      @lang('pages.delivery_second_text') </div>

                                </div>
                                <div class="payment-about">
                                    @lang('pages.delivery_second_block_text2')

                                </div>
                            </div>
                        </div>
                        <div class="block-object">
                            <picture>
                                @if(!empty($deliveryImages['a2']['mob']['src_webp']))
                                    <source media="(max-width: 576px)" srcset="{{ $deliveryImages['a2']['mob']['src_webp'] }}" type="image/webp">
                                @endif
                                @if(!empty($deliveryImages['a2']['mob']['type']))
                                    <source media="(max-width: 576px)" srcset="{{ $deliveryImages['a2']['mob']['src'] }}" type="{{ $deliveryImages['a2']['mob']['type'] }}">
                                @endif
                                @if(!empty($deliveryImages['a2']['desk']['src_webp']))
                                    <source srcset="{{ $deliveryImages['a2']['desk']['src_webp'] }}" type="image/webp">
                                @endif
                                @if(!empty($deliveryImages['a2']['desk']['type']))
                                    <source srcset="{{ $deliveryImages['a2']['desk']['src'] }}" type="{{ $deliveryImages['a2']['desk']['type'] }}">
                                @endif
                                <img width="236" height="224" src="{{ $deliveryImages['a2']['desk']['src'] }}" alt="{{ $deliveryImages['a2']['desk']['alt'] ?? '' }}" title="{{ $deliveryImages['a2']['desk']['title'] ?? '' }}">
                            </picture>
                        </div>
                    </div>
                    <div class="delivery-section__block px-2 pb">
                        <div class="title-m-row">
                            <div class="m-icon">
                                <picture>
                                    @if(!empty($deliveryImages['b2']['mob']['src_webp']))
                                        <source media="(max-width: 576px)" srcset="{{ $deliveryImages['b2']['mob']['src_webp'] }}" type="image/webp">
                                    @endif
                                    @if(!empty($deliveryImages['b2']['mob']['type']))
                                        <source media="(max-width: 576px)" srcset="{{ $deliveryImages['b2']['mob']['src'] }}" type="{{ $deliveryImages['b2']['mob']['type'] }}">
                                    @endif
                                    @if(!empty($deliveryImages['b2']['desk']['src_webp']))
                                        <source srcset="{{ $deliveryImages['b2']['desk']['src_webp'] }}" type="image/webp">
                                    @endif
                                    @if(!empty($deliveryImages['b2']['desk']['type']))
                                        <source srcset="{{ $deliveryImages['b2']['desk']['src'] }}" type="{{ $deliveryImages['b2']['desk']['type'] }}">
                                    @endif
                                    <img width="200" height="200" src="{{ $deliveryImages['b2']['desk']['src'] }}" alt="{{ $deliveryImages['b2']['desk']['alt'] ?? '' }}" title="{{ $deliveryImages['b2']['desk']['title'] ?? '' }}">
                                </picture>
                            </div>
                            <div class="title-block">

                                <div class="title-block__top">
                                    <span>3.</span>
                                    <h2 class="title-block__top" style="display: inline-block" >@lang('pages.delivery_third_title') </h2>
                                </div>

                                <div class="text">
                                    @lang('pages.delivery_third_text') </div>


{{--                                @lang('pages.delivery_del_block_title')--}}






                            </div>



                        </div>
                        <div class="half-grid">
                            <div class="delivery-items">
                                @include(env('THEME_RESOURCES') . '.cart.delivery')
                            </div>
                            <div class="delivery-about">
                                <div class="delivery-about__block">
                                    <p>@lang('pages.delivery_del_block_curier')</p>
                                    <div>
                                        <div class="img">
                                            @php
                                                $deliveryContactIcon1 = image_picture_sources(env('THEME') . 'images/contacts/d1.png', true);
                                            @endphp
                                            <picture>
                                                @if(!empty($deliveryContactIcon1['src_webp']))
                                                    <source srcset="{{ $deliveryContactIcon1['src_webp'] }}" type="image/webp">
                                                @endif
                                                @if(!empty($deliveryContactIcon1['src']) && !empty($deliveryContactIcon1['type']))
                                                    <source srcset="{{ $deliveryContactIcon1['src'] }}" type="{{ $deliveryContactIcon1['type'] }}">
                                                @endif
                                                <img width="120" height="120" src="{{ $deliveryContactIcon1['src'] }}" alt="">
                                            </picture>
                                        </div>
                                        <div class="img">
                                            @php
                                                $deliveryContactIcon2 = image_picture_sources(env('THEME') . 'images/contacts/d2.png', true);
                                            @endphp
                                            <picture>
                                                @if(!empty($deliveryContactIcon2['src_webp']))
                                                    <source srcset="{{ $deliveryContactIcon2['src_webp'] }}" type="image/webp">
                                                @endif
                                                @if(!empty($deliveryContactIcon2['src']) && !empty($deliveryContactIcon2['type']))
                                                    <source srcset="{{ $deliveryContactIcon2['src'] }}" type="{{ $deliveryContactIcon2['type'] }}">
                                                @endif
                                                <img width="120" height="120" src="{{ $deliveryContactIcon2['src'] }}" alt="">
                                            </picture>
                                        </div>
                                        <div class="img">
                                            @php
                                                $deliveryContactIcon3 = image_picture_sources(env('THEME') . 'images/contacts/d3.png', true);
                                            @endphp
                                            <picture>
                                                @if(!empty($deliveryContactIcon3['src_webp']))
                                                    <source srcset="{{ $deliveryContactIcon3['src_webp'] }}" type="image/webp">
                                                @endif
                                                @if(!empty($deliveryContactIcon3['src']) && !empty($deliveryContactIcon3['type']))
                                                    <source srcset="{{ $deliveryContactIcon3['src'] }}" type="{{ $deliveryContactIcon3['type'] }}">
                                                @endif
                                                <img width="120" height="120" src="{{ $deliveryContactIcon3['src'] }}" alt="">
                                            </picture>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-text">
                                    <p>@lang('pages.delivery_del_block_info')</p>
                                </div>
                                <a href="whatsapp://send?phone=%2B37127044470" class="d-whatsapp">
                                    <div class="img">
                                        <picture>
                                            <source srcset="https://viarcanvas.com/theme/viar/images/contacts/whatsapp.webp" type="image/webp">
                                            <source srcset="https://viarcanvas.com/theme/viar/images/contacts/whatsapp.png" type="image/png">
                                            <img width="35" height="35" src="https://viarcanvas.com/theme/viar/images/contacts/whatsapp.png" alt="">
                                        </picture>
                                    </div>
                                    <p>@lang('pages.delivery_del_block_contact')</p>
                                </a>
                            </div>
                        </div>
                        <div class="block-object">
                            <picture>
                                @if(!empty($deliveryImages['a3']['mob']['src_webp']))
                                    <source media="(max-width: 576px)" srcset="{{ $deliveryImages['a3']['mob']['src_webp'] }}" type="image/webp">
                                @endif
                                @if(!empty($deliveryImages['a3']['mob']['type']))
                                    <source media="(max-width: 576px)" srcset="{{ $deliveryImages['a3']['mob']['src'] }}" type="{{ $deliveryImages['a3']['mob']['type'] }}">
                                @endif
                                @if(!empty($deliveryImages['a3']['desk']['src_webp']))
                                    <source srcset="{{ $deliveryImages['a3']['desk']['src_webp'] }}" type="image/webp">
                                @endif
                                @if(!empty($deliveryImages['a3']['desk']['type']))
                                    <source srcset="{{ $deliveryImages['a3']['desk']['src'] }}" type="{{ $deliveryImages['a3']['desk']['type'] }}">
                                @endif
                                <img width="236" height="224" src="{{ $deliveryImages['a3']['desk']['src'] }}" alt="{{ $deliveryImages['a3']['desk']['alt'] ?? '' }}" title="{{ $deliveryImages['a3']['desk']['title'] ?? '' }}">
                            </picture>
                        </div>
                    </div>

                    <div class="delivery-section__block px-1 mpb">
                        <p class="info">@lang('pages.delivery_third_block_text_top')</p>
                        <div class="half-grid mob-reverse">
                            <div class="list-steps">
                                <div class="list-step">
                                    <div class="img">
                                        <picture>
                                            @if(!empty($deliveryImages['i4']['mob']['src_webp']))
                                                <source media="(max-width: 576px)" srcset="{{ $deliveryImages['i4']['mob']['src_webp'] }}" type="image/webp">
                                            @endif
                                            @if(!empty($deliveryImages['i4']['mob']['type']))
                                                <source media="(max-width: 576px)" srcset="{{ $deliveryImages['i4']['mob']['src'] }}" type="{{ $deliveryImages['i4']['mob']['type'] }}">
                                            @endif
                                            @if(!empty($deliveryImages['i4']['desk']['src_webp']))
                                                <source srcset="{{ $deliveryImages['i4']['desk']['src_webp'] }}" type="image/webp">
                                            @endif
                                            @if(!empty($deliveryImages['i4']['desk']['type']))
                                                <source srcset="{{ $deliveryImages['i4']['desk']['src'] }}" type="{{ $deliveryImages['i4']['desk']['type'] }}">
                                            @endif
                                            <img width="200" height="200" src="{{ $deliveryImages['i4']['desk']['src'] }}" alt="{{ $deliveryImages['i4']['desk']['alt'] ?? '' }}" title="{{ $deliveryImages['i4']['desk']['title'] ?? '' }}">
                                        </picture>
                                    </div>
                                    <div class="icon"><img width="24" height="24" src="/images/icon/check-circle.svg" alt=""> </div>
                                    @lang('pages.delivery_third_block_box1')
                                </div>
                                <div class="list-step">
                                    <div class="img">
                                        <picture>
                                            @if(!empty($deliveryImages['i5']['mob']['src_webp']))
                                                <source media="(max-width: 576px)" srcset="{{ $deliveryImages['i5']['mob']['src_webp'] }}" type="image/webp">
                                            @endif
                                            @if(!empty($deliveryImages['i5']['mob']['type']))
                                                <source media="(max-width: 576px)" srcset="{{ $deliveryImages['i5']['mob']['src'] }}" type="{{ $deliveryImages['i5']['mob']['type'] }}">
                                            @endif
                                            @if(!empty($deliveryImages['i5']['desk']['src_webp']))
                                                <source srcset="{{ $deliveryImages['i5']['desk']['src_webp'] }}" type="image/webp">
                                            @endif
                                            @if(!empty($deliveryImages['i5']['desk']['type']))
                                                <source srcset="{{ $deliveryImages['i5']['desk']['src'] }}" type="{{ $deliveryImages['i5']['desk']['type'] }}">
                                            @endif
                                            <img width="200" height="200" src="{{ $deliveryImages['i5']['desk']['src'] }}" alt="{{ $deliveryImages['i5']['desk']['alt'] ?? '' }}" title="{{ $deliveryImages['i5']['desk']['title'] ?? '' }}">
                                        </picture>
                                    </div>
                                    <div class="icon"><img width="24" height="24" src="/images/icon/check-circle.svg" alt=""> </div>
                                    @lang('pages.delivery_third_block_box2')
                                </div>
                                <div class="list-step">
                                    <div class="img">
                                        <picture>
                                            @if(!empty($deliveryImages['i6']['mob']['src_webp']))
                                                <source media="(max-width: 576px)" srcset="{{ $deliveryImages['i6']['mob']['src_webp'] }}" type="image/webp">
                                            @endif
                                            @if(!empty($deliveryImages['i6']['mob']['type']))
                                                <source media="(max-width: 576px)" srcset="{{ $deliveryImages['i6']['mob']['src'] }}" type="{{ $deliveryImages['i6']['mob']['type'] }}">
                                            @endif
                                            @if(!empty($deliveryImages['i6']['desk']['src_webp']))
                                                <source srcset="{{ $deliveryImages['i6']['desk']['src_webp'] }}" type="image/webp">
                                            @endif
                                            @if(!empty($deliveryImages['i6']['desk']['type']))
                                                <source srcset="{{ $deliveryImages['i6']['desk']['src'] }}" type="{{ $deliveryImages['i6']['desk']['type'] }}">
                                            @endif
                                            <img width="200" height="200" src="{{ $deliveryImages['i6']['desk']['src'] }}" alt="{{ $deliveryImages['i6']['desk']['alt'] ?? '' }}" title="{{ $deliveryImages['i6']['desk']['title'] ?? '' }}">
                                        </picture>
                                    </div>
                                    <div class="icon"><img width="24" height="24" src="/images/icon/check-circle.svg" alt=""> </div>
                                    @lang('pages.delivery_third_block_box3')
                                </div>
                            </div>
                            <div class="content-col">
                                <div class="title-block nob">
                                    <div class="title-block__top">
                                        <span>4.</span> <h2 class="title-block__top" style="display: inline-block" > @lang('pages.delivery_third_block_text') </h2>
                                    </div>
                                </div>
                                <div class="payment-about">
                                    @lang('pages.delivery_third_block_text2')
                                </div>
                            </div>
                        </div>
                        <div class="block-object">
                            <picture>
                                @if(!empty($deliveryImages['a4']['mob']['src_webp']))
                                    <source media="(max-width: 576px)" srcset="{{ $deliveryImages['a4']['mob']['src_webp'] }}" type="image/webp">
                                @endif
                                @if(!empty($deliveryImages['a4']['mob']['type']))
                                    <source media="(max-width: 576px)" srcset="{{ $deliveryImages['a4']['mob']['src'] }}" type="{{ $deliveryImages['a4']['mob']['type'] }}">
                                @endif
                                @if(!empty($deliveryImages['a4']['desk']['src_webp']))
                                    <source srcset="{{ $deliveryImages['a4']['desk']['src_webp'] }}" type="image/webp">
                                @endif
                                @if(!empty($deliveryImages['a4']['desk']['type']))
                                    <source srcset="{{ $deliveryImages['a4']['desk']['src'] }}" type="{{ $deliveryImages['a4']['desk']['type'] }}">
                                @endif
                                <img width="236" height="224" src="{{ $deliveryImages['a4']['desk']['src'] }}" alt="{{ $deliveryImages['a4']['desk']['alt'] ?? '' }}" title="{{ $deliveryImages['a4']['desk']['title'] ?? '' }}">
                            </picture>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @include('partials.index_new.services2', ['addedClass' => 'spt'])
        @include(env('THEME_RESOURCES') . 'pages.index.faq9')
        @include( env('THEME_RESOURCES').'pages.gallery.zpart_viarcanvas_is')
    </main>

    <script>
        $(function() {
            $(document).on('click', '.del_js-select', function() {
                $(this).next().toggleClass('open');
            })
            $(document).on('click', '.countries-list li', function() {
                $('.countries-list li').removeClass('active');
                $(this).addClass('active');
                let text = $(this).find('p').text();
                $('.del_js-select p span').text(text);
                $('.del_js-select').next().removeClass('open');
            })
            $(document).on('click', '.pickup-js', function(e) {
                e.preventDefault();
                $('.pickup-container').slideToggle();
                $(this).closest('.delivery-item').toggleClass('no-border')
            })
            $(document).on('click', '.select-inner', function() {
                $(this).parent().toggleClass('active');
            })
            $(document).on('click', '.list-item', function() {
                $('.list-item').removeClass('active');
                $(this).addClass('active');
            })
        })
    </script>
@endsection
