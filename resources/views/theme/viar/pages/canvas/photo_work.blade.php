@if (Route::currentRouteName() == 'canvas')
    <h2 class="page-title">
        {!! trans('canvas.photo_work_title') !!}
    </h2>
@else
    <h2 class="page-title">
        {!! trans('canvas.photo_work_title') !!}
    </h2>
@endif

<div class="retouch__tabs--wrapper">
    {{-- <div class="retouch-tabs">
        <div class="retouch-tab active">
            <a href="#">{{ trans('canvas.photo_work1') }}</a>
        </div>
        <div class="retouch-tab">
            <a href="#">{{ trans('canvas.photo_work2') }}</a>
        </div>
        <div class="retouch-tab">
            <a href="#">{{ trans('canvas.photo_work3') }}</a>
        </div>
    </div> --}}
</div>
<div class="retouch__blocks">
    <div class="retouch__block">
        <div class="retouch__block-col">
            <div class="ba-canvas">
                @php
                    $b1_before_items = $item->getMedia('photo_work_basic');
                    $b1_after_items = $item->getMedia('photo_work_basic_after');
                @endphp
                <div class="ba-slider ba-slider1 hb_alt">
                    @if(!$b1_before_items->isEmpty() && count($b1_before_items) == count($b1_after_items))
                        <picture>
                            @php
                                $basicBeforeImageSources = image_picture_sources($b1_before_items[0]->getUrl(), true);
                            @endphp
                            @if(!empty($basicBeforeImageSources['src_webp']))
                                <source srcset="{{ $basicBeforeImageSources['src_webp'] }}" type="image/webp">
                            @endif
                            @if(!empty($basicBeforeImageSources['src']) && !empty($basicBeforeImageSources['type']))
                                <source srcset="{{ $basicBeforeImageSources['src'] }}" type="{{ $basicBeforeImageSources['type'] }}">
                            @endif
                            <img src="{{ $basicBeforeImageSources['src'] }}"

                            @if ($b1_before_items[0]->getCustomProperty('image_title_'.Config::get('app.locale')))
                                title="{{ $b1_before_items[0]->getCustomProperty('image_title_'.Config::get('app.locale')) }}"
                            @endif

                            @if ($b1_before_items[0]->getCustomProperty('image_alt_'.Config::get('app.locale')))
                                alt="{{ $b1_before_items[0]->getCustomProperty('image_alt_'.Config::get('app.locale')) }}"
                            @endif

                            />

                        </picture>
                        <div class="resize">
                            <picture>
                                @php
                                    $basicAfterImageSources = image_picture_sources($b1_after_items[0]->getUrl(), true);
                                @endphp
                                @if(!empty($basicAfterImageSources['src_webp']))
                                    <source srcset="{{ $basicAfterImageSources['src_webp'] }}" type="image/webp">
                                @endif
                                @if(!empty($basicAfterImageSources['src']) && !empty($basicAfterImageSources['type']))
                                    <source srcset="{{ $basicAfterImageSources['src'] }}" type="{{ $basicAfterImageSources['type'] }}">
                                @endif
                                <img src="{{ $basicAfterImageSources['src'] }}"

                                @if ($b1_after_items[0]->getCustomProperty('image_title_'.Config::get('app.locale')))
                                    title="{{ $b1_after_items[0]->getCustomProperty('image_title_'.Config::get('app.locale')) }}"
                                @endif

                                @if ($b1_after_items[0]->getCustomProperty('image_alt_'.Config::get('app.locale')))
                                    alt="{{ $b1_after_items[0]->getCustomProperty('image_alt_'.Config::get('app.locale')) }}"
                                @endif

                                />
                            </picture>
                        </div>
                    @endif
                    <div class="absolute-block">
                        <div class="before-wrapper">
                            <div class="before-btn">
                                {{ trans('canvas.photo_work_before') }}
                            </div>
                        </div>
                        <div class="after-wrapper">
                            <div class="after-btn">
                                {{ trans('canvas.photo_work_after') }}
                            </div>
                        </div>
                    </div>
                    <span class="handle"></span>
                </div>
                {{-- <div class="before-after__nav before-after__nav1">
                    <div class="bf-arr bf-arr_l">
                        <svg width="5" height="9" viewBox="0 0 5 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M4.91409 8.45892C4.97136 8.3988 5 8.32966 5 8.2515C5 8.17335 4.97136 8.10421 4.91409 8.04409L1.5378 4.5L4.91409 0.955912C4.97136 0.895792 5 0.826653 5 0.748496C5 0.67034 4.97136 0.601202 4.91409 0.541081L4.48454 0.0901804C4.42726 0.0300598 4.3614 0 4.28694 0C4.21249 0 4.14662 0.0300598 4.08935 0.0901804L0.0859107 4.29258C0.0286369 4.35271 0 4.42184 0 4.5C0 4.57816 0.0286369 4.64729 0.0859107 4.70742L4.08935 8.90982C4.14662 8.96994 4.21249 9 4.28694 9C4.3614 9 4.42726 8.96994 4.48454 8.90982L4.91409 8.45892Z"
                                fill="white"/>
                        </svg>
                    </div>
                    <ul class="bf-obj bf-obj1">
                        @if(!$b1_before_items->isEmpty() && count($b1_before_items) == count($b1_after_items))
                            @foreach($b1_before_items as $b1_items)
                                <li @if($loop->first) class="active" @endif
                                    data-imageB="{{ $b1_items->getUrl() }}"
                                    data-imageA="{{ $b1_after_items[$loop->index]->getUrl() }}">{{ $loop->iteration }}
                                </li>
                            @endforeach
                        @endif
                    </ul>
                    <div class="bf-arr bf-arr_r">
                        <svg width="5" height="9" viewBox="0 0 5 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M0.0859108 8.45892C0.0286369 8.3988 0 8.32966 0 8.2515C0 8.17335 0.0286369 8.10421 0.0859108 8.04409L3.4622 4.5L0.0859108 0.955912C0.0286369 0.895792 0 0.826653 0 0.748496C0 0.67034 0.0286369 0.601202 0.0859108 0.541081L0.515464 0.0901804C0.572738 0.0300598 0.638603 0 0.713059 0C0.787515 0 0.853379 0.0300598 0.910653 0.0901804L4.91409 4.29258C4.97136 4.35271 5 4.42184 5 4.5C5 4.57816 4.97136 4.64729 4.91409 4.70742L0.910653 8.90982C0.853379 8.96994 0.787515 9 0.713059 9C0.638603 9 0.572738 8.96994 0.515464 8.90982L0.0859108 8.45892Z"
                                fill="white"/>
                        </svg>
                    </div>
                </div> --}}
            </div>
        </div>
        <div class="retouch__block-col">
            <ul class="type__features">
                {!! trans('canvas.photo_work_desc_list1') !!}
            </ul>
            {{-- <div class="price-block">
                <div class="price-block--inner">
                    <div class="price-amout">
                        {!! trans('canvas.photo_work_pay_desc1') !!}
                    </div>
                    <div class="price-note">
                        <span>*</span>
                        {!! trans('canvas.photo_work_pay_title1') !!}
                    </div>
                </div>
            </div> --}}
        </div>
    </div>
    <div class="retouch__block hidden-block">
        <div class="retouch__block-col">
            <div class="ba-canvas hb_alt">
                @php
                    $b2_before_items = $item->getMedia('photo_work_optimal');
                    $b2_after_items = $item->getMedia('photo_work_optimal_after');
                @endphp
                <div class="ba-slider ba-slider2">
                    @if(!$b2_before_items->isEmpty() && count($b2_before_items) == count($b2_after_items))
                        <picture>
                            @php
                                $optimalBeforeImageSources = image_picture_sources($b2_before_items[0]->getUrl(), true);
                            @endphp
                            @if(!empty($optimalBeforeImageSources['src_webp']))
                                <source srcset="{{ $optimalBeforeImageSources['src_webp'] }}" type="image/webp">
                            @endif
                            @if(!empty($optimalBeforeImageSources['src']) && !empty($optimalBeforeImageSources['type']))
                                <source srcset="{{ $optimalBeforeImageSources['src'] }}" type="{{ $optimalBeforeImageSources['type'] }}">
                            @endif
                            <img src="{{ $optimalBeforeImageSources['src'] }}"

                                    @if ($b2_before_items[0]->getCustomProperty('image_title_'.Config::get('app.locale')))
                                        title="{{ $b2_before_items[0]->getCustomProperty('image_title_'.Config::get('app.locale')) }}"
                                    @else

                                    @endif

                                    @if ($b2_before_items[0]->getCustomProperty('image_alt_'.Config::get('app.locale')))
                                        alt="{{ $b2_before_items[0]->getCustomProperty('image_alt_'.Config::get('app.locale')) }}"
                                    @else
                                    @endif
                            />
                        </picture>
                        <div class="resize">
                            <picture>
                                @php
                                    $optimalAfterImageSources = image_picture_sources($b2_after_items[0]->getUrl(), true);
                                @endphp
                                @if(!empty($optimalAfterImageSources['src_webp']))
                                    <source srcset="{{ $optimalAfterImageSources['src_webp'] }}" type="image/webp">
                                @endif
                                @if(!empty($optimalAfterImageSources['src']) && !empty($optimalAfterImageSources['type']))
                                    <source srcset="{{ $optimalAfterImageSources['src'] }}" type="{{ $optimalAfterImageSources['type'] }}">
                                @endif
                                <img src="{{ $optimalAfterImageSources['src'] }}" alt=""/>
                            </picture>
                        </div>
                    @endif
                    <div class="absolute-block">
                        <div class="before-wrapper">
                            <div class="before-btn">
                                {{ trans('canvas.photo_work_before') }}
                            </div>
                        </div>
                        <div class="after-wrapper">
                            <div class="after-btn">
                                {{ trans('canvas.photo_work_after') }}
                            </div>
                        </div>
                    </div>
                    <span class="handle"></span>
                </div>
                {{-- <div class="before-after__nav before-after__nav2">
                    <div class="bf-arr bf-arr_l">
                        <svg width="5" height="9" viewBox="0 0 5 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M4.91409 8.45892C4.97136 8.3988 5 8.32966 5 8.2515C5 8.17335 4.97136 8.10421 4.91409 8.04409L1.5378 4.5L4.91409 0.955912C4.97136 0.895792 5 0.826653 5 0.748496C5 0.67034 4.97136 0.601202 4.91409 0.541081L4.48454 0.0901804C4.42726 0.0300598 4.3614 0 4.28694 0C4.21249 0 4.14662 0.0300598 4.08935 0.0901804L0.0859107 4.29258C0.0286369 4.35271 0 4.42184 0 4.5C0 4.57816 0.0286369 4.64729 0.0859107 4.70742L4.08935 8.90982C4.14662 8.96994 4.21249 9 4.28694 9C4.3614 9 4.42726 8.96994 4.48454 8.90982L4.91409 8.45892Z"
                                fill="white"/>
                        </svg>
                    </div>
                    <ul class="bf-obj bf-obj2">
                        @if(!$b2_before_items->isEmpty() && count($b2_before_items) == count($b2_after_items))
                            @foreach($b2_before_items as $b2_items)
                                <li @if($loop->first) class="active" @endif
                                    data-imageB="{{ $b2_items->getUrl() }}"
                                    data-imageA="{{ $b2_after_items[$loop->index]->getUrl() }}">{{ $loop->iteration }}
                                </li>
                            @endforeach
                        @endif
                    </ul>
                    <div class="bf-arr bf-arr_r">
                        <svg width="5" height="9" viewBox="0 0 5 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M0.0859108 8.45892C0.0286369 8.3988 0 8.32966 0 8.2515C0 8.17335 0.0286369 8.10421 0.0859108 8.04409L3.4622 4.5L0.0859108 0.955912C0.0286369 0.895792 0 0.826653 0 0.748496C0 0.67034 0.0286369 0.601202 0.0859108 0.541081L0.515464 0.0901804C0.572738 0.0300598 0.638603 0 0.713059 0C0.787515 0 0.853379 0.0300598 0.910653 0.0901804L4.91409 4.29258C4.97136 4.35271 5 4.42184 5 4.5C5 4.57816 4.97136 4.64729 4.91409 4.70742L0.910653 8.90982C0.853379 8.96994 0.787515 9 0.713059 9C0.638603 9 0.572738 8.96994 0.515464 8.90982L0.0859108 8.45892Z"
                                fill="white"/>
                        </svg>
                    </div>
                </div> --}}
            </div>
        </div>
        <div class="retouch__block-col">
            <ul class="type__features">
                {!! trans('canvas.photo_work_desc_list2') !!}
            </ul>
            {{-- <div class="price-block">
                <div class="price-block--inner">
                    <div class="price-amout">
                        {!! trans('canvas.photo_work_pay_desc2') !!}
                    </div>
                    <div class="price-note">
                        <span>*</span>
                        {!! trans('canvas.photo_work_pay_title2') !!}
                    </div>
                </div>
            </div> --}}
        </div>
    </div>
    <div class="retouch__block hidden-block">
        <div class="retouch__block-col">
            <div class="ba-canvas">
                <div class="ba-slider ba-slider3">
                    @php
                        $b3_before_items = $item->getMedia('photo_work_premium');
                        $b3_after_items = $item->getMedia('photo_work_premium_after');
                    @endphp
                    @if(!$b3_before_items->isEmpty() && count($b3_before_items) == count($b3_after_items))
                        <picture>
                            @php
                                $premiumBeforeImageSources = image_picture_sources($b3_before_items[0]->getUrl(), true);
                            @endphp
                            @if(!empty($premiumBeforeImageSources['src_webp']))
                                <source srcset="{{ $premiumBeforeImageSources['src_webp'] }}" type="image/webp">
                            @endif
                            @if(!empty($premiumBeforeImageSources['src']) && !empty($premiumBeforeImageSources['type']))
                                <source srcset="{{ $premiumBeforeImageSources['src'] }}" type="{{ $premiumBeforeImageSources['type'] }}">
                            @endif
                            <img src="{{ $premiumBeforeImageSources['src'] }}" alt=""/>
                        </picture>
                        <div class="resize">
                            <picture>
                                @php
                                    $premiumAfterImageSources = image_picture_sources($b3_after_items[0]->getUrl(), true);
                                @endphp
                                @if(!empty($premiumAfterImageSources['src_webp']))
                                    <source srcset="{{ $premiumAfterImageSources['src_webp'] }}" type="image/webp">
                                @endif
                                @if(!empty($premiumAfterImageSources['src']) && !empty($premiumAfterImageSources['type']))
                                    <source srcset="{{ $premiumAfterImageSources['src'] }}" type="{{ $premiumAfterImageSources['type'] }}">
                                @endif
                                <img src="{{ $premiumAfterImageSources['src'] }}" alt=""/>
                            </picture>
                        </div>
                    @endif
                    <div class="absolute-block">
                        <div class="before-wrapper">
                            <div class="before-btn">
                                {{ trans('canvas.photo_work_before') }}
                            </div>
                        </div>
                        <div class="after-wrapper">
                            <div class="after-btn">
                                {{ trans('canvas.photo_work_after') }}
                            </div>
                        </div>
                    </div>
                    <span class="handle"></span>
                </div>
                {{-- <div class="before-after__nav before-after__nav3">
                    <div class="bf-arr bf-arr_l">
                        <svg width="5" height="9" viewBox="0 0 5 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M4.91409 8.45892C4.97136 8.3988 5 8.32966 5 8.2515C5 8.17335 4.97136 8.10421 4.91409 8.04409L1.5378 4.5L4.91409 0.955912C4.97136 0.895792 5 0.826653 5 0.748496C5 0.67034 4.97136 0.601202 4.91409 0.541081L4.48454 0.0901804C4.42726 0.0300598 4.3614 0 4.28694 0C4.21249 0 4.14662 0.0300598 4.08935 0.0901804L0.0859107 4.29258C0.0286369 4.35271 0 4.42184 0 4.5C0 4.57816 0.0286369 4.64729 0.0859107 4.70742L4.08935 8.90982C4.14662 8.96994 4.21249 9 4.28694 9C4.3614 9 4.42726 8.96994 4.48454 8.90982L4.91409 8.45892Z"
                                fill="white"/>
                        </svg>
                    </div>
                    <ul class="bf-obj bf-obj3">
                        @if(!$b3_before_items->isEmpty() && count($b3_before_items) == count($b3_after_items))
                            @foreach($b3_before_items as $b3_items)
                                <li @if($loop->first) class="active" @endif
                                data-imageB="{{ $b3_items->getUrl() }}"
                                    data-imageA="{{ $b3_after_items[$loop->index]->getUrl() }}">{{ $loop->iteration }}
                                </li>
                            @endforeach
                        @endif
                    </ul>
                    <div class="bf-arr bf-arr_r">
                        <svg width="5" height="9" viewBox="0 0 5 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M0.0859108 8.45892C0.0286369 8.3988 0 8.32966 0 8.2515C0 8.17335 0.0286369 8.10421 0.0859108 8.04409L3.4622 4.5L0.0859108 0.955912C0.0286369 0.895792 0 0.826653 0 0.748496C0 0.67034 0.0286369 0.601202 0.0859108 0.541081L0.515464 0.0901804C0.572738 0.0300598 0.638603 0 0.713059 0C0.787515 0 0.853379 0.0300598 0.910653 0.0901804L4.91409 4.29258C4.97136 4.35271 5 4.42184 5 4.5C5 4.57816 4.97136 4.64729 4.91409 4.70742L0.910653 8.90982C0.853379 8.96994 0.787515 9 0.713059 9C0.638603 9 0.572738 8.96994 0.515464 8.90982L0.0859108 8.45892Z"
                                fill="white"/>
                        </svg>
                    </div>
                </div> --}}
            </div>
        </div>
        <div class="retouch__block-col">
            <ul class="type__features">
                {!! trans('canvas.photo_work_desc_list3') !!}
            </ul>
            {{-- <div class="price-block">
                <div class="price-block--inner">
                    <div class="price-amout">
                        {!! trans('canvas.photo_work_pay_desc3') !!}
                    </div>
                    <div class="price-note">
                        <span>*</span>
                        {!! trans('canvas.photo_work_pay_title3') !!}
                    </div>
                </div>
            </div> --}}
        </div>
    </div>
</div>
