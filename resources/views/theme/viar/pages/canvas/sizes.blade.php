<div class="mobile-ell">
    <img src="{{ asset('images/sizes/union.png') }}" alt=""/>
    <div class="mobile-size-title">{{ trans('portrait.sizes__title') }}</div>
</div>
<div class="sizes__screen section-p">
    <div class="sizes__screen--inner">
        <picture>
            <source media="(max-width: 43.75em)" srcset="{{ asset('images/sizes/size-bgM.jpg') }}" type="image/jpeg"/>
            <source media="(max-width: 43.75em)" srcset="{{ asset('images/sizes/size-bgM.webp') }}" type="image/webp"/>
            <source srcset="{{ asset('images/sizes/size-bg.webp') }}" type="image/webp"/>
            <source srcset="{{ asset('images/sizes/size-bg.jpg') }}" type="image/jpeg"/>
            <img src="{{ asset('images/sizes/size-bg.jpg') }}" alt="img"/>
        </picture>
        <picture>
            <source media="(max-width: 500px)" srcset="{{ asset('images/sizes/10M.webp') }}" type="image/webp"/>
            <source media="(max-width: 500px)" srcset="{{ asset('images/sizes/10M.png') }}" type="image/png"/>
            <source srcset="{{ asset('images/sizes/10.webp') }}" type="image/webp"/>
            <source srcset="{{ asset('images/sizes/10.png') }}" type="image/png"/>
            <img class="boy" src="{{ asset('images/sizes/10.webp') }}" alt=""/>
        </picture>
        <picture>
            <source srcset="{{ asset('images/sizes/9.webp') }}" type="image/webp"/>
            <source srcset="{{ asset('images/sizes/9.png') }}" type="image/png"/>
            <img class="girl" src="{{ asset('images/sizes/9.webp') }}" alt=""/>
        </picture>
        <div class="size-frame">
            <div class="section-frame">

                <div class="size-title page-title">{{ trans('portrait.sizes__title') }}</div>

            </div>
            <div class="sizes__block hb_alt">
                @php
                    $sz_new_items = $item->getMedia('sizes_bot_items');
                @endphp
                @if(!$sz_new_items->isEmpty())
                    @foreach($sz_new_items as $sz_item)
                        <div class="sizes-item @if($loop->iteration === 1) a @endif
                        @if($loop->iteration === 2) b @endif
                        @if($loop->iteration === 3) c @endif
                        @if($loop->iteration === 4) d @endif
                        @if($loop->iteration === 5) e @endif
                        @if($loop->iteration === 6) f @endif
                        @if($loop->iteration === 7) g @endif
                        @if($loop->iteration === 8) h @endif ">
                            <div class="size-desc">
                                <svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                          d="M5.65417 5H5.1875V5.46667V10.1333H6.12083V6.5933L9.99085 10.4633L10.6508 9.80335L6.7808 5.93333H10.3208V5H5.65417ZM18.7208 5H19.1875V5.46667V10.1333H18.2542V6.5933L14.3841 10.4633L13.7242 9.80335L17.5942 5.93333H14.0542V5H18.7208ZM19.1875 19H18.7208H14.0542V18.0667H17.5942L13.7242 14.1966L14.3841 13.5367L18.2542 17.4067V13.8667H19.1875V18.5333V19ZM5.65417 19H5.1875V18.5333V13.8667H6.12083V17.4067L9.99085 13.5367L10.6508 14.1966L6.7808 18.0667H10.3208V19H5.65417Z"
                                          fill="#FA7846"/>
                                </svg>
                                @if ($sz_item->getCustomProperty('size'))
                                    <span>{{ str_trans($sz_item->getCustomProperty('size')) }}{{ trans('gl.cm') }}</span>
                                @endif
                            </div>
                            <picture>
                                @php
                                    $sizeBotItemImageSources = image_picture_sources($sz_item->getUrl(), true);
                                @endphp
                                @if(!empty($sizeBotItemImageSources['src_webp']))
                                    <source srcset="{{ $sizeBotItemImageSources['src_webp'] }}" type="image/webp">
                                @endif
                                @if(!empty($sizeBotItemImageSources['src']) && !empty($sizeBotItemImageSources['type']))
                                    <source srcset="{{ $sizeBotItemImageSources['src'] }}" type="{{ $sizeBotItemImageSources['type'] }}">
                                @endif
                                <img src="{{ $sizeBotItemImageSources['src'] }}"
                                @altAttrs($item, 'media:sizes_bot_items', $sz_item->getUrl(), null, $sz_item->getCustomProperty('image_alt_' . app()->getLocale()), $sz_item->getCustomProperty('image_title_' . app()->getLocale()))
                                />
                            </picture>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
    <div class="mobile-sizes">
        <div class="mobile-sizes--inner">
            @if(!$sz_new_items->isEmpty())
                <ul>
                    @foreach($sz_new_items as $sz_item)
                        <li>
                            <svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M5.65417 5H5.1875V5.46667V10.1333H6.12083V6.5933L9.99085 10.4633L10.6508 9.80335L6.7808 5.93333H10.3208V5H5.65417ZM18.7208 5H19.1875V5.46667V10.1333H18.2542V6.5933L14.3841 10.4633L13.7242 9.80335L17.5942 5.93333H14.0542V5H18.7208ZM19.1875 19H18.7208H14.0542V18.0667H17.5942L13.7242 14.1966L14.3841 13.5367L18.2542 17.4067V13.8667H19.1875V18.5333V19ZM5.65417 19H5.1875V18.5333V13.8667H6.12083V17.4067L9.99085 13.5367L10.6508 14.1966L6.7808 18.0667H10.3208V19H5.65417Z"
                                    fill="#FA7846"/>
                            </svg>
                            @if ($sz_item->getCustomProperty('size'))
                                <span>{{ str_trans($sz_item->getCustomProperty('size')) }}{{ trans('gl.cm') }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
<div class="ellipse ellipse_top">
    <a href="#composition" class="anchor ellipse-arrow ellipse-arrow_white" aria-label="anchor link">
        <i class="fa-arrow-down"></i>
    </a>
    <img alt="img" src="{{ asset('images/icon/ellipse-whete.svg') }}" decoding="async" height="99" width="1374">
</div>
