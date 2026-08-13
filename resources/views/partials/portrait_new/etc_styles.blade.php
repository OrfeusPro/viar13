<section class="others">
    <div class="section-frame">
        <div class="page-title others-title">
            {{ trans('portrait.tabs_etc_styles_but') }}
        </div>
        <div class="others-slider--wrapper">
            <div class="others-slider">
                @if($etc_styles_items)
                    @foreach($etc_styles_items as $item)
                    @php
                    switch ($item->getTranslatedAttribute('slug')) {
                        case 'simpsons-portrait':
                            $item_url = route('simpsons');
                            break;

                        case 'portrait-caricature':
                            $item_url = route('caricature');
                            break;

                        default:
                            $item_url = $item->getTranslatedAttribute('slug');
                            break;
                    }
                    @endphp
                        <div class="arts__item">
                    <div class="arts__title h2_old">{!! $item->getTranslatedAttribute('name') !!}</div>
                    <div class="arts__image arts__image_vertical">
                        <picture>
                            <source srcset="{{ asset('images/vertical.webp') }}" type="image/webp"/>
                            <img data-src="images/vertical.webp" src="{{ asset('images/vertical.webp') }}" alt=""
                                 class="arts__bg"/>
                        </picture>
                        <picture class="arts__product-image">
                            @php
                                $portraitStyleImageSources = image_picture_sources(data_get($item, 'etc_style_image'), true);
                            @endphp
                            @if(!empty($portraitStyleImageSources['src_webp']))
                                <source srcset="{{ $portraitStyleImageSources['src_webp'] }}" type="image/webp">
                            @endif
                            @if(!empty($portraitStyleImageSources['src']) && !empty($portraitStyleImageSources['type']))
                                <source srcset="{{ $portraitStyleImageSources['src'] }}" type="{{ $portraitStyleImageSources['type'] }}">
                            @endif
                            <img class="" src="{{ $portraitStyleImageSources['src'] }}" @altAttrs($item, 'etc_style_image', data_get($item, 'etc_style_image'))/>
                        </picture>
                    </div>
                    <div class="arts__description">
                    {!! $item->getTranslatedAttribute('short_desc') !!}
                    </div>
                    <p class="arts__price">{{ trans('gl.price_from_text') }} <span>{{ $item->price_from }}€</span></p>
                    <a href="{{ $item_url }}" class="arts__btn">{{ trans('portrait.tabs_etc_styles_but') }}</a>
                </div>
                    @endforeach
                @endif
            </div>
            <div class="examples-arrow">
                <a href="#" class="examples-prev examples-prev3 c-ex-prev" aria-label="examples prev">
                    <i class="fa-arrow-prev"></i>
                </a>
                <a href="#" class="examples-next examples-next3 c-ex-next" aria-label="examples next">
                    <i class="fa-arrow-next"></i>
                </a>
            </div>
        </div>
    </div>
</section>
