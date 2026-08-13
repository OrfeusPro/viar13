<div class="about__block about__fourth hidden-block brief-v">
    <div class="about-examples">
        @php
            $s_items = $item->getMedia('tab_examples');
        @endphp
        <div class="examples-about__slider">
            @if(!$s_items->isEmpty())
                @foreach($s_items as $s_item)
                    <figure class="examples-slide">
                        <a href="{{ $s_item->getUrl() }}" class="examples-slide__photo hb_alt"
                           data-fancybox="examples" aria-label="">
                            <picture>
                                @php
                                    $canvasTabExampleImageSources = image_picture_sources($s_item->getUrl(), true);
                                @endphp
                                @if(!empty($canvasTabExampleImageSources['src_webp']))
                                    <source srcset="{{ $canvasTabExampleImageSources['src_webp'] }}" type="image/webp">
                                @endif
                                @if(!empty($canvasTabExampleImageSources['src']) && !empty($canvasTabExampleImageSources['type']))
                                    <source srcset="{{ $canvasTabExampleImageSources['src'] }}" type="{{ $canvasTabExampleImageSources['type'] }}">
                                @endif
                                <img data-lazy="{{ $canvasTabExampleImageSources['src'] }}"
                                loading="lazy"
                                @altAttrs($item, 'media:tab_examples', $s_item->getUrl(), null, $s_item->getCustomProperty('image_alt_' . app()->getLocale()), $s_item->getCustomProperty('image_title_' . app()->getLocale()))
                                />
                            </picture>
                        </a>
                        <figcaption class="examples-slide__title">
                            @if ($s_item->getCustomProperty('name'))
                                 {{ str_trans($s_item->getCustomProperty('image_title_'.Config::get('app.locale'))) }}
                            @endif
                        </figcaption>
                        <div class="examples-size">
                            <svg class="happy-item__icon">
                                <use xlink:href="{{ asset(env('THEME').'sprite.svg#size') }}"></use>
                            </svg>
                            @if ($s_item->getCustomProperty('size'))
                                <p>{{ $s_item->getCustomProperty('size') }}</p>
                            @endif
                        </div>
                        <a href="#" class="examples-slide__btn js-examples"
                        data-catid="1" 
                           @if ($s_item->getCustomProperty('name')) data-style="{{ str_trans($s_item->getCustomProperty('name')) }}" @endif
                           @if ($s_item->getCustomProperty('size')) data-size="{{ str_trans($s_item->getCustomProperty('size')) }}" @else data-size="40x60" @endif
                        >{{ trans('homepage_new.our_portraits_btn_title') }}</a>
                    </figure>
                @endforeach
            @endif
        </div>
        <div class="examples-arrow">
            <a href="#" class="examples-prev examples-prev1" aria-label="examples prev">
                <svg width="48" height="51" viewBox="0 0 48 51" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M27.2335 31.1145C27.334 31.0141 27.3842 30.8986 27.3842 30.768C27.3842 30.6374 27.334 30.5219 27.2335 30.4215L21.3122 24.5001L27.2335 18.5788C27.334 18.4784 27.3842 18.3629 27.3842 18.2323C27.3842 18.1017 27.334 17.9862 27.2335 17.8857L26.4802 17.1324C26.3797 17.0319 26.2642 16.9817 26.1336 16.9817C26.0031 16.9817 25.8876 17.0319 25.7871 17.1324L18.7659 24.1536C18.6655 24.254 18.6152 24.3696 18.6152 24.5001C18.6152 24.6307 18.6655 24.7462 18.7659 24.8467L25.7871 31.8679C25.8876 31.9683 26.0031 32.0186 26.1336 32.0186C26.2642 32.0186 26.3797 31.9683 26.4802 31.8679L27.2335 31.1145Z"
                        fill="#fff"/>
                </svg>
            </a>
            <a href="#" class="examples-next examples-next1" aria-label="examples next">
                <svg width="48" height="51" viewBox="0 0 48 51" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M20.7665 31.1145C20.666 31.0141 20.6158 30.8986 20.6158 30.768C20.6158 30.6374 20.666 30.5219 20.7665 30.4215L26.6878 24.5001L20.7665 18.5788C20.666 18.4784 20.6158 18.3629 20.6158 18.2323C20.6158 18.1017 20.666 17.9862 20.7665 17.8857L21.5198 17.1324C21.6203 17.0319 21.7358 16.9817 21.8664 16.9817C21.9969 16.9817 22.1124 17.0319 22.2129 17.1324L29.2341 24.1536C29.3345 24.254 29.3848 24.3696 29.3848 24.5001C29.3848 24.6307 29.3345 24.7462 29.2341 24.8467L22.2129 31.8679C22.1124 31.9683 21.9969 32.0186 21.8664 32.0186C21.7358 32.0186 21.6203 31.9683 21.5198 31.8679L20.7665 31.1145Z"
                        fill="#fff"/>
                </svg>
            </a>
        </div>
    </div>
</div>
