<div class="about__block about__third hidden-block brief-v">
    <div class="about-size--inner">
        @php
            $ps_items = $item->getMedia('prices_sizes');
        @endphp
        @if(!$ps_items->isEmpty())
            @foreach($ps_items as $ps_item)
                @php
                    $mediaLocale = Config::get('app.locale');
                    $mediaAlt = $ps_item->getCustomProperty('image_alt_'.$mediaLocale);
                    $mediaTitle = $ps_item->getCustomProperty('image_title_'.$mediaLocale);
                @endphp
                <figure class="size-item hb_alt">
                    <picture>
                        @php
                            $canvasPriceSizeImageSources = image_picture_sources($ps_item->getUrl(), true);
                        @endphp
                        @if(!empty($canvasPriceSizeImageSources['src_webp']))
                            <source srcset="{{ $canvasPriceSizeImageSources['src_webp'] }}" type="image/webp">
                        @endif
                        @if(!empty($canvasPriceSizeImageSources['src']) && !empty($canvasPriceSizeImageSources['type']))
                            <source srcset="{{ $canvasPriceSizeImageSources['src'] }}" type="{{ $canvasPriceSizeImageSources['type'] }}">
                        @endif
                        <img
                            src="{{ $canvasPriceSizeImageSources['src'] }}"
                            loading="lazy"
                            @altAttrs($item, 'media:prices_sizes', $ps_item->getUrl(), null, $mediaAlt, $mediaTitle)
                        />
                    </picture>
                    @if ($mediaTitle)
                        <figcaption class="examples-slide__title" style="color: black;">
                            {{ str_trans($mediaTitle) }}
                        </figcaption>
                    @endif
                    <p class="examples-size">
                        <svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                  d="M5.65417 5H5.1875V5.46667V10.1333H6.12083V6.5933L9.99085 10.4633L10.6508 9.80335L6.7808 5.93333H10.3208V5H5.65417ZM18.7208 5H19.1875V5.46667V10.1333H18.2542V6.5933L14.3841 10.4633L13.7242 9.80335L17.5942 5.93333H14.0542V5H18.7208ZM19.1875 19H18.7208H14.0542V18.0667H17.5942L13.7242 14.1966L14.3841 13.5367L18.2542 17.4067V13.8667H19.1875V18.5333V19ZM5.65417 19H5.1875V18.5333V13.8667H6.12083V17.4067L9.99085 13.5367L10.6508 14.1966L6.7808 18.0667H10.3208V19H5.65417Z"
                                  fill="#FA7846"/>
                        </svg>
                        <span>
                            @if ($ps_item->getCustomProperty('size'))
                                <span>{{ str_trans($ps_item->getCustomProperty('size')) }} {{ trans('gl.cm') }}</span>
                            @endif
                        </span>
                    </p>
                    <a href="#"
                       class="examples-slide__btn js-examples"
                       @if ($mediaTitle) data-style="{{ str_trans($mediaTitle) }}" @endif
                       @if ($ps_item->getCustomProperty('size')) data-size="{{ str_trans($ps_item->getCustomProperty('size')) }}" @else data-size="40x60" @endif
                    >{{ trans('homepage_new.our_portraits_btn_title') }}</a>
                </figure>
            @endforeach
        @endif
    </div>
    <div class="about--notes">
        <div class="about--note">
            <span>*</span>
            <p>{{ trans('canvas.tab3__bot__text') }} </p>
        </div>
    </div>
    @if(!$ps_items->isEmpty())
        @if(count($ps_items)>3)
            <div class="hidden-trigger">
                <a class="anchor ellipse-arrow ellipse-arrow_white" aria-label="anchor link">
                    <i class="fa-arrow-down"></i>
                </a>
                <span>{{ trans('canvas.tabs_show_btn_text') }}</span>
                <span>{{ trans('canvas.tabs_hide_btn_text') }}</span>
            </div>
        @endif
    @endif
</div>
