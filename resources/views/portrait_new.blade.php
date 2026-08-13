<!DOCTYPE html>
@if (Config::get('app.locale') == 'ee')
<html lang="et">
@else
<html lang="{{ Config::get('app.locale') }}">
@endif

<head>
    <meta charset="UTF-8" />
    <title>{{ $item->getTranslatedAttribute('meta_title') }}</title>
    <meta content="width=device-width,initial-scale=1" name="viewport" />
    <meta content="" name="description" />
    <meta content="{{ $item->getTranslatedAttribute('meta_title') }}" property="og:title" />
    <meta content="{{ $item->getTranslatedAttribute('meta_desc') }}" property="og:description" />

    <meta property="og:image" content="{{ \URL::to('/') }}/img/logo.png" />
    <link rel="shortcut icon" href="{{ asset(env('THEME').'images/fav.png') }}" type="image/x-icon" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="verify-paysera" content="a25580ff84c541195306e67d151dbb58">
    <link rel="canonical" href="{{ url(Request::url()) }}" />

    <link rel="preload" href="{{ asset(env('THEME').'fonts/Trajan-Pro-3.woff2') }}" as="font" type="font/woff2" crossorigin />

    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/custom.css') }}">
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/cart.min.css') }}" /> <!-- main menu 5 -->
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/main.min.css') }}">
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/overall/overall.css') }}">
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/media.css') }}">
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/before-after.min.css') }}">
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/portrait/portrait.css') }}">
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/portrait/portrait-media.css') }}">
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/add.css') }}">
    <link rel="stylesheet" href="{{ ver_asset(env('THEME').'style/forms.css') }}">
    <link rel="preload" href="{{ ver_asset(env('THEME').'style/fontello.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    {!! $trackers !!}
</head>

<body class="vz-art load">
    @include('partials.socials.fb')

    @widget('Header')


    <main id="top">

        @if(isset($home_slides[0]) && $home_slides[0])
            @include('partials.portrait_new.breads')
            @include('partials.portrait_new.slider')
        @else
            <div class="portraits portrait__screen">
                <div class="portraits-wrap">
                    <div class="portraits-slider sl-slider">
                        <article class="portraits-slide">
                            <div class="section-frame">
                                @include('partials.portrait_new.breads2')
                                @include('partials.portrait_new.h_zero')
                            </div>
                        </article>
                    </div>
                </div>
            </div>
            <div class="ellipse">
                <img alt="img" src="{{ asset(env('THEME').'images/icon/ellipse-whete.svg') }}" decoding="async" height="99" width="1374" />
            </div>
        @endif

        @php
        $ba_items = $item->getMedia('new_before_items');
        $ba_items_after = $item->getMedia('new_after_items');
        $ba_items2 = $item->getMedia('works_examples_new');
        @endphp

        <section class="about__screen">
            <div class="section-frame section-m-frame">
                <div class="about__screen--inner">
                    <div class="page-title h2_old">{{ trans('portrait.tabs_title') }}</div>
                </div>
                <div class="about__screen--wrapper">
                    <div class="about-tabs">
                        <div class="about-tab active">
                            <a href="#">{{ trans('portrait.tab5_title') }}</a>
                        </div>
                        <div class="about-tab">
                            <a href="#">{{ trans('portrait.tab1_title') }}</a>
                        </div>
                        <div class="about-tab">
                            <a href="#">{{ trans('portrait.tab2_title') }}</a>
                        </div>
                        <div class="about-tab ">
                            <a href="#">{{ trans('portrait.tab4_title') }}</a>
                        </div>
                    </div>
                </div>
                <div class="about__screen--blocks">
                    @include('partials.portrait_new.tabs.fifth')
                    @include('partials.portrait_new.tabs.first')
                    @include('partials.portrait_new.tabs.second')
                    @include('partials.portrait_new.tabs.fourth')

                </div>
            </div>
        </section>
        @include('partials.portrait_new.examples')
        <div class="ellipse ellipse_black custom-ellipse">
            <img src="{{ asset(env('THEME').'images/icon/ellipse-black.svg') }}" alt="" loading="lazy" />
        </div>
        <div class="mobile-ell">
            <img src="{{ asset(env('THEME').'images/sizes/union.png') }}" alt="" />
            <div class="mobile-size-title">{{ trans('portrait.sizes__title') }}</div>
        </div>
        @include('partials.portrait_new.sizes')
        <div class="ellipse ellipse_black ellipse_top">
            <a href="#examples" class="anchor ellipse-arrow ellipse-arrow_white" aria-label="anchor link">
                <i class="fa-arrow-down"></i>
            </a>
            <img src="{{ asset(env('THEME').'images/icon/ellipse-black.svg') }}" alt="img" loading="lazy" />
        </div>
        @include('partials.portrait_new.order_steps')
        <section class="portraits-examples">
            <div class="p-examples-top">
                <div class="section-frame">
                    <div class="top-title">
                        <div class="page-title h2_old">{{ trans('portrait.ex_cl_works_title') }}</div>
                        <p>{{ trans('portrait.ex_cl_works_desc') }}</p>
                    </div>
                    <div class="p-example-slider--wrapper">
                        <div class="examples-slider p-examples-slider">
                            @php
                            $cl_works = $item->getMedia('new_clients_works');
                            @endphp
                            @if(! $cl_works->isEmpty())
                            @foreach( $cl_works as $work)
                            <div class="examples-slide">
                                <a href="{{  $work->getUrl() }}" class="examples-slide__photo" data-fancybox="examples" aria-label="examples slide link">
                                    <picture>
                                        <img src="{{ $work->getUrl() }}" @altAttrs($item, 'media:new_clients_works', $work->getUrl(), null, $work->getCustomProperty('image_alt_' . app()->getLocale()), $work->getCustomProperty('image_title_' . app()->getLocale())) loading="lazy" />
                                    </picture>
                                </a>
                                <a href="#" class="examples-slide__title">
                                    @if ( $work->getCustomProperty('name'))
                                    {{ str_trans( $work->getCustomProperty('name')) }}
                                    @endif
                                </a>
                                <div class="examples-size">
                                    <svg class="happy-item__icon">
                                        <use xlink:href="{{ asset(env('THEME').'sprite.svg#size') }}"></use>
                                    </svg>
                                    @if ( $work->getCustomProperty('size'))
                                    <p>{{ $work->getCustomProperty('size') }}</p>
                                    @endif
                                </div>
                                <a href="#" class="examples-slide__btn js-examples" data-catid="{{ $current_quiz_style_id }}" @if ($work->getCustomProperty('name')) data-style="{{ str_trans($work->getCustomProperty('name')) }}" @endif
                                    @if ($work->getCustomProperty('size')) data-size="{{ str_trans($work->getCustomProperty('size')) }}" @endif
                                    >{{ trans('homepage_new.our_portraits_btn_title') }}</a>
                            </div>
                            @endforeach
                            @endif
                        </div>
                        <div class="examples-arrow">
                            <a href="#" class="examples-prev c-ex-prev" aria-label="examples prev">
                                <i class="fa-arrow-prev"></i>
                            </a>
                            <a href="#" class="examples-next c-ex-next" aria-label="examples next">
                                <i class="fa-arrow-next"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            @if($item->video && ($item->video != '[]'))
            <div class="p-examples-bottom">
                <div class="section-frame">
                    <div class="p-examples-bottom--inner">
                        <div class="title-left">
                            <div class="page-title h2_old">
                                {{ trans('portrait.ex_video_title') }}
                            </div>
                            <p>{{ trans('portrait.ex_video_desc') }}</p>
                        </div>
                        <div class="video-wrapper">
                            <div class="video-container">
                                @php $vid = (json_decode($item->video))[0]->download_link; @endphp
                                <video width="1128" height="566" id="videoPlayer" preload="metadata" controls="controls">
                                    <source src="{{ Voyager::image( $vid ) }}#t=0.5" type="video/mp4" />
                                </video>
                                <div class="video-btn">
                                    <img src="{{ asset(env('THEME').'images/play.svg') }}" alt="" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </section>
        <section class="lozad portait-why">
            <div class="section-frame">
                <div class="top-title">
                    <div class="page-title h2_old">{{ trans('portrait.des_help_title') }}</div>
                    <p>{{ trans('portrait.des_help_desc') }}</p>
                </div>
                <div class="why-list">
                    <div class="why-box">
                        <div class="why-item">
                            <p>
                                {!! trans('portrait.des_help_step1') !!}
                            </p>
                        </div>
                        <div class="why-item">
                            <p>
                                {!! trans('portrait.des_help_step2') !!}
                            </p>
                        </div>
                    </div>
                    <div class="why-box">
                        <div class="why-item">
                            <p>
                                {!! trans('portrait.des_help_step3') !!}
                            </p>
                        </div>
                        <div class="why-item">
                            <p>
                                {!! trans('portrait.des_help_step4') !!}
                            </p>
                        </div>
                    </div>
                </div>
                <form action="{{ route('send_all_styles_form') }}" method="POST" enctype="multipart/form-data" class="why-form submit-form portait-why-form">
                    @csrf
                    <div class="why-form__title h3_old">{{ $top_form->getTranslatedAttribute('f_title') }}</div>
                    <input type="hidden" id="new_name" name="new_name" value="{{ $top_form->getTranslatedAttribute('f_title') }}">
                    <div class="f__errs">
                        @if ($errors)
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                    <div class="why-form__group">
                        <div class="kviz-input">
                            <p class="kviz-input__title">{!! $top_form->getTranslatedAttribute('f_email') !!}</p>
                            <div class="page-input__item">
                                <input type="email" name="email" placeholder="E-mail" required="" />
                                <svg class="kviz-input__icon">
                                    <use xlink:href="{{ asset(env('THEME').'sprite.svg#mail') }}"></use>
                                </svg>
                            </div>
                        </div>
                        <div class="kviz-input">
                            <p class="kviz-input__title">{!! $top_form->getTranslatedAttribute('f_phone') !!}</p>
                            <div class="page-input__item phone-input">
                                <div class="banner__input-item">
                                    <input type="text" id="phone2" name="phone" class="banner__input phone" required>
                                    <img src="{{ asset(env('THEME').'img/icons/phone.svg') }}" alt="" class="img-svg img-svg__posa">
                                </div>
                            </div>
                        </div>
                        <div class="kviz-input">
                            <p class="kviz-input__title">{{ trans('portrait.form_add_comment') }}</p>
                            <div class="page-input__item">
                                <div class="kviz-textarea">
                                    <textarea name="comments"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="kviz-input">
                            <p class="kviz-input__title">{{ trans('portrait.form_photo') }}</p>
                            <div class="file-save">
                                <div class="abs-close">
                                    X
                                </div>
                                <div class="file-save__item js-file-preview">
                                    <svg>
                                        <use xlink:href="{{ asset(env('THEME').'sprite.svg#save') }}"></use>
                                    </svg>
                                    <div class="file-save__title">
                                        <p>{{ trans('homepage_new.load_photo') }}</p>
                                        <span>{{ trans('homepage_new.pree_to_add_photo') }}</span>
                                    </div>
                                </div>
                                <div class="file-save__item js-file-upload">
                                    <svg>
                                        <use xlink:href="{{ asset(env('THEME').'sprite.svg#picture') }}"></use>
                                    </svg>
                                    <div class="file-save__title">
                                        <p>photo_34567.jpg</p>
                                        <span>2 Mb</span>
                                    </div>
                                </div>
                                <div class="file-save__item js-file-multiple">
                                    <svg>
                                        <use xlink:href="{{ asset(env('THEME').'sprite.svg#check') }}"></use>
                                    </svg>
                                    <div class="file-save__title">
                                        <p class="file-title_green">{{ trans('portrait.form_files_loaded') }}</p>
                                    </div>
                                </div>
                                <input type="file" class="file-input" name="file[]" multiple="" accept="image/*,image/heif,image/heic" aria-label="file input">
                            </div>
                        </div>
                    </div>
                    <label class="why-sub"> <input type="submit" />{!! $bot_form['send'] !!}</label>

                    <div class="loading-bar">
                        <span>
                            {{ trans('portrait_buy_form.loading') }}
                        </span>
                        <div class="l-progress">
                            <span></span><span></span><span></span><span></span><span></span><span></span>
                        </div>
                    </div>
                </form>
            </div>
        </section>
        @include('partials.index_new.faq9')
        @include('partials.portrait_new.etc_styles')
    </main>

{{--    @if($item->getTranslatedAttribute('seo'))--}}
{{--    <div class="seo-text section-frame">--}}
{{--        <div class="seo-text__wrapper">--}}
{{--            <div class="seo-text__text">--}}
{{--                {!! $item->getTranslatedAttribute('seo') !!}--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--    @endif--}}


    @widget('Footer')
    <a href="#top" class="vz-art scroll-button anchor" aria-label="anchor link">
        <i class="fa-arrow-next"></i>
    </a>
    @include('partials.index_new.modals')

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


    {{-- @include('partials.portrait_new.modals') --}}
    {{-- <script src="{{ ver_asset(env('THEME').'js/jquery3.min.js') }}"></script> --}}
    <script src="{{ ver_asset(env('THEME').'js/script.js') }}"></script>
    <script defer src="{{ ver_asset(env('THEME').'js/cart.min.js') }}"></script> <!-- main menu 4 -->
    <script src="{{ ver_asset(env('THEME').'js/custom.js') }}" defer=""></script>


    <script>
        setTimeout(function() {
            const elem = document.createElement('link');
            elem.type = 'text/css';
            elem.rel = 'stylesheet';
            elem.href = '/theme/viar/style/intlTelInput.min.css';
            document.body.appendChild(elem);
        }, 1500);
    </script>



    <script data-defdel="{{ ver_asset(env('THEME').'js/fancybox.js') }}"></script>
    <script data-defdel="{{ ver_asset(env('THEME').'js/swal.js') }}"></script>

    <script src="{{ ver_asset(env('THEME').'js/before-after.min.js') }}"></script>
    <script src="{{ ver_asset(env('THEME').'js/portrait_new.js') }}"></script>

    <script src="{{ ver_asset(env('THEME').'js/intlTelInput.min.js') }}"></script>
    <script src="{{ ver_asset(env('THEME').'js/portrait_new_phone.js') }}"></script>

    <script src="{{ ver_asset(env('THEME').'js/portrait_calc.js') }}"></script>

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
