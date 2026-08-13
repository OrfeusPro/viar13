@extends('layots.common')

@section('title', $data['meta_title'])

@section('og_tags')
    <meta property="og:title" content="{{ $data['meta_title'] }}" />
@endsection

@section('styles')

    @php
        $date_form_now = Carbon::now();
    @endphp

@endsection

@section('content')
        <div class="br-object">
            <div class="section-frame">
                <div class="breadcrumbs">
                    <div class="breadcrumbs__block">
                        <a href="https://viarcanvas.com" class="breadcrumbs__link breadcrumbs__link_main">
                            @lang('account.index1') </a>
                        <svg xmlns="http://www.w3.org/2000/svg" width="6" height="11" viewBox="0 0 6 11" fill="none" class="img-svg breadcrumbs__arrow replaced-svg">
                            <path
                                d="M0.24775 1.4341C0.180786 1.50106 0.147304 1.57807 0.147304 1.66513C0.147304 1.75218 0.180786 1.82919 0.24775 1.89615L4.1953 5.8437L0.247751 9.79124C0.180787 9.85821 0.147304 9.93522 0.147304 10.0223C0.147304 10.1093 0.180787 10.1863 0.247751 10.2533L0.749984 10.7555C0.816948 10.8225 0.893957 10.856 0.98101 10.856C1.06806 10.856 1.14507 10.8225 1.21204 10.7555L5.89284 6.07473C5.9598 6.00776 5.99329 5.93075 5.99329 5.8437C5.99329 5.75664 5.9598 5.67964 5.89284 5.61267L1.21204 0.931868C1.14507 0.864903 1.06806 0.831421 0.981009 0.831421C0.893956 0.831421 0.816947 0.864903 0.749983 0.931868L0.24775 1.4341Z"
                                fill="#FA7846"></path>
                        </svg>
                        <a href="#" class="breadcrumbs__link">{{ $data['title'] }}</a>
                    </div>
                    <script type="text/javascript">
                    </script>
                </div>
            </div>
        </div>



        <div class="main-gift">
            <div class="section-frame">
                <div class="main-gift__inner">
                    @php
                        /** @var \Illuminate\Support\Collection|\App\Models\SiteImage[] $siteImages */
                        $siteImages = $siteImages ?? \App\Models\SiteImage::where('is_show', true)->get();

                        $giftSlide = [
                            'desk' => site_image('gift_card_slide', env('THEME').'images/gift/1nMin.webp', ['collection' => $siteImages]),
                            'mob'  => site_image('gift_card_slide_mob', env('THEME').'images/gift/1nMin.webp', ['collection' => $siteImages]),
                        ];
                    @endphp
                    <div class="mgift-content">
                        <div class="mgift-title">

                           @lang('gift_card.title')

                        </div>
                        <hr>
                        <span>@lang('gift_card.sub_title1')</span>
                        <p>@lang('gift_card.sub_title2')</p>
                    </div>
                    <div class="mgift-image">
                        <picture>
                            @if(!empty($giftSlide['mob']['src_webp']))
                                <source media="(max-width: 550px)" srcset="{{ $giftSlide['mob']['src_webp'] }}" type="image/webp">
                            @endif
                            @if(!empty($giftSlide['mob']['type']))
                                <source media="(max-width: 550px)" srcset="{{ $giftSlide['mob']['src'] }}" type="{{ $giftSlide['mob']['type'] }}">
                            @endif
                            @if(!empty($giftSlide['desk']['src_webp']))
                                <source srcset="{{ $giftSlide['desk']['src_webp'] }}" type="image/webp">
                            @endif
                            @if(!empty($giftSlide['desk']['type']))
                                <source srcset="{{ $giftSlide['desk']['src'] }}" type="{{ $giftSlide['desk']['type'] }}">
                            @endif
                            <img width="409" height="369" src="{{ $giftSlide['desk']['src'] }}" alt="{{ $giftSlide['desk']['alt'] ?? '' }}" title="{{ $giftSlide['desk']['title'] ?? '' }}">
                        </picture>
                    </div>
                </div>
            </div>
        </div>

        <div class="ellipse">
            <img alt="img" src="https://viarcanvas.com/theme/viar/images/icon/ellipse-whete.svg" decoding="async" height="99" width="1374">
        </div>

        <form id="gift-card-form" action="{{ route('send_gift_card') }}" method="post">
            <div class="main-gcard">
                <div class="section-frame">
                    <div class="main-gcard__inner">
                        @php
                            /** @var \Illuminate\Support\Collection|\App\Models\SiteImage[] $siteImages */
                            $siteImages = $siteImages ?? \App\Models\SiteImage::where('is_show', true)->get();
                            $cardImage = site_image(
                                'gift_card_gift_card',
                                env('THEME').'images/gift/'.Config::get('app.locale').'.jpg',
                                ['collection' => $siteImages]
                            );
                        @endphp
                        <div class="gcard-img">
                            <picture>
                                @if(!empty($cardImage['src_webp']))
                                    <source srcset="{{ $cardImage['src_webp'] }}" type="image/webp">
                                @endif
                                @if(!empty($cardImage['type']))
                                    <source srcset="{{ $cardImage['src'] }}" type="{{ $cardImage['type'] }}">
                                @endif
                                <img width="409" height="369" src="{{ $cardImage['src'] }}" alt="{{ $cardImage['alt'] ?? '' }}" title="{{ $cardImage['title'] ?? '' }}">
                            </picture>
                        </div>
                        <div class="gcard-content">
                            <div class="gcard-title">
                                @lang('gift_card.nominal')
                            </div>
                            <p>@lang('gift_card.cart_studio')</p>
                            <div class="select-wrapper">
                                <select name="summ">
                                    @foreach ($noms as $nom)
                                        <option>{{ $nom['text'] }}</option>
                                    @endforeach
                                </select>
                                <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M7.78156 9.13746C7.8684 9.04582 7.96827 9 8.08116 9C8.19406 9 8.29392 9.04582 8.38076 9.13746L13.5 14.5395L18.6192 9.13746C18.7061 9.04582 18.8059 9 18.9188 9C19.0317 9 19.1316 9.04582 19.2184 9.13746L19.8697 9.82474C19.9566 9.91638 20 10.0218 20 10.1409C20 10.26 19.9566 10.3654 19.8697 10.457L13.7996 16.8625C13.7128 16.9542 13.6129 17 13.5 17C13.3871 17 13.2872 16.9542 13.2004 16.8625L7.13026 10.457C7.04342 10.3654 7 10.26 7 10.1409C7 10.0218 7.04342 9.91638 7.13026 9.82474L7.78156 9.13746Z" fill="#1E2533"/>
                                </svg>
                            </div>
                            {{-- <span>@lang('gift_card.summ')</span> --}}
                            <div class="r-flex kviz-item">
                                <div class="hb_whom kviz-radio js-checkbox kviz-radio_active" data-type="online" >
                                    <div class="check"></div>
                                    <label>
                                        <span>@lang('gift_card.cart_electron')</span>
                                        <input type="radio" name="whom" value="@lang('gift_card.cart_electron')" checked="checked">
                                    </label>
                                </div>
                                <div class="hb_whom kviz-radio js-checkbox" data-type="offline" >
                                    <div class="check"></div>
                                    <label>
                                        <span>@lang('gift_card.gift_cart')</span>
                                        <input type="radio" name="whom" value="@lang('gift_card.gift_cart')">
                                    </label>
                                </div>
                            </div>
                            <input class="sm-btn" type="submit" value="@lang('gift_card.submit_button')">
                        </div>
                    </div>
                </div>
            </div>
        </form>


        <div class="main-agift">
            <div class="section-frame">
                <div class="main-agift__inner">
                    <div class="page-title agift-title">
                        @lang('gift_card.six_advantages')
                    </div>
                    <div class="agift-grid">
                        @php
                            $advantages = [];
                            for ($i = 1; $i <= 6; $i++) {
                                $key = 'gift_card_step_'.$i;
                                $advantages[$i] = site_image($key, env('THEME').'images/gift/'.$i.'a.png', ['collection' => $siteImages]);
                            }
                        @endphp
                        <div class="agift-item">
                            <div class="agift-img" data-num="1">
                                <picture>
                                    @if(!empty($advantages[1]['src_webp']))
                                        <source srcset="{{ $advantages[1]['src_webp'] }}" type="image/webp">
                                    @endif
                                    @if(!empty($advantages[1]['type']))
                                        <source srcset="{{ $advantages[1]['src'] }}" type="{{ $advantages[1]['type'] }}">
                                    @endif
                                    <img width="145" height="145" src="{{ $advantages[1]['src'] }}" alt="{{ $advantages[1]['alt'] ?? '' }}" title="{{ $advantages[1]['title'] ?? '' }}">
                                </picture>
                            </div>
                            <div class="agift-text">
                                @lang('gift_card.congrats')
                            </div>
                        </div>
                        <div class="agift-item">
                            <div class="agift-img" data-num="2">
                                <picture>
                                    @if(!empty($advantages[2]['src_webp']))
                                        <source srcset="{{ $advantages[2]['src_webp'] }}" type="image/webp">
                                    @endif
                                    @if(!empty($advantages[2]['type']))
                                        <source srcset="{{ $advantages[2]['src'] }}" type="{{ $advantages[2]['type'] }}">
                                    @endif
                                    <img width="145" height="145" src="{{ $advantages[2]['src'] }}" alt="{{ $advantages[2]['alt'] ?? '' }}" title="{{ $advantages[2]['title'] ?? '' }}">
                                </picture>
                            </div>
                            <div class="agift-text">
                                @lang('gift_card.gift_time')
                            </div>
                        </div>
                        <div class="agift-item">
                            <div class="agift-img" data-num="3">
                                <picture>
                                    @if(!empty($advantages[3]['src_webp']))
                                        <source srcset="{{ $advantages[3]['src_webp'] }}" type="image/webp">
                                    @endif
                                    @if(!empty($advantages[3]['type']))
                                        <source srcset="{{ $advantages[3]['src'] }}" type="{{ $advantages[3]['type'] }}">
                                    @endif
                                    <img width="145" height="145" src="{{ $advantages[3]['src'] }}" alt="{{ $advantages[3]['alt'] ?? '' }}" title="{{ $advantages[3]['title'] ?? '' }}">
                                </picture>
                            </div>
                            <div class="agift-text">
                                @lang('gift_card.fast_delivery')
                            </div>
                        </div>
                        <div class="agift-item reverse">
                            <div class="agift-text">
                                @lang('gift_card.povod')
                            </div>
                            <div class="agift-img" data-num="4">
                                <picture>
                                    @if(!empty($advantages[4]['src_webp']))
                                        <source srcset="{{ $advantages[4]['src_webp'] }}" type="image/webp">
                                    @endif
                                    @if(!empty($advantages[4]['type']))
                                        <source srcset="{{ $advantages[4]['src'] }}" type="{{ $advantages[4]['type'] }}">
                                    @endif
                                    <img width="145" height="145" src="{{ $advantages[4]['src'] }}" alt="{{ $advantages[4]['alt'] ?? '' }}" title="{{ $advantages[4]['title'] ?? '' }}">
                                </picture>
                            </div>
                        </div>
                        <div class="agift-item reverse">
                            <div class="agift-text">
                                @lang('gift_card.ideal_gift')
                            </div>
                            <div class="agift-img" data-num="5">
                                <picture>
                                    @if(!empty($advantages[5]['src_webp']))
                                        <source srcset="{{ $advantages[5]['src_webp'] }}" type="image/webp">
                                    @endif
                                    @if(!empty($advantages[5]['type']))
                                        <source srcset="{{ $advantages[5]['src'] }}" type="{{ $advantages[5]['type'] }}">
                                    @endif
                                    <img width="145" height="145" src="{{ $advantages[5]['src'] }}" alt="{{ $advantages[5]['alt'] ?? '' }}" title="{{ $advantages[5]['title'] ?? '' }}">
                                </picture>
                            </div>
                        </div>
                        <div class="agift-item reverse">
                            <div class="agift-text">
                                @lang('gift_card.bonus')
                            </div>
                            <div class="agift-img" data-num="6">
                                <picture>
                                    @if(!empty($advantages[6]['src_webp']))
                                        <source srcset="{{ $advantages[6]['src_webp'] }}" type="image/webp">
                                    @endif
                                    @if(!empty($advantages[6]['type']))
                                        <source srcset="{{ $advantages[6]['src'] }}" type="{{ $advantages[6]['type'] }}">
                                    @endif
                                    <img width="145" height="145" src="{{ $advantages[6]['src'] }}" alt="{{ $advantages[6]['alt'] ?? '' }}" title="{{ $advantages[6]['title'] ?? '' }}">
                                </picture>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="ellipse ellipse_black custom-ellipse">
            <img src="./images/icon/ellipse-black.svg" alt="img" loading="lazy">
        </div>


        <div class="main-rgift">
            <div class="section-frame">
                <div class="main-rgift__inner">
                    <div class="rgift-grid">
                        <div class="title-fr page-title">
                            @lang('gift_card.rules')
                            <svg width="197" height="79" viewBox="0 0 197 79" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M112.5 52.9996L112.993 53.0826L112.5 52.9996ZM196.966 62.6796C197.066 62.4219 196.938 62.1325 196.68 62.0331L192.482 60.4135C192.224 60.3141 191.934 60.4424 191.835 60.7001C191.736 60.9577 191.864 61.2471 192.122 61.3465L195.854 62.7861L194.414 66.5181C194.315 66.7757 194.443 67.0652 194.7 67.1645C194.958 67.2639 195.248 67.1356 195.347 66.878L196.966 62.6796ZM0.54998 1.2179C6.4141 13.3286 17.3675 17.2991 30.0958 17.8304C42.7903 18.3603 57.4174 15.4743 70.7502 13.753C77.4359 12.8899 83.8025 12.3171 89.4851 12.5918C95.1696 12.8665 100.125 13.9878 104.018 16.4761C111.741 21.4125 115.54 31.9252 112.007 52.9166L112.993 53.0826C116.541 32.0005 112.818 20.914 104.557 15.6335C100.458 13.0134 95.3051 11.8719 89.5334 11.5929C83.7596 11.3139 77.3217 11.8964 70.6222 12.7613C57.1846 14.496 42.7185 17.3564 30.1376 16.8313C17.5904 16.3075 7.0859 12.4214 1.45002 0.782096L0.54998 1.2179ZM112.007 52.9166C110.934 59.2928 111.444 64.2853 113.215 68.1168C114.991 71.9579 118.008 74.578 121.839 76.2473C129.461 79.5686 140.356 79.1507 151.4 77.1796C162.47 75.2038 173.797 71.6468 182.348 68.5877C186.626 67.0575 190.212 65.6505 192.73 64.6258C193.99 64.1135 194.982 63.6966 195.659 63.4077C195.998 63.2632 196.259 63.1507 196.434 63.0742C196.522 63.0359 196.589 63.0067 196.634 62.9869C196.657 62.9771 196.674 62.9696 196.685 62.9645C196.691 62.962 196.695 62.9601 196.698 62.9588C196.699 62.9581 196.701 62.9576 196.701 62.9573C196.702 62.9569 196.703 62.9567 196.5 62.4996C196.297 62.0425 196.297 62.0426 196.297 62.0429C196.296 62.0432 196.295 62.0436 196.294 62.0442C196.291 62.0454 196.287 62.0472 196.281 62.0496C196.27 62.0544 196.254 62.0617 196.232 62.0714C196.188 62.0906 196.122 62.1194 196.035 62.1572C195.862 62.2328 195.604 62.3443 195.267 62.4878C194.594 62.7748 193.607 63.1895 192.354 63.6996C189.846 64.7199 186.273 66.1216 182.011 67.6461C173.484 70.6964 162.217 74.2333 151.225 76.1951C140.207 78.1616 129.57 78.525 122.239 75.3306C118.593 73.7421 115.779 71.2801 114.123 67.6973C112.462 64.1049 111.941 59.3316 112.993 53.0826L112.007 52.9166Z" fill="#FA7846"/>
                            </svg>
                        </div>
                        <div class="rgift-content">
                            <ul>
                                <li>
                                    <img src="{{ asset(env('THEME') . 'images/gift') }}/gal.svg" width="25" height="25" alt="Viar">
                                    @lang('gift_card.vozvrat')
                                </li>
                                <li>
                                    <img src="{{ asset(env('THEME') . 'images/gift') }}/gal.svg" width="25" height="25" alt="Viar">
                                    @lang('gift_card.card_payment')
                                </li>
                                <li>
                                    <img src="{{ asset(env('THEME') . 'images/gift') }}/gal.svg" width="25" height="25" alt="Viar">
                                    @lang('gift_card.gift_cart_friends')
                                </li>
                                <li>
                                    <img src="{{ asset(env('THEME') . 'images/gift') }}/gal.svg" width="25" height="25" alt="Viar">
                                    @lang('gift_card.sertificate')
                                </li>
                                <li>
                                    <img src="{{ asset(env('THEME') . 'images/gift') }}/gal.svg" width="25" height="25" alt="Viar">
                                    @lang('gift_card.code_sertificate')
                                </li>
                                <li>
                                    <img src="{{ asset(env('THEME') . 'images/gift') }}/gal.svg" width="25" height="25" alt="Viar">
                                    @lang('gift_card.card_value')
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    @include(config('theme.resource') . 'pages.index.faq9')

    <script src="{{ asset('js/jcf.min.js') }}"></script>
    <script src="{{ asset('js/jcf.radio.min.js') }}"></script>
    <script src="{{ asset('js/jcf.select.min.js') }}"></script>
    <script src="{{ asset('js/jcf.checkbox.min.js') }}"></script>
    <script src="{{ asset('js/gift-card.min.js') }}"></script>
@endsection
