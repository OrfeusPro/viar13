<!DOCTYPE html>
@if (Config::get('app.locale') == 'ee')
    <html lang="et">
@else
    <html lang="{{ Config::get('app.locale') }}">
@endif

<head>
    <meta charset="UTF-8">
    <title>{{ $home->getTranslatedAttribute('page_title') }}</title>
    <meta name="description" content="{{ $home->getTranslatedAttribute('meta_desc')  }}">
    {{-- og --}}
    <meta property="og:title" content="{{ $home->getTranslatedAttribute('page_title') }}" />
    <meta property="og:image" content="{{ \URL::to('/') }}/img/logo.png" />
    {{-- endog --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="{{ asset(env('THEME').'images/fav.png') }}" type="image/x-icon">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="verify-paysera" content="a25580ff84c541195306e67d151dbb58">
    <link rel="canonical" href="{{ url(Request::url()) }}" />

    @include('partials.index_new.head_styles')
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/main.min.css') }}" onload="this.media='all'">
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/custom.css') }}">
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/cart.min.css') }}" /> <!-- main menu 4 -->

    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/home-media.css') }}" onload="this.media='all'" >
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/overall/overall.css') }}" onload="this.media='all'">
    <!-- Preload Fonts -->
    <link rel="preload" href="{{ ver_asset(env('THEME').'fonts/Trajan-Pro-3-SemiBold.woff2') }}" as="font" type="font/woff2" crossorigin="anonymous">
    <link rel="preload" href="{{ ver_asset(env('THEME').'fonts/Trajan-Pro-3.woff2') }}" as="font" type="font/woff2" crossorigin="anonymous">
    <link rel="preload" href="{{ ver_asset(env('THEME').'fonts/icon-font.ttf') }}" as="font" type="font/ttf" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/add.css') }}" onload="this.media='all'">
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/forms.css')}}" onload="this.media='all'">
    <!-- Scripts -->


    <style>
        .slick-track { position: relative; top: 0; left: 0; display: flex; height: 100%; min-width: 100% !important; overflow: hidden; margin-left: auto; margin-right: auto;}
    </style>
    @include((config('theme.resource') ?: 'theme.viar.') . 'partials.compact_overlays')

    {!! $trackers !!}
</head>

<body class="load vz-home">
    @include('partials.socials.fb')

    {{-- before --}}
    @widget('Header', ['is_home' => 1])
{{--    @include('partials.index_new.header0', ['is_home' => 1])--}}
{{--    @include('partials.index_new.header1', ['is_home' => 1])--}}
    {{-- after --}}
    <main id="top">
        @include('partials.index_new.slider1')
        @include('partials.index_new.services2')
        @include('partials.index_new.happy3')
        @include('partials.index_new.examples4')
        @include('partials.index_new.emoj_4_5')
        @include('partials.index_new.works5')
        @include('partials.index_new.tops6')
        @include('partials.index_new.why7')
        @include('partials.index_new.revs8')
        @include('partials.index_new.faq9')
    </main>


{{--    @if($home->getTranslatedAttribute('seo'))--}}
{{--    <div class="seo-text section-frame">--}}
{{--        <div class="seo-text__wrapper">--}}
{{--            <div class="seo-text__text">--}}
{{--                {!! $home->getTranslatedAttribute('seo') !!}--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--    @endif--}}

    @widget('Footer')

    <a href="#top" class="scroll-button anchor" aria-label="anchor link">
        <i class="fa-arrow-next"></i>
    </a>

    @include('partials.index_new.modals')

    <script>
        setTimeout(function() {
            const elem = document.createElement('link');
            elem.type = 'text/css';
            elem.rel = 'stylesheet';
            elem.href = 'theme/viar/style/intlTelInput.min.css';
            document.body.appendChild(elem);
        }, 2000);
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/datepicker/1.0.10/datepicker.min.js" integrity="sha512-RCgrAvvoLpP7KVgTkTctrUdv7C6t7Un3p1iaoPr1++3pybCyCsCZZN7QEHMZTcJTmcJ7jzexTO+eFpHk4OCFAg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    {{-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script> --}}
    <script src="{{ ver_asset(env('THEME').'js/script.js') }}" defer=""></script>
    <script defer src="{{ ver_asset(env('THEME').'js/cart.min.js') }}"></script> <!-- main menu 3 -->

    <script src="{{ ver_asset(env('THEME') . 'js/emoj_form.js') }}" defer=""></script>

    <script src="{{ ver_asset(env('THEME').'js/custom.js') }}" defer=""></script>


    <script src="{{ ver_asset(env('THEME').'js/intlTelInput.min.js') }}"></script>
    <script src="{{ ver_asset(env('THEME').'js/add.js') }}"></script>

    @if (Session::has('success_photo'))
        <script>
            $('.js_pop_photo_form').addClass('active');

            $(".close-content, .n-p-btn-back").on("click", function() {
                $(".popup").removeClass("active");
            });
        </script>
    @endif

</body>

</html>
