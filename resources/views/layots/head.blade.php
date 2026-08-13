@php
$replace = '/'.app()->getLocale().'/';
if(app()->getLocale() == 'ru')
{
$replace = '/ru/';
}
$top_menu = preg_replace( '^/en/^', $replace, menu('header','layots.menu.default'));
@endphp

<!DOCTYPE html>
@if(Config::get('app.locale') == 'ee')
<html lang="et">
@else
<html lang="{{ Config::get('app.locale') }}">
@endif

<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <meta name="description" content="@yield('meta_desc')">
    <meta name="cmsmagazine" content="5a7189a28fb3dc68238dbdb44c9ea99c" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="verify-paysera" content="a25580ff84c541195306e67d151dbb58">
    <link rel="canonical" href="{{ url(Request::url()) }}" />

    @yield('og_tags')

    <meta name="robots" content="@yield('meta_robots')">

    @if(!empty($structured_data) && is_array($structured_data))
        @foreach($structured_data as $structured_item)
            <script type="application/ld+json">
                {!! json_encode($structured_item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
            </script>
        @endforeach
    @endif

    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('img/favicon.png') }}">
    {{-- <link rel="shortcut icon" href="{{ voyager_asset('images/favicon.ico') }}" type="image/x-icon"> --}}

    @if(isset($is_firefox) && $is_firefox == 1)
        <link rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;531;600;700;800;900&display=swap" />
        <link rel="stylesheet" href="{{ ver_asset('css/fonts.css') }}">
        <link rel="stylesheet" href="{{ ver_asset('css/style.css') }}">
        <link rel="stylesheet" href="{{ ver_asset('css/mod.css') }}">
    @else
        <link rel="preload"
            href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;531;600;700;800;900&display=swap"
            as="style" onload="this.onload=null;this.rel='stylesheet'" />

        <link rel="preload" href="{{ ver_asset('css/style.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">

        <link rel="preload" href="{{ ver_asset('css/mod.css') }}" as="style"
            onload="this.onload=null;this.rel='stylesheet'">
    @endif

	<link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/custom.css') }}">
	<link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/cart.min.css') }}" /> <!-- main menu 6 -->

    <!--[if IE]>-->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;531;600;700;800;900&display=swap" />
    <!--<![endif]-->

    @isset($trackers)
        {!! $trackers !!}
    @endisset

    <style>
        .personal-area img {
            max-width: 100%;
        }

        @media screen and (max-width: 1023px) {
            .contacts .contacts-content .connection .item img {
                width: 40px !important;
                height: 45px !important;
            }
        }

        .generate .generate-content .gallery-photo .slider-tabs .tabs-content .picture .slick-slide img {
            max-width: 100% !important;
            max-height: 450px !important;
            height: auto !important;
            width: auto !important;
            object-fit: contain !important;
        }

        @media screen and (min-width: 768px) {

            .tpl-stylization-paintings .tabs-container .tabs-items .what-canvas .what-canvas-content .what-items .what-item img,
            .tpl-portrait-in-image .tabs-container .tabs-items .what-canvas .what-canvas-content .what-items .what-item img {
                width: 220px !important;
            }
        }

        @media screen and (min-width: 1024px) {

            .tpl-stylization-paintings .tabs-container .tabs-items .what-canvas .what-canvas-content .what-items .what-item img,
            .tpl-portrait-in-image .tabs-container .tabs-items .what-canvas .what-canvas-content .what-items .what-item img {
                width: 315px !important;
            }
        }

        @media screen and (min-width: 1700px) {

            .tpl-stylization-paintings .tabs-container .tabs-items .what-canvas .what-canvas-content .what-items .what-item img,
            .tpl-portrait-in-image .tabs-container .tabs-items .what-canvas .what-canvas-content .what-items .what-item img {
                width: 433px !important;
            }
        }

        .gb__text {
            margin: 0 auto -50px auto;
            max-width: 980px;
            padding: 65px 0 0 0;
        }

        .style_sub_title {
            text-align: center;
            max-width: 980px;
            margin: 0 auto;
        }

        @media screen and (max-width: 1350px) {
            .header-content .menu-logo nav li a {
                font-size: 12px !important;
            }
        }

        @media screen and (min-width: 1400px) {
            header .header-content {
                padding: 15px 20px 0;
            }
        }

        @media screen and (min-width: 1820px) {
            header .header-content {
                padding: 15px 70px 0 68px !important;
            }
        }

        .painter__icon_compl span {
            font-weight: 700;
        }

        .painter__icon_compl span,
        .painter__icon_compl svg,
        .painter__icon_compl {
            display: inline-block;
            vertical-align: middle;
        }

        @media screen and (max-width: 768px) {
            .gb__text {
                margin: 105px auto 0 auto;
            }

            .gb__text,
            .style_sub_title {
                padding: 0 15px;
            }

            .oiled .oiled-content .oiled-text .title h2,
            .stylization .stylization-content .stylization-text .title h2,
            .portrait .portrait-content .portrait-text .title h2,
            .gallery .gallery-content .gallery-text .title h2,
            .foto .foto-content .foto-text .title h2,
            .collages .collages-content .collages-text .title h2,
            .pictures .pictures-content .pictures-text .title h2 {
                font-size: 22px !important;
            }
        }

        .collage-banner {
            z-index: 5 !important;
        }

        header {
            z-index: 10 !important;
        }
    </style>

    @php
    $localLang = array();
    $localLang['ru'] = asset('img/transfer-img1.png');
    $localLang['en'] = asset('img/transfer-img2.png');
    $localLang['lv'] = asset('img/transfer-img3.png');
    $localLang['ee'] = asset('img/transfer-img4.png');
    $localLang['lt'] = asset('img/transfer-img5.png');
    @endphp
    @php
    $url = str_replace('/'.app()->getLocale().'/', '/',$_SERVER['REQUEST_URI']);
    if($url == '/'.app()->getLocale())
    {
    $url = '';
    }
    @endphp
    @foreach($localLang as $key=>$iconLang)
    @php
    $lang_key = preg_replace("/\s+/", "", $key);
    $lang_key = strtolower($lang_key);
    @endphp
    @if($key != app()->getLocale())
    @if($key == 'ru')
    {{-- <link hreflang="x-default" rel="alternate" href="{{ \URL::to('/')}}{{ $url }}" hreflang="{{ $lang_key }}"> --}}
    @else
    {{-- <link rel="alternate" href="{{ \URL::to('/')}}/{{ $key.$url }}" hreflang="{{ $lang_key }}"> --}}
    @endif
    @endif
    @endforeach

    @include('layots.head_combine')
    {{-- endassets --}}
    @yield('styles')
    @include('partials.head_devs')

    <link rel="stylesheet" href="{{ ver_asset('css/overall/overall.css') }}">

    @if(empty($is_noscripts))
        <script src="{{ ver_asset(env('THEME').'js/custom.js') }}" defer=""></script>
        <script src="{{ ver_asset(env('THEME').'js/script.js') }}"></script>
        <script src="{{ ver_asset(env('THEME').'spinner/jm.spinner.js') }}"></script>
        <script src="{{ ver_asset(env('THEME').'js/swal.js') }}"></script>
        <script src="{{ ver_asset(env('THEME').'js/add.js') }}"></script>
        <script defer src="{{ ver_asset(env('THEME').'js/cart.min.js') }}"></script> <!-- main menu 5 -->
    @else
        <script src="{{ ver_asset(env('THEME').'js/add.js') }}"></script>
        <script defer src="{{ ver_asset(env('THEME').'js/cart.min.js') }}"></script> <!-- main menu 5 -->
    @endif
</head>

<body
    class="@isset($bodyclass) {{ $bodyclass }} @endisset template-{{ collect(\Request::segments())->implode('-') }} tpl-{{ Request::segment(2) }}"
    data-suc_send="{{ trans('gl.suc_send') }}" data-suc_dates="{{ trans('gl.suc_send_dates') }}"
    data-suc="{{ trans('gl.suc') }}" data-suc_send_fb_sale="{{ trans('gl.suc_send_fb_sale') }}"
    data-req="{{ trans('gl.req_field') }}" data-err_price="{{ trans('gl.err_price') }}"
    data-err_size="{{ trans('gl.req_size') }}" data-err_file="{{ trans('gl.inv_filesize_or_ext') }}"
    data-for_ram="{{ trans('gl.for_ram_text') }}" data-full="{{ trans('gl.is_total') }}"
    data-multiplier="{{ $contry_mult }}">

@if(Route::currentRouteName() == 'delivery_page' && filter_var(setting('site.top_sale', false), FILTER_VALIDATE_BOOLEAN))
    <div class="top-sale">
        <img width="77" height="66" src="{{ asset(env('THEME').'images/sale.webp') }}" alt="">
        <p>@lang("header_footer_new.header.top_sale")</p>
    </div>
    @endif
    <script>
        var mult = $('body').data('multiplier');

        if (isNaN(mult)){
            mult = 1;
        }
    </script>

    @include('partials.socials.fb')

    <div id="routes_links" style="display:none;" data-send_screen="{{ route('send_screen') }}"
        data-send_dates="{{ route('send_dates') }}" data-user_free_img="{{ route('user_free_img') }}"
        data-coupon_use="{{ route('coupon_use') }}" data-send_gift_card="{{ route('send_gift_card') }}"
        data-bask_add_construct="{{ route('add_item_to_basket_construct') }}"></div>

    <div class="wrapper">

        <div class="popup-login-registration popup">
            <span class="close-popup"></span>
            <div class="popup-content">
                <span class="close-content"><i class="icon-icon4"></i></span>
                <div class="h3_old">{{ __('header.account_form_button') }}</div>
                <ul>
                    <li><a class="login" href="javascript:void(0)"><span>{{ __('header.enter') }}</span></a></li>
                    <li><a class="registration"
                            href="javascript:void(0)"><span>{{ __('header.registration') }}</span></a></li>
                </ul>
            </div>
        </div>

        <div id="template-preview" style="display:none;">
            <div class="dz-preview dz-file-preview well" id="dz-preview-template">
                <div class="dz-image">
                    <img loading="lazy" data-dz-thumbnail="" src="{{ asset('img/loading.gif') }}" alt="loading">
                </div>
                <div class="dz-details">
                    <div class="dz-filename"><span data-dz-name></span></div>
                    <div class="dz-size" data-dz-size></div>
                </div>
                <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>
                <div class="dz-success-mark"><span></span></div>
                <div class="dz-error-mark"><span></span></div>
                <div class="dz-error-message"><span data-dz-errormessage></span></div>
            </div>
        </div>

        @widget('Header')
