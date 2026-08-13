<section class="portraits">
    <div class="portraits-wrap">
        <div class="portraits-slider">
            @if($home_slides)
            @foreach($home_slides as $slide)
            <article class="portraits-slide">
                <picture>
				{{--<source media="(max-width: 700px)" type="image/webp" srcset="{{ format_webp($slide[App::getLocale().'_mob']) }}">--}}
                    <source media="(max-width: 700px)" srcset="{{ Voyager::image($slide[App::getLocale().'_mob']) }}">
						{{--<source type="image/webp" srcset="{{ format_webp($slide[App::getLocale()]) }}">--}}
                    <img class="portraits-bg lozad" src="{{ Voyager::image($slide[App::getLocale()]) }}" alt="">
                </picture>

                <div class="section-frame">
                    <div class="portraits-info">
                        <div class="portraits-heading">
                            <h1 class="portraits-title">
                                {!! trans('homepage_new.slider_title') !!}
                            </h1>
                            <p>{!! trans('homepage_new.slider_desc') !!}</p>
                        </div>
                        <div class="portraits-btn">
                            <a href="#" class="js-examples" data-catid="1">{!! trans('homepage_new.slider_btn1_title') !!}</a>
                            <a href="{{ route('all_styles') }}" class="portraits-btn__style">{!! trans('homepage_new.slider_btn2_title') !!}</a>
                        </div>
                        <div class="portraits-gift">
                            <picture>
                                <source srcset="{{ asset('images/gift.webp') }}" type="image/webp">
                                <source srcset="{{ asset('images/gift.png') }}">
                                <img src="{{ asset('images/gift.png') }}" class="gift-photo" alt="img" loading="lazy" width="85" height="51">
                            </picture>
                            <svg class="gift-photo_mob">
                                <use xlink:href="{{ asset(env('THEME').'sprite.svg#gift') }}"></use>
                            </svg>
                            <p>{!! trans('homepage_new.slider_image_text') !!}</p>
                            <svg class="portraits-gift__arrow">
                                <use xlink:href="{{ asset(env('THEME').'sprite.svg#arrow') }}"></use>
                            </svg>
                        </div>
                    </div>
                </div>
            </article>
            @endforeach
            @endif
        </div>

        <div class="portraits-arrow">
            <a href="#" class="portraits-prev" aria-label="portrait prev">
                <i class="fa-arrow-prev"></i>
            </a>
            <a href="#" class="portraits-next" aria-label="portrait next">
                <i class="fa-arrow-next"></i>
            </a>
        </div>
    </div>

    <div class="section-frame frame-dots">
        <div class="portraits-dots"></div>
    </div>
</section>

<div class="ellipse">
    <a href="#services" class="anchor ellipse-arrow ellipse-arrow_white" aria-label="anchor link">
        <i class="fa-arrow-down"></i>
    </a>
    <img src="{{ asset('images/icon/ellipse-whete.svg') }}" alt="img" loading="eager" >
</div>
