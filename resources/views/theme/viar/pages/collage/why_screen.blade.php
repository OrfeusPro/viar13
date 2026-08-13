    <!-- why collage -->


    <div class="collage-why__screen">
        <div class="section-frame">
            <div class="collage-why--inner">
                <div class="page-title--row">
                    <h2 class="page-title page-collage-title">
                        {!! trans('collage_new.z_why_screen_text1') !!}
                    </h2>
                    <div class="col-subtitle">
                        {!! trans('collage_new.z_why_screen_text2') !!}
                    </div>
                    <div class="col-subtitle-m">
                        {!! trans('collage_new.z_why_screen_text3') !!}
                    </div>
                </div>
                <div class="collage-why--content">

				@if($ACollageWhyScreen)
					@foreach($ACollageWhyScreen as $why_screen)

                    <div class="services-item @if($why_screen->type == 0) col-service-item @else col-service-item-b @endif">
                        <div class="services-item__title">{{$why_screen->getTranslatedAttribute('title')}}</div>
                        <a href="#generator" class="services-photo">
                            <picture>
								@if(Voyager::image($why_screen->image))
									@if($webpSrc = image_webp_url("storage/".$why_screen->image))
										<source media="(max-width: 425px)" srcset="{{ $webpSrc }}" type="image/webp">
									@endif
									<source media="(max-width: 425px)" srcset="{{Voyager::image($why_screen->image)}}" type="image/jpeg">
									@if($webpSrc = image_webp_url("storage/".$why_screen->image))
										<source srcset="{{ $webpSrc }}" type="image/webp">
									@endif
									<source srcset="{{Voyager::image($why_screen->image)}}" type="image/jpeg">
									<img width="315" height="450" src="{{Voyager::image($why_screen->image)}}" data-src="{{Voyager::image($why_screen->image)}}" @altAttrs($why_screen, 'image', data_get($why_screen, 'image')) loading="lazy">
								@endif
                            </picture>
                            <div class="a">{!! trans('collage_new.more') !!}<i class="fa-arrow-next"></i>
                            </div>
                        </a>
                        <div class="services-info">
                            <p>{{$why_screen->getTranslatedAttribute('text')}}</p>
                        </div>
                        {{-- <a href="#generator" class="top-btn">{!! trans('collage_new.z1_popular_screen_btn') !!}</a> --}}
                    </div>

					@endforeach
				@endif
                </div>

                <a href="#" class="top-all hidden ">
                    <picture>
                        <source srcset="{{ asset('./images/icon/load-more.webp') }}" type="image/webp">
                        <source srcset="{{ asset('./images/icon/load-more.png') }}">
                        <img src="{{ asset('./images/icon/load-more.png') }}" alt="img" loading="lazy">
                    </picture>
                    <span>{!! trans('collage_new.more_portraits') !!}</span>
                </a>
            </div>
        </div>
    </div>
