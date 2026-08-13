<section class="top-frame">
    <div class="ellipse ellipse_cream fdfsf">
        <img src="{{ asset(env('THEME').'images/icon/ellipse-cream.svg') }}" alt="img" loading="lazy">
    </div>
    <div class="top">
        <div class="section-frame">
            <div class="top-title">
                @if (Route::currentRouteName() == 'delivery_page')
                    <h2 class="page-title h2_old">{!! trans('homepage_new.top_sales_title') !!}</h2>
                @elseif(Route::currentRouteName() == 'home')
                    <div class="page-title h2_old">{!! trans('homepage_new.top_sales_title') !!}</div>
                @elseif (Route::currentRouteName() == 'about')
                    <h2 class="page-title h2_old">{!! trans('homepage_new.top_sales_title') !!}</h2>
                @else
                    <h2 class="page-title h2_old">{!! trans('homepage_new.top_sales_title') !!}</h2>
                @endif



                <p>{!! trans('homepage_new.top_sales_desc') !!}</p>
            </div>
            <div class="top-list">
                @if($top_work_ex)
                    @foreach($top_work_ex as $item)
                        <div class="top-item @if($loop->index > 7) top-item_hide @endif">
                            <a href="{{ Voyager::image($item['image']) }}" data-fancybox="top"
                                class="top-photo" aria-label="top-photo">
                                <picture>
                                    @php
                                        $homeTopImageSources = image_picture_sources(data_get($item, 'image'), true);
                                    @endphp
                                    @if(!empty($homeTopImageSources['src_webp']))
                                        <source srcset="{{ $homeTopImageSources['src_webp'] }}" type="image/webp">
                                    @endif
                                    @if(!empty($homeTopImageSources['src']) && !empty($homeTopImageSources['type']))
                                        <source srcset="{{ $homeTopImageSources['src'] }}" type="{{ $homeTopImageSources['type'] }}">
                                    @endif
                                    <img class="lozad"
                                        src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs="
                                        data-src="{{ $homeTopImageSources['src'] }}" @altAttrs(['type' => \App\Models\NewhomeTopWorkEx::class, 'id' => $item['id'] ?? null], 'image', data_get($item, 'image'))
                                        loading="lazy">
                                </picture>
                            </a>
                            <a href="{{ $item->getTranslatedAttribute('link') }}" class="top-item__title">{{  $item->getTranslatedAttribute('title') }}</a>
                            <a href="{{ $item->getTranslatedAttribute('link') }}" class="top-btn">{!! trans('homepage_new.top_sales_btn_title') !!}</a>
                        </div>
                    @endforeach
                @endif
            </div>
            <a href="#" class="top-all js_more_wks" data-more="{{ trans('homepage_new.top_sales_more_btn_title') }}" data-less="{{ trans('homepage_new.hide') }}">
                <picture>
                    <source srcset="{{ asset('images/icon/load-more.webp') }}" type="image/webp">
                    <source srcset="{{ asset('images/icon/load-more.png') }}">
                    <img src="{{ asset('images/icon/load-more.png') }}" alt="img" loading="lazy">
                </picture>
                <span>{!! trans('homepage_new.top_sales_more_btn_title') !!}</span>
            </a>
        </div>
    </div>
</section>
