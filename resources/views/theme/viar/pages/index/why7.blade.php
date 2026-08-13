@php
    /** @var \Illuminate\Support\Collection|\App\Models\SiteImage[] $siteImages */
    $siteImages = $siteImages ?? \App\Models\SiteImage::where("is_show",true)->get();
    $banner1 = site_image('home_why_block', 'images/why-picture-girl.png', ['collection' => $siteImages]);
    $bgPng  = $banner1['src'];
    $bgWebp = $banner1['src_webp'] ?? null;
@endphp


<section class="why">
    <div class="section-frame">
        <div class="top-title">
            @if (Route::currentRouteName() == 'delivery_page')
                <div class="page-title ">{!! trans('homepage_new.why_pic_title') !!}</div>
            @elseif (Route::currentRouteName() == 'home')
                <h2 class="page-title ">{!! trans('homepage_new.why_pic_title') !!}</h2>
            @elseif (Route::currentRouteName() == 'about')
                <div class="page-title ">{!! trans('homepage_new.why_pic_title') !!}</div>
            @else
                <div class="page-title ">{!! trans('homepage_new.why_pic_title') !!}</div>
            @endif


            <p>{!! trans('homepage_new.why_pic_desc') !!}</p>
        </div>
        <div class="why-list">
            <div class="why-box">
                <div class="why-item">
                    <p>{!! trans('homepage_new.why_pic_text1') !!}</p>
                </div>
                <div class="why-item">
                    <p>{!! trans('homepage_new.why_pic_text2') !!}</p>
                </div>
                <div class="why-item">
                    <p>{!! trans('homepage_new.why_pic_text3') !!}</p>
                </div>
                <div class="why-item">
                    <p>{!! trans('homepage_new.why_pic_text4') !!}</p>
                </div>
                <div class="why-item">
                    <p>{!! trans('homepage_new.why_pic_text5') !!}</p>
                </div>
            </div>
            <div class="why-box">
                <div class="why-item">
                    <p>{!! trans('homepage_new.why_pic_text6') !!}</p>
                </div>
                <div class="why-item">
                    <p>{!! trans('homepage_new.why_pic_text7') !!}</p>
                </div>
                <div class="why-item">
                    <p>{!! trans('homepage_new.why_pic_text8') !!}</p>
                </div>
                <div class="why-item">
                    <p>{!! trans('homepage_new.why_pic_text9') !!}</p>
                </div>
                <div class="why-item">
                    <p>{!! trans('homepage_new.why_pic_text10') !!}</p>
                </div>
            </div>
            <div class="why-photo-box">
                <picture>
                    @if($bgWebp)
                        <source srcset="{{ $bgWebp }}" type="image/webp">
                    @endif
                    <source srcset="{{ $bgPng }}">
                    <img src="{{ $bgPng }}" class="why-photo" alt="img"
                        loading="lazy">
                </picture>
                <div class="why-gift">
                    <img src="{{ asset('images/icon/gift.svg') }}" alt="img" loading="lazy">
                    <p>{!! trans('homepage_new.why_pic_gift_text') !!}</p>
                </div>
            </div>
        </div>

		@include(env('THEME_RESOURCES') . 'pages.index.why7_form')

    </div>
</section>
