

<section class="@isset($class) {{ $class }} @else services @endif" id="services">
    <div class="section-frame">
        @if (Route::currentRouteName() == 'delivery_page')
            <h2 class="page-title " >{!! trans('pages.delivery_services_title') !!}</h2>
        @elseif (Route::currentRouteName() == 'home')
            <h2 class="page-title " >{!! trans('homepage_new.our_services_title') !!}</h2>
        @elseif (Route::currentRouteName() == 'about')
            <h2 class="page-title " >{!! trans('about.services_title') !!}</h2>
        @elseif (Route::currentRouteName() == 'faq')
            <h2 class="page-title " >{!! trans('pages.faq_services_title') !!}</h2>
        @elseif (Route::currentRouteName() == 'collage')
            <div class="page-title " >{!! trans('pages.delivery_services_title') !!}</div>
        @else
            <h2 class="page-title " >{!! trans('homepage_new.our_services_title') !!}</h2>
        @endif
        @if($services)
        <div class="services-list">
                @foreach($services as $item)
                    <a href="{{ $item->getTranslatedAttribute('link') }}" class="services-item @if($loop->index>8) services-item_hide @endif">
                        <div class="services-item__title">{{ $item->getTranslatedAttribute('title') }}</div>
                        <div class="services-photo">
                            <picture>
                                {{-- <source srcset="{{ Voyager::image($item->thumbnail('medium')) }}"
                                    type="image/webp"> --}}
                                <img class="lozad" width="315" height="450"
                                    src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                                    data-src="{{ Voyager::image($item['image']) }}" @altAttrs(['type' => \App\Models\NewhomeService::class, 'id' => $item['id'] ?? null], 'image', data_get($item, 'image'))
                                    loading="lazy">
                            </picture>
                            <div class="a">{{ trans('homepage_new.our_services_details') }} <i class="fa-arrow-next"></i>
                            </div>
                        </div>
                        <div class="services-info">
                            <div class="services-info__title">{{ $item->getTranslatedAttribute('name') }}</div>
                            <p>{{ $item->getTranslatedAttribute('desc') }}</p>
                        </div>
                    </a>
                @endforeach
        </div>
            @if(count($services)>8)
                <a data-all="{{ trans('homepage_new.our_services_btn_title') }}" data-hide="{{ trans('homepage_new.hide') }}" href="##" class="services-all">{{ trans('homepage_new.our_services_btn_title') }}</a>
            @endif
        @endif
    </div>
</section>
