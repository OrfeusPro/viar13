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
                        <a href="#">{{ trans('portrait.tab6_title') }}</a>
                    </div>
                    <div class="about-tab">
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
                @include(env('THEME_RESOURCES') . 'pages.portrait.tabs.six')
                @include(env('THEME_RESOURCES') . 'pages.portrait.tabs.portrait_oil')
                @include(env('THEME_RESOURCES') . 'pages.portrait.tabs.portrait_oil_two_pictures')
                @include(env('THEME_RESOURCES') . 'pages.portrait.tabs.second')
                @include(env('THEME_RESOURCES') . 'pages.portrait.tabs.fifth')
            </div>
        </div>
    </section>
    <div class="category-pop">
        <div class="section-frame">
            <div class="category-pop__inner">
                <h2 class="portrait-about__title">
                    Образы <span>для портретов</span>
                </h2>
                <div class="category-pop--block">
                    <div class="cp-tabs">
                        <div class="cp-tabs-inner">
                            <div class="cp-tab active" data-cat="all">Все</div>
                            @foreach ($list['list_category'] as $cat)
                                <div class="cp-tab" data-cat="{{ trim($cat) }}">@lang('portrait_royal.' . trim($cat))</div>
                            @endforeach
                        </div>
                    </div>
                    <div class="cp-blocks">
                        <div class="cp-block">
                            <div class="cp-block-inner">
                                @foreach ($list['list'] as $category => $listItem)

                                    @if($loop->iteration > 3) @break @endif

                                    <div class="cp-item">
                                        <div class="cp-item-inner">
                                            <div class="cp-item-inner-b">
                                                <a href="#" class="mm-btn">@lang("gallery.see")</a>
                                            </div>

                                            @if($listItem->src)
                                                <img width="433" height="583" src="{{ asset('/storage') }}/{{ $listItem->src }}" alt="{{ $listItem->title }}" loading="lazy">
                                            @endif
                                        </div>
                                        <p>{{ $listItem->title }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @foreach ($list['list'] as $category => $listItem)
                                <div class="cp-block hidden-block">
                                    <div class="cp-block-inner">
                                            @if($loop->iteration > 6) @break @endif
                                            <div class="cp-item">
                                                <div class="cp-item-inner">
                                                    <div class="cp-item-inner-b">
                                                        <a href="#" class="mm-btn">@lang("gallery.see")</a>
                                                    </div>
                                                    <img width="433" height="583" src="{{ asset('/storage') }}/{{ $listItem->src }}" alt="{{ $listItem->title }}" loading="lazy">
                                                </div>
                                                <p>{{ $listItem->title }}</p>
                                            </div>
                                    </div>
                                </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if(!$s_items->isEmpty())
        <div class="portrait__examples">
            <div class="container">
                <h2 class="portrait-about__title">
                    <span>Примеры работ</span> <br> студии ViarCanvas
                </h2>
                <br>
                <br>
                <div class="examples-portrait__slider">
                    @foreach($s_items as $s_item)
                    <div class="portrait__images-examples--item">
                        <div class="item-description">
                            {!! $item->getTranslatedAttribute('name') !!}
                        </div>
                        <div class="portrait__images-examples--img">
                            <img src="{{ $s_item->getUrl() }}" @altAttrs($item, 'media:our_works_new', $s_item->getUrl(), null, $s_item->getCustomProperty('image_alt_' . app()->getLocale()), $s_item->getCustomProperty('image_title_' . app()->getLocale()))>
                        </div>
                        <div class="portrait__images-examples--background">
                            <a
                                href="#"
                                class="portrait__images-examples--btn js-examples"
                                data-catid="{{ $current_quiz_style_id }}"
                                data-style="{{ $item->getTranslatedAttribute('name') }}"
                                @if ($s_item->getCustomProperty('name')) data-style="{{ str_trans($s_item->getCustomProperty('name')) }}" @endif
                                @if ($s_item->getCustomProperty('size')) data-size="{{ str_trans($s_item->getCustomProperty('size')) }}" @endif
                            >
                                Заказать портрет
                            </a>
                        </div>

                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
    <div class="portrait__video">
        <div class="container">
            <div class="portrait__video-content">
                <div class="portrait__video-content--left">
                    <img src="{{ ver_asset('img/video-image.svg') }}" alt="">
                    <svg width="112" height="112" viewBox="0 0 112 112" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g filter="url(#filter0_d_2168_181)">
                            <circle cx="56" cy="50" r="36" fill="#FA7846"/>
                        </g>
                        <g clip-path="url(#clip0_2168_181)">
                            <path d="M46.7239 33C47.0229 33 47.3219 33 47.6375 33C48.1191 33.1984 48.634 33.3637 49.0659 33.6117C55.1449 37.1829 61.2239 40.754 67.303 44.3252C68.748 45.1849 70.2262 45.995 71.6214 46.9043C72.7509 47.6318 73.1661 48.7395 72.9336 50.0621C72.7509 51.0872 72.1363 51.7981 71.256 52.3106C66.4725 55.1212 61.6724 57.9319 56.8889 60.759C54.2314 62.3297 51.5573 63.8838 48.8998 65.4544C47.9198 66.0331 46.9066 66.1819 45.8769 65.7024C44.6644 65.1568 44 64.1814 44 62.8587C44 53.9474 44 45.0195 44 36.0917C44 36.0752 44 36.0421 44 36.0256C44.0498 35.1493 44.3986 34.4053 45.063 33.7936C45.5281 33.3637 46.126 33.1653 46.7239 33Z" fill="white"/>
                        </g>
                        <defs>
                            <filter id="filter0_d_2168_181" x="0" y="0" width="112" height="112" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                                <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                                <feOffset dy="6"/>
                                <feGaussianBlur stdDeviation="10"/>
                                <feComposite in2="hardAlpha" operator="out"/>
                                <feColorMatrix type="matrix" values="0 0 0 0 0.117647 0 0 0 0 0.145098 0 0 0 0 0.2 0 0 0 0.2 0"/>
                                <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_2168_181"/>
                                <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_2168_181" result="shape"/>
                            </filter>
                            <clipPath id="clip0_2168_181">
                                <rect width="29" height="33" fill="white" transform="translate(44 33)"/>
                            </clipPath>
                        </defs>
                    </svg>
                </div>
                <div class="portrait__video-content--right">
                    <div class="portrait__video-content--title">
                        Портрет который изготавливается
                        <span>полностью вручную.</span>
                    </div>
                    <div class="portrait__video-content--text">
                        <span>Художники в нашей студии - высококвалифицированные специалисты,</span> обладающие специальными навыками
                        и секретами живописи.
                    </div>
                    <div class="portrait__video-content--text">
                        Благодаря их мастерству и техникам, портреты получаются качественными и точно передают внешность и характер персонажа, основываясь на его фотографии, а также воплощают вашу задумку.
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include(config('theme.resource') . 'pages.portrait.gift-oil')
    @include(config('theme.resource') . 'pages.index.faq9')
</div>
<link rel="stylesheet" href="{{ ver_asset(config('theme.current').'style/sharj/sharj.css') }}"/>
<link rel="stylesheet" href="{{ ver_asset(config('theme.current').'style/gallery/gallery.css') }}"/>
<script src="{{ ver_asset(env('THEME').'js/oil.js') }}"></script>
<script src="{{ ver_asset(env('THEME').'js/delivery/gallery.js') }}"></script>
