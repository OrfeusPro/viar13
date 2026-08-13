<!DOCTYPE html>
@if (Config::get('app.locale') == 'ee')
<html lang="et">
@else
<html lang="{{ Config::get('app.locale') }}">
@endif

<head>
    <meta charset="UTF-8">
    <title>@lang('collage_new.c_page_title')</title>
    <meta name="description" content="@lang('collage_new.c_page_description')">
    {{-- og --}}
    <meta property="og:title" content="@lang('collage_new.c_page_title')" />
    <meta property="og:image" content="{{ \URL::to('/') }}/img/logo.png" />
    {{-- endog --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="{{ asset('images/fav.png') }}" type="image/x-icon">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="verify-paysera" content="a25580ff84c541195306e67d151dbb58">
    <link rel="canonical" href="{{ url(Request::url()) }}" />


    {{-- <link rel="stylesheet" href="{{ asset(env('THEME').'min_css/min_collage.css') }}?v=0.03" media="all" />
    <link rel="stylesheet" href="{{ asset(env('THEME').'min_css/sub_css/min_sub_collage.css') }}?v=0.03" media="all" /> --}}

	<link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/main.min.css') }}" media="all" />
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/custom.css') }}">
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/cart.min.css') }}" /> <!-- main menu 3 -->
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/overall/overall.css') }}" media="all" />
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/media.css') }}" media="all" />
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/before-after.min.css') }}" media="all" />
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/portrait/portrait.css') }}" media="all" />
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/portrait/portrait-media.css') }}" media="all" />
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/collage.css') }}" media="all">
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/collage-media.css') }}" media="all">
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/collage_combine.css') }}" media="all">
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/col-mod.css') }}" media="all">
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/add.css') }}" media="all">
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/forms.css')}}" media="all">


    <link href="//cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" media="all" />

    <!-- Preload Fonts -->
    <link rel="preload" href="{{ asset(env('THEME').'fonts/Trajan-Pro-3-SemiBold.woff2') }}" as="font" type="font/woff2" crossorigin="anonymous">
    <link rel="preload" href="{{ asset(env('THEME').'fonts/Trajan-Pro-3.woff2') }}" as="font" type="font/woff2" crossorigin="anonymous">
    <link rel="preload" href="{{ asset(env('THEME').'fonts/icon-font.ttf') }}" as="font" type="font/ttf" crossorigin="anonymous">


    <!-- Scripts -->


    <style>
        .slick-track {
            position: relative;
            top: 0;
            left: 0;
            display: flex;
            height: 100%;
            min-width: 100% !important;
            overflow: hidden;

            margin-left: auto;
            margin-right: auto;
        }
    </style>

    {!! $trackers !!}

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script defer src="{{ ver_asset(env('THEME').'js/cart.min.js') }}"></script> <!-- main menu 7 -->

    <script type="text/javascript">
        var mult = $('body').data('multiplier');

        if (isNaN(mult)) {
            mult = 1;
        }
    </script>
</head>

<body class="load vz-art collage-bg">

    <div id="template-preview" style="display:none;">
        <div class="dz-preview dz-file-preview well" id="dz-preview-template">
            <div class="dz-image">
                <img loading="lazy" data-dz-thumbnail="" src="{{ asset('img/loading.gif') }}" alt="loading">
            </div>
            <div class="dz-details">
                <div class="dz-filename"><span data-dz-name=""></span></div>
                <div class="dz-size" data-dz-size=""></div>
            </div>
            <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress=""></span></div>
            <div class="dz-success-mark"><span></span></div>
            <div class="dz-error-mark"><span></span></div>
            <div class="dz-error-message"><span data-dz-errormessage=""></span></div>
        </div>
    </div>


    @include('partials.socials.fb')

    {{-- before --}}
    @widget('Header', ['is_home' => 1])
    {{-- after --}}
    <main id="top">

		@include('partials.collage_new.breads')
        @include('partials.collage_new.slider')
        @include('partials.collage_new.why_screen')


        <!-- advantage and popular combine -->
        <div class="general__screen collage_pop-advant">
            @include('partials.collage_new.advantage_screen')
            @include('partials.collage_new.popular_screen')
        </div>

        @include('partials.collage_new.order_screen')
        @include('partials.collage_new.f_order_screen')
        {{-- @include('partials.collage_new.doubt_screen') --}}
        @include('partials.collage_new.love_screen')
        @include('partials.collage_new.generator_info')
        @include('partials.collage_new.generator')
        @include('partials.collage_new.why')
        @include('partials.index_new.services2')
        @include('partials.index_new.faq9')

    </main>

{{--    @if(isset($collage_head['seo']) && $collage_head['seo'])--}}
{{--    <div class="seo-text section-frame">--}}
{{--        <div class="seo-text__wrapper">--}}
{{--            <div class="seo-text__text">--}}
{{--                {!! $collage_head['seo'] !!}--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--    @endif--}}

    @widget('Footer')

    <a href="#top" class="vz-art scroll-button anchor" aria-label="anchor link"><i class="fa-arrow-next"></i></a>

    <script>
        setTimeout(function() {
            const elem = document.createElement('link');
            elem.type = 'text/css';
            elem.rel = 'stylesheet';
            elem.href = '/theme/viar/style/intlTelInput.min.css';
            document.body.appendChild(elem);
        }, 2000);
    </script>
    <script src="{{ asset(env('THEME').'js/intlTelInput.min.js') }}"></script>
    <script src="{{ asset(env('THEME').'js/add.js') }}?v=0.03"></script>

    @if (Session::has('success_photo'))
    <script>
        $('.js_pop_photo_form').addClass('active');

        $(".close-content, .n-p-btn-back").on("click", function() {
            $(".popup").removeClass("active");
        });
    </script>
    @endif

    {{-- Товар добавлен в корзину. --}}
    <div class="popup-bask-add popup p__mod">
        <span class="close-popup"></span>
        <div class="popup-content">
            <span class="close-content"><i class="icon-icon4"></i></span>
            <div class="p__mod__title green__text h3_old">{{ trans('gl.suc') }}
                <span>
                    <img src="{{ asset('img/checkmark_circle.1.png') }}" alt="">
                </span>
            </div>
        </div>
    </div>
    {{-- Неправильный размер. --}}
    <div class="popup-inv-size popup p__mod">
        <span class="close-popup"></span>
        <div class="popup-content">
			<span class="close-content" style="display: flex; flex-direction: row-reverse; cursor: pointer ">
            <img src="{{ asset(env('THEME').'img/cross1.png') }}" alt="" style="width:15px; height: 15px">
            </span>
            <div class="p__mod__title red__text" id="err_msgs">
                <span style="display:block;">{{ trans('gl.inv_filesize_or_ext') }}</span>
            </div>
        </div>
    </div>

    {{-- thanks --}}
    <div class="vz-artjs-popup thanks">
        <div class="vz-art kviz-thanks">
            <img src="{{ asset('images/icon/check-done.svg') }}" class="kviz-thanks__icon" alt="img" loading="lazy">
            <div class="vz-art kviz-thanks__title">
                <div class="h3_old">{{ trans('portrait_buy_form.popup_thanks_text1') }}</div>
                <p>{{ trans('portrait_buy_form.popup_thanks_text2') }}</p>
            </div>
            <div class="vz-art kviz-thanks__info">
                <p>{{ trans('portrait_buy_form.popup_thanks_text3') }}</p>
                <a href="#" target="_blank" rel="noopener noreferrer">
                    <svg>
                        <use xlink:href="{{ asset(env('THEME').'sprite.svg#wh') }}"></use>
                    </svg>
                    {{ trans('portrait_buy_form.popup_thanks_text4') }}
                </a>
            </div>
        </div>
    </div>


    @include('partials.collage_new.modals')
    @include('partials.collage_new.footer_scripts')

</body>

</html>
