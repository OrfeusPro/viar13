<section class="portrait-gift__section">
    <div class="section-inner">
        <div class="portrait-gift__grid">
            <div class="portrait-content">
                <div class="portrait-content__inner">
                    <div class="page-title">{!! trans('portrait_royal.s_gift_title') !!}</div>
                    <p>{!! trans('portrait_royal.s_gift_text') !!}</p>
                    <div class="default-btn js-fast_order--royal" data-gift="1">
                        {{ trans('portrait_royal.btn_order_portrait') }}</div>
                    <div class="absolute-elements">
                        <div class="portrait-gift">
                            <img loading="lazy" width="149" height="138"
                                src="{{ ver_asset('images/collage/gift.svg') }}" alt="Viar Image">
                            <p>{!! trans('portrait_royal.s_gift_note') !!}</p>
                        </div>
                    </div>
                </div>
            </div>
            @php
                /** @var \Illuminate\Support\Collection|\App\Models\SiteImage[] $siteImages */
                $siteImages = $siteImages ?? \App\Models\SiteImage::where('is_show', true)->get();
                $giftImage = site_image_pair(
                    'portrait_historical_gift',
                    'portrait_historical_gift_mob',
                    'images/sharj/pgift1.webp',
                    'images/sharj/pgift1Min.webp',
                    ['collection' => $siteImages]
                );
            @endphp
            <div class="portrait-img">
                <picture>
                    @if(!empty($giftImage['mob']['src_webp']))
                        <source media="(max-width: 576px)" srcset="{{ $giftImage['mob']['src_webp'] }}" type="image/webp">
                    @endif
                    @if(!empty($giftImage['mob']['type']))
                        <source media="(max-width: 576px)" srcset="{{ $giftImage['mob']['src'] }}" type="{{ $giftImage['mob']['type'] }}">
                    @endif
                    @if(!empty($giftImage['desk']['src_webp']))
                        <source srcset="{{ $giftImage['desk']['src_webp'] }}" type="image/webp">
                    @endif
                    @if(!empty($giftImage['desk']['type']))
                        <source srcset="{{ $giftImage['desk']['src'] }}" type="{{ $giftImage['desk']['type'] }}">
                    @endif
                    <img width="800" height="451" src="{{ $giftImage['desk']['src'] }}" alt="{{ $giftImage['desk']['alt'] ?? '' }}" title="{{ $giftImage['desk']['title'] ?? '' }}">
                </picture>
            </div>
        </div>
    </div>
</section>
