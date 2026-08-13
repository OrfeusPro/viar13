@php
use App\Models\SiteImage;

/** @var \Illuminate\Support\Collection|SiteImage[] $siteImages */
$siteImages = $siteImages ?? SiteImage::where('is_show', true)->get();

$base = asset(env('THEME').'images').'/sharj/new/page/';

/*
 | Ключ -> дефолты desktop/mobile + размеры <img>
 | Если нужны другие дефолты — просто поменяй файлы тут.
*/
$defs = [
    'caricature_on_canvas' => ['desk' => 'a4.webp', 'mob' => 'a4Min.webp', 'w'=>450, 'h'=>360],
    'caricature_on_canvas_in_a_baguette_frame' => ['desk' => 'a1.webp', 'mob' => 'a1Min.webp', 'w'=>450, 'h'=>360],
    'caricature_on_paper_in_a_simple_frame'  => ['desk' => 'a3.webp', 'mob' => 'a3Min.webp', 'w'=>450, 'h'=>360],
    'caricature_on_paper_in_a_premium_frame' => ['desk' => 'a2.webp', 'mob' => 'a2Min.webp', 'w'=>450, 'h'=>360],
];

$car = [];
foreach ($defs as $key => $cfg) {
    $car[$key] = site_image_pair(
        $key,
        $key.'_mob',
        $base.$cfg['desk'],
        $base.$cfg['mob'],
        ['collection' => $siteImages]
    ) + ['w' => $cfg['w'], 'h' => $cfg['h']];
}
@endphp

<section class="about__screen" id="about">
    <div class="section-frame section-m-frame">
        <h2 class="page-title">
            @if ($h2_titles->zakaz_title_h2!='')
                {!! $h2_titles->zakaz_title_h2 !!}
            @else
                @lang('sharj.translate61')
            @endif
        </h2>
        <div class="about__screen--wrapper">
            <div class="about-tabs">
                <div class="about-tab active">
                    <a href="#">@lang('sharj.translate62')</a>
                </div>
                <div class="about-tab">
                    <a href="#">@lang('sharj.translate63')</a>
                </div>
                <div class="about-tab">
                    <a href="#">@lang('sharj.translate64')</a>
                </div>
                <div class="about-tab">
                    <a href="#">@lang('sharj.translate65')</a>
                </div>
            </div>
        </div>
        <div class="about__screen--blocks">
            <div class="about__block">
                <div class="about-afterBefore">
                    <div class="about-afterBefore__grid">
                        <div class="about-afterBefore__img">
                            <div class="before-after__block">
                                <div class="title-group">
                                    {{-- <div class="arrow-mobile">
                                        <img width="80" height="42"
                                            src="{{ asset(env('THEME') . 'images') }}/sharj/new/categories/arrMin.svg"
                                            alt="">
                                    </div> --}}
                                    <div class="before-after__title orange">@lang('sharj.translate66')</div>
                                </div>
                                <div class="beforeAfter__slider">
                                    <div class="beforeAfter__slider-inner"> <?php $i=0; foreach ($ba_items as $ba_item){ ?>
                                        <div class="ba-slider ba-slider1">
                                            @php
                                                $sharjBeforeImageSources = image_picture_sources($ba_item->getUrl(), true);
                                            @endphp
                                            <picture>
                                                @if(!empty($sharjBeforeImageSources['src_webp']))
                                                    <source srcset="{{ $sharjBeforeImageSources['src_webp'] }}" type="image/webp">
                                                @endif
                                                @if(!empty($sharjBeforeImageSources['src']) && !empty($sharjBeforeImageSources['type']))
                                                    <source srcset="{{ $sharjBeforeImageSources['src'] }}" type="{{ $sharjBeforeImageSources['type'] }}">
                                                @endif
                                                <img width="400" height="400" srcset="{{ $sharjBeforeImageSources['src'] }}"
                                                    alt="" />
                                            </picture>

                                            <div class="resize">
                                                @php
                                                    $sharjAfterImageSources = image_picture_sources($ba_items_after[$i]->getUrl(), true);
                                                @endphp
                                                <picture>
                                                    @if(!empty($sharjAfterImageSources['src_webp']))
                                                        <source srcset="{{ $sharjAfterImageSources['src_webp'] }}" type="image/webp">
                                                    @endif
                                                    @if(!empty($sharjAfterImageSources['src']) && !empty($sharjAfterImageSources['type']))
                                                        <source srcset="{{ $sharjAfterImageSources['src'] }}" type="{{ $sharjAfterImageSources['type'] }}">
                                                    @endif
                                                    <img width="400" height="400"
                                                        srcset="{{ $sharjAfterImageSources['src'] }}" alt="" />
                                                </picture>
                                            </div>
                                            <span class="handle"></span>
                                        </div>
                                        <?php $i++; } ?>
                                    </div>
                                    <div class="examples-arrow">
                                        <a href="#" class="examples-prev examples-prev2"
                                            aria-label="examples prev">
                                            <svg width="48" height="51" viewBox="0 0 48 51" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M27.2335 31.1145C27.334 31.0141 27.3842 30.8986 27.3842 30.768C27.3842 30.6374 27.334 30.5219 27.2335 30.4215L21.3122 24.5001L27.2335 18.5788C27.334 18.4784 27.3842 18.3629 27.3842 18.2323C27.3842 18.1017 27.334 17.9862 27.2335 17.8857L26.4802 17.1324C26.3797 17.0319 26.2642 16.9817 26.1336 16.9817C26.0031 16.9817 25.8876 17.0319 25.7871 17.1324L18.7659 24.1536C18.6655 24.254 18.6152 24.3696 18.6152 24.5001C18.6152 24.6307 18.6655 24.7462 18.7659 24.8467L25.7871 31.8679C25.8876 31.9683 26.0031 32.0186 26.1336 32.0186C26.2642 32.0186 26.3797 31.9683 26.4802 31.8679L27.2335 31.1145Z"
                                                    fill="#fff" />
                                            </svg>
                                        </a>
                                        <a href="#" class="examples-next examples-next2"
                                            aria-label="examples next">
                                            <svg width="48" height="51" viewBox="0 0 48 51" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M20.7665 31.1145C20.666 31.0141 20.6158 30.8986 20.6158 30.768C20.6158 30.6374 20.666 30.5219 20.7665 30.4215L26.6878 24.5001L20.7665 18.5788C20.666 18.4784 20.6158 18.3629 20.6158 18.2323C20.6158 18.1017 20.666 17.9862 20.7665 17.8857L21.5198 17.1324C21.6203 17.0319 21.7358 16.9817 21.8664 16.9817C21.9969 16.9817 22.1124 17.0319 22.2129 17.1324L29.2341 24.1536C29.3345 24.254 29.3848 24.3696 29.3848 24.5001C29.3848 24.6307 29.3345 24.7462 29.2341 24.8467L22.2129 31.8679C22.1124 31.9683 21.9969 32.0186 21.8664 32.0186C21.7358 32.0186 21.6203 31.9683 21.5198 31.8679L20.7665 31.1145Z"
                                                    fill="#fff" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                                <div class="before-after__text">@lang('sharj.translate67')</div>

                            </div>
                            {{-- <div class="pmo-block pmo-block1">
                                <p>@lang('sharj.translate47')</p>
                                <img width="52" height="80"
                                    src="{{ asset(env('THEME') . 'images') }}/sharj/new/pngwing.webp" alt="">
                            </div> --}}
                        </div>
                        <div class="about-afterBefore__content">
                            <ul>
                                <li>
                                    <div class="img">
                                        <picture>
                                            <source media="(max-width: 576px)"
                                                srcset="{{ asset(env('THEME') . 'images') }}/sharj/new/categories/c1Min.webp"
                                                type="image/webp">
                                            <source
                                                srcset="{{ asset(env('THEME') . 'images') }}/sharj/new/categories/c1.webp"
                                                type="image/webp">
                                            <img width="100" height="100"
                                                src="{{ asset(env('THEME') . 'images') }}/sharj/new/categories/c1.webp"
                                                alt="">
                                        </picture>
                                    </div>
                                    <div class="text">
                                        <p>@lang('sharj.translate68') </p>
                                    </div>
                                </li>
                                <li>
                                    <div class="img">
                                        <picture>
                                            <source media="(max-width: 576px)"
                                                srcset="{{ asset(env('THEME') . 'images') }}/sharj/new/categories/c2Min.webp"
                                                type="image/webp">
                                            <source
                                                srcset="{{ asset(env('THEME') . 'images') }}/sharj/new/categories/c2.webp"
                                                type="image/webp">
                                            <img width="100" height="100"
                                                src="{{ asset(env('THEME') . 'images') }}/sharj/new/categories/c2.webp"
                                                alt="">
                                        </picture>
                                    </div>
                                    <div class="text">
                                        <p><span class="orange">@lang('sharj.translate69')</span></p>
                                        <p>
                                            @lang('sharj.translate70')</p>
                                    </div>
                                </li>
                                <li>
                                    <div class="img">
                                        <picture>
                                            <source media="(max-width: 576px)"
                                                srcset="{{ asset(env('THEME') . 'images') }}/sharj/new/categories/c3Min.webp"
                                                type="image/webp">
                                            <source
                                                srcset="{{ asset(env('THEME') . 'images') }}/sharj/new/categories/c3.webp"
                                                type="image/webp">
                                            <img width="100" height="100"
                                                src="{{ asset(env('THEME') . 'images') }}/sharj/new/categories/c3.webp"
                                                alt="">
                                        </picture>
                                    </div>
                                    <div class="text">
                                        <p>@lang('sharj.translate71')</p>
                                        <p>
                                    </div>
                                </li>
                                <li>
                                    <div class="img">
                                        <picture>
                                            <source media="(max-width: 576px)"
                                                srcset="{{ asset(env('THEME') . 'images') }}/sharj/new/categories/c4Min.webp"
                                                type="image/webp">
                                            <source
                                                srcset="{{ asset(env('THEME') . 'images') }}/sharj/new/categories/c4.webp"
                                                type="image/webp">
                                            <img width="100" height="100"
                                                src="{{ asset(env('THEME') . 'images') }}/sharj/new/categories/c4.webp"
                                                alt="">
                                        </picture>
                                    </div>
                                    <div class="text">
                                        <p>@lang('sharj.translate72')</p>
                                        <p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="arrow-desk">
                            <img width="267" height="66"
                                src="{{ asset(env('THEME') . 'images') }}/sharj/new/categories/arr.svg"
                                alt="">
                        </div>
                    </div>
                </div>
            </div>
            <div class="about__block hidden-block">
                <div class="about__block-types">
                    <div class="page-title">@lang('sharj.translate5')</div>
                    <p>@lang('sharj.translate6')</p>
                    <div class="about__block-types-row">
                        <div class="img">
                            <div class="flex">
                                <img width="40" height="40"
                                    src="{{ asset(env('THEME') . 'images') }}/sharj/new/page/i1.svg" alt="">
                                <p> <span class="orange">@lang('sharj.translate7')</span></p>
                                <svg width="55" height="40" viewBox="0 0 55 40" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M49.6863 39.3056C49.8551 39.5241 50.1691 39.5644 50.3876 39.3956L53.9488 36.6446C54.1673 36.4758 54.2076 36.1617 54.0388 35.9432C53.87 35.7247 53.556 35.6844 53.3374 35.8532L50.172 38.2986L47.7266 35.1331C47.5578 34.9146 47.2438 34.8743 47.0252 35.0431C46.8067 35.2119 46.7664 35.5259 46.9352 35.7445L49.6863 39.3056ZM50.082 38.9999C50.5779 39.0636 50.578 39.0631 50.578 39.0625C50.5781 39.0621 50.5782 39.0613 50.5783 39.0605C50.5785 39.0589 50.5788 39.0567 50.5791 39.0539C50.5798 39.0484 50.5807 39.0406 50.5819 39.0306C50.5843 39.0104 50.5875 38.9813 50.5915 38.9435C50.5995 38.8678 50.6103 38.7572 50.6218 38.6141C50.645 38.3278 50.6714 37.911 50.6854 37.3823C50.7135 36.3251 50.6921 34.8188 50.4953 33.0118C50.1019 29.3995 49.0063 24.5733 46.193 19.7312C40.5446 10.0089 28.0619 0.369221 0.908681 0.175397L0.901546 1.17537C27.7942 1.36734 39.8999 10.8899 45.3284 20.2335C48.0537 24.9243 49.1186 29.6068 49.5012 33.1201C49.6924 34.8759 49.7129 36.3366 49.6858 37.3557C49.6723 37.8652 49.6469 38.2639 49.6251 38.5335C49.6142 38.6683 49.6042 38.7708 49.597 38.8387C49.5935 38.8727 49.5906 38.898 49.5887 38.9143C49.5877 38.9225 49.587 38.9284 49.5866 38.9321C49.5863 38.934 49.5862 38.9352 49.5861 38.9359C49.586 38.9363 49.586 38.9364 49.586 38.9365C49.586 38.9365 49.586 38.9363 50.082 38.9999Z"
                                        fill="#FA7846" />
                                </svg>
                            </div>
                        @php($k = 'caricature_on_canvas')
                        <picture>
                        @if(!empty($car[$k]['mob']['src_webp']))
                            <source media="(max-width: 576px)" srcset="{{ $car[$k]['mob']['src_webp'] }}" type="image/webp">
                        @endif
                        @if(!empty($car[$k]['mob']['type']))
                            <source media="(max-width: 576px)" srcset="{{ $car[$k]['mob']['src'] }}" type="{{ $car[$k]['mob']['type'] }}"> @endif
                        @if(!empty($car[$k]['desk']['src_webp']))
                            <source srcset="{{ $car[$k]['desk']['src_webp'] }}" type="image/webp"> @endif
                        @if(!empty($car[$k]['desk']['type']))
                            <source srcset="{{ $car[$k]['desk']['src'] }}" type="{{ $car[$k]['desk']['type'] }}"> @endif
                            <img loading="lazy" width="{{ $car[$k]['w'] }}" height="{{ $car[$k]['h'] }}" src="{{ $car[$k]['desk']['src'] }}" alt="{{ $car[$k]['desk']['alt'] ?? '' }}" title="{{ $car[$k]['desk']['title'] ?? '' }}">
                        </picture>
                        </div>
                        <ul>
                            <li>
                                <img width="65" height="65" src="{{ asset(env('THEME') . 'images') }}/sharj/new/page/ai1.webp" lt="">
                                    <p>{!! trans('portrait_royal.tab_two_types_body_list_1') !!}</p>
                            </li>
                            <li>
                                <img width="65" height="65" src="{{ asset(env('THEME') . 'images') }}/sharj/new/page/ai2.webp" alt="">
                                <p>{!! trans('portrait_royal.tab_two_types_body_list_2') !!}</p>
                            </li>
                            <li>
                                <img width="65" height="65" src="{{ asset(env('THEME') . 'images') }}/sharj/new/page/ai3.webp" alt="">
                                <p>{!! trans('portrait_royal.tab_two_types_body_list_3') !!}</b></p>
                            </li>
                        </ul>
                        <div class="img">
                            <div class="flex">
                                <img width="40" height="40" src="{{ asset(env('THEME') . 'images') }}/sharj/new/page/i2.svg"
                                    alt="">
                                <p> @lang('sharj.translate11')</p>
                                <svg width="55" height="40" viewBox="0 0 55 40" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M49.6863 39.3056C49.8551 39.5241 50.1691 39.5644 50.3876 39.3956L53.9488 36.6446C54.1673 36.4758 54.2076 36.1617 54.0388 35.9432C53.87 35.7247 53.556 35.6844 53.3374 35.8532L50.172 38.2986L47.7266 35.1331C47.5578 34.9146 47.2438 34.8743 47.0252 35.0431C46.8067 35.2119 46.7664 35.5259 46.9352 35.7445L49.6863 39.3056ZM50.082 38.9999C50.5779 39.0636 50.578 39.0631 50.578 39.0625C50.5781 39.0621 50.5782 39.0613 50.5783 39.0605C50.5785 39.0589 50.5788 39.0567 50.5791 39.0539C50.5798 39.0484 50.5807 39.0406 50.5819 39.0306C50.5843 39.0104 50.5875 38.9813 50.5915 38.9435C50.5995 38.8678 50.6103 38.7572 50.6218 38.6141C50.645 38.3278 50.6714 37.911 50.6854 37.3823C50.7135 36.3251 50.6921 34.8188 50.4953 33.0118C50.1019 29.3995 49.0063 24.5733 46.193 19.7312C40.5446 10.0089 28.0619 0.369221 0.908681 0.175397L0.901546 1.17537C27.7942 1.36734 39.8999 10.8899 45.3284 20.2335C48.0537 24.9243 49.1186 29.6068 49.5012 33.1201C49.6924 34.8759 49.7129 36.3366 49.6858 37.3557C49.6723 37.8652 49.6469 38.2639 49.6251 38.5335C49.6142 38.6683 49.6042 38.7708 49.597 38.8387C49.5935 38.8727 49.5906 38.898 49.5887 38.9143C49.5877 38.9225 49.587 38.9284 49.5866 38.9321C49.5863 38.934 49.5862 38.9352 49.5861 38.9359C49.586 38.9363 49.586 38.9364 49.586 38.9365C49.586 38.9365 49.586 38.9363 50.082 38.9999Z"
                                        fill="#FA7846" />
                                </svg>
                            </div>
                        @php($k = 'caricature_on_canvas_in_a_baguette_frame')
                        <picture>
                        @if(!empty($car[$k]['mob']['src_webp']))
                            <source media="(max-width: 576px)" srcset="{{ $car[$k]['mob']['src_webp'] }}" type="image/webp">
                        @endif
                        @if(!empty($car[$k]['mob']['type']))
                            <source media="(max-width: 576px)" srcset="{{ $car[$k]['mob']['src'] }}" type="{{ $car[$k]['mob']['type'] }}"> @endif
                        @if(!empty($car[$k]['desk']['src_webp']))
                            <source srcset="{{ $car[$k]['desk']['src_webp'] }}" type="image/webp"> @endif
                        @if(!empty($car[$k]['desk']['type']))
                            <source srcset="{{ $car[$k]['desk']['src'] }}" type="{{ $car[$k]['desk']['type'] }}"> @endif
                            <img loading="lazy" width="{{ $car[$k]['w'] }}" height="{{ $car[$k]['h'] }}" src="{{ $car[$k]['desk']['src'] }}" alt="{{ $car[$k]['desk']['alt'] ?? '' }}" title="{{ $car[$k]['desk']['title'] ?? '' }}">
                        </picture>
                        </div>
                    </div>
                    <div class="page-title center-title">
                        {!! trans('homepage_new_login_reg.or') !!}
                    </div>
                    <div class="about__block-types-row">
                        <div class="img">
                            <div class="flex mb-2">
                                <img width="40" height="40" src="{{ asset(env('THEME') . 'images') }}/sharj/new/page/i3.svg"
                                    alt="">
                                <p> <span class="orange">@lang('sharj.translate12')</p>
                                <svg width="55" height="40" viewBox="0 0 55 40" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M49.6863 39.3056C49.8551 39.5241 50.1691 39.5644 50.3876 39.3956L53.9488 36.6446C54.1673 36.4758 54.2076 36.1617 54.0388 35.9432C53.87 35.7247 53.556 35.6844 53.3374 35.8532L50.172 38.2986L47.7266 35.1331C47.5578 34.9146 47.2438 34.8743 47.0252 35.0431C46.8067 35.2119 46.7664 35.5259 46.9352 35.7445L49.6863 39.3056ZM50.082 38.9999C50.5779 39.0636 50.578 39.0631 50.578 39.0625C50.5781 39.0621 50.5782 39.0613 50.5783 39.0605C50.5785 39.0589 50.5788 39.0567 50.5791 39.0539C50.5798 39.0484 50.5807 39.0406 50.5819 39.0306C50.5843 39.0104 50.5875 38.9813 50.5915 38.9435C50.5995 38.8678 50.6103 38.7572 50.6218 38.6141C50.645 38.3278 50.6714 37.911 50.6854 37.3823C50.7135 36.3251 50.6921 34.8188 50.4953 33.0118C50.1019 29.3995 49.0063 24.5733 46.193 19.7312C40.5446 10.0089 28.0619 0.369221 0.908681 0.175397L0.901546 1.17537C27.7942 1.36734 39.8999 10.8899 45.3284 20.2335C48.0537 24.9243 49.1186 29.6068 49.5012 33.1201C49.6924 34.8759 49.7129 36.3366 49.6858 37.3557C49.6723 37.8652 49.6469 38.2639 49.6251 38.5335C49.6142 38.6683 49.6042 38.7708 49.597 38.8387C49.5935 38.8727 49.5906 38.898 49.5887 38.9143C49.5877 38.9225 49.587 38.9284 49.5866 38.9321C49.5863 38.934 49.5862 38.9352 49.5861 38.9359C49.586 38.9363 49.586 38.9364 49.586 38.9365C49.586 38.9365 49.586 38.9363 50.082 38.9999Z"
                                        fill="#FA7846" />
                                </svg>
                            </div>
                        @php($k = 'caricature_on_paper_in_a_simple_frame')
                        <picture>
                        @if(!empty($car[$k]['mob']['src_webp']))
                            <source media="(max-width: 576px)" srcset="{{ $car[$k]['mob']['src_webp'] }}" type="image/webp">
                        @endif
                        @if(!empty($car[$k]['mob']['type']))
                            <source media="(max-width: 576px)" srcset="{{ $car[$k]['mob']['src'] }}" type="{{ $car[$k]['mob']['type'] }}"> @endif
                        @if(!empty($car[$k]['desk']['src_webp']))
                            <source srcset="{{ $car[$k]['desk']['src_webp'] }}" type="image/webp"> @endif
                        @if(!empty($car[$k]['desk']['type']))
                            <source srcset="{{ $car[$k]['desk']['src'] }}" type="{{ $car[$k]['desk']['type'] }}"> @endif
                            <img loading="lazy" width="{{ $car[$k]['w'] }}" height="{{ $car[$k]['h'] }}" src="{{ $car[$k]['desk']['src'] }}" alt="{{ $car[$k]['desk']['alt'] ?? '' }}" title="{{ $car[$k]['desk']['title'] ?? '' }}">
                        </picture>
                        </div>
                        <ul>
                            <li>
                                <img width="65" height="65" src="{{ asset(env('THEME') . 'images') }}/sharj/new/page/ai4.webp" alt="">
                                    <p>{!! trans('portrait_royal.tab_two_types_body_list_4') !!}</p>
                            </li>
                            <li>
                                <img width="65" height="65" src="{{ asset(env('THEME') . 'images') }}/sharj/new/page/ai5.webp" alt="">
                                <p>{!! trans('portrait_royal.tab_two_types_body_list_5') !!}/p>
                            </li>
                        </ul>
                        <div class="img">
                            <div class="flex">
                                <img width="40" height="40" src="{{ asset(env('THEME') . 'images') }}/sharj/new/page/i4.svg"
                                    alt="">
                                <p> @lang('sharj.translate15')</p>
                                <svg width="55" height="40" viewBox="0 0 55 40" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M49.6863 39.3056C49.8551 39.5241 50.1691 39.5644 50.3876 39.3956L53.9488 36.6446C54.1673 36.4758 54.2076 36.1617 54.0388 35.9432C53.87 35.7247 53.556 35.6844 53.3374 35.8532L50.172 38.2986L47.7266 35.1331C47.5578 34.9146 47.2438 34.8743 47.0252 35.0431C46.8067 35.2119 46.7664 35.5259 46.9352 35.7445L49.6863 39.3056ZM50.082 38.9999C50.5779 39.0636 50.578 39.0631 50.578 39.0625C50.5781 39.0621 50.5782 39.0613 50.5783 39.0605C50.5785 39.0589 50.5788 39.0567 50.5791 39.0539C50.5798 39.0484 50.5807 39.0406 50.5819 39.0306C50.5843 39.0104 50.5875 38.9813 50.5915 38.9435C50.5995 38.8678 50.6103 38.7572 50.6218 38.6141C50.645 38.3278 50.6714 37.911 50.6854 37.3823C50.7135 36.3251 50.6921 34.8188 50.4953 33.0118C50.1019 29.3995 49.0063 24.5733 46.193 19.7312C40.5446 10.0089 28.0619 0.369221 0.908681 0.175397L0.901546 1.17537C27.7942 1.36734 39.8999 10.8899 45.3284 20.2335C48.0537 24.9243 49.1186 29.6068 49.5012 33.1201C49.6924 34.8759 49.7129 36.3366 49.6858 37.3557C49.6723 37.8652 49.6469 38.2639 49.6251 38.5335C49.6142 38.6683 49.6042 38.7708 49.597 38.8387C49.5935 38.8727 49.5906 38.898 49.5887 38.9143C49.5877 38.9225 49.587 38.9284 49.5866 38.9321C49.5863 38.934 49.5862 38.9352 49.5861 38.9359C49.586 38.9363 49.586 38.9364 49.586 38.9365C49.586 38.9365 49.586 38.9363 50.082 38.9999Z"
                                        fill="#FA7846" />
                                </svg>
                            </div>
                        @php($k = 'caricature_on_paper_in_a_premium_frame')
                        <picture>
                        @if(!empty($car[$k]['mob']['src_webp']))
                            <source media="(max-width: 576px)" srcset="{{ $car[$k]['mob']['src_webp'] }}" type="image/webp">
                        @endif
                        @if(!empty($car[$k]['mob']['type']))
                            <source media="(max-width: 576px)" srcset="{{ $car[$k]['mob']['src'] }}" type="{{ $car[$k]['mob']['type'] }}"> @endif
                        @if(!empty($car[$k]['desk']['src_webp']))
                            <source srcset="{{ $car[$k]['desk']['src_webp'] }}" type="image/webp"> @endif
                        @if(!empty($car[$k]['desk']['type']))
                            <source srcset="{{ $car[$k]['desk']['src'] }}" type="{{ $car[$k]['desk']['type'] }}"> @endif
                            <img loading="lazy" width="{{ $car[$k]['w'] }}" height="{{ $car[$k]['h'] }}" src="{{ $car[$k]['desk']['src'] }}" alt="{{ $car[$k]['desk']['alt'] ?? '' }}" title="{{ $car[$k]['desk']['title'] ?? '' }}">
                        </picture>
                        </div>
                    </div>
                </div>
            </div>
            <div class="about__block about__second hidden-block brief-v">
                <div class="about-fit--block">
                    <div class="fit-block--inner">
                        <div class="fit-icon">
                            <svg width="53" height="52" viewBox="0 0 53 52" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M51.5 26C51.5 39.8071 40.3071 51 26.5 51C12.6929 51 1.5 39.8071 1.5 26C1.5 12.1929 12.6929 1 26.5 1C40.3071 1 51.5 12.1929 51.5 26Z"
                                    stroke="#FA7846" stroke-width="2" />
                                <path
                                    d="M24.4258 29.0752H26.8484V28.8578C26.8612 27.6114 27.3086 27.0297 28.3185 26.4225C29.5138 25.7129 30.2937 24.7733 30.2937 23.2712C30.2937 21.034 28.4911 19.73 25.9535 19.73C23.6332 19.73 21.7411 20.9445 21.6836 23.5013H24.2915C24.3299 22.4594 25.1033 21.9033 25.9407 21.9033C26.8036 21.9033 27.5004 22.4786 27.5004 23.3671C27.5004 24.2044 26.8931 24.7605 26.1069 25.2591C25.033 25.9367 24.4322 26.6206 24.4258 28.8578V29.0752ZM25.685 33.1661C26.5032 33.1661 27.2127 32.4821 27.2191 31.632C27.2127 30.7946 26.5032 30.1107 25.685 30.1107C24.8413 30.1107 24.1445 30.7946 24.1509 31.632C24.1445 32.4821 24.8413 33.1661 25.685 33.1661Z"
                                    fill="#FA7846" />
                            </svg>
                        </div>
                        <div class="fit-content">
                            <div class="fit-title">
                                @lang('sharj.translate75')
                            </div>
                            <div class="fit-text">
                                @lang('sharj.translate76')
                            </div>
                        </div>
                    </div>
                    <div class="fit-image--bg">
                        <picture>
                            <source srcset="/images/about-m1.webp" type="image/webp" />
                            <source srcset="/images/about-m1.png" type="image/png" />
                            <img width="100" height="100" src="/images/about-m1.png" alt="" />
                        </picture>
                    </div>
                </div>
                <div class="about-offers">
                    <div class="about-offer">
                        <div class="offer-text">
                            <p>
                                @lang('sharj.translate77')
                            </p>
                        </div>
                        <div class="offer-img">
                            <picture>
                                <source media="(max-width: 500px)" srcset="/images/fit-1min.webp"
                                    type="image/webp" />
                                <source media="(max-width: 500px)" srcset="/images/fit-1min.jpg" type="image/jpg" />
                                <source srcset="/images/fit-1.webp" type="image/webp" />
                                <source srcset="/images/fit-1.jpg" type="image/jpg" />
                                <img width="100" height="100" src="/images/fit-1.jpg" alt="" />
                            </picture>
                        </div>
                    </div>
                    <div class="about-offer">
                        <div class="offer-text">
                            <p>
                                @lang('sharj.translate78')
                            </p>
                        </div>
                        <div class="offer-img">
                            <picture>
                                <source media="(max-width: 500px)" srcset="/images/fit-2min.webp"
                                    type="image/webp" />
                                <source media="(max-width: 500px)" srcset="/images/fit-2min.jpg" type="image/jpg" />
                                <source srcset="/images/fit-2.webp" type="image/webp" />
                                <source srcset="/images/fit-2.jpg" type="image/jpg" />
                                <img width="100" height="100" src="/images/fit-2.jpg" alt="" />
                            </picture>
                        </div>
                    </div>
                    <div class="about-offer">
                        <div class="offer-text">
                            <p>
                                @lang('sharj.translate79')
                            </p>
                        </div>
                        <div class="offer-img">
                            <picture>
                                <source media="(max-width: 500px)" srcset="/images/fit-3min.webp"
                                    type="image/webp" />
                                <source media="(max-width: 500px)" srcset="/images/fit-3min.jpg" type="image/jpg" />
                                <source srcset="/images/fit-3.webp" type="image/webp" />
                                <source srcset="/images/fit-3.jpg" type="image/jpg" />
                                <img width="100" height="100" src="/images/fit-3.jpg" alt="" />
                            </picture>
                        </div>
                    </div>
                </div>
                <div class="about--notes">
                    <div class="about--note">
                        <span>*</span>
                        <p>
                            @lang('sharj.translate80')
                        </p>
                    </div>
                    <div class="about--note">
                        <span>*</span>
                        <p>
                            @lang('sharj.translate80')
                        </p>
                    </div>
                </div>
                <div class="hidden-trigger">
                    <a href="#examples" class="anchor ellipse-arrow ellipse-arrow_white" aria-label="anchor link">
                        <i class="fa-arrow-down"></i>
                    </a>
                    <span>@lang('sharj.translate74')</span>
                    <span>@lang('sharj.translate73')</span>
                </div>
            </div>
            <div class="about__block about__fifth hidden-block brief-v">
                <div class="about-deadline">
                    <div class="deadline-block">
                        <div class="deadline-title">
                            <img width="100" height="100" src="/images/clock.png" alt="" />
                            <p>@lang('sharj.translate81')</p>
                        </div>
                        <div class="deadline-items">
                            <div class="deadline-item">
                                <div class="deadline-img">
                                    <picture>
                                        <source srcset="/images/three-days.webp" type="image/webp" />
                                        <source srcset="/images/three-days.jpg" type="image/jpg" />
                                        <img width="100" height="100" src="/images/three-days.jpg"
                                            alt="" />
                                    </picture>
                                </div>
                                <div class="deadline-content">
                                    <div class="deadline-desc">
                                        @lang('sharj.translate82')
                                    </div>
                                    <div class="deadline-text">
                                        @lang('sharj.translate3')
                                    </div>
                                </div>
                            </div>
                            <div class="deadline-item">
                                <div class="deadline-img">
                                    <picture>
                                        <source media="(max-width: 500px)" srcset="/images/one-dayMin.webp"
                                            type="image/webp" />
                                        <source media="(max-width: 500px)" srcset="/images/one-dayMin.jpg"
                                            type="image/jpg" />
                                        <source srcset="/images/one-day.webp" type="image/webp" />
                                        <source srcset="/images/one-day.jpg" type="image/jpg" />
                                        <img width="100" height="100" src="/images/one-day.jpg"
                                            alt="" />
                                    </picture>
                                </div>
                                <div class="deadline-content">
                                    <div class="deadline-desc">
                                        @lang('sharj.translate84')
                                    </div>
                                    <div class="deadline-text">
                                        @lang('sharj.translate85')
                                    </div>
                                </div>
                            </div>
                            <div class="deadline-item">
                                <div class="deadline-img">
                                    <picture>
                                        <source srcset="/images/on-date.webp" type="image/webp" />
                                        <source srcset="/images/on-date.jpg" type="image/jpg" />
                                        <img width="100" height="100" src="/images/on-date.jpg"
                                            alt="" />
                                    </picture>
                                </div>
                                <div class="deadline-content">
                                    <div class="deadline-desc">
                                        @lang('sharj.translate86')
                                    </div>
                                    <div class="deadline-text">
                                        @lang('sharj.translate87')
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="deadline-block">
                        <div class="deadline-title">
                            <img width="100" height="100" src="/images/delivery.png" alt="" />
                            <p>@lang('sharj.translate59')</p>
                        </div>
                        <div class="deadline-items">
                            <div class="deadline-item">
                                <div class="deadline-img">
                                    <picture>
                                        <source srcset="/images/van.webp" type="image/webp" />
                                        <source srcset="/images/van.jpg" type="image/jpg" />
                                        <img width="100" height="100" src="/images/van.jpg" alt="" />
                                    </picture>
                                </div>
                                <div class="deadline-content">
                                    <div class="deadline-desc">
                                        @lang('sharj.translate82')
                                    </div>
                                </div>
                            </div>
                            <div class="deadline-item">
                                <div class="deadline-img">
                                    <picture>
                                        <source srcset="/images/on-adress.webp" type="image/webp" />
                                        <source srcset="/images/on-adress.jpg" type="image/jpg" />
                                        <img width="100" height="100" src="/images/on-adress.jpg"
                                            alt="" />
                                    </picture>
                                </div>
                                <div class="deadline-content">
                                    <div class="deadline-text">
                                        @lang('sharj.translate88')
                                    </div>
                                    <div class="deadline-desc">@lang('sharj.translate89')</div>
                                </div>
                            </div>
                            <div class="deadline-item">
                                <div class="deadline-img">
                                    <picture>
                                        <source srcset="/images/abroad.webp" type="image/webp" />
                                        <source srcset="/images/abroad.jpg" type="image/jpg" />
                                        <img width="100" height="100" src="/images/abroad.jpg"
                                            alt="" />
                                    </picture>
                                </div>
                                <div class="deadline-content">
                                    <div class="deadline-text">
                                        @lang('sharj.translate90')
                                    </div>
                                    <div class="deadline-desc">@lang('sharj.translate91')</div>
                                </div>
                            </div>
                        </div>
                        <div class="courier-info">
                            <div class="courier-text">
                                @lang('sharj.translate92')
                            </div>
                            <div class="courier-items">
                                <div class="courier-item">
                                    <div class="courier-inner">
                                        <div class="courier-logo">
                                            <img width="100" height="100" src="/images/venipak.png"
                                                alt="" />
                                        </div>
                                        <div class="courier-txt">
                                            @lang('sharj.translate93')
                                        </div>
                                    </div>
                                </div>
                                <div class="courier-item">
                                    <div class="courier-inner">
                                        <div class="courier-logo">
                                            <img width="100" height="100" src="/images/dpd.png"
                                                alt="" />
                                        </div>
                                        <div class="courier-txt">
                                            @lang('sharj.translate94')
                                            <a href="mailto:info@viarstudia">info@viarstudia</a>
                                            @lang('sharj.translate95')
                                            <a href="tel:+37125444744">+371 25444744</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="hidden-trigger">
                    <a href="#examples" class="anchor ellipse-arrow ellipse-arrow_white" aria-label="anchor link">
                        <i class="fa-arrow-down"></i>
                    </a>
                    <span>@lang('sharj.translate74')</span>
                    <span>@lang('sharj.translate73')</span>
                </div>
            </div>
        </div>
    </div>
</section>
