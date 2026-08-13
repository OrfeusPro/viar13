<div itemtype="https://schema.org/Product" itemscope>
    @php
        $name=$home_slides[0]['title'];
        $strippedName = strip_tags($name);
        $cleanName = str_replace('"', '', $strippedName);
        $s_items = $item->getMedia('our_works_new');
    @endphp
    @if ($s_items)
        @foreach ($s_items as $image)
            <link itemprop="image" href="{{ $image->getUrl() }}">
        @endforeach
    @endif
{{--Для портретов--}}
    <meta itemprop="name" content="{{ $cleanName }}" />
        @if(isset($home_slides[0]) && $home_slides[0])
            @include(env('THEME_RESOURCES') . 'pages.portrait.breads')
            @include(env('THEME_RESOURCES') . 'pages.portrait.slider')
        @else
            <div class="portraits portrait__screen">
                <div class="portraits-wrap">
                    <div class="portraits-slider sl-slider">
                        <article class="portraits-slide">
                            <div class="section-frame">
                                @include(env('THEME_RESOURCES') . 'pages.portrait.breads2')
                                @include(env('THEME_RESOURCES') . 'pages.portrait.h_zero')
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
                    <h2 class="page-title h2_old">
                        @if ($h2_titles->zakaz_title_h2!='')
                            {!!   $h2_titles->zakaz_title_h2 !!}
                        @else
                            {{ trans('portrait.tabs_title') }}
                        @endif
                    </h2>
                </div>
                <div class="about__screen--wrapper">
                    <div class="about-tabs">
                        <div class="about-tab active">
                            <a href="#">{{ trans('portrait.tab4_title') }}</a>
                        </div>
                        <div class="about-tab">
                            <a href="#">{{ trans('portrait.tab5_title') }}</a>
                        </div>
                        <div class="about-tab">
                            <a href="#">{{ trans('portrait.tab1_title') }}</a>
                        </div>
                        <div class="about-tab">
                            <a href="#">{{ trans('portrait.tab2_title') }}</a>
                        </div>
                    </div>
                </div>
                <div class="about__screen--blocks">
                    @include(env('THEME_RESOURCES') . 'pages.portrait.tabs.fourth', ['is_hidden'=>false])
                    @include(env('THEME_RESOURCES') . 'pages.portrait.tabs.fifth')
                    @include(env('THEME_RESOURCES') . 'pages.portrait.tabs.first')
                    @include(env('THEME_RESOURCES') . 'pages.portrait.tabs.second')

                </div>
            </div>
        </section>
        @include(env('THEME_RESOURCES') . 'pages.portrait.examples')
        <div class="ellipse ellipse_black custom-ellipse">
            <img src="{{ asset(env('THEME').'images/icon/ellipse-black.svg') }}" class="ellipse_bottom" alt="" loading="lazy" />
        </div>
        <div class="mobile-ell">
            <img src="{{ asset(env('THEME').'images/sizes/union.png') }}" alt="" />
            <div class="mobile-size-title">{{ trans('portrait.sizes__title') }}</div>
        </div>
        @include(env('THEME_RESOURCES') . 'pages.portrait.sizes')
        <div class="ellipse ellipse_black ellipse_top">
            <a href="#examples" class="anchor ellipse-arrow ellipse-arrow_white" aria-label="anchor link">
                <i class="fa-arrow-down"></i>
            </a>
            <img src="{{ asset(env('THEME').'images/icon/ellipse-black.svg') }}" alt="img" loading="lazy" />
        </div>
        @include(env('THEME_RESOURCES') . 'pages.portrait.order_steps')
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
                                        @php
                                            $portraitClientWorkImageSources = image_picture_sources($work->getUrl(), true);
                                        @endphp
                                        @if(!empty($portraitClientWorkImageSources['src_webp']))
                                            <source srcset="{{ $portraitClientWorkImageSources['src_webp'] }}" type="image/webp">
                                        @endif
                                        @if(!empty($portraitClientWorkImageSources['src']) && !empty($portraitClientWorkImageSources['type']))
                                            <source srcset="{{ $portraitClientWorkImageSources['src'] }}" type="{{ $portraitClientWorkImageSources['type'] }}">
                                        @endif
                                        <img src="{{ $portraitClientWorkImageSources['src'] }}" @altAttrs($item, 'media:new_clients_works', $work->getUrl(), null, $work->getCustomProperty('image_alt_' . app()->getLocale()), $work->getCustomProperty('image_title_' . app()->getLocale())) loading="lazy" />
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
            </div>
        </section>
        <br>
        @include(env('THEME_RESOURCES') . 'pages.gallery.item-card_part-about')
        @include(env('THEME_RESOURCES') . 'pages.index.faq9')
        @include(env('THEME_RESOURCES') . 'pages.portrait.etc_styles')
    @include(  'partials.schema_reviews')
</div>
