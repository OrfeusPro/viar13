@include(config('theme.resource') . 'pages.modular-generator.breads')
<div>
    @php
        $name=__('pages.modular-generator.slider.page-title');
        $strippedName = strip_tags($name);
        $cleanName = str_replace('"', '', $strippedName);
//        $s_items = $item->getMedia('our_works_new');
    @endphp
    <meta itemprop="name" content="{{ $cleanName }}" />
{{--    @if ($s_items)--}}
{{--        @foreach ($s_items as $image)--}}
{{--            <link itemprop="image" href="{{ $image->getUrl() }}">--}}
{{--        @endforeach--}}
{{--    @endif--}}

    <link itemprop="image" href="{{ asset(env('THEME') . 'images') }}/module-generator/main.png">
    <div itemprop="offers" itemtype="https://schema.org/Offer" itemscope>
        <link itemprop="url" href="{{ url(Request::url()) }}" />
        <meta itemprop="availability" content="https://schema.org/InStock" />
        <meta itemprop="priceCurrency" content="EUR" />
        <meta itemprop="price" content="75"/>
        <meta itemprop="description"  content="{{$cleanName }}" >
    </div>

<div class="mg-main">
    <div class="section-frame">
        <div class="mg-main__inner">
            <div class="mg-bg">
                <picture>
                    <source media="(max-width:768px)" srcset="{{ asset(env('THEME') . 'images') }}/module-generator/mainMin.webp" type="image/webp">
                    <source media="(max-width:768px)" srcset="{{ asset(env('THEME') . 'images') }}/module-generator/mainMin.png" type="image/png">
                    <!-- <source srcset="{{ asset(env('THEME') . 'images') }}/module-generator/main.avif" type="image/avif"> -->
                    <source srcset="{{ asset(env('THEME') . 'images') }}/module-generator/main.webp" type="image/webp">
                    <source srcset="{{ asset(env('THEME') . 'images') }}/module-generator/main.png" type="image/png">
                    <img width="1350" height="745"
                        src="{{ asset(env('THEME') . 'images') }}/module-generator/main.png" alt="Viar">
                </picture>
            </div>
            <div class="ellipse">
                <img alt="img" src="{{ asset(env('THEME') . 'images') }}/module-generator/el.svg" decoding="async"
                    height="99" width="1374">
            </div>
            <div class="mg-content">
                <h1 class="mg-title page-title">
                    @lang('pages.modular-generator.slider.page-title')
                </h1>
                <ul>
                    <li>
                        <svg width="25" height="25" viewBox="0 0 25 25" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_1314_808)">
                                <path
                                    d="M24.709 18.3996C24.637 16.8084 23.9591 15.3322 22.8004 14.2429C22.7573 14.2024 22.615 14.0748 22.5891 14.0518C21.8838 13.4241 20.977 13.1187 20.0353 13.1915C19.9936 13.1948 19.9524 13.1996 19.911 13.2043L3.54216 0.220261C3.1196 -0.115005 2.50763 -0.0618676 2.149 0.341017C1.7906 0.743728 1.80871 1.3576 2.19031 1.73851L16.8728 16.3925C16.8086 17.3798 17.1657 18.3859 17.9274 19.1112C18.3754 19.5378 18.9283 19.8361 19.5262 19.9738C20.7972 20.2667 21.7624 21.1951 22.045 22.3966C22.0701 22.5033 22.0916 22.6115 22.1102 22.7201C19.7985 23.7395 16.897 24.2991 13.905 24.2991C12.9394 24.2991 11.9903 24.2423 11.0702 24.1321C9.07471 23.8432 7.24803 23.2038 5.77934 22.262C3.75869 20.9662 2.64944 19.2399 2.64944 17.4009C2.64944 15.005 4.1415 13.1344 7.33901 11.5326C7.511 11.4464 7.5901 11.2441 7.52064 11.0647C7.44938 10.8806 7.24658 10.7838 7.05873 10.8442C5.11366 11.4704 3.47212 12.358 2.29554 13.4221C0.979751 14.6121 0.28418 15.9907 0.28418 17.409C0.28418 19.4744 1.72909 21.3995 4.35277 22.8297C6.20917 23.8416 8.50595 24.5231 11.0036 24.8209C11.8278 24.9392 12.6772 24.9999 13.5385 24.9999V24.9885C13.6604 24.9903 13.7826 24.9913 13.9051 24.9913C16.9383 24.9913 19.8731 24.3935 22.2662 23.4074C22.5534 23.289 23.0348 22.8783 23.1754 22.7203C24.2366 21.5282 24.7812 19.9937 24.709 18.3996ZM0.97652 17.409C0.97652 15.8403 1.99513 14.3416 3.77975 13.1412C2.54727 14.3763 1.95549 15.775 1.95549 17.4008C1.95549 19.0789 2.75041 20.6596 4.22313 21.9572C2.12482 20.6914 0.97652 19.0899 0.97652 17.409ZM2.66618 0.80125C2.78099 0.672302 2.97681 0.655455 3.11199 0.762652L17.5644 12.2265C17.0611 12.4405 16.6175 12.7719 16.26 13.2063C16.0095 13.5108 15.815 13.8567 15.683 14.2268L2.67939 1.24844C2.55731 1.12659 2.55148 0.930141 2.66618 0.80125ZM16.2413 14.784C16.3342 14.3663 16.5235 13.9757 16.7946 13.6462C17.1652 13.196 17.6511 12.8839 18.2069 12.7362L19.0591 13.4122C18.5133 13.6225 18.0276 13.9709 17.6435 14.4376C17.3608 14.781 17.1561 15.1658 17.0277 15.5689L16.2413 14.784ZM22.7101 22.201C22.3558 20.7588 21.1988 19.6487 19.6817 19.2991C19.2046 19.1891 18.763 18.9508 18.4049 18.6097C17.3678 17.6222 17.2682 15.9829 18.1781 14.8775C18.6586 14.2937 19.3372 13.9401 20.0889 13.8818C20.162 13.8762 20.2348 13.8733 20.3072 13.8733C20.9802 13.8733 21.6201 14.1162 22.1286 14.5688C22.157 14.5942 22.2888 14.7123 22.326 14.7473C23.3528 15.7126 23.9535 17.0208 24.0174 18.4309C24.0802 19.8175 23.6165 21.1531 22.7101 22.201Z"
                                    fill="url(#paint0_linear_1314_808)" />
                            </g>
                            <defs>
                                <linearGradient id="paint0_linear_1314_808" x1="12.4998" y1="0" x2="12.4998"
                                    y2="24.9999" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#FA7846" />
                                    <stop offset="1" stop-color="#91665E" />
                                </linearGradient>
                                <clipPath id="clip0_1314_808">
                                    <rect width="25" height="25" fill="white" />
                                </clipPath>
                            </defs>
                        </svg>
                        <p>
                            @lang('pages.modular-generator.slider.text1')
                        </p>
                    </li>
                    <li>
                        <svg width="25" height="25" viewBox="0 0 25 25" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_1314_808)">
                                <path
                                    d="M24.709 18.3996C24.637 16.8084 23.9591 15.3322 22.8004 14.2429C22.7573 14.2024 22.615 14.0748 22.5891 14.0518C21.8838 13.4241 20.977 13.1187 20.0353 13.1915C19.9936 13.1948 19.9524 13.1996 19.911 13.2043L3.54216 0.220261C3.1196 -0.115005 2.50763 -0.0618676 2.149 0.341017C1.7906 0.743728 1.80871 1.3576 2.19031 1.73851L16.8728 16.3925C16.8086 17.3798 17.1657 18.3859 17.9274 19.1112C18.3754 19.5378 18.9283 19.8361 19.5262 19.9738C20.7972 20.2667 21.7624 21.1951 22.045 22.3966C22.0701 22.5033 22.0916 22.6115 22.1102 22.7201C19.7985 23.7395 16.897 24.2991 13.905 24.2991C12.9394 24.2991 11.9903 24.2423 11.0702 24.1321C9.07471 23.8432 7.24803 23.2038 5.77934 22.262C3.75869 20.9662 2.64944 19.2399 2.64944 17.4009C2.64944 15.005 4.1415 13.1344 7.33901 11.5326C7.511 11.4464 7.5901 11.2441 7.52064 11.0647C7.44938 10.8806 7.24658 10.7838 7.05873 10.8442C5.11366 11.4704 3.47212 12.358 2.29554 13.4221C0.979751 14.6121 0.28418 15.9907 0.28418 17.409C0.28418 19.4744 1.72909 21.3995 4.35277 22.8297C6.20917 23.8416 8.50595 24.5231 11.0036 24.8209C11.8278 24.9392 12.6772 24.9999 13.5385 24.9999V24.9885C13.6604 24.9903 13.7826 24.9913 13.9051 24.9913C16.9383 24.9913 19.8731 24.3935 22.2662 23.4074C22.5534 23.289 23.0348 22.8783 23.1754 22.7203C24.2366 21.5282 24.7812 19.9937 24.709 18.3996ZM0.97652 17.409C0.97652 15.8403 1.99513 14.3416 3.77975 13.1412C2.54727 14.3763 1.95549 15.775 1.95549 17.4008C1.95549 19.0789 2.75041 20.6596 4.22313 21.9572C2.12482 20.6914 0.97652 19.0899 0.97652 17.409ZM2.66618 0.80125C2.78099 0.672302 2.97681 0.655455 3.11199 0.762652L17.5644 12.2265C17.0611 12.4405 16.6175 12.7719 16.26 13.2063C16.0095 13.5108 15.815 13.8567 15.683 14.2268L2.67939 1.24844C2.55731 1.12659 2.55148 0.930141 2.66618 0.80125ZM16.2413 14.784C16.3342 14.3663 16.5235 13.9757 16.7946 13.6462C17.1652 13.196 17.6511 12.8839 18.2069 12.7362L19.0591 13.4122C18.5133 13.6225 18.0276 13.9709 17.6435 14.4376C17.3608 14.781 17.1561 15.1658 17.0277 15.5689L16.2413 14.784ZM22.7101 22.201C22.3558 20.7588 21.1988 19.6487 19.6817 19.2991C19.2046 19.1891 18.763 18.9508 18.4049 18.6097C17.3678 17.6222 17.2682 15.9829 18.1781 14.8775C18.6586 14.2937 19.3372 13.9401 20.0889 13.8818C20.162 13.8762 20.2348 13.8733 20.3072 13.8733C20.9802 13.8733 21.6201 14.1162 22.1286 14.5688C22.157 14.5942 22.2888 14.7123 22.326 14.7473C23.3528 15.7126 23.9535 17.0208 24.0174 18.4309C24.0802 19.8175 23.6165 21.1531 22.7101 22.201Z"
                                    fill="url(#paint0_linear_1314_808)" />
                            </g>
                            <defs>
                                <linearGradient id="paint0_linear_1314_808" x1="12.4998" y1="0" x2="12.4998"
                                    y2="24.9999" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#FA7846" />
                                    <stop offset="1" stop-color="#91665E" />
                                </linearGradient>
                                <clipPath id="clip0_1314_808">
                                    <rect width="25" height="25" fill="white" />
                                </clipPath>
                            </defs>
                        </svg>
                        <p>
                            @lang('pages.modular-generator.slider.text2')
                        </p>
                    </li>
                    <li>
                        <svg width="25" height="25" viewBox="0 0 25 25" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_1314_808)">
                                <path
                                    d="M24.709 18.3996C24.637 16.8084 23.9591 15.3322 22.8004 14.2429C22.7573 14.2024 22.615 14.0748 22.5891 14.0518C21.8838 13.4241 20.977 13.1187 20.0353 13.1915C19.9936 13.1948 19.9524 13.1996 19.911 13.2043L3.54216 0.220261C3.1196 -0.115005 2.50763 -0.0618676 2.149 0.341017C1.7906 0.743728 1.80871 1.3576 2.19031 1.73851L16.8728 16.3925C16.8086 17.3798 17.1657 18.3859 17.9274 19.1112C18.3754 19.5378 18.9283 19.8361 19.5262 19.9738C20.7972 20.2667 21.7624 21.1951 22.045 22.3966C22.0701 22.5033 22.0916 22.6115 22.1102 22.7201C19.7985 23.7395 16.897 24.2991 13.905 24.2991C12.9394 24.2991 11.9903 24.2423 11.0702 24.1321C9.07471 23.8432 7.24803 23.2038 5.77934 22.262C3.75869 20.9662 2.64944 19.2399 2.64944 17.4009C2.64944 15.005 4.1415 13.1344 7.33901 11.5326C7.511 11.4464 7.5901 11.2441 7.52064 11.0647C7.44938 10.8806 7.24658 10.7838 7.05873 10.8442C5.11366 11.4704 3.47212 12.358 2.29554 13.4221C0.979751 14.6121 0.28418 15.9907 0.28418 17.409C0.28418 19.4744 1.72909 21.3995 4.35277 22.8297C6.20917 23.8416 8.50595 24.5231 11.0036 24.8209C11.8278 24.9392 12.6772 24.9999 13.5385 24.9999V24.9885C13.6604 24.9903 13.7826 24.9913 13.9051 24.9913C16.9383 24.9913 19.8731 24.3935 22.2662 23.4074C22.5534 23.289 23.0348 22.8783 23.1754 22.7203C24.2366 21.5282 24.7812 19.9937 24.709 18.3996ZM0.97652 17.409C0.97652 15.8403 1.99513 14.3416 3.77975 13.1412C2.54727 14.3763 1.95549 15.775 1.95549 17.4008C1.95549 19.0789 2.75041 20.6596 4.22313 21.9572C2.12482 20.6914 0.97652 19.0899 0.97652 17.409ZM2.66618 0.80125C2.78099 0.672302 2.97681 0.655455 3.11199 0.762652L17.5644 12.2265C17.0611 12.4405 16.6175 12.7719 16.26 13.2063C16.0095 13.5108 15.815 13.8567 15.683 14.2268L2.67939 1.24844C2.55731 1.12659 2.55148 0.930141 2.66618 0.80125ZM16.2413 14.784C16.3342 14.3663 16.5235 13.9757 16.7946 13.6462C17.1652 13.196 17.6511 12.8839 18.2069 12.7362L19.0591 13.4122C18.5133 13.6225 18.0276 13.9709 17.6435 14.4376C17.3608 14.781 17.1561 15.1658 17.0277 15.5689L16.2413 14.784ZM22.7101 22.201C22.3558 20.7588 21.1988 19.6487 19.6817 19.2991C19.2046 19.1891 18.763 18.9508 18.4049 18.6097C17.3678 17.6222 17.2682 15.9829 18.1781 14.8775C18.6586 14.2937 19.3372 13.9401 20.0889 13.8818C20.162 13.8762 20.2348 13.8733 20.3072 13.8733C20.9802 13.8733 21.6201 14.1162 22.1286 14.5688C22.157 14.5942 22.2888 14.7123 22.326 14.7473C23.3528 15.7126 23.9535 17.0208 24.0174 18.4309C24.0802 19.8175 23.6165 21.1531 22.7101 22.201Z"
                                    fill="url(#paint0_linear_1314_808)" />
                            </g>
                            <defs>
                                <linearGradient id="paint0_linear_1314_808" x1="12.4998" y1="0" x2="12.4998"
                                    y2="24.9999" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#FA7846" />
                                    <stop offset="1" stop-color="#91665E" />
                                </linearGradient>
                                <clipPath id="clip0_1314_808">
                                    <rect width="25" height="25" fill="white" />
                                </clipPath>
                            </defs>
                        </svg>
                        <p>
                            @lang('pages.modular-generator.slider.text3')
                        </p>
                    </li>
                </ul>
                <a href="#generate" class="mg-btn btn">
                    @lang('pages.modular-generator.slider.btn')
                </a>
            </div>
        </div>
    </div>
</div>


<div class="ellipse">
    <img alt="img" src="https://viarcanvas.com/theme/viar/images/icon/ellipse-whete.svg" decoding="async"
        height="99" width="1374">
</div>

@include(config('theme.resource') . 'pages.modular-generator.mg-types')

<div class="mg-design">
    <div class="section-frame">
        <div class="mg-design__inner">
            <div class="mg-design__title">
                <h2>@lang('pages.modular-generator.mg-design.mg-design__title_t1')</h2>
{{--                <p>@lang('pages.modular-generator.mg-design.mg-design__title_t2')</p>--}}
{{--                <p>@lang('pages.modular-generator.mg-design.mg-design__title_t3')</p>--}}

            </div>
            <div class="mg-design__grid">
                <div class="mg-design__item">
                    <div class="icon">
                        <img width="71" height="71"
                            src="{{ asset(env('THEME') . 'images') }}/module-generator/i1.svg" alt="">
                    </div>
                    <div class="mg-design__content">
                        <div class="title">
                            <span>1.</span>
                            <p>
                                @lang('pages.modular-generator.mg-design.p1')
                            </p>
                        </div>
                        <picture>
                            <source srcset="{{ asset(env('THEME') . 'images') }}/module-generator/d1.webp"
                                type="image/webp">
                            <source srcset="{{ asset(env('THEME') . 'images') }}/module-generator/d1.jpg"
                                type="image/jpeg">
                            <img src="{{ asset(env('THEME') . 'images') }}/module-generator/d1.jpg" alt="">
                        </picture>
                    </div>
                </div>
                <div class="mg-design__item">
                    <div class="icon">
                        <img width="71" height="71"
                            src="{{ asset(env('THEME') . 'images') }}/module-generator/i2.svg" alt="">
                    </div>
                    <div class="mg-design__content">
                        <div class="title">
                            <span>2.</span>
                            <p>
                                @lang('pages.modular-generator.mg-design.p2')
                            </p>
                        </div>
                        <picture>
                            <source srcset="{{ asset(env('THEME') . 'images') }}/module-generator/d2.webp"
                                type="image/webp">
                            <source srcset="{{ asset(env('THEME') . 'images') }}/module-generator/d2.jpg"
                                type="image/jpeg">
                            <img src="{{ asset(env('THEME') . 'images') }}/module-generator/d2.jpg" alt="">
                        </picture>
                    </div>
                </div>
                <div class="mg-design__item">
                    <div class="icon">
                        <img width="71" height="71"
                            src="{{ asset(env('THEME') . 'images') }}/module-generator/i3.svg" alt="">
                    </div>
                    <div class="mg-design__content">
                        <div class="title">
                            <span>3.</span>
                            <p>
                                @lang('pages.modular-generator.mg-design.p3')
                            </p>
                        </div>
                        <picture>
                            <source srcset="{{ asset(env('THEME') . 'images') }}/module-generator/d3.webp"
                                type="image/webp">
                            <source srcset="{{ asset(env('THEME') . 'images') }}/module-generator/d3.jpg"
                                type="image/jpeg">
                            <img src="{{ asset(env('THEME') . 'images') }}/module-generator/d3.jpg" alt="">
                        </picture>
                    </div>
                </div>
                <div class="mg-design__item">
                    <div class="icon">
                        <img width="71" height="71"
                            src="{{ asset(env('THEME') . 'images') }}/module-generator/i4.svg" alt="">
                    </div>
                    <div class="mg-design__content">
                        <div class="title">
                            <span>4.</span>
                            <p>
                                @lang('pages.modular-generator.mg-design.p4')
                            </p>
                        </div>
                        <picture>
                            <source srcset="{{ asset(env('THEME') . 'images') }}/module-generator/d4.webp"
                                type="image/webp">
                            <source srcset="{{ asset(env('THEME') . 'images') }}/module-generator/d4.jpg"
                                type="image/jpeg">
                            <img src="{{ asset(env('THEME') . 'images') }}/module-generator/d4.jpg" alt="">
                        </picture>
                    </div>
                </div>
            </div>
            <a href="#generate" class="mm-btn">
                @lang('pages.modular-generator.mg-design.btn')
            </a>
        </div>
    </div>
</div>

<div class="mg-program">
    <div class="section-frame">
        <div class="mg-program__inner">
            <div class="mg-program-content">
                <div class="title">
                    @lang('pages.modular-generator.mg-program.title')
                </div>
                <p>
                    @lang('pages.modular-generator.mg-program.text1')
                </p>
                <div class="subtext">
                    <p>@lang('pages.modular-generator.mg-program.text2')</p>
                    <svg width="84" height="49" viewBox="0 0 84 49" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M83.3153 4.61018C83.5296 4.43604 83.5622 4.12114 83.3881 3.90682L80.5503 0.414361C80.3762 0.200046 80.0613 0.167476 79.847 0.341613C79.6327 0.51575 79.6001 0.830653 79.7742 1.04497L82.2966 4.14938L79.1922 6.67181C78.9779 6.84594 78.9454 7.16085 79.1195 7.37516C79.2936 7.58947 79.6085 7.62204 79.8228 7.44791L83.3153 4.61018ZM1 41.2209C0.891277 41.7089 0.891704 41.709 0.892498 41.7092C0.893252 41.7093 0.894413 41.7096 0.895918 41.7099C0.898929 41.7106 0.9034 41.7116 0.909317 41.7129C0.921151 41.7155 0.93877 41.7194 0.962069 41.7246C1.00867 41.7349 1.07799 41.7501 1.16917 41.77C1.35155 41.8099 1.62142 41.8685 1.97201 41.9435C2.67318 42.0936 3.69724 42.3095 4.98998 42.573C7.57537 43.1 11.2359 43.8176 15.5377 44.5801C24.1386 46.1046 35.3146 47.8109 45.59 48.5313C50.7274 48.8915 55.6504 49.0061 59.9195 48.7257C64.1771 48.4461 67.8333 47.7712 70.406 46.5213C71.6968 45.8941 72.7414 45.1097 73.4453 44.1323C74.1554 43.1463 74.4996 41.9898 74.425 40.6658C74.278 38.0564 72.5087 34.8366 68.8678 30.8822L68.1322 31.5595C71.7413 35.4795 73.3001 38.4781 73.4266 40.722C73.4887 41.8245 73.206 42.7534 72.6338 43.5479C72.0555 44.3509 71.1625 45.0419 69.969 45.6218C67.573 46.7858 64.0729 47.4508 59.854 47.7278C55.6465 48.0042 50.7726 47.8922 45.66 47.5338C35.4354 46.817 24.2989 45.1174 15.7123 43.5954C11.4204 42.8347 7.76838 42.1188 5.18971 41.5931C3.90042 41.3303 2.87956 41.1151 2.18131 40.9657C1.83219 40.8909 1.56373 40.8327 1.3827 40.7931C1.29219 40.7733 1.22354 40.7582 1.17759 40.7481C1.15462 40.743 1.13732 40.7392 1.12581 40.7366C1.12005 40.7353 1.11574 40.7344 1.11289 40.7337C1.11146 40.7334 1.11042 40.7332 1.10971 40.733C1.10903 40.7329 1.10872 40.7328 1 41.2209ZM68.8678 30.8822C61.6297 23.0206 59.0312 17.5191 59.0312 13.7141C59.0312 10.0053 61.5196 7.74772 65.0558 6.37593C68.5976 5.002 73.0579 4.58121 76.666 4.51119C78.4636 4.4763 80.0369 4.52859 81.1604 4.58958C81.7219 4.62007 82.1706 4.65271 82.4781 4.67765C82.6319 4.69012 82.7503 4.70066 82.8299 4.70804C82.8697 4.71173 82.8998 4.71463 82.9197 4.71659C82.9297 4.71757 82.9371 4.71831 82.9419 4.7188C82.9443 4.71904 82.9461 4.71922 82.9472 4.71933C82.9478 4.71939 82.9481 4.71942 82.9484 4.71945C82.9486 4.71947 82.9486 4.71947 83 4.22212C83.0514 3.72478 83.0511 3.72474 83.0506 3.72469C83.0502 3.72465 83.0496 3.72459 83.0488 3.72451C83.0473 3.72435 83.0451 3.72413 83.0423 3.72384C83.0366 3.72327 83.0283 3.72244 83.0174 3.72138C82.9958 3.71925 82.9639 3.71618 82.9223 3.71232C82.839 3.70459 82.7167 3.69372 82.559 3.68092C82.2435 3.65534 81.7859 3.62207 81.2146 3.59106C80.0724 3.52904 78.4739 3.47591 76.6465 3.51138C73.0046 3.58206 68.4024 4.0051 64.6942 5.44362C60.9804 6.88427 58.0312 9.40779 58.0312 13.7141C58.0312 17.9243 60.8703 23.6721 68.1322 31.5595L68.8678 30.8822Z"
                            fill="#FC8C5F" />
                    </svg>
                    <svg class="mobile" width="87" height="46" viewBox="0 0 87 46" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M83.4138 45.4409C83.2587 45.6694 82.9478 45.729 82.7193 45.5739L78.9954 43.0474C78.7669 42.8924 78.7074 42.5815 78.8624 42.353C79.0174 42.1245 79.3284 42.0649 79.5569 42.2199L82.867 44.4657L85.1127 41.1556C85.2677 40.9271 85.5787 40.8675 85.8072 41.0226C86.0357 41.1776 86.0953 41.4885 85.9402 41.717L83.4138 45.4409ZM1 8.16143C0.891277 7.67339 0.891704 7.6733 0.892498 7.67312C0.893252 7.67295 0.894413 7.6727 0.895918 7.67236C0.898929 7.67169 0.9034 7.6707 0.909317 7.66939C0.921151 7.66676 0.93877 7.66286 0.962069 7.65772C1.00867 7.64743 1.07799 7.63218 1.16917 7.61226C1.35155 7.57239 1.62142 7.5138 1.97201 7.43876C2.67318 7.28868 3.69724 7.0728 4.98998 6.80929C7.57537 6.28229 11.2359 5.56473 15.5377 4.80221C24.1386 3.27767 35.3146 1.57135 45.59 0.850945C50.7274 0.490765 55.6504 0.376228 59.9195 0.656593C64.1771 0.936203 67.8333 1.61111 70.406 2.86102C71.6968 3.48815 72.7414 4.27261 73.4453 5.25002C74.1554 6.236 74.4996 7.39244 74.425 8.71653C74.278 11.3258 72.5087 14.5457 68.8678 18.5001L68.1322 17.8228C71.7413 13.9027 73.3001 10.9042 73.4266 8.66028C73.4887 7.55775 73.206 6.62886 72.6338 5.83442C72.0555 5.0314 71.1625 4.34034 69.969 3.76049C67.573 2.59644 64.0729 1.93152 59.854 1.65444C55.6465 1.37813 50.7726 1.49006 45.66 1.8485C35.4354 2.56533 24.2989 4.26486 15.7123 5.78687C11.4204 6.54762 7.76838 7.26351 5.18971 7.78914C3.90042 8.05194 2.87956 8.26716 2.18131 8.41661C1.83219 8.49134 1.56373 8.54962 1.3827 8.58919C1.29219 8.60897 1.22354 8.62408 1.17759 8.63422C1.15462 8.63929 1.13732 8.64312 1.12581 8.64567C1.12005 8.64695 1.11574 8.64791 1.11289 8.64854C1.11146 8.64886 1.11042 8.64909 1.10971 8.64925C1.10903 8.6494 1.10872 8.64947 1 8.16143ZM68.8678 18.5001C65.2509 22.4286 62.7314 25.537 61.0899 27.9999C59.4398 30.4757 58.7139 32.2459 58.6076 33.5079C58.5555 34.1271 58.6537 34.6101 58.8531 34.9977C59.053 35.3863 59.3725 35.714 59.818 35.9929C60.7284 36.5627 62.102 36.8912 63.8117 37.1038C65.5121 37.3152 67.4544 37.4037 69.4813 37.542C71.494 37.6793 73.5751 37.8652 75.4951 38.2754C77.4129 38.6851 79.2074 39.3258 80.6272 40.3966C82.0608 41.4777 83.092 42.9828 83.4911 45.0661L82.5089 45.2542C82.158 43.4225 81.2674 42.1319 80.0251 41.195C78.7691 40.2478 77.1339 39.6481 75.2862 39.2533C73.4406 38.8591 71.42 38.6766 69.4132 38.5396C67.4206 38.4037 65.4254 38.3121 63.6883 38.0961C61.9605 37.8813 60.3966 37.5348 59.2874 36.8405C58.7232 36.4874 58.2625 36.0357 57.9638 35.455C57.6646 34.8733 57.5461 34.1969 57.6111 33.424C57.7393 31.9019 58.5837 29.9571 60.2578 27.4453C61.9405 24.9206 64.4991 21.7688 68.1322 17.8228L68.8678 18.5001Z"
                            fill="#FC8C5F" />
                    </svg>
                </div>
            </div>
            <div class="mg-program-video">
                <div class="video-wrapper">
                    <video width="690" height="400" id="videoPlayer"
                        poster="{{ asset(env('THEME') . 'images') }}/module-generator/video.webp">
                        <source src="" type="video/mp4">
                    </video>
                    <div class="video-btn">
                        <svg width="112" height="112" viewBox="0 0 112 112" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <g filter="url(#filter0_d_1314_688)">
                                <circle cx="56" cy="50" r="36" fill="#FA7846" />
                            </g>
                            <g clip-path="url(#clip0_1314_688)">
                                <path
                                    d="M46.7239 33C47.0229 33 47.3219 33 47.6375 33C48.1191 33.1984 48.634 33.3637 49.0659 33.6117C55.1449 37.1829 61.2239 40.754 67.303 44.3252C68.748 45.1849 70.2262 45.995 71.6214 46.9043C72.7509 47.6318 73.1661 48.7395 72.9336 50.0621C72.7509 51.0872 72.1363 51.7981 71.256 52.3106C66.4725 55.1212 61.6724 57.9319 56.8889 60.759C54.2314 62.3297 51.5573 63.8838 48.8998 65.4544C47.9198 66.0331 46.9066 66.1819 45.8769 65.7024C44.6644 65.1568 44 64.1814 44 62.8587C44 53.9474 44 45.0195 44 36.0917C44 36.0752 44 36.0421 44 36.0256C44.0498 35.1493 44.3986 34.4053 45.063 33.7936C45.5281 33.3637 46.126 33.1653 46.7239 33Z"
                                    fill="white" />
                            </g>
                            <defs>
                                <filter id="filter0_d_1314_688" x="0" y="0" width="112"
                                    height="112" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                    <feFlood flood-opacity="0" result="BackgroundImageFix" />
                                    <feColorMatrix in="SourceAlpha" type="matrix"
                                        values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                    <feOffset dy="6" />
                                    <feGaussianBlur stdDeviation="10" />
                                    <feComposite in2="hardAlpha" operator="out" />
                                    <feColorMatrix type="matrix"
                                        values="0 0 0 0 0.117647 0 0 0 0 0.145098 0 0 0 0 0.2 0 0 0 0.2 0" />
                                    <feBlend mode="normal" in2="BackgroundImageFix"
                                        result="effect1_dropShadow_1314_688" />
                                    <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_1314_688"
                                        result="shape" />
                                </filter>
                                <clipPath id="clip0_1314_688">
                                    <rect width="29" height="33" fill="white"
                                        transform="translate(44 33)" />
                                </clipPath>
                            </defs>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="mg-why">
    <h2 class="mg-why__title">
        @lang('pages.modular-generator.mg-why.title')
    </h2>
    <div class="mg-why__content">
        <div class="section-frame">
            <div class="mg-why__inner">
                <ul>
                    <li>
                        <p>
                            @lang('pages.modular-generator.mg-why.p1')
                        </p>
                        <img width="85" height="85"
                            src="{{ asset(env('THEME') . 'images') }}/module-generator/w1.svg" alt="">
                    </li>
                    <li>
                        <p>
                            @lang('pages.modular-generator.mg-why.p2')
                        </p>
                        <img width="85" height="85"
                            src="{{ asset(env('THEME') . 'images') }}/module-generator/w2.svg" alt="">
                    </li>
                    <li>
                        <p>
                            @lang('pages.modular-generator.mg-why.p3')
                        </p>
                        <img width="85" height="85"
                            src="{{ asset(env('THEME') . 'images') }}/module-generator/w3.svg" alt="">
                    </li>
                    <li>
                        <p>
                            @lang('pages.modular-generator.mg-why.p4')
                        </p>
                        <img width="85" height="85"
                            src="{{ asset(env('THEME') . 'images') }}/module-generator/w4.svg" alt="">
                    </li>
                </ul>
                <picture>
                    <source srcset="{{ asset(env('THEME') . 'images') }}/module-generator/why1.webp"
                        type="image/webp">
                    <source srcset="{{ asset(env('THEME') . 'images') }}/module-generator/why1.png" type="image/png">
                    <img width="1321" height="864" class="img-bg"
                        src="{{ asset(env('THEME') . 'images') }}/module-generator/why1.png" alt="">
                </picture>
            </div>
        </div>
    </div>
</div>


<div class="ellipse">
    <img alt="img" src="https://viarcanvas.com/theme/viar/images/icon/ellipse-whete.svg" decoding="async"
        height="99" width="1374">
</div>

<div class="mg-inspire">
    <div class="section-frame">
        <div class="mg-inspire__inner">
            <div class="mg-inspire__row">
                <div class="mg-inspire__title">
                    @lang('pages.modular-generator.mg-inspire.title')
                </div>
                <p>
                    @lang('pages.modular-generator.mg-inspire.p1')
                </p>
            </div>
            <div class="mg-inspire__slider">
                <div class="mg-inspire-slider__inner swiper-wrapper">
                    <div class="swiper-slide">
                        <picture>
                            <source srcset="{{ asset(env('THEME') . 'images') }}/module-generator/ins1.webp"
                                type="image/webp">
                            <source srcset="{{ asset(env('THEME') . 'images') }}/module-generator/ins1.jpg"
                                type="image/jpeg">
                            <img width="460" height="340"
                                src="{{ asset(env('THEME') . 'images') }}/module-generator/ins1.jpg" alt="Viar">
                        </picture>
                    </div>
                    <div class="swiper-slide">
                        <picture>
                            <source srcset="{{ asset(env('THEME') . 'images') }}/module-generator/ins2.webp"
                                type="image/webp">
                            <source srcset="{{ asset(env('THEME') . 'images') }}/module-generator/ins2.jpg"
                                type="image/jpeg">
                            <img width="460" height="340"
                                src="{{ asset(env('THEME') . 'images') }}/module-generator/ins2.jpg" alt="Viar">
                        </picture>
                    </div>
                    <div class="swiper-slide">
                        <picture>
                            <source srcset="{{ asset(env('THEME') . 'images') }}/module-generator/ins3.webp"
                                type="image/webp">
                            <source srcset="{{ asset(env('THEME') . 'images') }}/module-generator/ins3.jpg"
                                type="image/jpeg">
                            <img width="460" height="340"
                                src="{{ asset(env('THEME') . 'images') }}/module-generator/ins3.jpg" alt="Viar">
                        </picture>
                    </div>
                    <div class="swiper-slide">
                        <picture>
                            <source srcset="{{ asset(env('THEME') . 'images') }}/module-generator/ins1.webp"
                                type="image/webp">
                            <source srcset="{{ asset(env('THEME') . 'images') }}/module-generator/ins1.jpg"
                                type="image/jpeg">
                            <img width="460" height="340"
                                src="{{ asset(env('THEME') . 'images') }}/module-generator/ins1.jpg" alt="Viar">
                        </picture>
                    </div>
                    <div class="swiper-slide">
                        <picture>
                            <source srcset="{{ asset(env('THEME') . 'images') }}/module-generator/ins1.webp"
                                type="image/webp">
                            <source srcset="{{ asset(env('THEME') . 'images') }}/module-generator/ins1.jpg"
                                type="image/jpeg">
                            <img width="460" height="340"
                                src="{{ asset(env('THEME') . 'images') }}/module-generator/ins1.jpg" alt="Viar">
                        </picture>
                    </div>
                    <div class="swiper-slide">
                        <picture>
                            <source srcset="{{ asset(env('THEME') . 'images') }}/module-generator/ins2.webp"
                                type="image/webp">
                            <source srcset="{{ asset(env('THEME') . 'images') }}/module-generator/ins2.jpg"
                                type="image/jpeg">
                            <img width="460" height="340"
                                src="{{ asset(env('THEME') . 'images') }}/module-generator/ins2.jpg" alt="Viar">
                        </picture>
                    </div>
                </div>
                <div class="swiper-button swiper-prev">
                    <svg width="12" height="19" viewBox="0 0 12 19" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M11 18L2 9.5L11 1" stroke="#1E2533" stroke-width="2" />
                    </svg>
                </div>
                <div class="swiper-button swiper-next">
                    <svg width="12" height="19" viewBox="0 0 12 19" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M0.999999 0.999999L10 9.5L1 18" stroke="white" stroke-width="2" />
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>



<div class="module-generator__screen">
    <div class="section-frame">

        <div class="collage__formalization" id="generate">
            <form action="#">
                <div class="formalization__main--wrapper">
                    <div class="formalization__block--top">
                        <div class="formalization__block--top-inner">
                            <div class="collage-gen--title">
                                <h2 class="vz-art page-title h2_old">
                                    @lang('pages.modular-generator.generate.title')
                                </h2>
                                <div class="col-gen--element">

                                    <picture>
                                        <source
                                            srcset="{{ asset(env('THEME') . 'images') }}/module-generator/pngwing.webp"
                                            type="image/webp">
                                        <source
                                            srcset="{{ asset(env('THEME') . 'images') }}/module-generator/pngwing.png"
                                            type="image/png">
                                        <img width="95" height="114"
                                            src="{{ asset(env('THEME') . 'images') }}/module-generator/pngwing.png"
                                            alt="Viar">
                                    </picture>
                                    <p>
                                        @lang('pages.modular-generator.generate.p1')
                                    </p>
                                </div>
                            </div>
                            <div class="collage-formalization-row">
                                <section class="generate" id="generator">
                                    <div class="generate-content">
                                        <div class="gallery-photo">
                                            <div class="collage-tabs slider-tabs">
                                                <ul class="tabs">
                                                    <li>
                                                        <a class="active" data-tabs="tabs-item1">
                                                            @lang('gl.pic_on_wall')
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a data-tabs="tabs-item2">
                                                            @lang('canvas.form_interiour')
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="photo-content__inner">
                                                <div class="collage-settings">
                                                    <div class="filter-picture tabs-item tabs-item1 active">
                                                        <div class="mg-flex">
                                                            <div class="cs-tabs-column--wrap">
                                                                <div class="cs-tabs-column">
                                                                    <div class="mg-tab opened">
                                                                        <div class="mg-tab__target">
                                                                            <span>1.</span>
                                                                            <img src="{{ asset(env('THEME') . 'images') }}/module-generator/mt1.svg"
                                                                                alt="">
                                                                            <p>@lang('pages.modular-generator.generate.step1.select_photo')</p>
                                                                        </div>
                                                                        <div class="mg-tab__content">
                                                                            <div class="file-box">
                                                                                <div
                                                                                    class="file-save file-save__popup">
                                                                                    <!-- <div class="abs-close">
                                                                X
                                                                </div> -->
                                                                                    <div
                                                                                        class="file-save__item js-file-preview">
                                                                                        <svg>
                                                                                            <use
                                                                                                xlink:href="sprite.svg#save">
                                                                                            </use>
                                                                                        </svg>
                                                                                        <div class="file-save__title">
                                                                                            <p>@lang('homepage_new.load_photo')</p>
                                                                                            <span>
                                                                                                @lang('homepage_new.pree_to_add_photo')</span>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="file-save__item js-file-upload"
                                                                                        style="display: none;">
                                                                                        <svg>
                                                                                            <use
                                                                                                xlink:href="sprite.svg#picture">
                                                                                            </use>
                                                                                        </svg>
                                                                                        <div class="file-save__title">
                                                                                            <p>photo_34567.jpg</p>
                                                                                            <span>2 Mb</span>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="file-save__item js-file-multiple"
                                                                                        style="display: none;">
                                                                                        <svg>
                                                                                            <use
                                                                                                xlink:href="sprite.svg#check">
                                                                                            </use>
                                                                                        </svg>
                                                                                        <div class="file-save__title">
                                                                                            <p
                                                                                                class="file-title_green">
                                                                                                @lang('stock.modal_facebook_downloaded')</p>
                                                                                        </div>
                                                                                    </div>
                                                                                    <input type="file"
                                                                                        class="file-input file-input_save imageFile imageModularInput"
                                                                                        name="file[]"
                                                                                        accept="image/*,image/heif,image/heic"
                                                                                        aria-label="file input"
                                                                                        readonly="">
                                                                                </div>
                                                                                <div class="images-container"></div>
                                                                            </div>
                                                                            <!-- <div class="product-download">
                                                            <div class="download">
                                                                <div class="img">
                                                                    <img src="" alt="" class="download-img">
                                                                    <img src="img/download-icon.png" alt="" class="download-icon">
                                                                </div>
                                                                <input type="file" class="imageFile" accept="image/*,image/heif,image/heic">
                                                            </div>
                                                            <div class="download">
                                                                <div class="img">
                                                                    <img src="" alt="" class="download-img">
                                                                    <img src="img/download-icon.png" alt="" class="download-icon">
                                                                </div>
                                                                <input type="file" class="imageFile" accept="image/*,image/heif,image/heic">
                                                            </div>
                                                            <div class="download">
                                                                <div class="img">
                                                                    <img src="" alt="" class="download-img">
                                                                    <img src="img/download-icon.png" alt="" class="download-icon">
                                                                </div>
                                                                <input type="file" class="imageFile" accept="image/*,image/heif,image/heic">
                                                            </div>
                                                        </div> -->
                                                                        </div>
                                                                    </div>
                                                                    <div class="mg-tab">
                                                                        <div class="mg-tab__target">
                                                                            <span>2.</span>
                                                                            <img src="{{ asset(env('THEME') . 'images') }}/module-generator/mt2.svg"
                                                                                alt="">
                                                                            <p>
                                                                                @lang('pages.modular-generator.generate.step2.select_form')
                                                                            </p>
                                                                        </div>
                                                                        <div class="mg-tab__content">
                                                                            <div class="modular-shapes">

                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mg-tab">
                                                                        <div class="mg-tab__target">
                                                                            <span>3.</span>
                                                                            <img src="{{ asset(env('THEME') . 'images') }}/module-generator/mt3.svg"
                                                                                alt="">
                                                                            <p>
                                                                                @lang('pages.modular-generator.generate.step3.select_size')
                                                                            </p>
                                                                        </div>
                                                                        <div class="mg-tab__content">
                                                                            <div class="inner_tab-block">
                                                                                <div class="modular-size">
                                                                                    <div class="size-item width">
                                                                                        <div class="input-row">
                                                                                            <p>
                                                                                                <span>
                                                                                                    @lang('pages.modular-generator.generate.step3.width')
                                                                                                </span>
                                                                                                <input type="number"
                                                                                                    name="whole-width"
                                                                                                    min="40"
                                                                                                    value="90"
                                                                                                    max="300"
                                                                                                    step=".1">
                                                                                                <b>{!! trans('collage_new.sm') !!}</b>
                                                                                            </p>
                                                                                        </div>
                                                                                        <div class="range_box">
                                                                                            <input type="range"
                                                                                                class="zoomRange range-input"
                                                                                                id="range-w"
                                                                                                min="40"
                                                                                                value="90"
                                                                                                max="300"
                                                                                                step=".1"
                                                                                                style="background-size: 0%;">
                                                                                            <div class="changeSm">
                                                                                                <div><span>40</span>
                                                                                                    <i>{!! trans('collage_new.sm') !!}</i>
                                                                                                </div>
                                                                                                <div><span>300</span>
                                                                                                    <i>{!! trans('collage_new.sm') !!}</i>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="size-item height">
                                                                                        <div class="input-row">
                                                                                            <p>
                                                                                                <span>
                                                                                                    @lang('pages.modular-generator.generate.step3.height')
                                                                                                </span>
                                                                                                <input type="number"
                                                                                                    name="whole-height"
                                                                                                    step=".1"
                                                                                                    min="40"
                                                                                                    value="90"
                                                                                                    max="300">
                                                                                                <b>{!! trans('collage_new.sm') !!}</b>
                                                                                            </p>
                                                                                        </div>
                                                                                        <div class="range_box">
                                                                                            <input type="range"
                                                                                                class="zoomRange range-input"
                                                                                                id="range-h"
                                                                                                min="40"
                                                                                                value="30"
                                                                                                max="300"
                                                                                                step=".1"
                                                                                                style="background-size: 0%;">
                                                                                            <div class="changeSm">
                                                                                                <div><span>40</span>
                                                                                                    <i>{!! trans('collage_new.sm') !!}</i>
                                                                                                </div>
                                                                                                <div><span>300</span>
                                                                                                    <i>{!! trans('collage_new.sm') !!}</i>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mg-tab">
                                                                        <div class="mg-tab__target">
                                                                            <span>4.</span>
                                                                            <img src="{{ asset(env('THEME') . 'images') }}/module-generator/mt4.svg"
                                                                                alt="">
                                                                            <p>@lang('gl.hud_of_text')</p>
                                                                        </div>
                                                                        <div class="mg-tab__content">
                                                                            <div class="inner_tab-block">
                                                                                <div
                                                                                    class="kviz-row-single kviz-c-group">

                                                                                    <div class="kviz-group__item">
                                                                                        @if (isset($art_items))
                                                                                            @foreach ($art_items as $item)
                                                                                                <div class="kviz-radio js-checkbox @if ($loop->last) kviz-radio_active @endif"
                                                                                                    data-stock="{{ $loop->iteration }}">
                                                                                                    <div
                                                                                                        class="check">
                                                                                                    </div>
                                                                                                    <label>
                                                                                                        <span>{{ $item->getTranslatedAttribute('name', app()->getLocale()) }}
                                                                                                            <img src="{{ asset('images/icon/info.svg') }}"
                                                                                                                alt=""></span>
                                                                                                        <input
                                                                                                            name="decoration"
                                                                                                            type="radio"
                                                                                                            data-name="{{ $item->getTranslatedAttribute('name', app()->getLocale()) }}"
                                                                                                            data-id="{{ $item->id }}"
                                                                                                            data-coef_sm="{{ $item->coef_sm }}"
                                                                                                            data-coef_md="{{ $item->coef_md }}"
                                                                                                            data-coef_lg="{{ $item->coef_lg }}"
                                                                                                            value="{{ $item->price }}"
                                                                                                            @if ($loop->last) checked @endif />
                                                                                                        @if ($item->image)
                                                                                                            <div
                                                                                                                class="pic-pop">
                                                                                                                <picture>
                                                                                                                    <source
                                                                                                                        srcset="{{ Voyager::image($item->image) }}"
                                                                                                                        type="image/jpeg">
                                                                                                                    <img loading="lazy"
                                                                                                                        src="{{ Voyager::image($item->image) }}"
                                                                                                                        @altAttrs($item, 'image', data_get($item, 'image'))>
                                                                                                                </picture>
                                                                                                                <div
                                                                                                                    class="hint">
                                                                                                                    {!! $item->getTranslatedAttribute('hint', app()->getLocale()) !!}
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        @endif
                                                                                                    </label>
                                                                                                </div>
                                                                                            @endforeach
                                                                                        @endif
                                                                                    </div>

                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mg-tab">
                                                                        <div class="mg-tab__target">
                                                                            <span>5.</span>
                                                                            <img src="{{ asset(env('THEME') . 'images') }}/module-generator/mt5.svg"
                                                                                alt="">
                                                                            <p>@lang('cart.comment')</p>
                                                                        </div>
                                                                        <div class="mg-tab__content">
                                                                            <div class="inner_tab-block">
                                                                                <div class="mg-tab__title">
                                                                                    @lang('pages.modular-generator.generate.step5.add_comment')
                                                                                </div>
                                                                                <div
                                                                                    class="textarea-wrapper cs-txt-wrapper">
                                                                                    <textarea placeholder="@lang('collage_new.z7_generator_stiker_text3')" id="userComment" name="user_comment"></textarea>
                                                                                </div>
                                                                                {{-- <div class="btn mgt-btn">
                                                                                    @lang('pages.modular-generator.generate.step5.add')
                                                                                </div> --}}
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                            <div class="cs-controls cs-ds-controls">
                                                              <div class="csc-item split_v" id="split-up" title="????????? ?? ?????????">
                                                              </div>
                                                              <div class="csc-item split_h" id="split-down" title="????????? ?? ???????????">
                                                              </div>
                                                              <div class="csc-item add_block" id="add_block" title="???????? ????">??
                                                              </div>
                                                              <div class="csc-item delete" id="img_delete" title="??????? ????">
                                                              </div>
                                                              <div class="csc-item" id="clear" title="????????">
                                                              </div>
                                                              <div class="csc-item-row">
                                                                <div class="csc-item" id="undo" title="?????">
                                                                </div>
                                                                <div class="csc-item" id="redo" title="??????">
                                                                </div>
                                                              </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <style>
                                                        .range__box {
                                                            display: none;
                                                        }
                                                    </style>
                                                    <div class="filter-interior tabs-item tabs-item2"
                                                        style="width: 100%;">
                                                        <div class="filter-accordion">
                                                            <div class="accordion-title h2_old open">
                                                                <span>1. @lang('collage_new.z7_generator_interer_text1')</span>
                                                                <span class="tab-icon"></span>
                                                            </div>
                                                            <div class="accordion-content">
                                                                <div class="interior-slider">
                                                                    <div>
                                                                        <div data-item="0"
                                                                            class="interior-item active"
                                                                            data-size="250,300"
                                                                            data-interior="{{ asset(env('THEME') . 'images') }}/canvas/interio1.jpg">
                                                                            <div class="selected-icon">
                                                                                <svg width="12" height="9"
                                                                                    viewBox="0 0 12 9" fill="none"
                                                                                    xmlns="http://www.w3.org/2000/svg">
                                                                                    <path
                                                                                        d="M4.90391 8.96884C4.643 8.96884 4.3911 8.87105 4.19842 8.69487L0.337368 5.16245C-0.086973 4.77427 -0.114713 4.11735 0.275141 3.69409C0.665745 3.27307 1.32475 3.2447 1.74984 3.63213L4.83643 6.45688L10.1827 0.582689C10.5695 0.157186 11.23 0.125087 11.6574 0.510279C12.084 0.895472 12.117 1.55239 11.7293 1.97789L5.67762 8.62844C5.49019 8.83298 5.23003 8.9554 4.95189 8.96884C4.93539 8.96884 4.91965 8.96884 4.90391 8.96884Z"
                                                                                        fill="white"></path>
                                                                                </svg>
                                                                            </div>
                                                                            <div class="interio-img">
                                                                                <picture>
                                                                                    <source media="(max-width: 550px)"
                                                                                        srcset="{{ asset(env('THEME') . 'images') }}/canvas/interio1Min.webp"
                                                                                        type="image/webp">
                                                                                    <source media="(max-width: 550px)"
                                                                                        srcset="{{ asset(env('THEME') . 'images') }}/canvas/interio1Min.jpg"
                                                                                        type="image/jpeg">
                                                                                    <source
                                                                                        srcset="{{ asset(env('THEME') . 'images') }}/canvas/interio1.webp"
                                                                                        type="image/webp">
                                                                                    <source
                                                                                        srcset="{{ asset(env('THEME') . 'images') }}/canvas/interio1.jpg"
                                                                                        type="image/jpeg">
                                                                                    <img src="{{ asset(env('THEME') . 'images') }}/canvas/interio1.jpg"
                                                                                        alt="" />
                                                                                </picture>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div>
                                                                        <div data-item="1" class="interior-item"
                                                                            data-size="300,400"
                                                                            data-interior="{{ asset(env('THEME') . 'images') }}/canvas/interio2.jpg">

                                                                            <div class="selected-icon">
                                                                                <svg width="12" height="9"
                                                                                    viewBox="0 0 12 9" fill="none"
                                                                                    xmlns="http://www.w3.org/2000/svg">
                                                                                    <path
                                                                                        d="M4.90391 8.96884C4.643 8.96884 4.3911 8.87105 4.19842 8.69487L0.337368 5.16245C-0.086973 4.77427 -0.114713 4.11735 0.275141 3.69409C0.665745 3.27307 1.32475 3.2447 1.74984 3.63213L4.83643 6.45688L10.1827 0.582689C10.5695 0.157186 11.23 0.125087 11.6574 0.510279C12.084 0.895472 12.117 1.55239 11.7293 1.97789L5.67762 8.62844C5.49019 8.83298 5.23003 8.9554 4.95189 8.96884C4.93539 8.96884 4.91965 8.96884 4.90391 8.96884Z"
                                                                                        fill="white"></path>
                                                                                </svg>
                                                                            </div>
                                                                            <div class="interio-img">
                                                                                <picture>
                                                                                    <source media="(max-width: 550px)"
                                                                                        srcset="{{ asset(env('THEME') . 'images') }}/canvas/interio2Min.webp"
                                                                                        type="image/webp">
                                                                                    <source media="(max-width: 550px)"
                                                                                        srcset="{{ asset(env('THEME') . 'images') }}/canvas/interio2Min.jpg"
                                                                                        type="image/jpeg">
                                                                                    <source
                                                                                        srcset="{{ asset(env('THEME') . 'images') }}/canvas/interio2.webp"
                                                                                        type="image/webp">
                                                                                    <source
                                                                                        srcset="{{ asset(env('THEME') . 'images') }}/canvas/interio2.jpg"
                                                                                        type="image/jpeg">
                                                                                    <img src="{{ asset(env('THEME') . 'images') }}/canvas/interio2.jpg"
                                                                                        alt="" />
                                                                                </picture>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div>
                                                                        <div data-item="2" class="interior-item"
                                                                            data-size="350,400"
                                                                            data-interior="{{ asset(env('THEME') . 'images') }}/canvas/interio3.jpg">

                                                                            <div class="selected-icon">
                                                                                <svg width="12" height="9"
                                                                                    viewBox="0 0 12 9" fill="none"
                                                                                    xmlns="http://www.w3.org/2000/svg">
                                                                                    <path
                                                                                        d="M4.90391 8.96884C4.643 8.96884 4.3911 8.87105 4.19842 8.69487L0.337368 5.16245C-0.086973 4.77427 -0.114713 4.11735 0.275141 3.69409C0.665745 3.27307 1.32475 3.2447 1.74984 3.63213L4.83643 6.45688L10.1827 0.582689C10.5695 0.157186 11.23 0.125087 11.6574 0.510279C12.084 0.895472 12.117 1.55239 11.7293 1.97789L5.67762 8.62844C5.49019 8.83298 5.23003 8.9554 4.95189 8.96884C4.93539 8.96884 4.91965 8.96884 4.90391 8.96884Z"
                                                                                        fill="white"></path>
                                                                                </svg>
                                                                            </div>
                                                                            <div class="interio-img">
                                                                                <picture>
                                                                                    <source media="(max-width: 550px)"
                                                                                        srcset="{{ asset(env('THEME') . 'images') }}/canvas/interio3Min.webp"
                                                                                        type="image/webp">
                                                                                    <source media="(max-width: 550px)"
                                                                                        srcset="{{ asset(env('THEME') . 'images') }}/canvas/interio3Min.jpg"
                                                                                        type="image/jpeg">
                                                                                    <source
                                                                                        srcset="{{ asset(env('THEME') . 'images') }}/canvas/interio3.webp"
                                                                                        type="image/webp">
                                                                                    <source
                                                                                        srcset="{{ asset(env('THEME') . 'images') }}/canvas/interio3.jpg"
                                                                                        type="image/jpeg">
                                                                                    <img src="{{ asset(env('THEME') . 'images') }}/canvas/interio3.jpg"
                                                                                        alt="" />
                                                                                </picture>
                                                                            </div>

                                                                        </div>
                                                                    </div>
                                                                    <div>
                                                                        <div data-item="3" class="interior-item"
                                                                            data-size="250,300"
                                                                            data-interior="{{ asset(env('THEME') . 'images') }}/canvas/interio4.jpg">

                                                                            <div class="selected-icon">
                                                                                <svg width="12" height="9"
                                                                                    viewBox="0 0 12 9" fill="none"
                                                                                    xmlns="http://www.w3.org/2000/svg">
                                                                                    <path
                                                                                        d="M4.90391 8.96884C4.643 8.96884 4.3911 8.87105 4.19842 8.69487L0.337368 5.16245C-0.086973 4.77427 -0.114713 4.11735 0.275141 3.69409C0.665745 3.27307 1.32475 3.2447 1.74984 3.63213L4.83643 6.45688L10.1827 0.582689C10.5695 0.157186 11.23 0.125087 11.6574 0.510279C12.084 0.895472 12.117 1.55239 11.7293 1.97789L5.67762 8.62844C5.49019 8.83298 5.23003 8.9554 4.95189 8.96884C4.93539 8.96884 4.91965 8.96884 4.90391 8.96884Z"
                                                                                        fill="white"></path>
                                                                                </svg>
                                                                            </div>
                                                                            <div class="interio-img">
                                                                                <picture>
                                                                                    <source media="(max-width: 550px)"
                                                                                        srcset="{{ asset(env('THEME') . 'images') }}/canvas/interio4Min.webp"
                                                                                        type="image/webp">
                                                                                    <source media="(max-width: 550px)"
                                                                                        srcset="{{ asset(env('THEME') . 'images') }}/canvas/interio4Min.jpg"
                                                                                        type="image/jpeg">
                                                                                    <source
                                                                                        srcset="{{ asset(env('THEME') . 'images') }}/canvas/interio4.webp"
                                                                                        type="image/webp">
                                                                                    <source
                                                                                        srcset="{{ asset(env('THEME') . 'images') }}/canvas/interio4.jpg"
                                                                                        type="image/jpeg">
                                                                                    <img src="{{ asset(env('THEME') . 'images') }}/canvas/interio4.jpg"
                                                                                        alt="" />
                                                                                </picture>
                                                                            </div>

                                                                        </div>
                                                                    </div>
                                                                    <div>
                                                                        <div data-item="4" class="interior-item"
                                                                            data-size="350,400"
                                                                            data-interior="{{ asset(env('THEME') . 'images') }}/canvas/interio5.jpg">

                                                                            <div class="selected-icon">
                                                                                <svg width="12" height="9"
                                                                                    viewBox="0 0 12 9" fill="none"
                                                                                    xmlns="http://www.w3.org/2000/svg">
                                                                                    <path
                                                                                        d="M4.90391 8.96884C4.643 8.96884 4.3911 8.87105 4.19842 8.69487L0.337368 5.16245C-0.086973 4.77427 -0.114713 4.11735 0.275141 3.69409C0.665745 3.27307 1.32475 3.2447 1.74984 3.63213L4.83643 6.45688L10.1827 0.582689C10.5695 0.157186 11.23 0.125087 11.6574 0.510279C12.084 0.895472 12.117 1.55239 11.7293 1.97789L5.67762 8.62844C5.49019 8.83298 5.23003 8.9554 4.95189 8.96884C4.93539 8.96884 4.91965 8.96884 4.90391 8.96884Z"
                                                                                        fill="white"></path>
                                                                                </svg>
                                                                            </div>
                                                                            <div class="interio-img">
                                                                                <picture>
                                                                                    <source media="(max-width: 550px)"
                                                                                        srcset="{{ asset(env('THEME') . 'images') }}/canvas/interio5Min.webp"
                                                                                        type="image/webp">
                                                                                    <source media="(max-width: 550px)"
                                                                                        srcset="{{ asset(env('THEME') . 'images') }}/canvas/interio5Min.jpg"
                                                                                        type="image/jpeg">
                                                                                    <source
                                                                                        srcset="{{ asset(env('THEME') . 'images') }}/canvas/interio5.webp"
                                                                                        type="image/webp">
                                                                                    <source
                                                                                        srcset="{{ asset(env('THEME') . 'images') }}/canvas/interio5.jpg"
                                                                                        type="image/jpeg">
                                                                                    <img src="{{ asset(env('THEME') . 'images') }}/canvas/interio5.jpg"
                                                                                        alt="" />
                                                                                </picture>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="interior-sizes">
                                                                    <div class="size-item">
                                                                        <span>@lang('collage_new.z7_generator_interer_text2')</span>
                                                                        <div class="range__box"
                                                                            style="display: block">
                                                                            <input class="main__input--wall"
                                                                                type="number"
                                                                                placeholder="250 {!! trans('collage_new.sm') !!}"
                                                                                min="250" max="300"
                                                                                name="wall_size" />
                                                                            <div class="range_box">
                                                                                <input type="range"
                                                                                    class="zoomRange range-input"
                                                                                    min="250" max="300"
                                                                                    step="1" />
                                                                                <div class="changeSm">
                                                                                    <div>
                                                                                        <span class="js_s1">250</span>
                                                                                        {!! trans('collage_new.sm') !!}
                                                                                    </div>
                                                                                    <div>
                                                                                        <span class="js_s2">300</span>
                                                                                        {!! trans('collage_new.sm') !!}
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="range__box">
                                                                            <input class="main__input--wall"
                                                                                type="number"
                                                                                placeholder="300 {!! trans('collage_new.sm') !!}"
                                                                                min="300" max="400"
                                                                                name="wall_size" />
                                                                            <div class="range_box">
                                                                                <input type="range"
                                                                                    class="zoomRange range-input"
                                                                                    min="300" max="400"
                                                                                    step="1" />
                                                                                <div class="changeSm">
                                                                                    <div>
                                                                                        <span class="js_s1">300</span>
                                                                                        {!! trans('collage_new.sm') !!}
                                                                                    </div>
                                                                                    <div>
                                                                                        <span class="js_s2">400</span>
                                                                                        {!! trans('collage_new.sm') !!}
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="range__box">
                                                                            <input class="main__input--wall"
                                                                                type="number"
                                                                                placeholder="350 {!! trans('collage_new.sm') !!}"
                                                                                min="350" max="400"
                                                                                name="wall_size" />
                                                                            <div class="range_box">
                                                                                <input type="range"
                                                                                    class="zoomRange range-input"
                                                                                    min="350" max="400"
                                                                                    step="1" />
                                                                                <div class="changeSm">
                                                                                    <div>
                                                                                        <span class="js_s1">350</span>
                                                                                        {!! trans('collage_new.sm') !!}
                                                                                    </div>
                                                                                    <div>
                                                                                        <span class="js_s2">400</span>
                                                                                        {!! trans('collage_new.sm') !!}
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="range__box">
                                                                            <input class="main__input--wall"
                                                                                type="number"
                                                                                placeholder="250 {!! trans('collage_new.sm') !!}"
                                                                                min="250" max="300"
                                                                                name="wall_size" />
                                                                            <div class="range_box">
                                                                                <input type="range"
                                                                                    class="zoomRange range-input"
                                                                                    min="250" max="300"
                                                                                    step="1" />
                                                                                <div class="changeSm">
                                                                                    <div>
                                                                                        <span class="js_s1">250</span>
                                                                                        {!! trans('collage_new.sm') !!}
                                                                                    </div>
                                                                                    <div>
                                                                                        <span class="js_s2">300</span>
                                                                                        {!! trans('collage_new.sm') !!}
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="range__box">
                                                                            <input class="main__input--wall"
                                                                                type="number"
                                                                                placeholder="350 {!! trans('collage_new.sm') !!}"
                                                                                min="350" max="400"
                                                                                name="wall_size" />
                                                                            <div class="range_box">
                                                                                <input type="range"
                                                                                    class="zoomRange range-input"
                                                                                    min="350" max="400"
                                                                                    step="1" />
                                                                                <div class="changeSm">
                                                                                    <div>
                                                                                        <span class="js_s1">350</span>
                                                                                        {!! trans('collage_new.sm') !!}
                                                                                    </div>
                                                                                    <div>
                                                                                        <span class="js_s2">400</span>
                                                                                        {!! trans('collage_new.sm') !!}
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
                                                <div class="canvas-wrapper">
                                                    <div class="modular-wrap tabs-item tabs-item1 active db-block"
                                                        id="tab_screen1" style="padding:0;">
                                                        <!-- <div class="PhotoEditor" id="PhotoEditor">
                                                                <div class="wrapper">
                                                                        <div class="canvas_container">
                                                                                <canvas id='canvas'> </canvas>
                                                                        </div>
                                                                </div>
                                                        </div> -->
                                                        <div class="tabs-content">
                                                            <div class="modular">
                                                                <table>
                                                                    <tr>
                                                                        <td>&nbsp;</td>
                                                                        <td>
                                                                            <div class="h-ruler"></div>
                                                                        </td>
                                                                        <td></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>
                                                                            <div class="v-ruler"></div>
                                                                        </td>
                                                                        <td class="canv_cel">
                                                                            <svg>
                                                                                <rect width="100%" height="100%"
                                                                                    fill="none" />
                                                                                <pattern id="image"
                                                                                    patternUnits="userSpaceOnUse"
                                                                                    width="100%" height="100%">
                                                                                    <image
                                                                                        preserveAspectRatio="xMidYMid slice"
                                                                                        width="100%"
                                                                                        height="100%" />
                                                                                </pattern>
                                                                                <g></g>
                                                                            </svg>
                                                                        </td>
                                                                        <td class="controls" style="display: none;">
                                                                            <button title="????????? ???? ???????????"
                                                                                class="split_v"
                                                                                style="background-image: url(img/split_v.svg)"></button>
                                                                            <button
                                                                                title="????????? ???? ?????????????"
                                                                                class="split_h"
                                                                                style="background-image: url(img/split_v.svg); transform: rotate(90deg)"></button>
                                                                            <button title="??????? ????"
                                                                                class="delete"
                                                                                style="background-image: url(img/collage7.png)"></button>
                                                                            <!--button title="???????? ???" class="clear" style="background-image: url(img/collage2.png)"></button>
                                                            <button title="???????? ????????? ????????" class="" style="background-image: url(img/undo.svg)"></button>
                                                            <button title="????????? ????????? ????????" class="" style="background-image: url(img/undo.svg); transform: scaleX(-1);"></button-->
                                                                        </td>
                                                                    </tr>
                                                                </table>

                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="interior tabs-item tabs-item2">
                                                        <img src="" alt="">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="formalization__block--bottom">

                    <div class="formalization__final">
                        <div class="formalizaton__submit">
                            <div class="formalization-price" id="totalSum">
                                {!! trans('collage_new.z7_generator_total_text1') !!} <span><span data-total="15.00"
                                        class="totalPriceNew">15</span>€</span>
                            </div>
                            <div class="formalization-btn" id="t3_submit_btn" data-pid="3" data-name="Canvas">
                                {!! trans('collage_new.z7_generator_total_text2') !!}</div>
                        </div>
                        <div class="formalization-bottom--check">


                            <div class="kviz-wrap">
                                <p> {!! trans('portrait_buy_form.final_pack') !!}</p>
                                <div class="kviz-c-row kviz-c-group">
                                    @if ($sets)
                                        @foreach ($sets as $set)
                                            <div class="kviz-radio js-checkbox @if ($loop->last) kviz-radio_active @endif"
                                                data-stock="1">
                                                <div class="check check-border"></div>
                                                <label>
                                                    <span>{{ $set->getTranslatedAttribute('name', app()->getLocale()) }}<img
                                                            src="{{ asset('images/icon/info.svg') }}"
                                                            alt="" /></span>
                                                    <input name="boxes[]" type="radio" checked
                                                        data-id="{{ $set['id'] }}"
                                                        data-name="{{ $set->getTranslatedAttribute('name', app()->getLocale()) }}"
                                                        value="{{ $set->price * $contry_mult }}" />

                                                </label>
                                                <div class="pic-pop">
                                                    <picture>
                                                        <source srcset="" type="image/webp">
                                                        <source srcset="{{ Voyager::image($set->image) }}"
                                                            type="image/jpeg">
                                                        <img loading="lazy" src="{{ Voyager::image($set->image) }}"
                                                            @altAttrs($set, 'image', data_get($set, 'image'))>
                                                    </picture>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>

                            <div class="kviz-wrap delivery-inputs">
                                <p>{!! trans('collage_new.z7_generator_total_text7') !!}</p>
                                <div class="kviz-c-row kviz-c-group">
                                    <div class="kviz-radio js-checkbox kviz-radio_active" data-stock="1">
                                        <div class="check check-border"></div>
                                        <label>
                                            <span>{!! $AProductionTime->standart_text !!} {{ $AProductionTime->standart_price }}
                                                €</span>
                                            <input type="radio" name="dost_time"
                                                value="{{ $AProductionTime->standart_price }}" />
                                        </label>
                                    </div>
                                    <div class="kviz-radio js-checkbox" data-stock="2">
                                        <div class="check check-border"></div>
                                        <label>
                                            <span>{!! $AProductionTime->express_text !!} {{ $AProductionTime->express_price }}
                                                €</span>
                                            <input type="radio" name="dost_time"
                                                value="{{ $AProductionTime->express_price }}" />
                                        </label>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </form>
        </div>

    </div>
</div>

<div class="ellipse ellipse_black">
    <img  src="https://viarcanvas.com/theme/viar/images/icon/ellipse-black.svg" alt="img"
        loading="lazy" style="background: linear-gradient(180deg, rgb(31 36 51) 50%, rgb(250 242 234) 50%);">
</div>

<div class="about-work">
    <div class="section-frame">
        <div class="about-work__inner">
            <h2 class="page-title about-work__title">
                @lang('pages.modular-generator.how_we_work_title')
            </h2>
            <p>@lang('pages.modular-generator.about-work.text')</p>
            <div class="about-work__block">
                <div class="aw-block">
                    <span class="num">
                        1
                    </span>
                    <div class="ab-img">
                        <picture>
                            <source srcset="{{ asset(env('THEME') . 'images') }}/contacts/w1.avif"
                                type="image/avif">
                            <source srcset="{{ asset(env('THEME') . 'images') }}/contacts/w1.webp"
                                type="image/webp">
                            <source srcset="{{ asset(env('THEME') . 'images') }}/contacts/w1.jpg" type="image/jpeg">
                            <img width="120" height="120"
                                src="{{ asset(env('THEME') . 'images') }}/contacts/w1.jpg" alt="">
                        </picture>
                    </div>
                    <div class="ab-title">
                        @lang('about.text_8_3')
                    </div>
                    <div class="ab-text">
                        @lang('about.text_8_4')
                    </div>
                    <div class="ab-p">
                        <sup>*</sup>@lang('about.text_8_5')
                    </div>
                </div>
                <div class="aw-block">
                    <span class="num">
                        2
                    </span>
                    <div class="ab-img">
                        <picture>
                            <source srcset="{{ asset(env('THEME') . 'images') }}/contacts/w2.avif"
                                type="image/avif">
                            <source srcset="{{ asset(env('THEME') . 'images') }}/contacts/w2.webp"
                                type="image/webp">
                            <source srcset="{{ asset(env('THEME') . 'images') }}/contacts/w2.jpg" type="image/jpeg">
                            <img width="120" height="120"
                                src="{{ asset(env('THEME') . 'images') }}/contacts/w2.jpg" alt="">
                        </picture>
                    </div>
                    <div class="ab-title">
                        @lang('about.text_8_6')
                    </div>
                    <div class="ab-text">
                        @lang('about.text_8_7')
                    </div>
                    <div class="ab-p">
                        <sup>*</sup>@lang('about.text_8_8')
                    </div>
                </div>
                <div class="aw-block">
                    <span class="num">
                        3
                    </span>
                    <div class="ab-img">
                        <picture>
                            <source srcset="{{ asset(env('THEME') . 'images') }}/contacts/w3.avif"
                                type="image/avif">
                            <source srcset="{{ asset(env('THEME') . 'images') }}/contacts/w3.webp"
                                type="image/webp">
                            <source srcset="{{ asset(env('THEME') . 'images') }}/contacts/w3.jpg" type="image/jpeg">
                            <img width="120" height="120"
                                src="{{ asset(env('THEME') . 'images') }}/contacts/w3.jpg" alt="">
                        </picture>
                    </div>
                    <div class="ab-title">
                        @lang('about.text_8_9')
                    </div>
                    <div class="ab-text">
                        @lang('about.text_8_10')
                    </div>
                    <div class="ab-p">
                        <sup>*</sup>@lang('about.text_8_11')
                    </div>
                </div>
                <div class="aw-block">
                    <span class="num">
                        4
                    </span>
                    <div class="ab-img">
                        <picture>
                            <source srcset="{{ asset(env('THEME') . 'images') }}/contacts/w4.avif"
                                type="image/avif">
                            <source srcset="{{ asset(env('THEME') . 'images') }}/contacts/w4.webp"
                                type="image/webp">
                            <source srcset="{{ asset(env('THEME') . 'images') }}/contacts/w4.jpg" type="image/jpeg">
                            <img width="120" height="120"
                                src="{{ asset(env('THEME') . 'images') }}/contacts/w4.jpg" alt="">
                        </picture>
                    </div>
                    <div class="ab-title">
                        @lang('about.text_8_12')
                    </div>
                    <div class="ab-text">
                        @lang('about.text_8_13')
                    </div>
                    <div class="ab-p">
                        <sup>*</sup>@lang('about.text_8_14')
                    </div>
                </div>
            </div>
            <a href="#" class="ab-btn">@lang('about.text_8_15')</a>
        </div>
    </div>
</div>

<div class="similar">
    <div class="section-frame">
        <div class="similar-inner">
            <div class="similar-title page-title">
                @lang('pages.modular-generator.popular.title')
            </div>
            <div class="similar-slider">
                <div class="similar-slider--wrapper swiper-wrapper">
                    {{-- @include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.reproduction_one_item', ['items' => $popular, 'type' => 'reproduction', 'slide' => true]) --}}
                    @include((config('theme.resource') ?: 'theme.viar.') . 'pages.modular-generator.one_item', [
                        'items' => $popular,
                        'type' => 'module',
                        'slide' => true,
                    ])
                </div>
                <div class="swiper-button swiper-prev">
                    <svg width="12" height="19" viewBox="0 0 12 19" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M11 18L2 9.5L11 1" stroke="#1E2533" stroke-width="2" />
                    </svg>
                </div>
                <div class="swiper-button swiper-next">
                    <svg width="12" height="19" viewBox="0 0 12 19" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M0.999999 0.999999L10 9.5L1 18" stroke="white" stroke-width="2" />
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>

@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.item-card_part-about', ['info_block' => "hidden"])
@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.zpart_viarcanvas_is')
<script>
    $('#t3_submit_btn').click(function() {
        obj=$(this);
        if (obj.data('processed')) {return; }
        $('.loading-bar').css('display', 'none').hide().fadeOut();
        obj.data('processed', true);

        if (window.loadedImages) {
            let loadedImage = window.loadedImages;
        }
        let loadedImage = $('.image-block').length;
        if (loadedImage == 0) {
            $('.popup-inv-size').addClass('active');
            return;
        }
        let data = formData();
        data.append('pid', $('#t3_submit_btn').data('pid'));

        $.ajax({
            type: 'post',
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('add_item_to_basket_construct') }}',
            data: data,
            beforeSend: function() {
                $('.loading-bar').css('display', 'flex').hide().fadeIn();
            },
            success: function(response) {
                if (response) {
                    var data = jQuery.parseJSON(response);
                    if (data['Error']) {
                        alert(data['Error']);
                    } else if (data['success']) {
                        // seo
                        var total_seo__price = $('.tabs-item #totalPrice').data('total');
                        window.dataLayer = window.dataLayer || [];
                        dataLayer.push({
                            'ecommerce': {
                                'currencyCode': 'EUR',
                                'add': {
                                    'actionField': {
                                        'list': 'modular-picture'
                                    },
                                    'products': [{
                                        'name': 'modular-picture',
                                        'price': total_seo__price,
                                        'quantity': 1,
                                    }]
                                }
                            },
                            'event': 'EE-event',
                            'EE-event-category': 'Enhanced Ecommerce',
                            'EE-event-action': 'Add To Cart',
                            'EE-event-non-interaction': 'False'
                        });

                        // endseo
                        $('body').addClass('open-frame');
                        $('.popup-frame').css("display", "flex").hide().fadeIn();
                        $('.popup-cart').fadeIn();
                        $('.loading-bar').fadeOut();

                        // $('.popup-bask-add').addClass('active');
                        var cur_count = $('#smallCart').text();
                        cur_count = parseInt(cur_count);
                        cur_count++;
                        $('#smallCart').text(cur_count);
                    }

                    $('#t1_submit_btn').attr('data-disabled', '0');
                }

                $('.images-container').html("");
                $('.file-input').val('');
                $('.loading-bar').css('display','none');
                obj.addClass("js_submit_form");
            },
            error: function(error) {

                $('.images-container').html("");
                $('.file-input').val('');
                $('.loading-bar').css('display','none');
                obj.data('processed', false);

                if (error.responseText) {
                    let response = JSON.parse(error.responseText);
                    $.each(response.errors, function(key, value) {
                        alert(value[0])
                    });
                } else {
                    alert('Server error');
                }

                // $('.js_spinner').jmspinner(false);
                $('#t1_submit_btn').attr('data-disabled', '0');
            }
        });
    });
</script>


<script>
    var saved_price = 0;
    var dost_price = 0;
    var ram_price = 0;
    var price_hud_of = 0;
    let execution = 1;
    var rp = 0;

    calcTotalPrice();

    $(document).on('click', '.delete', function(e) {
        $('.download .img').removeAttr('style');
        //location.reload();
    });

    /*
    $(document).on('change', 'input[name="whole-width"]', function() {
        calcTotalPrice();
    });

    $(document).on('change', 'input[name="whole-height"]', function() {
        calcTotalPrice();
    });

    $(document).on('change input keyup', '.range-input', function() {
        calcTotalPrice();
    });

    $('input[name="file[]"]').on('change', function() {
        console.log('lk')
        calcTotalPrice();
    });
    $('input[name="boxes[]"]').on('change', function() {
        calcTotalPrice();
    });

    $('.imageFile').on('change', function() {
        console.log('hi')
        setTimeout(() => {
            calcTotalPrice();
        }, 500)
    });
    */

    $('input[name="decoration"]').on('change', function() {
        calcTotalPrice();
    });

    function calc_float_val(t) {
        let a = Number(t);
        return parseFloat(t).toFixed(2);
    }

    function formData() {
        let height = $('input[name="whole-height"]').val();
        let width = $('input[name="whole-width"]').val();
        let sizeSize = height + "x" + width;

        let userImages = saveModular();
        let collageSvgImage = userImages.svg;
        let activeImage = $('.imageFile').prop('files')[0];

        let formData = new FormData();

        let size = sizeSize;
        // let canvasId = $('input[name="canvas_type"]:checked').data('id');
        let executionId = $('.calcExecution.active').data('execution');
        let decorationId = $('input[name="decoration"]:checked').data('id');
        let userComment = $('#userComment').val();
        let formId = $('.modular-shapes button.js__selected').children().data('index');
        let boxIds = [];

        $('input[name="boxes[]"]:checked').each(function() {
            boxIds.push(parseInt($(this).data('id')));
        });

        formData.append('basketType', '{{ \App\Entity\BasketType::MODULAR_PICTURES_TYPE }}');
        formData.append('is_orig_file', 1);
        formData.append('photo_ex', "");
        formData.append('wall_size_mod', "");
        formData.append('dost_time', $('.delivery-inputs').find('.kviz-radio_active').find('span').text());
        formData.append('terms_price', $('.delivery-inputs .kviz-radio_active input').val());
        formData.append('price', $('#totalSum .totalPriceNew').data('price'));

        if (size)
            formData.append('size', size);
        if (executionId)
            formData.append('executionId', executionId);
        // if (canvasId)
        //     formData.append('canvasId', canvasId);
        if (decorationId)
            formData.append('holst_id', decorationId);
        if (boxIds)
            formData.append('boxIds', JSON.stringify(boxIds));
        if (userComment)
            formData.append('userComment', userComment);
        if (formId && formId != undefined)
            formData.append('formId', formId);

        formData.append('name', "Modular pictures");
        formData.append('image', activeImage);
        formData.append('collageSvgImage', collageSvgImage);

        formData.append('collageSvgImage_hash', file_hash);

        for (var pair of formData.entries()) {
            console.log(pair[0] + ', ' + pair[1]);
        }

        return formData;
    }


    $(document).on('click', '.tabs a:eq(1)', function(e) {
        calcTotalPrice();
    });

    $(document).on('change', 'input[name="dost_time"]', function(e) {
        dost_price = $(this).val();
        calcTotalPrice();
    });

    function calcExecutionPrice(sizeSize, execution) {

        let executionPrice = 0, area=0;

        $('g.module > rect').each((i, el) => {

            const {width, height} = el.getBBox();
            area += width * height / 10000;
            //area += width*2 + height*2; // ???? ????? ?????? ??????? ??????? ???????????? ?????? (????? 2 ??)
        })

        if (area <= 0.4)
            area *= {{ setting('modulnye-kartiny.area_less_04') }};
        else if (area > 0.4 && area <= 1)
            area *= {{ setting('modulnye-kartiny.area_less_1') }};
        else if (area > 1)
            area *= {{ setting('modulnye-kartiny.area_more_01') }};

        executionPrice += area;


        if (execution == 1) {
            executionPrice *= {{ setting('modulnye-kartiny.area_is_1') }};
        }

        return executionPrice.toFixed(2);
    }

    function calcTotalPrice() {
        let height = $('input[name="whole-height"]').val();
        let width = $('input[name="whole-width"]').val();
        let sizeSize = height + "x" + width;

        let totalPrice = 0;

        price_hud_of = parseInt($('input[name="decoration"]:checked').val());
        totalPrice += price_hud_of;
        let ex_price = calcExecutionPrice(sizeSize, execution);

        totalPrice = parseFloat(totalPrice) + parseFloat(ex_price);


        totalPrice = parseFloat(totalPrice).toFixed(2);


        totalPrice = calc_float_val(totalPrice);
        // endnew
        totalPrice = parseFloat(totalPrice) + parseFloat(dost_price);
        totalPrice = totalPrice.toFixed(2);

        let coef_1 = parseInt($('input[name="decoration"]:checked').data('coef_sm'));
        let coef_2 = parseInt($('input[name="decoration"]:checked').data('coef_md'));
        let coef_3 = parseInt($('input[name="decoration"]:checked').data('coef_lg'));

        let orderWrap = parseInt($('input[name="boxes[]"]:checked').attr('value'));

        let s1 = $('input[name="whole-width"]').val();
        let s2 = $('input[name="whole-height"]').val();
        let area = s1 * s2 / 10000;

        if (area <= 0.4) {
            decorAddPrice = area * coef_1;
        } else if (area > 0.4 && area <= 1) {
            decorAddPrice = area * coef_2;
        } else if (area >= 1.01) {
            decorAddPrice = area * coef_3;
        }

        decorAddPrice = parseFloat(decorAddPrice);
        //

        if (isNaN(decorAddPrice)) {
            decorAddPrice = 0;
        }

        totalPrice = parseFloat(totalPrice);

        totalPrice += decorAddPrice;

        totalPrice += parseInt(orderWrap);


        totalPrice = parseFloat(totalPrice).toFixed(2);

        var data_total_price = totalPrice;

        totalPrice = parseFloat(totalPrice).toFixed(2);

        var mult = $('body').data('multiplier');

        if (isNaN(mult)) {
            mult = 1;
        }

        totalPrice = totalPrice * mult;

        totalPrice = parseFloat(totalPrice).toFixed(2);

        $('#totalSum .totalPriceNew').html(totalPrice);



        $('#totalSum .totalPriceNew').attr('data-price', totalPrice);
    }

    $('.dost_items2 input, .dost_items1 input').change(function(e) {
        dost_price = $(this).val();

        if (dost_price == 0) {
            $('.tabs-item1 .dost_items').children('li').eq(1).find('label').trigger('click');
            $('.tabs-item2 .dost_items').children('li').eq(1).find('label').trigger('click');
        } else {
            $('.tabs-item1 .dost_items').children('li').eq(2).find('label').trigger('click');
            $('.tabs-item2 .dost_items').children('li').eq(2).find('label').trigger('click');
        }
        setTimeout(() => {
            calcTotalPrice();
        }, 200);
    });

    $(document).on('click', '.js_decor_item .jcf-radio', function(e) {
        price_hud_of = $(this).find('input').val();
        setTimeout(() => {
            calcTotalPrice();
        }, 200);
    });
</script>
</div>

<h2 class="page-title about-work__title">
    @lang('pages.modular-generator.bestprice')
</h2>
<br>
@include(  'partials.schema_reviews')
