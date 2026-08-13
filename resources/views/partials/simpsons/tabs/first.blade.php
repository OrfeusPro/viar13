<div class="about__block about__first hidden-block">
    <div class="about-portrait">
        <picture>
            <source srcset="{{ asset('images/about-borderB.webp') }}" type="image/webp"/>
            <source srcset="{{ asset('images/about-borderB.png') }}" type="image/png"/>
            <img class="about-border big-border" src="{{ asset('images/about-borderB.png') }}" alt=""/>
        </picture>
        <div class="about__portrait-img">
            @if($item->new_main_image)
                <picture>
                    @if($webpSrc = image_webp_url("storage/".$item->new_main_image))
                        <source srcset="{{ $webpSrc }}" type="image/webp"/>
                    @endif
                    <source srcset="{{ Voyager::image($item->new_main_image) }}"/>
                    <img src="{{ Voyager::image($item->new_main_image) }}" @altAttrs($item, 'new_main_image', data_get($item, 'new_main_image'))/>
                </picture>
            @endif
        </div>
    </div>
    <div class="about-text">
        {!! $item->getTranslatedAttribute('description') !!}
    </div>
</div>
