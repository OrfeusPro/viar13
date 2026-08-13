<div class="about__block about__third hidden-block brief-v @if($is_hidden ?? true) hidden-block @endif">
    <div class="about-size--inner">
        @php
            $sp_items = $item->getMedia('new_prices_sizes');
        @endphp
         @if(!$sp_items->isEmpty())
             @foreach($sp_items as $s_item)
                <div class="size-item">
            <picture>
                @php
                    $portraitPagePriceSizeImageSources = image_picture_sources($s_item->getUrl(), true);
                @endphp
                @if(!empty($portraitPagePriceSizeImageSources['src_webp']))
                    <source srcset="{{ $portraitPagePriceSizeImageSources['src_webp'] }}" type="image/webp">
                @endif
                @if(!empty($portraitPagePriceSizeImageSources['src']) && !empty($portraitPagePriceSizeImageSources['type']))
                    <source srcset="{{ $portraitPagePriceSizeImageSources['src'] }}" type="{{ $portraitPagePriceSizeImageSources['type'] }}">
                @endif
                <img src="{{ $portraitPagePriceSizeImageSources['src'] }}" @altAttrs($item, 'media:new_prices_sizes', $s_item->getUrl(), null, $s_item->getCustomProperty('image_alt_' . app()->getLocale()), $s_item->getCustomProperty('image_title_' . app()->getLocale()))/>
            </picture>
            <p>
                <svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                          d="M5.65417 5H5.1875V5.46667V10.1333H6.12083V6.5933L9.99085 10.4633L10.6508 9.80335L6.7808 5.93333H10.3208V5H5.65417ZM18.7208 5H19.1875V5.46667V10.1333H18.2542V6.5933L14.3841 10.4633L13.7242 9.80335L17.5942 5.93333H14.0542V5H18.7208ZM19.1875 19H18.7208H14.0542V18.0667H17.5942L13.7242 14.1966L14.3841 13.5367L18.2542 17.4067V13.8667H19.1875V18.5333V19ZM5.65417 19H5.1875V18.5333V13.8667H6.12083V17.4067L9.99085 13.5367L10.6508 14.1966L6.7808 18.0667H10.3208V19H5.65417Z"
                          fill="#FA7846"/>
                </svg>
                @if ($s_item->getCustomProperty('size'))
                    <span> {{ str_trans($s_item->getCustomProperty('size')) }} {{ trans('gl.cm') }}</span>
                @endif
            </p>@if ($s_item->getCustomProperty('price'))<a href="#" class="price-size"> {{ trans('gl.price_from_text') }} {{ str_trans($s_item->getCustomProperty('price')) }}</a>@endif
        </div>
            @endforeach
        @endif
    </div>
    <div class="about--notes">
        <div class="about--note">
            <span>*</span>
            <p>{{ trans('portrait.tab3__bot__text') }} </p>
        </div>
    </div>
    <div class="hidden-trigger">
        <a href="#examples" class="anchor ellipse-arrow ellipse-arrow_white" aria-label="anchor link">
            <i class="fa-arrow-down"></i>
        </a>
        <span></span>
        <span>{{ trans('portrait.tabs_hide_btn_text') }}</span>
    </div>
</div>
