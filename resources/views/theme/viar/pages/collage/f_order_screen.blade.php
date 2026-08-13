<!-- fast order -->
@php
    /** @var \Illuminate\Support\Collection|\App\Models\SiteImage[] $siteImages */
    $siteImages = $siteImages ?? \App\Models\SiteImage::where("is_show",true)->get();
    $collage_1 = site_image('collage_f_order_1', 'images/collage/fo1.jpg', ['collection' => $siteImages]);
    $collage_2 = site_image('collage_f_order_2', 'images/collage/fo3.jpg', ['collection' => $siteImages]);
    $collage_3 = site_image('collage_f_order_3', 'images/collage/fo2.jpg', ['collection' => $siteImages]);
    $collage_4 = site_image('collage_f_order_4', 'images/collage/fo4.png', ['collection' => $siteImages]);
@endphp

    <div class="collage-f-order__screen">
        <div class="section-frame">
            <div class="collage-f-order--inner">
                <div class="page-title--row">
                    <h2 class="page-title page-collage-title f-order-title">
                        {!! trans('collage_new.z3_f_order_screen_title') !!}
                    </h2>
                    <div class="col-subtitle">
                        {!! trans('collage_new.z3_f_order_screen_subtitle') !!}
                    </div>
                </div>
                <div class="collage-f-order--items">
                    <div class="collage-f-order--item--wrap">
                        <div class="collage-f-order--item">
                            <div class="f-order-head">
                                <div class="f-order-num">
                                    1.
                                </div>
                                <div class="f-order-img">
                                    <picture>
                                        @if(!empty($collage_1['src_webp']))
                                            <source srcset="{{ $collage_1['src_webp'] }}" type="image/webp">
                                        @endif
                                        @if(!empty($collage_1['type']))
                                            <source srcset="{{ $collage_1['src'] }}" type="{{ $collage_1['type'] }}">
                                        @endif
                                        <img width="208" height="145" loading="lazy" src="{{ $collage_1['src'] }}" alt="{{ $collage_1['alt'] ?? '' }}" title="{{ $collage_1['title'] ?? '' }}">
                                    </picture>
                                </div>
                            </div>
                            <div class="f-order-body">
                                <div class="collage-advantage_s--title">
                                    {!! trans('collage_new.z3_f_order_screen_1_title') !!}
                                </div>
                                <div class="f-order-text">
                                    {!! trans('collage_new.z3_f_order_screen_1_text') !!}
                                </div>
                                <div class="f-order-abt">
                                    <span>*</span>
                                    <div class="f-order-abt-text">
                                        <span>
                                            {!! trans('collage_new.z3_f_order_screen_1_time') !!}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="collage-f-order--item--wrap">
                        <div class="collage-f-order--item">
                            <div class="f-order-head">
                                <div class="f-order-num">
                                    2.
                                </div>
                                <div class="f-order-img">
                                    <picture>
                                        @if(!empty($collage_2['src_webp']))
                                            <source srcset="{{ $collage_2['src_webp'] }}" type="image/webp">
                                        @endif
                                        @if(!empty($collage_2['type']))
                                            <source srcset="{{ $collage_2['src'] }}" type="{{ $collage_2['type'] }}">
                                        @endif
                                        <img width="208" height="145" loading="lazy" src="{{ $collage_2['src'] }}" alt="{{ $collage_2['alt'] ?? '' }}" title="{{ $collage_2['title'] ?? '' }}">
                                    </picture>
                                </div>
                            </div>
                            <div class="f-order-body">
                                <div class="collage-advantage_s--title">
                                    {!! trans('collage_new.z3_f_order_screen_2_title') !!}
                                </div>
                                <div class="f-order-text">
                                    {!! trans('collage_new.z3_f_order_screen_2_text') !!}
                                </div>
                                <div class="f-order-abt">
                                    <span>*</span>
                                    <div class="f-order-abt-text">
                                        <span>
                                            {!! trans('collage_new.z3_f_order_screen_2_time') !!}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="collage-f-order--item--wrap">
                        <div class="collage-f-order--item">
                            <div class="f-order-head">
                                <div class="f-order-num">
                                    3.
                                </div>
                                <div class="f-order-img">
                                    <picture>
                                        @if(!empty($collage_3['src_webp']))
                                            <source srcset="{{ $collage_3['src_webp'] }}" type="image/webp">
                                        @endif
                                        @if(!empty($collage_3['type']))
                                            <source srcset="{{ $collage_3['src'] }}" type="{{ $collage_3['type'] }}">
                                        @endif
                                        <img width="208" height="145" loading="lazy" src="{{ $collage_3['src'] }}" alt="{{ $collage_3['alt'] ?? '' }}" title="{{ $collage_3['title'] ?? '' }}">
                                    </picture>
                                </div>
                            </div>
                            <div class="f-order-body">
                                <div class="collage-advantage_s--title">
                                    {!! trans('collage_new.z3_f_order_screen_3_title') !!}
                                </div>
                                <div class="f-order-text">
                                    {!! trans('collage_new.z3_f_order_screen_3_text') !!}
                                </div>
                                <div class="f-order-abt">
                                    <span>*</span>
                                    <div class="f-order-abt-text">
                                        <span>
                                            {!! trans('collage_new.z3_f_order_screen_3_time') !!}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="collage-f-order--item--wrap">
                        <div class="collage-f-order--item">
                            <div class="f-order-head">
                                <div class="f-order-num">
                                    4.
                                </div>
                                <div class="f-order-img">
                                    <picture>
                                        @if(!empty($collage_4['src_webp']))
                                            <source srcset="{{ $collage_4['src_webp'] }}" type="image/webp">
                                        @endif
                                        @if(!empty($collage_4['type']))
                                            <source srcset="{{ $collage_4['src'] }}" type="{{ $collage_4['type'] }}">
                                        @endif
                                        <img width="208" height="145" loading="lazy" src="{{ $collage_4['src'] }}" alt="{{ $collage_4['alt'] ?? '' }}" title="{{ $collage_4['title'] ?? '' }}">
                                    </picture>
                                </div>
                            </div>
                            <div class="f-order-body">
                                <div class="collage-advantage_s--title">
                                    {!! trans('collage_new.z3_f_order_screen_4_title') !!}
                                </div>
                                <div class="f-order-text">
                                    {!! trans('collage_new.z3_f_order_screen_4_text') !!}
                                </div>
                                <div class="f-order-abt">
                                    <span>*</span>
                                    <div class="f-order-abt-text">
                                        <span>
                                            {!! trans('collage_new.z3_f_order_screen_4_time') !!}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
