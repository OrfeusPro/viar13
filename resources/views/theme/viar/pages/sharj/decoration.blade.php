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

<div class="section aboutInfo">
    <div class="pmo-block pmo-block1">
        <p>@lang("sharj.translate4") </p>
        <img width="52" height="80" src="{{ asset(env('THEME') . 'images') }}/sharj/new/pngwing.webp" alt="">
        <svg width="81" height="199" viewBox="0 0 81 199" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0.025152 195.277C-0.0979362 195.816 0.238735 196.352 0.777129 196.475L9.55076 198.481C10.0892 198.604 10.6254 198.267 10.7485 197.729C10.8716 197.19 10.5349 196.654 9.9965 196.531L2.19772 194.748L3.98069 186.949C4.10378 186.411 3.7671 185.875 3.22871 185.752C2.69032 185.628 2.15408 185.965 2.03099 186.503L0.025152 195.277ZM1 1C0.56927 1.90248 0.569544 1.90261 0.570486 1.90306C0.571691 1.90364 0.573298 1.90441 0.575702 1.90556C0.580508 1.90786 0.587965 1.91145 0.598043 1.9163C0.618198 1.92601 0.648833 1.94081 0.689698 1.96067C0.771426 2.0004 0.894067 2.06039 1.05561 2.14041C1.37869 2.30047 1.85735 2.54066 2.47548 2.85923C3.71179 3.4964 5.50574 4.44696 7.72863 5.69685C12.1751 8.19699 18.3342 11.8928 25.1775 16.6715C38.8786 26.2389 55.2565 40.1016 66.1547 57.3467C77.0351 74.5638 82.4306 95.104 74.3068 118.16C66.1641 141.27 44.3794 167.084 0.468272 194.653L1.53173 196.347C45.6206 168.666 67.8359 142.543 76.1932 118.825C84.5694 95.0523 78.9649 73.8737 67.8453 56.2783C56.7435 38.7109 40.1214 24.6674 26.3225 15.0317C19.4158 10.2088 13.1999 6.47879 8.70887 3.95354C6.46301 2.69073 4.64759 1.7287 3.39171 1.08145C2.76375 0.757806 2.27561 0.512828 1.94342 0.348265C1.77732 0.265983 1.6502 0.2038 1.56409 0.161937C1.52103 0.141005 1.48822 0.125153 1.46591 0.114408C1.45475 0.109035 1.44622 0.104939 1.44035 0.102123C1.43741 0.100715 1.43501 0.0995638 1.43353 0.0988597C1.4318 0.0980293 1.43073 0.097519 1 1Z" fill="#FA7846"/>
        </svg>
    </div>
    <div class="section-frame">
        <div class="section-inner">
            <div class="about__block-types">
                @if (Route::currentRouteName() == 'caricature')
                    <h2 class="page-title">@lang("sharj.translate5")</h2>
                @elseif (Route::currentRouteName() == 'home')
                    <h2 class="page-title">@lang("sharj.translate5")</h2>
                @else
                    <h2 class="page-title">@lang("sharj.translate5")</h2>
                @endif
                <p>@lang("sharj.translate6")</p>
                <div class="about__block-types-row">
                    <div class="img">
                        <div class="flex">
                            <img width="40" height="40" src="{{ asset(env('THEME') . 'images') }}/sharj/new/page/i1.svg" alt="">
                            <p> <span class="orange">@lang("sharj.translate7")</span></p>
                            <svg width="55" height="40" viewBox="0 0 55 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M49.6863 39.3056C49.8551 39.5241 50.1691 39.5644 50.3876 39.3956L53.9488 36.6446C54.1673 36.4758 54.2076 36.1617 54.0388 35.9432C53.87 35.7247 53.556 35.6844 53.3374 35.8532L50.172 38.2986L47.7266 35.1331C47.5578 34.9146 47.2438 34.8743 47.0252 35.0431C46.8067 35.2119 46.7664 35.5259 46.9352 35.7445L49.6863 39.3056ZM50.082 38.9999C50.5779 39.0636 50.578 39.0631 50.578 39.0625C50.5781 39.0621 50.5782 39.0613 50.5783 39.0605C50.5785 39.0589 50.5788 39.0567 50.5791 39.0539C50.5798 39.0484 50.5807 39.0406 50.5819 39.0306C50.5843 39.0104 50.5875 38.9813 50.5915 38.9435C50.5995 38.8678 50.6103 38.7572 50.6218 38.6141C50.645 38.3278 50.6714 37.911 50.6854 37.3823C50.7135 36.3251 50.6921 34.8188 50.4953 33.0118C50.1019 29.3995 49.0063 24.5733 46.193 19.7312C40.5446 10.0089 28.0619 0.369221 0.908681 0.175397L0.901546 1.17537C27.7942 1.36734 39.8999 10.8899 45.3284 20.2335C48.0537 24.9243 49.1186 29.6068 49.5012 33.1201C49.6924 34.8759 49.7129 36.3366 49.6858 37.3557C49.6723 37.8652 49.6469 38.2639 49.6251 38.5335C49.6142 38.6683 49.6042 38.7708 49.597 38.8387C49.5935 38.8727 49.5906 38.898 49.5887 38.9143C49.5877 38.9225 49.587 38.9284 49.5866 38.9321C49.5863 38.934 49.5862 38.9352 49.5861 38.9359C49.586 38.9363 49.586 38.9364 49.586 38.9365C49.586 38.9365 49.586 38.9363 50.082 38.9999Z" fill="#FA7846"/>
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
                            <img width="65" height="65" src="{{ asset(env('THEME') . 'images') }}/sharj/new/page/ai1.webp" alt="">
                            <p>@lang("sharj.translate8")</p>
                        </li>
                        <li>
                            <img width="65" height="65" src="{{ asset(env('THEME') . 'images') }}/sharj/new/page/ai2.webp" alt="">
                            <p>@lang("sharj.translate9")</p>
                        </li>
                        <li>
                            <img width="65" height="65" src="{{ asset(env('THEME') . 'images') }}/sharj/new/page/ai3.webp" alt="">
                            <p>@lang("sharj.translate10")</p>
                        </li>
                    </ul>
                    <div class="img">
                        <div class="flex">
                            <img width="40" height="40" src="{{ asset(env('THEME') . 'images') }}/sharj/new/page/i2.svg" alt="">
                            <p> @lang("sharj.translate11")</p>
                            <svg width="55" height="40" viewBox="0 0 55 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M49.6863 39.3056C49.8551 39.5241 50.1691 39.5644 50.3876 39.3956L53.9488 36.6446C54.1673 36.4758 54.2076 36.1617 54.0388 35.9432C53.87 35.7247 53.556 35.6844 53.3374 35.8532L50.172 38.2986L47.7266 35.1331C47.5578 34.9146 47.2438 34.8743 47.0252 35.0431C46.8067 35.2119 46.7664 35.5259 46.9352 35.7445L49.6863 39.3056ZM50.082 38.9999C50.5779 39.0636 50.578 39.0631 50.578 39.0625C50.5781 39.0621 50.5782 39.0613 50.5783 39.0605C50.5785 39.0589 50.5788 39.0567 50.5791 39.0539C50.5798 39.0484 50.5807 39.0406 50.5819 39.0306C50.5843 39.0104 50.5875 38.9813 50.5915 38.9435C50.5995 38.8678 50.6103 38.7572 50.6218 38.6141C50.645 38.3278 50.6714 37.911 50.6854 37.3823C50.7135 36.3251 50.6921 34.8188 50.4953 33.0118C50.1019 29.3995 49.0063 24.5733 46.193 19.7312C40.5446 10.0089 28.0619 0.369221 0.908681 0.175397L0.901546 1.17537C27.7942 1.36734 39.8999 10.8899 45.3284 20.2335C48.0537 24.9243 49.1186 29.6068 49.5012 33.1201C49.6924 34.8759 49.7129 36.3366 49.6858 37.3557C49.6723 37.8652 49.6469 38.2639 49.6251 38.5335C49.6142 38.6683 49.6042 38.7708 49.597 38.8387C49.5935 38.8727 49.5906 38.898 49.5887 38.9143C49.5877 38.9225 49.587 38.9284 49.5866 38.9321C49.5863 38.934 49.5862 38.9352 49.5861 38.9359C49.586 38.9363 49.586 38.9364 49.586 38.9365C49.586 38.9365 49.586 38.9363 50.082 38.9999Z" fill="#FA7846"/>
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
                    @lang("sharj.translate96")
                </div>
                <div class="about__block-types-row">
                    <div class="img">
                        <div class="flex mb-2">
                            <img width="40" height="40" src="{{ asset(env('THEME') . 'images') }}/sharj/new/page/i3.svg" alt="">
                            <p> <span class="orange">@lang("sharj.translate12")</p>
                            <svg width="55" height="40" viewBox="0 0 55 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M49.6863 39.3056C49.8551 39.5241 50.1691 39.5644 50.3876 39.3956L53.9488 36.6446C54.1673 36.4758 54.2076 36.1617 54.0388 35.9432C53.87 35.7247 53.556 35.6844 53.3374 35.8532L50.172 38.2986L47.7266 35.1331C47.5578 34.9146 47.2438 34.8743 47.0252 35.0431C46.8067 35.2119 46.7664 35.5259 46.9352 35.7445L49.6863 39.3056ZM50.082 38.9999C50.5779 39.0636 50.578 39.0631 50.578 39.0625C50.5781 39.0621 50.5782 39.0613 50.5783 39.0605C50.5785 39.0589 50.5788 39.0567 50.5791 39.0539C50.5798 39.0484 50.5807 39.0406 50.5819 39.0306C50.5843 39.0104 50.5875 38.9813 50.5915 38.9435C50.5995 38.8678 50.6103 38.7572 50.6218 38.6141C50.645 38.3278 50.6714 37.911 50.6854 37.3823C50.7135 36.3251 50.6921 34.8188 50.4953 33.0118C50.1019 29.3995 49.0063 24.5733 46.193 19.7312C40.5446 10.0089 28.0619 0.369221 0.908681 0.175397L0.901546 1.17537C27.7942 1.36734 39.8999 10.8899 45.3284 20.2335C48.0537 24.9243 49.1186 29.6068 49.5012 33.1201C49.6924 34.8759 49.7129 36.3366 49.6858 37.3557C49.6723 37.8652 49.6469 38.2639 49.6251 38.5335C49.6142 38.6683 49.6042 38.7708 49.597 38.8387C49.5935 38.8727 49.5906 38.898 49.5887 38.9143C49.5877 38.9225 49.587 38.9284 49.5866 38.9321C49.5863 38.934 49.5862 38.9352 49.5861 38.9359C49.586 38.9363 49.586 38.9364 49.586 38.9365C49.586 38.9365 49.586 38.9363 50.082 38.9999Z" fill="#FA7846"/>
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
                            <p>@lang("sharj.translate13")</p>
                        </li>
                        <li>
                            <img width="65" height="65" src="{{ asset(env('THEME') . 'images') }}/sharj/new/page/ai5.webp" alt="">
                            <p>@lang("sharj.translate14")</p>
                        </li>
                    </ul>
                    <div class="img">
                        <div class="flex">
                            <img width="40" height="40" src="{{ asset(env('THEME') . 'images') }}/sharj/new/page/i4.svg" alt="">
                            <p> @lang("sharj.translate15")</p>
                            <svg width="55" height="40" viewBox="0 0 55 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M49.6863 39.3056C49.8551 39.5241 50.1691 39.5644 50.3876 39.3956L53.9488 36.6446C54.1673 36.4758 54.2076 36.1617 54.0388 35.9432C53.87 35.7247 53.556 35.6844 53.3374 35.8532L50.172 38.2986L47.7266 35.1331C47.5578 34.9146 47.2438 34.8743 47.0252 35.0431C46.8067 35.2119 46.7664 35.5259 46.9352 35.7445L49.6863 39.3056ZM50.082 38.9999C50.5779 39.0636 50.578 39.0631 50.578 39.0625C50.5781 39.0621 50.5782 39.0613 50.5783 39.0605C50.5785 39.0589 50.5788 39.0567 50.5791 39.0539C50.5798 39.0484 50.5807 39.0406 50.5819 39.0306C50.5843 39.0104 50.5875 38.9813 50.5915 38.9435C50.5995 38.8678 50.6103 38.7572 50.6218 38.6141C50.645 38.3278 50.6714 37.911 50.6854 37.3823C50.7135 36.3251 50.6921 34.8188 50.4953 33.0118C50.1019 29.3995 49.0063 24.5733 46.193 19.7312C40.5446 10.0089 28.0619 0.369221 0.908681 0.175397L0.901546 1.17537C27.7942 1.36734 39.8999 10.8899 45.3284 20.2335C48.0537 24.9243 49.1186 29.6068 49.5012 33.1201C49.6924 34.8759 49.7129 36.3366 49.6858 37.3557C49.6723 37.8652 49.6469 38.2639 49.6251 38.5335C49.6142 38.6683 49.6042 38.7708 49.597 38.8387C49.5935 38.8727 49.5906 38.898 49.5887 38.9143C49.5877 38.9225 49.587 38.9284 49.5866 38.9321C49.5863 38.934 49.5862 38.9352 49.5861 38.9359C49.586 38.9363 49.586 38.9364 49.586 38.9365C49.586 38.9365 49.586 38.9363 50.082 38.9999Z" fill="#FA7846"/>
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
    </div>
</div>
