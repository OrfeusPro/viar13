@include(config('theme.resource') . 'pages.sizesprices.breads')

<div class="portraits portrait__screen new-screen size-screen no-bg">
    <div class="portraits-wrap">
        <div class="portraits-slider">
            <div class="portraits-slide sl-slider pp-screen">
                <div class="m-screen-slide">
                    <div class="pmain__screen">
                        {{-- <div class="pm-object">
                            <picture>
                                <img class="canvas-object" width="440" height="450"
                                     src="{{ asset(config('theme.current') . '/images/sizesprices/1.webp') }}"
                                     alt="Viar Image">
                            </picture>
                        </div> --}}
                        {{-- <div class="mp-m-bg">
                            <picture>
                                <source media="(max-width: 520px)"
                                    srcset="{{ asset(config('theme.current') . '/images/sizesprices/1Min.webp') }}"
                                    type="image/jpeg">
                                <img width="520" height="400"
                                    src="{{ asset(config('theme.current') . '/images/sizesprices/1Min.webp') }}"
                                    alt="Viar Image">
                            </picture>
                        </div> --}}
                        <div class="mp__inner">
                            <div class="section-frame">
                                <h1 class="portraits-title mp-title">
                                    <span>@lang('pages.sizeprices.silder.title')</span>
                                </h1>
                                <div class="mp__inner--list">
                                    <div class="mp__list--item">
                                        <svg width="35" height="35" viewBox="0 0 35 35" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M34.5928 25.7594C34.492 23.5318 33.543 21.4651 31.9207 19.9401C31.8605 19.8834 31.6612 19.7047 31.6249 19.6725C30.6376 18.7937 29.368 18.3662 28.0497 18.4682C27.9913 18.4727 27.9335 18.4795 27.8757 18.486L4.95924 0.308366C4.36766 -0.161007 3.5109 -0.0866146 3.00882 0.477423C2.50705 1.04122 2.53242 1.90064 3.06665 2.43391L23.6221 22.9494C23.5322 24.3317 24.0323 25.7402 25.0986 26.7557C25.7258 27.3529 26.4998 27.7705 27.3368 27.9633C29.1164 28.3734 30.4676 29.6731 30.8632 31.3553C30.8983 31.5046 30.9284 31.6561 30.9545 31.8081C27.7182 33.2352 23.656 34.0187 19.4672 34.0187C18.1153 34.0187 16.7866 33.9392 15.4985 33.785C12.7048 33.3805 10.1475 32.4853 8.0913 31.1667C5.26238 29.3527 3.70944 26.9358 3.70944 24.3612C3.70944 21.007 5.79831 18.3882 10.2748 16.1456C10.5156 16.025 10.6264 15.7417 10.5291 15.4905C10.4294 15.2329 10.1454 15.0973 9.88244 15.1819C7.15934 16.0586 4.86118 17.3012 3.21398 18.7909C1.37187 20.4569 0.398071 22.387 0.398071 24.3726C0.398071 27.2642 2.42095 29.9593 6.0941 31.9615C8.69306 33.3783 11.9086 34.3324 15.4052 34.7493C16.5591 34.9148 17.7483 34.9999 18.9541 34.9999V34.9839C19.1248 34.9864 19.2958 34.9879 19.4673 34.9879C23.7139 34.9879 27.8225 34.1509 31.1728 32.7703C31.5749 32.6047 32.249 32.0295 32.4458 31.8085C33.9315 30.1395 34.6939 27.9912 34.5928 25.7594ZM1.36735 24.3726C1.36735 22.1765 2.7934 20.0783 5.29187 18.3976C3.56639 20.1268 2.7379 22.085 2.7379 24.3611C2.7379 26.7105 3.85079 28.9235 5.91261 30.7401C2.97497 28.9679 1.36735 26.7259 1.36735 24.3726ZM3.73287 1.12175C3.8936 0.941222 4.16775 0.917637 4.357 1.06771L24.5903 17.1171C23.8857 17.4167 23.2648 17.8807 22.7643 18.4888C22.4136 18.9151 22.1412 19.3993 21.9565 19.9175L3.75136 1.74782C3.58045 1.57723 3.57229 1.3022 3.73287 1.12175ZM22.738 20.6977C22.8682 20.1128 23.1331 19.566 23.5127 19.1047C24.0315 18.4744 24.7117 18.0375 25.4898 17.8306L26.6829 18.777C25.9188 19.0715 25.2388 19.5593 24.7011 20.2126C24.3054 20.6935 24.0188 21.2321 23.839 21.7965L22.738 20.6977ZM31.7943 31.0814C31.2983 29.0624 29.6785 27.5081 27.5546 27.0187C26.8866 26.8648 26.2685 26.5311 25.767 26.0536C24.3151 24.6711 24.1757 22.376 25.4495 20.8285C26.1223 20.0112 27.0723 19.5161 28.1246 19.4345C28.227 19.4266 28.3289 19.4227 28.4303 19.4227C29.3726 19.4227 30.2684 19.7627 30.9803 20.3964C31.02 20.4318 31.2045 20.5972 31.2567 20.6462C32.6942 21.9976 33.5351 23.8291 33.6245 25.8033C33.7125 27.7445 33.0633 29.6144 31.7943 31.0814Z"
                                                fill="url(#paint0_linear_1_915122)"></path>
                                            <defs>
                                                <linearGradient id="paint0_linear_1_915122" x1="17.4999"
                                                    y1="0" x2="17.4999" y2="34.9999"
                                                    gradientUnits="userSpaceOnUse">
                                                    <stop offset="100%" stop-color="#FA7846"></stop>
                                                    <stop offset="1" stop-color="#91665E"></stop>
                                                </linearGradient>
                                            </defs>
                                        </svg>
                                        @lang('pages.sizeprices.silder.text1')
                                    </div>
                                    <div class="mp__list--item">
                                        <svg width="35" height="35" viewBox="0 0 35 35" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M34.5928 25.7594C34.492 23.5318 33.543 21.4651 31.9207 19.9401C31.8605 19.8834 31.6612 19.7047 31.6249 19.6725C30.6376 18.7937 29.368 18.3662 28.0497 18.4682C27.9913 18.4727 27.9335 18.4795 27.8757 18.486L4.95924 0.308366C4.36766 -0.161007 3.5109 -0.0866146 3.00882 0.477423C2.50705 1.04122 2.53242 1.90064 3.06665 2.43391L23.6221 22.9494C23.5322 24.3317 24.0323 25.7402 25.0986 26.7557C25.7258 27.3529 26.4998 27.7705 27.3368 27.9633C29.1164 28.3734 30.4676 29.6731 30.8632 31.3553C30.8983 31.5046 30.9284 31.6561 30.9545 31.8081C27.7182 33.2352 23.656 34.0187 19.4672 34.0187C18.1153 34.0187 16.7866 33.9392 15.4985 33.785C12.7048 33.3805 10.1475 32.4853 8.0913 31.1667C5.26238 29.3527 3.70944 26.9358 3.70944 24.3612C3.70944 21.007 5.79831 18.3882 10.2748 16.1456C10.5156 16.025 10.6264 15.7417 10.5291 15.4905C10.4294 15.2329 10.1454 15.0973 9.88244 15.1819C7.15934 16.0586 4.86118 17.3012 3.21398 18.7909C1.37187 20.4569 0.398071 22.387 0.398071 24.3726C0.398071 27.2642 2.42095 29.9593 6.0941 31.9615C8.69306 33.3783 11.9086 34.3324 15.4052 34.7493C16.5591 34.9148 17.7483 34.9999 18.9541 34.9999V34.9839C19.1248 34.9864 19.2958 34.9879 19.4673 34.9879C23.7139 34.9879 27.8225 34.1509 31.1728 32.7703C31.5749 32.6047 32.249 32.0295 32.4458 31.8085C33.9315 30.1395 34.6939 27.9912 34.5928 25.7594ZM1.36735 24.3726C1.36735 22.1765 2.7934 20.0783 5.29187 18.3976C3.56639 20.1268 2.7379 22.085 2.7379 24.3611C2.7379 26.7105 3.85079 28.9235 5.91261 30.7401C2.97497 28.9679 1.36735 26.7259 1.36735 24.3726ZM3.73287 1.12175C3.8936 0.941222 4.16775 0.917637 4.357 1.06771L24.5903 17.1171C23.8857 17.4167 23.2648 17.8807 22.7643 18.4888C22.4136 18.9151 22.1412 19.3993 21.9565 19.9175L3.75136 1.74782C3.58045 1.57723 3.57229 1.3022 3.73287 1.12175ZM22.738 20.6977C22.8682 20.1128 23.1331 19.566 23.5127 19.1047C24.0315 18.4744 24.7117 18.0375 25.4898 17.8306L26.6829 18.777C25.9188 19.0715 25.2388 19.5593 24.7011 20.2126C24.3054 20.6935 24.0188 21.2321 23.839 21.7965L22.738 20.6977ZM31.7943 31.0814C31.2983 29.0624 29.6785 27.5081 27.5546 27.0187C26.8866 26.8648 26.2685 26.5311 25.767 26.0536C24.3151 24.6711 24.1757 22.376 25.4495 20.8285C26.1223 20.0112 27.0723 19.5161 28.1246 19.4345C28.227 19.4266 28.3289 19.4227 28.4303 19.4227C29.3726 19.4227 30.2684 19.7627 30.9803 20.3964C31.02 20.4318 31.2045 20.5972 31.2567 20.6462C32.6942 21.9976 33.5351 23.8291 33.6245 25.8033C33.7125 27.7445 33.0633 29.6144 31.7943 31.0814Z"
                                                fill="url(#paint0_linear_1_915122)"></path>
                                            <defs>
                                                <linearGradient id="paint0_linear_1_915122" x1="17.4999"
                                                    y1="0" x2="17.4999" y2="34.9999"
                                                    gradientUnits="userSpaceOnUse">
                                                    <stop offset="100%" stop-color="#FA7846"></stop>
                                                    <stop offset="1" stop-color="#91665E"></stop>
                                                </linearGradient>
                                            </defs>
                                        </svg>
                                        @lang('pages.sizeprices.silder.text2')
                                    </div>
                                    <div class="mp__list--item">
                                        <svg width="35" height="35" viewBox="0 0 35 35" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M34.5928 25.7594C34.492 23.5318 33.543 21.4651 31.9207 19.9401C31.8605 19.8834 31.6612 19.7047 31.6249 19.6725C30.6376 18.7937 29.368 18.3662 28.0497 18.4682C27.9913 18.4727 27.9335 18.4795 27.8757 18.486L4.95924 0.308366C4.36766 -0.161007 3.5109 -0.0866146 3.00882 0.477423C2.50705 1.04122 2.53242 1.90064 3.06665 2.43391L23.6221 22.9494C23.5322 24.3317 24.0323 25.7402 25.0986 26.7557C25.7258 27.3529 26.4998 27.7705 27.3368 27.9633C29.1164 28.3734 30.4676 29.6731 30.8632 31.3553C30.8983 31.5046 30.9284 31.6561 30.9545 31.8081C27.7182 33.2352 23.656 34.0187 19.4672 34.0187C18.1153 34.0187 16.7866 33.9392 15.4985 33.785C12.7048 33.3805 10.1475 32.4853 8.0913 31.1667C5.26238 29.3527 3.70944 26.9358 3.70944 24.3612C3.70944 21.007 5.79831 18.3882 10.2748 16.1456C10.5156 16.025 10.6264 15.7417 10.5291 15.4905C10.4294 15.2329 10.1454 15.0973 9.88244 15.1819C7.15934 16.0586 4.86118 17.3012 3.21398 18.7909C1.37187 20.4569 0.398071 22.387 0.398071 24.3726C0.398071 27.2642 2.42095 29.9593 6.0941 31.9615C8.69306 33.3783 11.9086 34.3324 15.4052 34.7493C16.5591 34.9148 17.7483 34.9999 18.9541 34.9999V34.9839C19.1248 34.9864 19.2958 34.9879 19.4673 34.9879C23.7139 34.9879 27.8225 34.1509 31.1728 32.7703C31.5749 32.6047 32.249 32.0295 32.4458 31.8085C33.9315 30.1395 34.6939 27.9912 34.5928 25.7594ZM1.36735 24.3726C1.36735 22.1765 2.7934 20.0783 5.29187 18.3976C3.56639 20.1268 2.7379 22.085 2.7379 24.3611C2.7379 26.7105 3.85079 28.9235 5.91261 30.7401C2.97497 28.9679 1.36735 26.7259 1.36735 24.3726ZM3.73287 1.12175C3.8936 0.941222 4.16775 0.917637 4.357 1.06771L24.5903 17.1171C23.8857 17.4167 23.2648 17.8807 22.7643 18.4888C22.4136 18.9151 22.1412 19.3993 21.9565 19.9175L3.75136 1.74782C3.58045 1.57723 3.57229 1.3022 3.73287 1.12175ZM22.738 20.6977C22.8682 20.1128 23.1331 19.566 23.5127 19.1047C24.0315 18.4744 24.7117 18.0375 25.4898 17.8306L26.6829 18.777C25.9188 19.0715 25.2388 19.5593 24.7011 20.2126C24.3054 20.6935 24.0188 21.2321 23.839 21.7965L22.738 20.6977ZM31.7943 31.0814C31.2983 29.0624 29.6785 27.5081 27.5546 27.0187C26.8866 26.8648 26.2685 26.5311 25.767 26.0536C24.3151 24.6711 24.1757 22.376 25.4495 20.8285C26.1223 20.0112 27.0723 19.5161 28.1246 19.4345C28.227 19.4266 28.3289 19.4227 28.4303 19.4227C29.3726 19.4227 30.2684 19.7627 30.9803 20.3964C31.02 20.4318 31.2045 20.5972 31.2567 20.6462C32.6942 21.9976 33.5351 23.8291 33.6245 25.8033C33.7125 27.7445 33.0633 29.6144 31.7943 31.0814Z"
                                                fill="url(#paint0_linear_1_915122)"></path>
                                            <defs>
                                                <linearGradient id="paint0_linear_1_915122" x1="17.4999"
                                                    y1="0" x2="17.4999" y2="34.9999"
                                                    gradientUnits="userSpaceOnUse">
                                                    <stop offset="100%" stop-color="#FA7846"></stop>
                                                    <stop offset="1" stop-color="#91665E"></stop>
                                                </linearGradient>
                                            </defs>
                                        </svg>
                                        @lang('pages.sizeprices.silder.text3')
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="ellipse">
    <img alt="img" src="{{ asset(config('theme.current') . '/images/icon/ellipse-whete.svg') }}"
         decoding="async" height="99" width="1374">
</div>

<div class="sizes-page">
    <div class="section-frame">
        <div class="sizes-page__inner">
            <div class="sizes-list">
                @php
                    $a_canvas_image = \App\Models\CanvasSlider::where('is_show', 1)->where('sub_canvas', 0)->first();
                @endphp
                <div class="sizes-item">
                    <a href="{{ route('canvas') }}" class="title-top title-top-mobile">
                        <p>
                            @lang('pages.sizeprices.item.style'):
                            <span class="mobTitle">
                                {!! $a_canvas_image->getTranslatedAttribute('size_title', app()->getLocale()) ?  $a_canvas_image->getTranslatedAttribute('size_title', app()->getLocale()) : $canvas->getTranslatedAttribute('meta_title', app()->getLocale()) !!}
                            </span>
                        </p>
                    </a>
                    <div class="img">
                        <picture>
                            <source media="(max-width: 576px)" srcset="{{ asset('storage/' . $a_canvas_image->size_img) }}"
                                    type="image/webp">
                            <source srcset="{{ asset('storage/' . $a_canvas_image->size_img) }}" type="image/webp">
                            <img width="430" height="450" src="{{ asset('storage/' . $a_canvas_image->size_img) }}"
                                 alt="">
                        </picture>
                    </div>
                    <div class="content text-content">
                        <div class="title-top">
                            <p>
                                @lang('pages.sizeprices.item.style'):
                                <span class="mobTitle">
                                    {!! $a_canvas_image->getTranslatedAttribute('size_title', app()->getLocale()) ?  $a_canvas_image->getTranslatedAttribute('size_title', app()->getLocale()) : $canvas->getTranslatedAttribute('meta_title', app()->getLocale()) !!}
                                </span>
                            </p>
                            <div class="title">
                                {!! $a_canvas_image->getTranslatedAttribute('size_title', app()->getLocale()) ?  $a_canvas_image->getTranslatedAttribute('size_title', app()->getLocale()) : $canvas->getTranslatedAttribute('meta_title', app()->getLocale()) !!}
                            </div>
                        </div>
                        <div class="text">
                            {!! $a_canvas_image->getTranslatedAttribute('size_text', app()->getLocale()) ?  $a_canvas_image->getTranslatedAttribute('size_text', app()->getLocale()) : $canvas->getTranslatedAttribute('meta_desc', app()->getLocale()) !!}
                        </div>
                        <a href="{{ route('canvas') }}" class="btn btn-desk">
                            @lang('pages.sizeprices.item.go_to_page')
                        </a>
                    </div>
                    <div class="content">
                        <p>@lang('pages.sizeprices.item.available_sizes_and_prices'): </p>
                        <p class="check-js">
                            @lang('pages.sizeprices.item.see_portraits')
                        </p>
                        <div class="sz-list">
                            @if ($canvas->sizes_30x40)
                                @foreach (collect(explode(',', $canvas->sizes_30x40)) as $size_item)
                                    @php
                                        $size_clear_vals = explode('x', substr($size_item, 0, strpos($size_item, '[')));
                                        $prices_vals = explode('-', get_string_between($size_item, '[', ']'));

                                        $sale_price = null;

                                        $check_price = $prices_vals[0];
                                        if (isset($prices_vals[1])) {
                                            $sale_price = $prices_vals[1];
                                            $check_price = $sale_price;
                                        }

                                        $sale_price = $sale_price * $contry_mult;
                                        $check_price = $check_price * $contry_mult;
                                        $prices_vals[0] = $prices_vals[0] * $contry_mult;
                                    @endphp
                                    <div class="sz-item">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                  d="M0.666667 0H0V0.666667V7.33333H1.33333V2.27614L6.86193 7.80473L7.80473 6.86193L2.27614 1.33333H7.33333V0H0.666667ZM19.3333 0H20V0.666667V7.33333H18.6667V2.27614L13.1381 7.80473L12.1953 6.86193L17.7239 1.33333H12.6667V0H19.3333ZM20 20H19.3333H12.6667V18.6667H17.7239L12.1953 13.1381L13.1381 12.1953L18.6667 17.7239V12.6667H20V19.3333V20ZM0.666667 20H0V19.3333V12.6667H1.33333V17.7239L6.86193 12.1953L7.80473 13.1381L2.27614 18.6667H7.33333V20H0.666667Z"
                                                  fill="#FA7846"></path>
                                        </svg>
                                        <p>{{ $size_clear_vals[0] }}x{{ $size_clear_vals[1] }} {{ trans('gl.cm') }}
                                        </p>
                                        <p class="sz-price">
                                            от @if ($sale_price != null)
                                                <del>{{ $prices_vals[0] }}€</del> <b>{{ $sale_price }}€</b>
                                            @else
                                                {{ $prices_vals[0] }}€
                                            @endif
                                        </p>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <a href="{{ route('canvas') }}" class="btn btn-mob">
                            @lang('pages.sizeprices.item.go_to_page')
                        </a>
                    </div>
                </div>

                @php
                    $a_collage_image = \App\Models\ACollageSlider::first();
                @endphp

                <div class="sizes-item">
                    <a href="{{ route('collage') }}" class="title-top title-top-mobile">
                        <p>
                            @lang('pages.sizeprices.item.style'):
                            <span class="mobTitle">
                                {!! $a_collage_image->getTranslatedAttribute('size_title', app()->getLocale()) ?  $a_collage_image->getTranslatedAttribute('size_title', app()->getLocale()) : $collage->name !!}
                            </span>
                        </p>
                    </a>
                    <div class="img">
                        <picture>
                            <source media="(max-width: 576px)"
                                    srcset="{{ asset('storage/' . $a_collage_image->size_img) }}" type="image/webp">
                            <source srcset="{{ asset('storage/' . $a_collage_image->size_img) }}" type="image/webp">
                            <img width="430" height="450"
                                 src="{{ asset('storage/' . $a_collage_image->size_img) }}" alt="">
                        </picture>
                    </div>
                    <div class="content text-content">
                        <div class="title-top">
                            <p>
                                @lang('pages.sizeprices.item.style'):
                                <span class="mobTitle">
                                    {!! $a_collage_image->getTranslatedAttribute('size_title', app()->getLocale()) ?  $a_collage_image->getTranslatedAttribute('size_title', app()->getLocale()) : $collage->name !!}
                                </span>
                            </p>
                            <div class="title">
                                {!! $a_collage_image->getTranslatedAttribute('size_title', app()->getLocale()) ?  $a_collage_image->getTranslatedAttribute('size_title', app()->getLocale()) : $collage->name !!}
                            </div>
                        </div>
                        <div class="text">
                            {!! $a_collage_image->getTranslatedAttribute('size_text', app()->getLocale()) ?  $a_collage_image->getTranslatedAttribute('size_text', app()->getLocale()) : $collage->name !!}
                        </div>
                        <a href="{{ route('collage') }}" class="btn btn-desk">
                            @lang('pages.sizeprices.item.go_to_page')
                        </a>
                    </div>
                    <div class="content">
                        <p>@lang('pages.sizeprices.item.available_sizes_and_prices'): </p>
                        <p class="check-js">
                            @lang('pages.sizeprices.item.see_portraits')
                        </p>
                        <div class="sz-list">
                            @if ($collage->quiz_sizes_prices)
                                @foreach (collect(explode(',', $collage->quiz_sizes_prices)) as $size_item)
                                    @php
                                        $size_clear_vals = explode('x', substr($size_item, 0, strpos($size_item, '[')));
                                        $prices_vals = explode('-', get_string_between($size_item, '[', ']'))[0];

                                    @endphp
                                    <div class="sz-item">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                  d="M0.666667 0H0V0.666667V7.33333H1.33333V2.27614L6.86193 7.80473L7.80473 6.86193L2.27614 1.33333H7.33333V0H0.666667ZM19.3333 0H20V0.666667V7.33333H18.6667V2.27614L13.1381 7.80473L12.1953 6.86193L17.7239 1.33333H12.6667V0H19.3333ZM20 20H19.3333H12.6667V18.6667H17.7239L12.1953 13.1381L13.1381 12.1953L18.6667 17.7239V12.6667H20V19.3333V20ZM0.666667 20H0V19.3333V12.6667H1.33333V17.7239L6.86193 12.1953L7.80473 13.1381L2.27614 18.6667H7.33333V20H0.666667Z"
                                                  fill="#FA7846"></path>
                                        </svg>
                                        <p>{{ $size_clear_vals[0] }}x{{ $size_clear_vals[1] }} {{ trans('gl.cm') }}
                                        </p>
                                        <p class="sz-price">
                                            от {{ intval($prices_vals * $contry_mult) }}€
                                        </p>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <a href="{{ route('collage') }}" class="btn btn-mob">
                            @lang('pages.sizeprices.item.go_to_page')
                        </a>
                    </div>
                </div>

                @foreach ($galleries as $item)
                    @php
                        $custom_sizes = explode(',', $item['custom_size_prices']);

                        switch ($item->slug) {
                            case 'simpsons-portrait':
                                $item_url = route('simpsons');
                                break;

                            case 'portrait-caricature':
                                $item_url = route('caricature');
                                break;

                            default:
                                $item_url = route('graphic_portrait.new_page', $item->slug);
                                break;
                        }
                    @endphp
                    <div class="sizes-item">

                        <a href="{{ $item_url }}" class="title-top title-top-mobile">
                            <p>
                                @lang('pages.sizeprices.item.style'):
                                <span class="mobTitle">
                                    {!! $item->getTranslatedAttribute('name', app()->getLocale()) !!}
                                </span>
                            </p>
                        </a>
                        <div class="img">
                            <picture>
                                <source media="(max-width: 576px)"
                                        srcset="{{ asset('storage/' .
                                            (optional($item->sliderImage)->size_img
                                                ? $item->sliderImage->size_img
                                                : ($item->sliderImage
                                                   ? $item->sliderImage->png
                                                   : (json_decode($item->images)[0] ?? '')
                                                )
                                            )) }}"
                                        type="image/webp">
                                <source
                                    srcset="{{ asset('storage/' .
                                        (optional($item->sliderImage)->size_img
                                            ? $item->sliderImage->size_img
                                            : ($item->sliderImage
                                               ? $item->sliderImage->png
                                               : (json_decode($item->images)[0] ?? '')
                                            )
                                        )) }}"
                                    type="image/webp">
                                <img width="430" height="450"
                                     srcset="{{ asset('storage/' .
                                        (optional($item->sliderImage)->size_img
                                            ? $item->sliderImage->size_img
                                            : ($item->sliderImage
                                               ? $item->sliderImage->png
                                               : (json_decode($item->images)[0] ?? '')
                                            )
                                        )) }}"
                                     alt="">
                            </picture>
                        </div>
                        <div class="content text-content">
                            <div class="title-top">
                                <p>
                                    @lang('pages.sizeprices.item.style'):
                                    <span class="mobTitle">
                                        {!! optional($item->sliderImage)->getTranslatedAttribute('size_title', app()->getLocale()) ?  $item->sliderImage->getTranslatedAttribute('size_title', app()->getLocale()): $item->getTranslatedAttribute('name', app()->getLocale()) !!}
                                    </span>
                                </p>
                                <div class="title">
                                    {!! optional($item->sliderImage)->getTranslatedAttribute('size_title', app()->getLocale()) ?  $item->sliderImage->getTranslatedAttribute('size_title', app()->getLocale()): $item->getTranslatedAttribute('name', app()->getLocale()) !!}
                                </div>
                            </div>
                            <div class="text">
                                {!! optional($item->sliderImage)->getTranslatedAttribute('size_text', app()->getLocale()) ?  $item->sliderImage->getTranslatedAttribute('size_text', app()->getLocale()) : $item->getTranslatedAttribute('description', app()->getLocale()) !!}
                            </div>
                            <a href="{{ $item_url }}" class="btn btn-desk">
                                @lang('pages.sizeprices.item.go_to_page')
                            </a>
                        </div>
                        <div class="content">
                            <p>@lang('pages.sizeprices.item.available_sizes_and_prices'): </p>
                            <p class="check-js">
                                @lang('pages.sizeprices.item.see_portraits')
                            </p>
                            <div class="sz-list">
                                @if (is_array($custom_sizes) && !empty($custom_sizes))
                                    @foreach (collect($custom_sizes) as $size_item)
                                        @php
                                            $size_clear_vals = explode('x', substr($size_item, 0, strpos($size_item, '[')));
                                            $prices_vals = explode('-', get_string_between($size_item, '[', ']'));

                                            $sale_price = null;
                                            if (isset($prices_vals[1])) {
                                                $sale_price = $prices_vals[1];
                                            }
                                        @endphp
                                        <div class="sz-item">
                                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                    d="M0.666667 0H0V0.666667V7.33333H1.33333V2.27614L6.86193 7.80473L7.80473 6.86193L2.27614 1.33333H7.33333V0H0.666667ZM19.3333 0H20V0.666667V7.33333H18.6667V2.27614L13.1381 7.80473L12.1953 6.86193L17.7239 1.33333H12.6667V0H19.3333ZM20 20H19.3333H12.6667V18.6667H17.7239L12.1953 13.1381L13.1381 12.1953L18.6667 17.7239V12.6667H20V19.3333V20ZM0.666667 20H0V19.3333V12.6667H1.33333V17.7239L6.86193 12.1953L7.80473 13.1381L2.27614 18.6667H7.33333V20H0.666667Z"
                                                    fill="#FA7846"></path>
                                            </svg>
                                            <p>{{ $size_clear_vals[0] }}x{{ $size_clear_vals[1] }} {{ trans('gl.cm') }}
                                            </p>
                                            <p class="sz-price">
                                                от
                                                @if ($sale_price != null)
                                                    <span class="dec_lt">
                                                        @endif{{ $prices_vals[0] * $contry_mult }}€ @if ($sale_price != null)
                                                    </span>
                                                @endif
                                                @if ($sale_price != null)
                                                    <b>{{ $sale_price * $contry_mult }}€</b>
                                                @endif
                                            </p>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <a href="{{ $item_url }}" class="btn btn-mob">
                                @lang('pages.sizeprices.item.go_to_page')
                            </a>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </div>
</div>

{{-- -------------------------- --}}
@section('modals')
    @include(config('theme.resource') . 'pages.sizesprices.sizes')
@endsection

{{-- -------------------------- --}}
<script src="{{ ver_asset(config('theme.current') . 'js/custom.js') }}"></script>

@include(config('theme.resource') . 'pages.index.faq9')

@include(config('theme.resource') . 'pages.gallery.zpart_viarcanvas_is')
