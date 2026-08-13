@php
use App\Models\SiteImage;

/** @var \Illuminate\Support\Collection|SiteImage[] $siteImages */
$siteImages = $siteImages ?? SiteImage::where('is_show', true)->get();

$base = asset('/images/sharj').'/';

/*
| Ключ -> дефолты desktop/mobile + размеры <img>
| Если нужны другие дефолты — просто поменяй файлы тут.
*/
$defs = [
    'portrait_historical_on_canvas' => ['desk' => 'hi4.webp', 'mob' => 'hi4Min.webp', 'w'=>450, 'h'=>360],
    'portrait_historical_on_canvas_in_a_baguette_frame' => ['desk' => 'hi1.webp', 'mob' => 'hi1Min.webp', 'w'=>450, 'h'=>360],
    'portrait_historical_on_paper_in_a_simple_frame'  => ['desk' => 'hi3.webp', 'mob' => 'hi3Min.webp', 'w'=>450, 'h'=>360],
    'portrait_historical_on_paper_in_a_premium_frame' => ['desk' => 'hi2.webp', 'mob' => 'hi2Min.webp', 'w'=>450, 'h'=>360],
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

<div class="about__block @if($is_hidden ?? true) hidden-block @endif">
    <div class="about__block-types">
        <div class="page-title h2_old">{!! trans('portrait_royal.tab_two_types_body_title') !!}</div>

        <p>{!! trans('portrait_royal.tab_two_types_body_subtitle') !!}</p>
        <div class="about__block-types-row">
            <div class="img imgCenter">
                <div class="flex">
                    <img width="40" height="40" src="{{ ver_asset('images/sharj/i1.svg') }}" alt="">
                    <p> <span class="orange">{!! trans('portrait_royal.tab_two_types_body_type_canvas') !!}</span></p>
                    <svg width="55" height="40" viewBox="0 0 55 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M49.6863 39.3056C49.8551 39.5241 50.1691 39.5644 50.3876 39.3956L53.9488 36.6446C54.1673 36.4758 54.2076 36.1617 54.0388 35.9432C53.87 35.7247 53.556 35.6844 53.3374 35.8532L50.172 38.2986L47.7266 35.1331C47.5578 34.9146 47.2438 34.8743 47.0252 35.0431C46.8067 35.2119 46.7664 35.5259 46.9352 35.7445L49.6863 39.3056ZM50.082 38.9999C50.5779 39.0636 50.578 39.0631 50.578 39.0625C50.5781 39.0621 50.5782 39.0613 50.5783 39.0605C50.5785 39.0589 50.5788 39.0567 50.5791 39.0539C50.5798 39.0484 50.5807 39.0406 50.5819 39.0306C50.5843 39.0104 50.5875 38.9813 50.5915 38.9435C50.5995 38.8678 50.6103 38.7572 50.6218 38.6141C50.645 38.3278 50.6714 37.911 50.6854 37.3823C50.7135 36.3251 50.6921 34.8188 50.4953 33.0118C50.1019 29.3995 49.0063 24.5733 46.193 19.7312C40.5446 10.0089 28.0619 0.369221 0.908681 0.175397L0.901546 1.17537C27.7942 1.36734 39.8999 10.8899 45.3284 20.2335C48.0537 24.9243 49.1186 29.6068 49.5012 33.1201C49.6924 34.8759 49.7129 36.3366 49.6858 37.3557C49.6723 37.8652 49.6469 38.2639 49.6251 38.5335C49.6142 38.6683 49.6042 38.7708 49.597 38.8387C49.5935 38.8727 49.5906 38.898 49.5887 38.9143C49.5877 38.9225 49.587 38.9284 49.5866 38.9321C49.5863 38.934 49.5862 38.9352 49.5861 38.9359C49.586 38.9363 49.586 38.9364 49.586 38.9365C49.586 38.9365 49.586 38.9363 50.082 38.9999Z" fill="#FA7846"></path>
                    </svg>
                </div>
                @php
                    $k = 'portrait_historical_on_canvas';
                @endphp
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
                    <img width="65" height="65" src="{{ ver_asset('images/sharj/ai1.webp') }}" alt="">
                    <p>{!! trans('portrait_royal.tab_two_types_body_list_1') !!}</p>
                </li>
                <li>
                    <img width="65" height="65" src="{{ ver_asset('images/sharj/ai2.webp') }}" alt="">
                    <p>{!! trans('portrait_royal.tab_two_types_body_list_2') !!}</p>
                </li>
                <li>
                    <img width="65" height="65" src="{{ ver_asset('images/sharj/ai3.webp') }}" alt="">
                    <p>{!! trans('portrait_royal.tab_two_types_body_list_3') !!}</b></p>
                </li>
            </ul>
            <div class="img imgCenter">
                <div class="flex">
                    <img width="40" height="40" src="{{ ver_asset('images/sharj/i2.svg') }}" alt="">
                    <p>{!! trans('portrait_royal.tab_two_types_body_type_canvas_border') !!}</p>
                    <svg width="55" height="40" viewBox="0 0 55 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M49.6863 39.3056C49.8551 39.5241 50.1691 39.5644 50.3876 39.3956L53.9488 36.6446C54.1673 36.4758 54.2076 36.1617 54.0388 35.9432C53.87 35.7247 53.556 35.6844 53.3374 35.8532L50.172 38.2986L47.7266 35.1331C47.5578 34.9146 47.2438 34.8743 47.0252 35.0431C46.8067 35.2119 46.7664 35.5259 46.9352 35.7445L49.6863 39.3056ZM50.082 38.9999C50.5779 39.0636 50.578 39.0631 50.578 39.0625C50.5781 39.0621 50.5782 39.0613 50.5783 39.0605C50.5785 39.0589 50.5788 39.0567 50.5791 39.0539C50.5798 39.0484 50.5807 39.0406 50.5819 39.0306C50.5843 39.0104 50.5875 38.9813 50.5915 38.9435C50.5995 38.8678 50.6103 38.7572 50.6218 38.6141C50.645 38.3278 50.6714 37.911 50.6854 37.3823C50.7135 36.3251 50.6921 34.8188 50.4953 33.0118C50.1019 29.3995 49.0063 24.5733 46.193 19.7312C40.5446 10.0089 28.0619 0.369221 0.908681 0.175397L0.901546 1.17537C27.7942 1.36734 39.8999 10.8899 45.3284 20.2335C48.0537 24.9243 49.1186 29.6068 49.5012 33.1201C49.6924 34.8759 49.7129 36.3366 49.6858 37.3557C49.6723 37.8652 49.6469 38.2639 49.6251 38.5335C49.6142 38.6683 49.6042 38.7708 49.597 38.8387C49.5935 38.8727 49.5906 38.898 49.5887 38.9143C49.5877 38.9225 49.587 38.9284 49.5866 38.9321C49.5863 38.934 49.5862 38.9352 49.5861 38.9359C49.586 38.9363 49.586 38.9364 49.586 38.9365C49.586 38.9365 49.586 38.9363 50.082 38.9999Z" fill="#FA7846"></path>
                    </svg>
                </div>
                @php
                    $k = 'portrait_historical_on_canvas_in_a_baguette_frame';
                @endphp
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
                    <img width="40" height="40" src="{{ ver_asset('images/sharj/i3.svg') }}" alt="">
                    <p> <span class="orange">{!! trans('portrait_royal.tab_two_types_body_type_paper') !!}</p>
                    <svg width="55" height="40" viewBox="0 0 55 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M49.6863 39.3056C49.8551 39.5241 50.1691 39.5644 50.3876 39.3956L53.9488 36.6446C54.1673 36.4758 54.2076 36.1617 54.0388 35.9432C53.87 35.7247 53.556 35.6844 53.3374 35.8532L50.172 38.2986L47.7266 35.1331C47.5578 34.9146 47.2438 34.8743 47.0252 35.0431C46.8067 35.2119 46.7664 35.5259 46.9352 35.7445L49.6863 39.3056ZM50.082 38.9999C50.5779 39.0636 50.578 39.0631 50.578 39.0625C50.5781 39.0621 50.5782 39.0613 50.5783 39.0605C50.5785 39.0589 50.5788 39.0567 50.5791 39.0539C50.5798 39.0484 50.5807 39.0406 50.5819 39.0306C50.5843 39.0104 50.5875 38.9813 50.5915 38.9435C50.5995 38.8678 50.6103 38.7572 50.6218 38.6141C50.645 38.3278 50.6714 37.911 50.6854 37.3823C50.7135 36.3251 50.6921 34.8188 50.4953 33.0118C50.1019 29.3995 49.0063 24.5733 46.193 19.7312C40.5446 10.0089 28.0619 0.369221 0.908681 0.175397L0.901546 1.17537C27.7942 1.36734 39.8999 10.8899 45.3284 20.2335C48.0537 24.9243 49.1186 29.6068 49.5012 33.1201C49.6924 34.8759 49.7129 36.3366 49.6858 37.3557C49.6723 37.8652 49.6469 38.2639 49.6251 38.5335C49.6142 38.6683 49.6042 38.7708 49.597 38.8387C49.5935 38.8727 49.5906 38.898 49.5887 38.9143C49.5877 38.9225 49.587 38.9284 49.5866 38.9321C49.5863 38.934 49.5862 38.9352 49.5861 38.9359C49.586 38.9363 49.586 38.9364 49.586 38.9365C49.586 38.9365 49.586 38.9363 50.082 38.9999Z" fill="#FA7846"></path>
                    </svg>
                </div>
                @php
                    $k = 'portrait_historical_on_paper_in_a_simple_frame';
                @endphp
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
                    <img width="65" height="65" src="{{ ver_asset('images/sharj/ai4.webp') }}" alt="">
                    <p>{!! trans('portrait_royal.tab_two_types_body_list_4') !!}</p>
                </li>
                <li>
                    <img width="65" height="65" src="{{ ver_asset('images/sharj/ai5.webp') }}" alt="">
                    <p>{!! trans('portrait_royal.tab_two_types_body_list_5') !!}</p>
                </li>
            </ul>
            <div class="img imgCenter">
                <div class="flex">
                    <img width="40" height="40" src="{{ ver_asset('images/sharj/i4.svg') }}" alt="">
                    <p>{!! trans('portrait_royal.tab_two_types_body_type_paper_border') !!}</p>
                    <svg width="55" height="40" viewBox="0 0 55 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M49.6863 39.3056C49.8551 39.5241 50.1691 39.5644 50.3876 39.3956L53.9488 36.6446C54.1673 36.4758 54.2076 36.1617 54.0388 35.9432C53.87 35.7247 53.556 35.6844 53.3374 35.8532L50.172 38.2986L47.7266 35.1331C47.5578 34.9146 47.2438 34.8743 47.0252 35.0431C46.8067 35.2119 46.7664 35.5259 46.9352 35.7445L49.6863 39.3056ZM50.082 38.9999C50.5779 39.0636 50.578 39.0631 50.578 39.0625C50.5781 39.0621 50.5782 39.0613 50.5783 39.0605C50.5785 39.0589 50.5788 39.0567 50.5791 39.0539C50.5798 39.0484 50.5807 39.0406 50.5819 39.0306C50.5843 39.0104 50.5875 38.9813 50.5915 38.9435C50.5995 38.8678 50.6103 38.7572 50.6218 38.6141C50.645 38.3278 50.6714 37.911 50.6854 37.3823C50.7135 36.3251 50.6921 34.8188 50.4953 33.0118C50.1019 29.3995 49.0063 24.5733 46.193 19.7312C40.5446 10.0089 28.0619 0.369221 0.908681 0.175397L0.901546 1.17537C27.7942 1.36734 39.8999 10.8899 45.3284 20.2335C48.0537 24.9243 49.1186 29.6068 49.5012 33.1201C49.6924 34.8759 49.7129 36.3366 49.6858 37.3557C49.6723 37.8652 49.6469 38.2639 49.6251 38.5335C49.6142 38.6683 49.6042 38.7708 49.597 38.8387C49.5935 38.8727 49.5906 38.898 49.5887 38.9143C49.5877 38.9225 49.587 38.9284 49.5866 38.9321C49.5863 38.934 49.5862 38.9352 49.5861 38.9359C49.586 38.9363 49.586 38.9364 49.586 38.9365C49.586 38.9365 49.586 38.9363 50.082 38.9999Z" fill="#FA7846"></path>
                    </svg>
                </div>
                @php
                    $k = 'portrait_historical_on_paper_in_a_premium_frame';
                @endphp
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
