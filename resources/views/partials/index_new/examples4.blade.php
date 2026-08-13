<section class="examples" id="examples">
    <div class="section-frame">
        <h2 class="page-title page-title_white">{{ trans('homepage_new.our_portraits_ex_title') }}</h2>
        <div class="examples-wrap">
            <div class="examples-slider">
                @if($work_ex)
                    @foreach($work_ex as $item)
                        <div class="examples-slide">
                            <a href="{{ Voyager::image($item['image']) }}" class="examples-slide__photo"
                                data-fancybox="examples" aria-label="examples slide link">
                                <picture>
                                    @php
                                        $homeExampleImageSources = image_picture_sources(data_get($item, 'image'), true);
                                    @endphp
                                    @if(!empty($homeExampleImageSources['src_webp']))
                                        <source srcset="{{ $homeExampleImageSources['src_webp'] }}" type="image/webp">
                                    @endif
                                    @if(!empty($homeExampleImageSources['src']) && !empty($homeExampleImageSources['type']))
                                        <source srcset="{{ $homeExampleImageSources['src'] }}" type="{{ $homeExampleImageSources['type'] }}">
                                    @endif
                                    <img src="{{ $homeExampleImageSources['src'] }}" @altAttrs(['type' => \App\Models\NewhomeWorkEx::class, 'id' => $item['id'] ?? null], 'image', data_get($item, 'image'))
                                        loading="lazy">
                                </picture>
                            </a>
                            <a href="#" class="examples-slide__title">{{ $item->getTranslatedAttribute('title') }}</a>
                            <div class="examples-size">
                                <svg class="happy-item__icon">
                                    <use xlink:href="{{ asset(env('THEME').'sprite.svg#size') }}"></use>
                                </svg>
                                <p>{{ $item->getTranslatedAttribute('size') }}</p>
                            </div>
                            <a href="#" class="examples-slide__btn js-examples"
                                data-catid="{{ $item->catid }}"
                                data-url="{{ $item->caturl }}"
                                data-style="{{ $item->getTranslatedAttribute('title') }}" data-size="{{ $item->getTranslatedAttribute('size') }}">{{ trans('homepage_new.our_portraits_btn_title') }}</a>
                        </div>
                    @endforeach
                @endif
            </div>

            <div class="examples-arrow">
                <a href="#" class="examples-prev" aria-label="examples prev">
                    <i class="fa-arrow-prev"></i>
                </a>
                <a href="#" class="examples-next" aria-label="examples next">
                    <i class="fa-arrow-next"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<div class="ellipse ellipse_black">
    <img src="{{ asset(env('THEME').'images/icon/ellipse-black.svg') }}" alt="img" loading="lazy">
</div>
