<div class="pop-painters">
    <div class="section-frame">
        <div class="pop-painters__inner">
            <h2 class="paintners-title page-title">
                @lang("gallery.rep_popular_painters")
            </h2>
            <div class="pp-list">
                <div class="pp-list-wrapper swiper-wrapper">

					@foreach ($painters as $painter)
                    <div class="cp-item swiper-slide">
                        <div class="cp-item-inner">
                            <div class="cp-item-inner-b">
                                <a href="{{ route('hb.gallery.category', ["type"=> $type, "category"=> $painter->url]) }}" class="mm-btn">@lang("gallery.see")</a>
                            </div>
                            <picture>
                                @if($painter->image)
                                    @if ($webpSrc = image_webp_url("storage/".$painter->image))
                                        <source srcset="{{ $webpSrc }}" type="image/webp">
                                    @endif
                                	<source srcset="{{ Voyager::image($painter->image) }}" type="image/jpeg">
                                	<img width="433" height="583" src="{{ Voyager::image($painter->image) }}" @altAttrs($painter, 'image', data_get($painter, 'image')) loading="lazy">
								@else
									<source srcset="{{ asset(env('THEME').'images') }}/gallery/7.jpg" type="image/jpeg">
                                	<img width="433" height="583" src="{{ asset(env('THEME').'images') }}/gallery/7.jpg" alt="{{ $painter->name }}" title="{{ $painter->name }}" loading="lazy">
								@endif
                            </picture>
                        </div>
                        <p>{{ $painter->name }}</p>
                    </div>
					@endforeach
                </div>
                <div class="swiper-button swiper-prev">
                    <svg width="12" height="19" viewBox="0 0 12 19" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M11 18L2 9.5L11 1" stroke="#1E2533" stroke-width="2" />
                    </svg>
                </div>
                <div class="swiper-button swiper-next">
                    <svg width="12" height="19" viewBox="0 0 12 19" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M0.999999 0.999999L10 9.5L1 18" stroke="white" stroke-width="2" />
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>
