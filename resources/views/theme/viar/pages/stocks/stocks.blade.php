@include(env('THEME_RESOURCES') . 'pages.about.breads')
<script src="{{ ver_asset(env('THEME') . 'js/intlTelInput.min.js') }}"></script>
<div class="main-stock">
    <div class="section-frame">
        <div class="main-stock__inner">
            <div class="ms-content">
                <H1 class="ms-title">
                    @lang('stock.text_1_1')
                </H1>
                <hr>
                <p>
                    @lang('stock.text_1_2')
                </p>
            </div>
            <div class="ms-img">
                @php
                    /** @var \Illuminate\Support\Collection|\App\Models\SiteImage[] $siteImages */
                    $siteImages = $siteImages ?? \App\Models\SiteImage::where('is_show', true)->get();

                    $stockOptions = ['collection' => $siteImages, 'alt' => 'Viar'];
                    $stockAttr = function (array $image): string {
                        $alt = trim((string)($image['alt'] ?? ''));
                        $title = trim((string)($image['title'] ?? ''));

                        if ($alt === '') {
                            $alt = 'Viar';
                        }

                        return 'alt="' . e($alt) . '"' . ($title !== '' ? ' title="' . e($title) . '"' : '');
                    };

                    $stockImage = [
                        'desk' => site_image('stocks_slide', env('THEME').'images/stock/1.png', $stockOptions),
                        'mob'  => site_image('stocks_slide_mob', env('THEME').'images/stock/1Min.png', $stockOptions),
                    ];
                @endphp
                <picture>
                    @if(!empty($stockImage['mob']['src_webp']))
                        <source media="(max-width: 550px)" srcset="{{ $stockImage['mob']['src_webp'] }}" type="image/webp">
                    @endif
                    @if(!empty($stockImage['mob']['type']))
                        <source media="(max-width: 550px)" srcset="{{ $stockImage['mob']['src'] }}" type="{{ $stockImage['mob']['type'] }}">
                    @endif
                    @if(!empty($stockImage['desk']['src_webp']))
                        <source srcset="{{ $stockImage['desk']['src_webp'] }}" type="image/webp">
                    @endif
                    @if(!empty($stockImage['desk']['type']))
                        <source srcset="{{ $stockImage['desk']['src'] }}" type="{{ $stockImage['desk']['type'] }}">
                    @endif
                    <img width="409" height="369" src="{{ $stockImage['desk']['src'] }}" {!! $stockAttr($stockImage['desk']) !!}>
                </picture>
            </div>
        </div>
    </div>
</div>

<div class="ellipse">
    <img alt="img" src="https://viarcanvas.com/theme/viar/images/icon/ellipse-whete.svg" decoding="async"
        height="99" width="1374">
</div>

@php
    /** @var \Illuminate\Support\Collection|\App\Models\SiteImage[] $siteImages */
    $siteImages = $siteImages ?? \App\Models\SiteImage::where('is_show', true)->get();

    $stockBlocks = [];
    for ($i = 1; $i <= 3; $i++) {
        $key = 'stocks_block_1_'.$i;
        $stockBlocks[$i] = site_image($key, env('THEME').'images/stock/sm'.$i.'.jpg', ['collection' => $siteImages, 'alt' => 'Viar']);
    }

    $bonusMap = [
        1 => 'stocks_bonus_1_1',
        2 => 'stocks_bonus_1_2',
        3 => 'stocks_bonus_2_1',
        4 => 'stocks_bonus_2_2',
        5 => 'stocks_bonus_3_1',
    ];

    $bonusDefaults = [
        1 => env('THEME').'images/stock/b1.jpg',
        2 => env('THEME').'images/stock/b2.png',
        3 => env('THEME').'images/stock/b3.png',
        4 => env('THEME').'images/stock/b4.png',
        5 => env('THEME').'images/stock/b5.png',
    ];

    $bonusImages = [];
    foreach ($bonusMap as $idx => $key) {
        $bonusImages[$idx] = site_image($key, $bonusDefaults[$idx], ['collection' => $siteImages, 'alt' => 'Viar']);
    }
@endphp

<div class="stock-method">
    <div class="section-frame">
        <div class="stock-method__inner">
            <h2 class="stock-method__title">
                @lang('stock.text_2_1')
            </h2>
            <div class="stock-method__block">
                <div class="sm-item">
                    <span class="num">1.</span>
                    <div class="sm-img">
                        <span class="sm-add">
                            <svg width="30" height="30" viewBox="0 0 30 30" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <circle cx="15" cy="15" r="15" fill="#C80B0B" />
                                <path
                                    d="M14.0455 16.5114V16.3182C14.0492 15.6553 14.108 15.1269 14.2216 14.733C14.339 14.339 14.5095 14.0208 14.733 13.7784C14.9564 13.536 15.2254 13.3163 15.5398 13.1193C15.7746 12.9678 15.9848 12.8106 16.1705 12.6477C16.3561 12.4848 16.5038 12.3049 16.6136 12.108C16.7235 11.9072 16.7784 11.6837 16.7784 11.4375C16.7784 11.1761 16.7159 10.947 16.5909 10.75C16.4659 10.553 16.2973 10.4015 16.0852 10.2955C15.8769 10.1894 15.6458 10.1364 15.392 10.1364C15.1458 10.1364 14.9129 10.1913 14.6932 10.3011C14.4735 10.4072 14.2936 10.5663 14.1534 10.7784C14.0133 10.9867 13.9375 11.2462 13.9261 11.5568H11.608C11.6269 10.7992 11.8087 10.1742 12.1534 9.68182C12.4981 9.18561 12.9545 8.81629 13.5227 8.57386C14.0909 8.32765 14.7178 8.20455 15.4034 8.20455C16.1572 8.20455 16.8239 8.32955 17.4034 8.57955C17.983 8.82576 18.4375 9.18371 18.767 9.65341C19.0966 10.1231 19.2614 10.6894 19.2614 11.3523C19.2614 11.7955 19.1875 12.1894 19.0398 12.5341C18.8958 12.875 18.6932 13.178 18.4318 13.4432C18.1705 13.7045 17.8617 13.9413 17.5057 14.1534C17.2064 14.3314 16.9602 14.517 16.767 14.7102C16.5777 14.9034 16.4356 15.1269 16.3409 15.3807C16.25 15.6345 16.2027 15.947 16.1989 16.3182V16.5114H14.0455ZM15.1705 20.1477C14.7917 20.1477 14.4678 20.0152 14.1989 19.75C13.9337 19.4811 13.803 19.1591 13.8068 18.7841C13.803 18.4129 13.9337 18.0947 14.1989 17.8295C14.4678 17.5644 14.7917 17.4318 15.1705 17.4318C15.5303 17.4318 15.8466 17.5644 16.1193 17.8295C16.392 18.0947 16.5303 18.4129 16.5341 18.7841C16.5303 19.0341 16.464 19.2633 16.3352 19.4716C16.2102 19.6761 16.0455 19.8409 15.8409 19.9659C15.6364 20.0871 15.4129 20.1477 15.1705 20.1477Z"
                                    fill="white" />
                            </svg>
                        </span>
                        <picture>
                            @if(!empty($stockBlocks[1]['src_webp']))
                                <source srcset="{{ $stockBlocks[1]['src_webp'] }}" type="image/webp">
                            @endif
                            @if(!empty($stockBlocks[1]['type']))
                                <source srcset="{{ $stockBlocks[1]['src'] }}" type="{{ $stockBlocks[1]['type'] }}">
                            @endif
                            <img width="150" height="150" src="{{ $stockBlocks[1]['src'] }}" {!! $stockAttr($stockBlocks[1]) !!}>
                        </picture>
                    </div>
                    <div class="sm-title">
                        @lang('stock.text_3_1')
                    </div>
                    <p class="sm-inf">
                        @lang('stock.text_3_2')
                    </p>
                    <hr>
                    <p class="sm-text">
                        @lang('stock.text_3_3')
                    </p>
                    <p class="sm-disc">
                        @lang('stock.text_3_4')
                    </p>
                    @auth
                        <a href="#" class="sm-btn js-popup-msg">
                            @lang('stock.text_3_5')
                        </a>
                    @else
                        <a href="#" class="sm-btn js-popup-login">
                            @lang('stock.text_3_5')
                        </a>
                    @endauth

                </div>
                <div class="sm-item">
                    <span class="num">2.</span>
                    <div class="sm-img">
                        <span class="sm-add">
                            <svg width="30" height="30" viewBox="0 0 30 30" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <circle cx="15" cy="15" r="15" fill="#C80B0B" />
                                <path
                                    d="M14.0455 16.5114V16.3182C14.0492 15.6553 14.108 15.1269 14.2216 14.733C14.339 14.339 14.5095 14.0208 14.733 13.7784C14.9564 13.536 15.2254 13.3163 15.5398 13.1193C15.7746 12.9678 15.9848 12.8106 16.1705 12.6477C16.3561 12.4848 16.5038 12.3049 16.6136 12.108C16.7235 11.9072 16.7784 11.6837 16.7784 11.4375C16.7784 11.1761 16.7159 10.947 16.5909 10.75C16.4659 10.553 16.2973 10.4015 16.0852 10.2955C15.8769 10.1894 15.6458 10.1364 15.392 10.1364C15.1458 10.1364 14.9129 10.1913 14.6932 10.3011C14.4735 10.4072 14.2936 10.5663 14.1534 10.7784C14.0133 10.9867 13.9375 11.2462 13.9261 11.5568H11.608C11.6269 10.7992 11.8087 10.1742 12.1534 9.68182C12.4981 9.18561 12.9545 8.81629 13.5227 8.57386C14.0909 8.32765 14.7178 8.20455 15.4034 8.20455C16.1572 8.20455 16.8239 8.32955 17.4034 8.57955C17.983 8.82576 18.4375 9.18371 18.767 9.65341C19.0966 10.1231 19.2614 10.6894 19.2614 11.3523C19.2614 11.7955 19.1875 12.1894 19.0398 12.5341C18.8958 12.875 18.6932 13.178 18.4318 13.4432C18.1705 13.7045 17.8617 13.9413 17.5057 14.1534C17.2064 14.3314 16.9602 14.517 16.767 14.7102C16.5777 14.9034 16.4356 15.1269 16.3409 15.3807C16.25 15.6345 16.2027 15.947 16.1989 16.3182V16.5114H14.0455ZM15.1705 20.1477C14.7917 20.1477 14.4678 20.0152 14.1989 19.75C13.9337 19.4811 13.803 19.1591 13.8068 18.7841C13.803 18.4129 13.9337 18.0947 14.1989 17.8295C14.4678 17.5644 14.7917 17.4318 15.1705 17.4318C15.5303 17.4318 15.8466 17.5644 16.1193 17.8295C16.392 18.0947 16.5303 18.4129 16.5341 18.7841C16.5303 19.0341 16.464 19.2633 16.3352 19.4716C16.2102 19.6761 16.0455 19.8409 15.8409 19.9659C15.6364 20.0871 15.4129 20.1477 15.1705 20.1477Z"
                                    fill="white" />
                            </svg>
                        </span>
                        <picture>
                            @if(!empty($stockBlocks[2]['src_webp']))
                                <source srcset="{{ $stockBlocks[2]['src_webp'] }}" type="image/webp">
                            @endif
                            @if(!empty($stockBlocks[2]['type']))
                                <source srcset="{{ $stockBlocks[2]['src'] }}" type="{{ $stockBlocks[2]['type'] }}">
                            @endif
                            <img width="150" height="150" src="{{ $stockBlocks[2]['src'] }}" {!! $stockAttr($stockBlocks[2]) !!}>
                        </picture>
                    </div>
                    <div class="sm-title">
                        @lang('stock.text_4_1')
                    </div>
                    <p class="sm-inf">
                        @lang('stock.text_4_2')
                    </p>
                    <hr>
                    <p class="sm-text">
                        @lang('stock.text_4_3')
                    </p>
                    <p class="sm-disc">
                        @lang('stock.text_4_4')
                    </p>
                    @auth
                        <a href="#" class="sm-btn js-popup-friend">
                            @lang('stock.text_4_5')
                        </a>
                    @else
                        <a href="#" class="sm-btn js-popup-login">
                            @lang('stock.text_4_5')
                        </a>
                    @endauth
                </div>
                <div class="sm-item">
                    <span class="num">3.</span>
                    <div class="sm-img">
                        <span class="sm-add">
                            <svg width="30" height="30" viewBox="0 0 30 30" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <circle cx="15" cy="15" r="15" fill="#C80B0B" />
                                <path
                                    d="M14.0455 16.5114V16.3182C14.0492 15.6553 14.108 15.1269 14.2216 14.733C14.339 14.339 14.5095 14.0208 14.733 13.7784C14.9564 13.536 15.2254 13.3163 15.5398 13.1193C15.7746 12.9678 15.9848 12.8106 16.1705 12.6477C16.3561 12.4848 16.5038 12.3049 16.6136 12.108C16.7235 11.9072 16.7784 11.6837 16.7784 11.4375C16.7784 11.1761 16.7159 10.947 16.5909 10.75C16.4659 10.553 16.2973 10.4015 16.0852 10.2955C15.8769 10.1894 15.6458 10.1364 15.392 10.1364C15.1458 10.1364 14.9129 10.1913 14.6932 10.3011C14.4735 10.4072 14.2936 10.5663 14.1534 10.7784C14.0133 10.9867 13.9375 11.2462 13.9261 11.5568H11.608C11.6269 10.7992 11.8087 10.1742 12.1534 9.68182C12.4981 9.18561 12.9545 8.81629 13.5227 8.57386C14.0909 8.32765 14.7178 8.20455 15.4034 8.20455C16.1572 8.20455 16.8239 8.32955 17.4034 8.57955C17.983 8.82576 18.4375 9.18371 18.767 9.65341C19.0966 10.1231 19.2614 10.6894 19.2614 11.3523C19.2614 11.7955 19.1875 12.1894 19.0398 12.5341C18.8958 12.875 18.6932 13.178 18.4318 13.4432C18.1705 13.7045 17.8617 13.9413 17.5057 14.1534C17.2064 14.3314 16.9602 14.517 16.767 14.7102C16.5777 14.9034 16.4356 15.1269 16.3409 15.3807C16.25 15.6345 16.2027 15.947 16.1989 16.3182V16.5114H14.0455ZM15.1705 20.1477C14.7917 20.1477 14.4678 20.0152 14.1989 19.75C13.9337 19.4811 13.803 19.1591 13.8068 18.7841C13.803 18.4129 13.9337 18.0947 14.1989 17.8295C14.4678 17.5644 14.7917 17.4318 15.1705 17.4318C15.5303 17.4318 15.8466 17.5644 16.1193 17.8295C16.392 18.0947 16.5303 18.4129 16.5341 18.7841C16.5303 19.0341 16.464 19.2633 16.3352 19.4716C16.2102 19.6761 16.0455 19.8409 15.8409 19.9659C15.6364 20.0871 15.4129 20.1477 15.1705 20.1477Z"
                                    fill="white" />
                            </svg>
                        </span>
                        <span class="sm-fc">
                            <svg width="40" height="40" viewBox="0 0 40 40" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <rect width="40" height="40" rx="20" fill="#2656FF" />
                                <path
                                    d="M23.2676 20.875L23.6504 18.3594H21.2168V16.7188C21.2168 16.0078 21.5449 15.3516 22.6387 15.3516H23.7598V13.1914C23.7598 13.1914 22.748 13 21.791 13C19.7949 13 18.4824 14.2305 18.4824 16.418V18.3594H16.2402V20.875H18.4824V27H21.2168V20.875H23.2676Z"
                                    fill="white" />
                            </svg>
                        </span>
                        <picture>
                            @if(!empty($stockBlocks[3]['src_webp']))
                                <source srcset="{{ $stockBlocks[3]['src_webp'] }}" type="image/webp">
                            @endif
                            @if(!empty($stockBlocks[3]['type']))
                                <source srcset="{{ $stockBlocks[3]['src'] }}" type="{{ $stockBlocks[3]['type'] }}">
                            @endif
                            <img width="150" height="150"
                                src="{{ $stockBlocks[3]['src'] }}" {!! $stockAttr($stockBlocks[3]) !!}>
                        </picture>
                    </div>
                    <div class="sm-title">
                        @lang('stock.text_5_1')
                    </div>
                    <p class="sm-inf">
                        @lang('stock.text_5_2')
                    </p>
                    <hr>
                    <p class="sm-text">
                        @lang('stock.text_5_3')
                    </p>
                    <p class="sm-disc">
                        @lang('stock.text_5_4')
                    </p>
                    @auth
                        <a href="#" class="sm-btn js-popup-bonus">
                            @lang('stock.text_5_5')
                        </a>
                    @else
                        <a href="#" class="sm-btn js-popup-login">
                            @lang('stock.text_5_5')
                        </a>
                    @endauth

                </div>
            </div>
        </div>
    </div>
</div>


<div class="stock-bonus">
    <div class="section-frame">
        <div class="stock-bonus__inner">
            <h2 class="page-title stock-bonus__title">
                @lang('stock.text_6_1')
            </h2>
            <div class="sb-block">
                <div class="sb-item">
                    <div class="sb-left sb-side">
                        <div class="sb-title b-title">
                            @lang('stock.text_6_2')
                        </div>
                        <div class="sb-subtitle">
                            @lang('stock.text_6_3')
                        </div>
                    </div>
                    <div class="sb-center">
                        <div class="sb-center__inner">
                            <div class="sb1-img">
                                <p>
                                    @lang('stock.text_6_4')
                                </p>
                                <div class="sb1-image">
                                    <picture>
                                        @if(!empty($bonusImages[1]['src_webp']))
                                            <source srcset="{{ $bonusImages[1]['src_webp'] }}"
                                                type="image/webp">
                                        @endif
                                        @if(!empty($bonusImages[1]['type']))
                                            <source srcset="{{ $bonusImages[1]['src'] }}"
                                                type="{{ $bonusImages[1]['type'] }}">
                                        @endif
                                        <img width="144" height="186"
                                            src="{{ $bonusImages[1]['src'] }}" {!! $stockAttr($bonusImages[1]) !!}>
                                    </picture>
                                    <img width="120" height="150"
                                        src="{{ asset(env('THEME') . 'images') }}/stock/v1.svg" alt="Viar">
                                </div>
                            </div>
                            <div class="sb2-img">
                                <p>
                                    @lang('stock.text_6_5')
                                </p>
                                <div class="sb2-image">
                                    <picture>
                                        @if(!empty($bonusImages[2]['src_webp']))
                                            <source srcset="{{ $bonusImages[2]['src_webp'] }}"
                                                type="image/webp">
                                        @endif
                                        @if(!empty($bonusImages[2]['type']))
                                            <source srcset="{{ $bonusImages[2]['src'] }}"
                                                type="{{ $bonusImages[2]['type'] }}">
                                        @endif
                                        <img width="195" height="217"
                                            src="{{ $bonusImages[2]['src'] }}" {!! $stockAttr($bonusImages[2]) !!}>
                                    </picture>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sb-right sb-side">
                        <ul>
                            <li>
                                <span>1.</span>
                                <p>
                                    @lang('stock.text_6_6')
                                </p>
                            </li>
                            <li>
                                <span>2.</span>
                                <p>
                                    @lang('stock.text_6_7')
                                </p>
                            </li>
                            <li>
                                <span>3.</span>
                                <p>
                                    @lang('stock.text_6_8')
                                </p>
                            </li>
                        </ul>



                        <a href="#" class="sb-btn js-popup-screen_bonus">
                            @lang('stock.text_6_9')
                        </a>



                    </div>
                </div>
                <div class="sb-item">
                    <div class="sb-left sb-side">
                        <div class="sb-title w-title">
                            @lang('stock.text_7_1')
                        </div>
                    </div>
                    <div class="sb-center">
                        <div class="sb-center__inner">
                            <div class="sb3-img">
                                <div class="sb3-image">
                                    <picture>
                                        @if(!empty($bonusImages[3]['src_webp']))
                                            <source srcset="{{ $bonusImages[3]['src_webp'] }}"
                                                type="image/webp">
                                        @endif
                                        @if(!empty($bonusImages[3]['type']))
                                            <source srcset="{{ $bonusImages[3]['src'] }}"
                                                type="{{ $bonusImages[3]['type'] }}">
                                        @endif
                                        <img width="215" height="327"
                                            src="{{ $bonusImages[3]['src'] }}" {!! $stockAttr($bonusImages[3]) !!}>
                                    </picture>
                                    <img width="120" height="150"
                                        src="{{ asset(env('THEME') . 'images') }}/stock/v2.svg" alt="Viar">
                                </div>
                                <p>
                                    @lang('stock.text_7_2')
                                </p>
                            </div>
                            <div class="sb4-img">
                                <p>
                                    @lang('stock.text_7_3')
                                </p>
                                <div class="sb4-image">
                                    <picture>
                                        @if(!empty($bonusImages[4]['src_webp']))
                                            <source srcset="{{ $bonusImages[4]['src_webp'] }}"
                                                type="image/webp">
                                        @endif
                                        @if(!empty($bonusImages[4]['type']))
                                            <source srcset="{{ $bonusImages[4]['src'] }}"
                                                type="{{ $bonusImages[4]['type'] }}">
                                        @endif
                                        <img width="220" height="190"
                                            src="{{ $bonusImages[4]['src'] }}" {!! $stockAttr($bonusImages[4]) !!}>
                                    </picture>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sb-right sb-side">
                        <div class="sb-title w-title sb-txt-c">
                            @lang('stock.text_7_4')
                        </div>
                        @auth
                            <a href="#" user_id="{{ $user_id }}" coloumn="is_40_60"
                                class="sb-btn js-popup-activation">
                                @lang('stock.modal_40_60_button')
                            </a>
                        @else
                            <a href="#" class="sm-btn js-popup-login">
                                @lang('stock.modal_40_60_button')
                            </a>
                        @endauth

                    </div>
                </div>
                <div class="sb-item">
                    <div class="sb-left sb-side">
                        <div class="sb-title w-title">
                            @lang('stock.text_8_1')
                        </div>
                    </div>
                    <div class="sb-center">
                        <div class="sb-center__inner">
                            <div class="sb5-img">
                            <div class="sb5-image">
                                <picture>
                                    @if(!empty($bonusImages[5]['src_webp']))
                                        <source srcset="{{ $bonusImages[5]['src_webp'] }}"
                                            type="image/webp">
                                    @endif
                                    @if(!empty($bonusImages[5]['type']))
                                        <source srcset="{{ $bonusImages[5]['src'] }}"
                                            type="{{ $bonusImages[5]['type'] }}">
                                    @endif
                                    <img width="350" height="350"
                                        src="{{ $bonusImages[5]['src'] }}" {!! $stockAttr($bonusImages[5]) !!}>
                                </picture>
                            </div>
                                <img src="{{ asset(env('THEME') . 'images') }}/stock/v3.svg" alt="Viar">
                                <div class="sb-abs">
                                    <p>
                                        @lang('stock.text_8_2')
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sb-right sb-side">
                        <div class="sb-title w-title sb-txt-c">
                            @lang('stock.modal_3_1_podarok_button')
                        </div>

                        @auth
                            <a href="#" user_id="{{ $user_id }}" coloumn="is_1free"
                                class="sb-btn js-popup-activation">
                                @lang('stock.modal_3_1_podarok_button')
                            </a>
                        @else
                            <a href="#" class="sm-btn js-popup-login">
                                @lang('stock.modal_3_1_podarok_button')
                            </a>
                        @endauth



                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="stock-offer" id="top_mail_section">
    <div class="custom-shape-divider-top-1665087820">
        <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120"
            preserveAspectRatio="none">
            <path d="M600,112.77C268.63,112.77,0,65.52,0,7.23V120H1200V7.23C1200,65.52,931.37,112.77,600,112.77Z"
                class="shape-fill"></path>
        </svg>
    </div>
    <div class="section-frame">
        <div class="stock-offer__inner">
            <div class="so-title__row">
                <h2 class="so-title page-title">
                    @lang('stock.text_9_1')
                </h2>
                <div class="so-add">
                    <picture>
                        <source srcset="{{ asset(env('THEME') . 'images') }}/stock/o1.avif" type="image/avif">
                        <source srcset="{{ asset(env('THEME') . 'images') }}/stock/o1.webp" type="image/webp">
                        <source srcset="{{ asset(env('THEME') . 'images') }}/stock/o1.png" type="image/png">
                        <img width="120" height="86" src="{{ asset(env('THEME') . 'images') }}/stock/o1.png"
                            alt="Viar">
                    </picture>
                    <p>
                        @lang('stock.text_9_2')
                    </p>
                    <img width="90" height="88" src="{{ asset(env('THEME') . 'images') }}/stock/v4.svg"
                        alt="Viar">
                </div>
            </div>


            <div class="so-slider__wrapper">
                <div class="so-slider">

                    @foreach ($top_mail as $element)
                        <div class="so-slide">

                            {{--                        <p>-20% скидка еще 2 дня</p> --}}
                            <picture>
                                {{--                            <source srcset="{{ asset(env('THEME') . 'images') }}/stock/o2.avif" type="image/avif"> --}}
                                {{--                            <source srcset="{{ asset(env('THEME') . 'images') }}/stock/o2.webp" type="image/webp"> --}}
                                <source srcset="{{ $images_folder . $element->image }}" type="image/jpeg">
                                <img width="120px" height="86px" src="{{ $images_folder . $element->image }}"
                                    alt="Viar">
                            </picture>
                            <div class="sos-row">
                                <div class="so-t">
                                    {{ $element->value }}
                                </div>
                                <div class="so-s">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M0.466667 0H0V0.466667V5.13333H0.933333V1.5933L4.80335 5.46331L5.46331 4.80335L1.5933 0.933333H5.13333V0H0.466667ZM13.5333 0H14V0.466667V5.13333H13.0667V1.5933L9.19665 5.46331L8.53669 4.80335L12.4067 0.933333H8.86667V0H13.5333ZM14 14H13.5333H8.86667V13.0667H12.4067L8.53669 9.19665L9.19665 8.53669L13.0667 12.4067V8.86667H14V13.5333V14ZM0.466667 14H0V13.5333V8.86667H0.933333V12.4067L4.80335 8.53669L5.46331 9.19665L1.5933 13.0667H5.13333V14H0.466667Z"
                                            fill="#FA7846" />
                                    </svg>
                                    <span> {{ $element->size }}</span>
                                </div>
                            </div>
                            <div class="sos-row m-row">
                                <div class="so-prices">
                                    @if ($element->sale_price != 0)
                                        <div class="so-p-n">
                                            {{ $element->sale_price * $multiplyer }}
                                        </div>
                                        <div class="so-p-o">
                                            {{ $element->price * $multiplyer }}
                                        </div>
                                    @else
                                        <div class="so-p-n">
                                            {{ $element->price * $multiplyer }}
                                        </div>
                                    @endif
                                </div>
                                <div class="so-s">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M0.466667 0H0V0.466667V5.13333H0.933333V1.5933L4.80335 5.46331L5.46331 4.80335L1.5933 0.933333H5.13333V0H0.466667ZM13.5333 0H14V0.466667V5.13333H13.0667V1.5933L9.19665 5.46331L8.53669 4.80335L12.4067 0.933333H8.86667V0H13.5333ZM14 14H13.5333H8.86667V13.0667H12.4067L8.53669 9.19665L9.19665 8.53669L13.0667 12.4067V8.86667H14V13.5333V14ZM0.466667 14H0V13.5333V8.86667H0.933333V12.4067L4.80335 8.53669L5.46331 9.19665L1.5933 13.0667H5.13333V14H0.466667Z"
                                            fill="#FA7846" />
                                    </svg>
                                    <span>{{ $element->size }}</span>
                                </div>
                            </div>
                            <div class="sos-row">

                                <div class="so-prices">
                                    @if ($element->sale_price != 0)
                                        @php $total_price = $element->sale_price*$multiplyer @endphp

                                        <div class="so-p-n">
                                            {{ $element->sale_price * $multiplyer }}€
                                        </div>
                                        <div class="so-p-o">
                                            {{ $element->price * $multiplyer }}€
                                        </div>
                                    @else
                                        @php $total_price = $element->price*$multiplyer @endphp
                                        <div class="so-p-n">
                                            {{ $element->price * $multiplyer }}€
                                        </div>
                                    @endif
                                </div>
                                <a href="#" class="so-btn top-mail-button" data-price="<?= $total_price ?>"
                                    data-size="<?= $element->size ?>" data-name="<?= $element->value ?>"
                                    data-catid="<?= $element->cat_id ?>">
                                    @lang('stock.topmail_buy_button')
                                </a>
                            </div>

                        </div>
                    @endforeach

                </div>

                <div class="so-nav">
                    <div class="so-arr so-prev">
                        <svg width="12" height="19" viewBox="0 0 12 19" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M11 18L2 9.5L11 1" stroke="#1E2533" stroke-width="2" />
                        </svg>
                    </div>
                    <div class="so-arr so-next">
                        <svg width="12" height="19" viewBox="0 0 12 19" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M1.00024 0.999999L10.0002 9.5L1.00024 18" stroke="white" stroke-width="2" />
                        </svg>
                    </div>
                </div>
            </div>





        </div>
    </div>
</div>






@include(config('theme.resource') . 'pages.index.faq9')

@include(env('THEME_RESOURCES') . 'pages.gallery.zpart_trybuy', [
    'white' => true,
    'module_sale' => $mod_sale,
    'foto_sale' => $foto_sale,
    'repr_sale' => $repr_sale,
    'gallery' => $gallery,
])
