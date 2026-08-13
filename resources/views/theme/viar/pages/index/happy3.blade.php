@php
    /** @var \Illuminate\Support\Collection|\App\Models\SiteImage[] $siteImages */
    $siteImages = $siteImages ?? \App\Models\SiteImage::where("is_show",true)->get();
    $banner1 = site_image('home_banner_1', 'images/bg/happy-bg.jpg', ['collection' => $siteImages]);
    $bgJpg  = $banner1['src'];
    $bgWebp = $banner1['src_webp'] ?? $bgJpg;
@endphp

<section class="happy lozad" data-background-image="{{ $bgWebp }}, {{ $bgJpg }}">
    <div class="section-frame">
        <h2 class="page-title">{{ trans('homepage_new.you_feel_nice') }}</h2>
        <div class="happy-list">
            <div class="happy-item">
                <img class="lozad"
                    src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs="
                    data-src="{{ asset('images/happy/happy-1.svg') }}" alt="img" loading="lazy">
                <p>{{ trans('homepage_new.you_feel_nice_1_text') }}</p>
            </div>
            <div class="happy-item">
                <img class="lozad"
                    src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs="
                    data-src="{{ asset('images/happy/happy-2.svg') }}" alt="img" loading="lazy">
                <p>{{ trans('homepage_new.you_feel_nice_2_text') }}</p>
            </div>
            <div class="happy-item">
                <img class="lozad"
                    src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs="
                    data-src="{{ asset('images/happy/happy-3.svg') }}" alt="img" loading="lazy">
                <p>{{ trans('homepage_new.you_feel_nice_3_text') }}</p>
            </div>
            <div class="happy-item">
                <img class="lozad"
                    src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs="
                    data-src="{{ asset('images/happy/happy-4.svg') }}" alt="img" loading="lazy">
                <p>{{ trans('homepage_new.you_feel_nice_4_text') }}</p>
            </div>
        </div>
    </div>
</section>

<div class="ellipse ellipse_black ellipse_top">
    <a href="#examples" class="anchor ellipse-arrow ellipse-arrow_white"
        aria-label="anchor link">
        <i class="fa-arrow-down"></i>
    </a>
    <img src="{{ asset(env('THEME').'images/icon/ellipse-black.svg') }}" alt="img" loading="lazy">
</div>
