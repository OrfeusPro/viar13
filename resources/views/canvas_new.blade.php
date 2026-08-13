<!DOCTYPE html>
		@if (Config::get('app.locale') == 'ee')
			<html lang="et">
		@else
			<html lang="{{ Config::get('app.locale') }}">
        @endif
        <head>
            <meta charset="UTF-8"/>
            <title>{{ $canvas_head['meta_title'] }}</title>
            <meta content="width=device-width,initial-scale=1" name="viewport"/>
            <meta content="{{  $canvas_head['meta_desc'] }}" name="description"/>
            <meta content="{{  $canvas_head['meta_title'] }}" property="og:title"/>
            <meta content="{{ $canvas_head['meta_desc'] }}" property="og:description"/>
            <meta property="og:image" content="{{ \URL::to('/') }}/img/logo.png"/>
            <link rel="shortcut icon" href="{{ asset(env('THEME').'images/fav.png') }}" type="image/x-icon"/>
            <meta name="csrf-token" content="{{ csrf_token() }}">
            <meta name="verify-paysera" content="a25580ff84c541195306e67d151dbb58">
            <link rel="canonical" href="{{ url(Request::url()) }}" />

            <link rel="preload" href="{{ ver_asset(env('THEME').'fonts/Trajan-Pro-3.woff2') }}" as="font" type="font/woff2" crossorigin/>
            <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/main.min.css') }}">
            <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/custom.css') }}">
            <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/cart.min.css') }}" /> <!-- main menu 2 -->
            <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/overall/overall.css') }}">
            <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/media.css') }}">
            <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/before-after.min.css') }}">
            <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/portrait/portrait.css') }}">
            <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/portrait/portrait-media.css') }}">
            <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/add.css') }}">
            <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/mod.css') }}">
            <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/forms.css') }}">
            <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/combine_canvas.css') }}">
            <link rel="preload" href="{{ ver_asset(env('THEME').'style/fontello.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
            @include((config('theme.resource') ?: 'theme.viar.') . 'partials.compact_overlays')

            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
            <script src="{{ ver_asset(env('THEME').'js/custom.js') }}" defer=""></script>
            <script src="{{ ver_asset(env('THEME').'js/script_canvas.js') }}" defer=""></script>

            <script defer src="{{ ver_asset(env('THEME').'js/cart.min.js') }}"></script> <!-- main menu 2 -->

            <script src="{{ ver_asset(env('THEME').'js/intlTelInput.min.js') }}"></script>
            <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/intlTelInput.min.css') }}">
            {!! $trackers !!}

            <script src="{{ ver_asset(env('THEME').'js/InteriorGenerator.js') }}"></script>
        </head>
        <body class="vz-art vz-canvas load"  data-multiplier="{{ $contry_mult }}">
        @include('partials.socials.fb')

        @widget('Header')

        <script>
            var mult = $('body').data('multiplier');

            if (isNaN(mult)){
                mult = 1;
            }
        </script>
        <main id="top">


		{{--
            <div class="portraits portrait__screen canvas-portrait">
                <div class="portraits-wrap">
                    <div class="portraits-slider sl-slider">
                        <article class="portraits-slide">
                            <div class="section-frame">
                                @include('partials.canvas_new.breads')
                                @include('partials.canvas_new.header')
                            </div>
                        </article>
                    </div>
                </div>
            </div>
            <div class="ellipse">
                <img alt="img" src="{{ asset(env('THEME').'images/icon/ellipse-whete.svg') }}" decoding="async" height="99"
                     width="1374"/>
            </div>
		--}}

		 @include('partials.canvas_new.breads')
		 @include('partials.canvas_new.slider')

            <section class="about__screen about__canvas">
                @include('partials.canvas_new.tabs.tabs')
            </section>
            <div class="retouch__screen p-section">
                <div class="section-frame">
                    <div class="retouch__inner">
                        @include('partials.canvas_new.photo_work')
                        @include('partials.canvas_new.form')
                    </div>
                </div>
                {{-- <div class="form-bg">
                    <img src="{{ asset(env('THEME').'images/canvas/Union.svg') }}" alt="">
                </div> --}}
            </div>
            <div class="ellipse ellipse_black custom-ellipse">
                <img src="{{ asset(env('THEME').'images/icon/ellipse-black.svg') }}" alt="img" decoding="async">
            </div>
            @include('partials.canvas_new.sizes')


            <div class="ellipse ellipse_black ellipse_top">
                <a href="#formalizaton" class="anchor ellipse-arrow ellipse-arrow_white" aria-label="anchor link">
                    <i class="fa-arrow-down"></i>
                </a>
                <img src="{{ asset(env('THEME').'images/icon/ellipse-black.svg') }}" alt="img" decoding="async">
            </div>
             @include('partials.canvas_new.steps')

            <div class="ellipse ellipse_black">
                <img src="{{ asset(env('THEME').'images/icon/ellipse-black.svg') }}" alt="img" loading="lazy">
            </div>
            @include('partials.canvas_new.also_like_examples')
        </main>

{{--        @if(isset($canvas_head['seo']) && $canvas_head['seo'])--}}
{{--        <div class="seo-text section-frame">--}}
{{--            <div class="seo-text__wrapper">--}}
{{--                <div class="seo-text__text">--}}
{{--                    {!! $canvas_head['seo'] !!}--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--        @endif--}}

        @widget('Footer')
        <a href="#top" class="vz-art scroll-button anchor" aria-label="anchor link">
            <i class="fa-arrow-next"></i>
        </a>


        {{-- Товар добавлен в корзину. --}}
        <div class="popup-bask-add popup p__mod">
            <span class="close-popup"></span>
            <div class="popup-content">
                <span class="close-content"><i class="icon-icon4"></i></span>
                <div class="p__mod__title green__text">{{ trans('gl.suc') }}
                    <span>
                    <img src="{{ asset(env('THEME').'img/checkmark_circle.1.png') }}" alt="">
                    </span>
                </div>
            </div>
        </div>


        @include('partials.canvas_new.new_bot_scripts')
        @include('partials.canvas_new.modals')
{{--        @include('partials.canvas_new.new_bot_scripts2')--}}


        {{-- Неправильный размер. --}}
        <div class="popup-inv-size popup p__mod">
            <span class="close-popup"></span>
            <div class="popup-content">
			<span class="close-content" style="display: flex; flex-direction: row-reverse; cursor: pointer ">
            <img src="{{ asset(env('THEME').'img/cross1.png') }}" alt="" style="width:15px; height: 15px">
            </span>
                <div class="p__mod__title red__text h3_old" id="err_msgs">
                    <span style="display:block;">{{ trans('gl.inv_filesize_or_ext') }}</span>
                </div>
            </div>
        </div>


        <style>
            @media screen and (max-width: 43.75em) {
                .popup-photo-canvas .kviz-input_pc {
                    display: block;
                    order: -1;
                }
            }
            img.img-svg__posa {
                position: absolute;
                right: 16px;
                top: 50%;
                transform: translateY(-50%);
            }
            .tabs-container .tabs-items .prices-sizes .prices-sizes-content .prices-cintant a::after,
            .canvas-banner .canvas-banner-content .canvas-tabs .tabs-content .order::after {
                content: '';
                background-size: 100% 100%;
            }
            .js-popup .jcf-fake-input,
            .hidden__labels .jcf-fake-input,
            .why-composition-inner  .jcf-fake-input,
            .js-popup .jcf-upload-button,
            .hidden__labels .jcf-upload-button,
            .why-composition-inner .jcf-upload-button,
            .why-form .jcf-fake-input,
            .why-form .jcf-upload-button{
                display: none;
            }
            @media screen and (max-width: 1024px) {
                .vz-art.vz-header {
                    background-color: unset;
                }
            }
            .jcf-extension-svg .jcf-fake-input,
            .jcf-extension-png .jcf-fake-input,
            .jcf-extension-jpg .jcf-fake-input,
            .jcf-extension-jpeg .jcf-fake-input,
            .jcf-extension-webp .jcf-fake-input,
            .jcf-extension-bmp .jcf-fake-input{
                display: block!important;
                font-size: 12px;
                text-align: center;
            }
            .tabs-item2 .tools__desk{
                opacity: 0;
                pointer-events: none;
            }
            .tabs-item2.active + .tools__mob{
                position: absolute;
                right: 30px;
                bottom: 31px;
                padding: 0;
            }
            .js_cost_canvas{
                padding-left: 26px
            }
        </style>

		{{-- @include('partials.all_styles.modals') --}}

        @if (Session::has('success_photo'))
            <script>
                $('.js_pop_photo_form').addClass('active');

                $(".close-content, .n-p-btn-back").on("click", function () {
                    $(".popup").removeClass("active");
                });
            </script>
        @endif
        </body>
        </html>
