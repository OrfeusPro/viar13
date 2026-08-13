<div class="portraits__wrapper">
    <div class="portraits--info">
        <div class="portraits-heading">
            <h2 class="portraits-title">
                {!! $item->getTranslatedAttribute('name')!!}
            </h2>
            {!! $item->getTranslatedAttribute('short_desc') !!}
        </div>
        <div class="portraits-btn">
            <a href="#generator">{{ trans('portrait.to_order_portrait') }}</a>
            <a href="#" class="js-popup-photo hidden" data-style="{{ $item->getTranslatedAttribute('name') }}">{{ trans('portrait.to_order_portrait') }}</a>
        </div>
    </div>
    <div class="portraits-image">
        <div class="portraits-image__inner">
            @if($item->new_image1)
                <div class="portrait-image">
                    <picture>
                        @if($webpSrc = image_webp_url($item->new_image1))
                            <source srcset="{{ $webpSrc }}" type="image/webp"/>
                        @endif
                        <source srcset="{{ Voyager::image($item->new_image1) }}"/>
                        <img src="{{ Voyager::image($item->new_image1)  }}" @altAttrs($item, 'new_image1', data_get($item, 'new_image1'))/>
                    </picture>
                </div>
            @endif
            @if($item->new_image2)
                <div class="portrait-image">
                    <picture>
                        @if($webpSrc = image_webp_url($item->new_image2))
                            <source srcset="{{ $webpSrc }}" type="image/webp"/>
                        @endif
                        <source srcset="{{ Voyager::image($item->new_image2) }}"/>
                        <img src="{{ Voyager::image($item->new_image2) }}" @altAttrs($item, 'new_image2', data_get($item, 'new_image2'))/>
                    </picture>
                </div>
           @endif
        </div>
        <div class="portraits-images--bg">
            <picture>
                <source srcset="{{ asset('images/portrait-bg.webp') }}" type="image/webp"/>
                <source srcset="{{ asset('images/portrait-bg.png') }}" type="image/png"/>
                <img src="{{ asset('images/portrait-bg.png') }}" alt=""/>
            </picture>
        </div>
    </div>
</div>
