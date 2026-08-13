    @include(env('THEME_RESOURCES') . 'pages.about.breads')

    @php
        $areaServedCountries = [
            ['@type' => 'Country', 'name' => trans('countries.lv')],
            ['@type' => 'Country', 'name' => trans('countries.lt')],
            ['@type' => 'Country', 'name' => trans('countries.es')],
            ['@type' => 'Country', 'name' => trans('countries.pl')],
            ['@type' => 'Country', 'name' => trans('countries.ge')],
            ['@type' => 'Country', 'name' => trans('countries.fl')],
            ['@type' => 'Country', 'name' => trans('countries.nl')],
        ];

        /** @var \Illuminate\Support\Collection|\App\Models\SiteImage[] $siteImages */
        $siteImages = $siteImages ?? \App\Models\SiteImage::where("is_show",true)->get();
        $banner1 = site_image('about_slide', env('THEME').'images/contacts/about-bg.jpg', ['collection' => $siteImages]);
        $bgJpg  = $banner1['src'];
        $bgWebp = $banner1['src_webp'] ?? null;
        $aboutSchemaTitle = trans('about.text_1_1');
        if (!$aboutSchemaTitle || $aboutSchemaTitle === 'about.text_1_1') {
            $aboutSchemaTitle = trans('settings.site_name');
        }
        $brandSchema = site_brand_schema([
            'name' => $aboutSchemaTitle,
            'url' => url('/'),
            'logo' => asset('img/icons/logo.svg'),
            'areaServed' => $areaServedCountries,
            'contactPoint' => site_brand_contact_points([
                trans('header_footer_new.footer_phone_clean'),
            ], 'orders@viarcanvas.com', $areaServedCountries),
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => trans('header_footer_new.footer_org_streetAddress'),
                'addressLocality' => trans('header_footer_new.footer_org_addressLocality'),
                'addressCountry' => trans('header_footer_new.footer_org_addressCountry'),
                'postalCode' => trans('header_footer_new.footer_org_postalCode'),
            ],
        ]);
    @endphp

    <style>
    .about-main {
        background-image: url('{{ $bgJpg }}');
    }
    @if(!empty($bgWebp))
    @supports (background-image: url("image.webp")) {
        .about-main {
            background-image: url('{{ $bgWebp }}');
        }
    }
    @endif
    </style>

    <script type="application/ld+json">
        {!! json_encode($brandSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
    <div class="about-main">
        <div class="section-frame">
            <div class="about-main__inner">
                <div class="about-main__title">
                    <h1 class="page-title about-title">
                        @lang("about.text_1_1")
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <div class="about-text">
        <div class="section-frame">
            <div class="about-text__inner">
                <div class="about-text__row">
                    <div class="about-text__title">
                        <h2 class="about-text__b">
                            @lang("about.text_2_1")
                        </h2>
                        <p>@lang("about.text_2_2")</p>
                    </div>
                    <div class="about-text__content text-flex">
                        <div class="about-text__col">
                            @lang("about.text_2_3")
                        </div>
                        <div class="about-text__col">
                            @lang("about.text_2_4")
                        </div>
                    </div>
                </div>
                <div class="about-text__row">
                    <div class="about-text__title">
                        <h2 class="about-text__b">
                            @lang("about.text_3_1")
                        </h2>
                        <p>@lang("about.text_3_2")</p>
                    </div>
                    <div class="about-text__content">
                        @lang("about.text_3_3")
                    </div>
                </div>
                <div class="about-text__row">
                    <div class="about-text__title">
                        <h2 class="about-text__b">
                            @lang("about.text_4_1")
                        </h2>
                        <p>@lang("about.text_4_2")</p>
                    </div>
                    <div class="about-text__content">
                        @lang("about.text_4_3")
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="about-quality">
        <div class="custom-shape-divider-top-1665087820">
            <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M600,112.77C268.63,112.77,0,65.52,0,7.23V120H1200V7.23C1200,65.52,931.37,112.77,600,112.77Z" class="shape-fill"></path>
            </svg>
        </div>
        <div class="section-frame about-frame">
            <div class="about-quality__inner">
                <h2 class="page-title about-quality__title">
                    @lang("about.text_5_1")
                </h2>
                <p>
                    @lang("about.text_5_2")
                </p>
                <div class="about-video">
                    <div class="pmo-block">
                        @lang("about.text_5_3")
                    </div>
                    <div class="video-container">

                        <video width="1128" height="500" id="videoPlayer" preload="none" poster="{{ asset( 'storage/'.$page['video_img'] ) }}" controls="controls">
                          <source src="@isset(json_decode($page['video_file'])[0]->download_link){{ asset( 'storage/'.json_decode($page['video_file'])[0]->download_link ) }}@endisset" type="video/mp4">
                        </video>
                        <div class="video-btn">
                          <img width="120" height="120" src="{{ asset(env('THEME') . 'images') }}/play.svg" alt="">
                        </div>
                      </div>
                </div>

            </div>
        </div>
    </div>

    <div class="about-team">
        <div class="section-frame about-frame">
            <div class="about-team__inner">
                <h2 class="page-title about-team__title">
                    @lang("about.text_6_1")
                </h2>
                <div class="at-blocks">
                    @foreach($OurTeam as $item)
                        <div class="at-block">
                            <div class="at-img">
                                @php
                                    $teamImageSources = image_picture_sources(data_get($item, 'image'), true);
                                @endphp
                                <picture>
                                    @if(!empty($teamImageSources['src_webp']))
                                        <source srcset="{{ $teamImageSources['src_webp'] }}" type="image/webp">
                                    @endif
                                    @if(!empty($teamImageSources['src']) && !empty($teamImageSources['type']))
                                        <source srcset="{{ $teamImageSources['src'] }}" type="{{ $teamImageSources['type'] }}">
                                    @endif
                                    <img width="120" height="120" src="{{ $teamImageSources['src'] }}" @altAttrs(['type' => \App\Models\OurTeam::class, 'id' => data_get($item, 'id')], 'image', data_get($item, 'image'), null, data_get($item, 'title'))>
                                </picture>
                            </div>
                            <div class="at-name">
                                {{ $item['title'] }}
                            </div>
                            <div class="at-about">
                                <span>{{ $item['job_title'] }}</span>
                                <ul>
                                    @if($item['link_facebook'])
                                    <li>
                                        <a href="{{ $item['link_facebook'] }}" target="_blank">
                                        <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect width="30" height="30" rx="15" fill="#FC8C5F"/>
                                        <path d="M18.2676 15.875L18.6504 13.3594H16.2168V11.7188C16.2168 11.0078 16.5449 10.3516 17.6387 10.3516H18.7598V8.19141C18.7598 8.19141 17.748 8 16.791 8C14.7949 8 13.4824 9.23047 13.4824 11.418V13.3594H11.2402V15.875H13.4824V22H16.2168V15.875H18.2676Z" fill="white"/>
                                        </svg>

                                        </a>
                                    </li>
                                    @endif
                                    @if($item['link_instagram'])
                                    <li>
                                        <a href="{{ $item['link_instagram'] }}" target="_blank">
                                            <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <rect width="30" height="30" rx="15" fill="#FC8C5F"/>
                                                <path d="M15.0156 11.3906C13.0156 11.3906 11.4219 13.0156 11.4219 14.9844C11.4219 16.9844 13.0156 18.5781 15.0156 18.5781C16.9844 18.5781 18.6094 16.9844 18.6094 14.9844C18.6094 13.0156 16.9844 11.3906 15.0156 11.3906ZM15.0156 17.3281C13.7344 17.3281 12.6719 16.2969 12.6719 14.9844C12.6719 13.7031 13.7031 12.6719 15.0156 12.6719C16.2969 12.6719 17.3281 13.7031 17.3281 14.9844C17.3281 16.2969 16.2969 17.3281 15.0156 17.3281ZM19.5781 11.2656C19.5781 10.7969 19.2031 10.4219 18.7344 10.4219C18.2656 10.4219 17.8906 10.7969 17.8906 11.2656C17.8906 11.7344 18.2656 12.1094 18.7344 12.1094C19.2031 12.1094 19.5781 11.7344 19.5781 11.2656ZM21.9531 12.1094C21.8906 10.9844 21.6406 9.98438 20.8281 9.17188C20.0156 8.35938 19.0156 8.10938 17.8906 8.04688C16.7344 7.98438 13.2656 7.98438 12.1094 8.04688C10.9844 8.10938 10.0156 8.35938 9.17188 9.17188C8.35938 9.98438 8.10938 10.9844 8.04688 12.1094C7.98438 13.2656 7.98438 16.7344 8.04688 17.8906C8.10938 19.0156 8.35938 19.9844 9.17188 20.8281C10.0156 21.6406 10.9844 21.8906 12.1094 21.9531C13.2656 22.0156 16.7344 22.0156 17.8906 21.9531C19.0156 21.8906 20.0156 21.6406 20.8281 20.8281C21.6406 19.9844 21.8906 19.0156 21.9531 17.8906C22.0156 16.7344 22.0156 13.2656 21.9531 12.1094ZM20.4531 19.1094C20.2344 19.7344 19.7344 20.2031 19.1406 20.4531C18.2031 20.8281 16.0156 20.7344 15.0156 20.7344C13.9844 20.7344 11.7969 20.8281 10.8906 20.4531C10.2656 20.2031 9.79688 19.7344 9.54688 19.1094C9.17188 18.2031 9.26562 16.0156 9.26562 14.9844C9.26562 13.9844 9.17188 11.7969 9.54688 10.8594C9.79688 10.2656 10.2656 9.79688 10.8906 9.54688C11.7969 9.17188 13.9844 9.26562 15.0156 9.26562C16.0156 9.26562 18.2031 9.17188 19.1406 9.54688C19.7344 9.76562 20.2031 10.2656 20.4531 10.8594C20.8281 11.7969 20.7344 13.9844 20.7344 14.9844C20.7344 16.0156 20.8281 18.2031 20.4531 19.1094Z" fill="white"/>
                                            </svg>
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                            <div class="at-text">
                                {{ $item['text'] }}
                            </div>
                            <p>*{{ $item['short_text'] }}</p>
                        </div>
                    @endforeach




                    <div class="at-block">
                        <div class="at-logo">
                            <img width="120" height="120" src="{{ asset('img') }}/icons/logo.svg" alt="Viar">
                        </div>
                        <div class="atl-title">
                            @lang("about.text_6_2")
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="about-procces category-pop">
        <div class="section-frame">
            <div class="category-pop__inner">
                <div class="ap-title__row">
                    <h2 class="category-pop__title page-title">
                        @lang("about.text_7_1")
                    </h2>
                </div>
                <div class="category-pop--block">
                    <div class="cp-tabs">
                        <div class="cp-tabs-inner">
							@foreach($OurWorkingProcess as $item)
								<h3 class="cp-tab @if($loop->index==0) active @endif">
									{{ $item->title }}
								</h3>
							@endforeach
                        </div>
                    </div>
                    <div class="cp-blocks">

						@foreach($OurWorkingProcess as $item)
						<div class="cp-block @if(!$loop->first) hidden-block @endif">
							<div class="cp-block-inner">
								@php $images = json_decode($item['photo']); @endphp
								@foreach($images as $image)
									<div class="cp-item" aria-hidden="true" @if($loop->index==0) tabindex="0" @else tabindex="-1" @endif>
										<div class="cp-item-inner">
											<picture>
                                                @if($webpSrc = image_webp_url("storage/".$image))
                                                    <source srcset="{{ $webpSrc }}" type="image/webp">
                                                @endif
												<source srcset="{{ asset(Voyager::image($image)) }}" type="image/jpeg">
												<img width="433" height="583" src="{{ asset(Voyager::image($image)) }}" alt="{{ $item->title }} {{ $item->title }}" loading="lazy">
											</picture>
										</div>
									</div>
								@endforeach
							</div>
						</div>
						@endforeach

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="ellipse">
        <img alt="img" src="{{ asset(env('THEME') . 'images') }}/icon/ellipse-whete.svg" decoding="async" height="99" width="1374">
    </div>

    <div class="about-work">
        <div class="section-frame">
            <div class="about-work__inner">
                <h2 class="page-title about-work__title">
                    @lang("about.text_8_1")
                </h2>
                <p>@lang("about.text_8_2")</p>
                <div class="about-work__block">
                    <div class="aw-block">
                        <span class="num">
                            1
                        </span>
                        <div class="ab-img">
                            <picture>
                                <source srcset="{{ asset(env('THEME') . 'images') }}/contacts/w1.avif" type="image/avif">
                                <source srcset="{{ asset(env('THEME') . 'images') }}/contacts/w1.webp" type="image/webp">
                                <source srcset="{{ asset(env('THEME') . 'images') }}/contacts/w1.jpg" type="image/jpeg">
                                <img width="120" height="120" src="{{ asset(env('THEME') . 'images') }}/contacts/w1.jpg" alt="">
                            </picture>
                        </div>
                        <div class="ab-title">
                            @lang("about.text_8_3")
                        </div>
                        <div class="ab-text">
                            @lang("about.text_8_4")
                        </div>
                        <div class="ab-p">
                            <sup>*</sup>@lang("about.text_8_5")
                        </div>
                    </div>
                    <div class="aw-block">
                        <span class="num">
                            2
                        </span>
                        <div class="ab-img">
                            <picture>
                                <source srcset="{{ asset(env('THEME') . 'images') }}/contacts/w2.avif" type="image/avif">
                                <source srcset="{{ asset(env('THEME') . 'images') }}/contacts/w2.webp" type="image/webp">
                                <source srcset="{{ asset(env('THEME') . 'images') }}/contacts/w2.jpg" type="image/jpeg">
                                <img width="120" height="120" src="{{ asset(env('THEME') . 'images') }}/contacts/w2.jpg" alt="">
                            </picture>
                        </div>
                        <div class="ab-title">
                            @lang("about.text_8_6")
                        </div>
                        <div class="ab-text">
                            @lang("about.text_8_7")
                        </div>
                        <div class="ab-p">
                            <sup>*</sup>@lang("about.text_8_8")
                        </div>
                    </div>
                    <div class="aw-block">
                        <span class="num">
                            3
                        </span>
                        <div class="ab-img">
                            <picture>
                                <source srcset="{{ asset(env('THEME') . 'images') }}/contacts/w3.avif" type="image/avif">
                                <source srcset="{{ asset(env('THEME') . 'images') }}/contacts/w3.webp" type="image/webp">
                                <source srcset="{{ asset(env('THEME') . 'images') }}/contacts/w3.jpg" type="image/jpeg">
                                <img width="120" height="120" src="{{ asset(env('THEME') . 'images') }}/contacts/w3.jpg" alt="">
                            </picture>
                        </div>
                        <div class="ab-title">
                            @lang("about.text_8_9")
                        </div>
                        <div class="ab-text">
                            @lang("about.text_8_10")
                        </div>
                        <div class="ab-p">
                            <sup>*</sup>@lang("about.text_8_11")
                        </div>
                    </div>
                    <div class="aw-block">
                        <span class="num">
                            4
                        </span>
                        <div class="ab-img">
                            <picture>
                                <source srcset="{{ asset(env('THEME') . 'images') }}/contacts/w4.avif" type="image/avif">
                                <source srcset="{{ asset(env('THEME') . 'images') }}/contacts/w4.webp" type="image/webp">
                                <source srcset="{{ asset(env('THEME') . 'images') }}/contacts/w4.jpg" type="image/jpeg">
                                <img width="120" height="120" src="{{ asset(env('THEME') . 'images') }}/contacts/w4.jpg" alt="">
                            </picture>
                        </div>
                        <div class="ab-title">
                            @lang("about.text_8_12")
                        </div>
                        <div class="ab-text">
                            @lang("about.text_8_13")
                        </div>
                        <div class="ab-p">
                            <sup>*</sup>@lang("about.text_8_14")
                        </div>
                    </div>
                </div>
                <a href="#" class="ab-btn">@lang("about.text_8_15")</a>
            </div>
        </div>
    </div>


    @include(env('THEME_RESOURCES') . 'pages.index.services2')
    @include(config('theme.resource') . 'pages.index.faq9')
