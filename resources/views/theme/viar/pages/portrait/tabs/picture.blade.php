@php
    /** @var \Illuminate\Support\Collection|\App\Models\SiteImage[] $siteImages */
    $siteImages = $siteImages ?? \App\Models\SiteImage::where('is_show', true)->get();

    $royalImage = site_image_pair(
        'portrait_historical_royal_special',
        'portrait_historical_royal_special_mob',
        'images/sharj/about1.webp',
        'images/sharj/about1Min.webp',
        ['collection' => $siteImages]
    );
@endphp

<div class="about__block @if($is_hidden ?? true) hidden-block @endif">
    <div class="about-image">
        <div class="about-image__grid">
            <div class="about-image__content">
                <div class="page-title">{!! trans('portrait_royal.tab_picture_body_title') !!}</div>
                <p>{!! trans('portrait_royal.tab_picture_body_subtitle') !!}</span></p>
                <picture>
                    <source media="(max-width: 1200px)" srcset="{{ver_asset('images/sharj/v1Min.svg')}}">
                    <source srcset="{{ver_asset('images/sharj/v1.svg')}} ">
                    <img src="{{ver_asset('images/sharj/v1.svg')}}" width="69" height="98" alt="">
                </picture>

            </div>
            <div class="about-image__img">
                <picture>
                    @if(!empty($royalImage['mob']['src_webp']))
                        <source media="(max-width: 576px)" srcset="{{ $royalImage['mob']['src_webp'] }}" type="image/webp">
                    @endif
                    @if(!empty($royalImage['mob']['type']))
                        <source media="(max-width: 576px)" srcset="{{ $royalImage['mob']['src'] }}" type="{{ $royalImage['mob']['type'] }}">
                    @endif
                    @if(!empty($royalImage['desk']['src_webp']))
                        <source srcset="{{ $royalImage['desk']['src_webp'] }}" type="image/webp">
                    @endif
                    @if(!empty($royalImage['desk']['type']))
                        <source srcset="{{ $royalImage['desk']['src'] }}" type="{{ $royalImage['desk']['type'] }}">
                    @endif
                    <img width="806" height="565" src="{{ $royalImage['desk']['src'] }}" alt="{{ $royalImage['desk']['alt'] ?? '' }}" title="{{ $royalImage['desk']['title'] ?? '' }}">
                </picture>
            </div>
        </div>
    </div>
</div>
