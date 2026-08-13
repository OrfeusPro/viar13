@php
    use App\Models\SiteImage;

    /** @var \Illuminate\Support\Collection|SiteImage[] $siteImages */
    $siteImages = $siteImages ?? SiteImage::where('is_show', true)->get();

    $defaults = [
        0 => 'images/about-m1.png',
        1 => 'images/fit-1.jpg',
        2 => 'images/fit-2.jpg',
        3 => 'images/fit-3.jpg',
    ];

    $req = [];

    // 0 — десктоп + опциональный моб
    $req[0] = site_image_pair(
        'simpsons_snapshot_requirement_0',
        'simpsons_snapshot_requirement_0_mob',
        $defaults[0],
        $defaults[0],
        ['collection' => $siteImages]
    );

    // 1..3 — десктоп + моб
    for ($i = 1; $i <= 3; $i++) {
        $keyDesk = 'simpsons_snapshot_requirement_'.$i;
        $keyMob  = 'simpsons_snapshot_requirement_'.$i.'_mob';

        $req[$i] = site_image_pair($keyDesk, $keyMob, $defaults[$i], $defaults[$i], ['collection' => $siteImages]);
    }
@endphp

<div class="about__block about__second hidden-block brief-v @if($is_hidden ?? true) hidden-block @endif">
    <div class="about-fit--block">
        <div class="fit-block--inner">
            <div class="fit-icon">
                <svg width="53" height="52" viewBox="0 0 53 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M51.5 26C51.5 39.8071 40.3071 51 26.5 51C12.6929 51 1.5 39.8071 1.5 26C1.5 12.1929 12.6929 1 26.5 1C40.3071 1 51.5 12.1929 51.5 26Z"
                        stroke="#FA7846" stroke-width="2"/>
                    <path
                        d="M24.4258 29.0752H26.8484V28.8578C26.8612 27.6114 27.3086 27.0297 28.3185 26.4225C29.5138 25.7129 30.2937 24.7733 30.2937 23.2712C30.2937 21.034 28.4911 19.73 25.9535 19.73C23.6332 19.73 21.7411 20.9445 21.6836 23.5013H24.2915C24.3299 22.4594 25.1033 21.9033 25.9407 21.9033C26.8036 21.9033 27.5004 22.4786 27.5004 23.3671C27.5004 24.2044 26.8931 24.7605 26.1069 25.2591C25.033 25.9367 24.4322 26.6206 24.4258 28.8578V29.0752ZM25.685 33.1661C26.5032 33.1661 27.2127 32.4821 27.2191 31.632C27.2127 30.7946 26.5032 30.1107 25.685 30.1107C24.8413 30.1107 24.1445 30.7946 24.1509 31.632C24.1445 32.4821 24.8413 33.1661 25.685 33.1661Z"
                        fill="#FA7846"/>
                </svg>
            </div>
            <div class="fit-content">
                <div class="fit-title">
                    {{ trans('portrait.tab2__inner_title') }}
                </div>
                <div class="fit-text">
                     {!! trans('portrait.tab2__inner_text') !!}
                </div>
            </div>
        </div>
        <div class="fit-image--bg">
                <picture>
                    @if(!empty($req[0]['mob']['src_webp']))
                        <source media="(max-width: 576px)" srcset="{{ $req[0]['mob']['src_webp'] }}" type="image/webp"/>
                    @endif
                    @if(!empty($req[0]['mob']['type']))
                        <source media="(max-width: 576px)" srcset="{{ $req[0]['mob']['src'] }}" type="{{ $req[0]['mob']['type'] }}"/>
                    @endif
                    @if(!empty($req[0]['desk']['src_webp']))
                        <source srcset="{{ $req[0]['desk']['src_webp'] }}" type="image/webp"/>
                    @endif
                    @if(!empty($req[0]['desk']['type']))
                        <source srcset="{{ $req[0]['desk']['src'] }}" type="{{ $req[0]['desk']['type'] }}"/>
                    @endif
                    <img src="{{ $req[0]['desk']['src'] }}" alt="{{ $req[0]['desk']['alt'] ?? '' }}" title="{{ $req[0]['desk']['title'] ?? '' }}"/>
                </picture>
        </div>
    </div>
    <div class="about-offers">
        <div class="about-offer">
            <div class="offer-text">
                <p>{!! trans('portrait.tab2__inner_block1_text') !!}</p>
            </div>
            <div class="offer-img">
                <picture>
                    @if(!empty($req[1]['mob']['src_webp']))
                        <source media="(max-width: 576px)" srcset="{{ $req[1]['mob']['src_webp'] }}" type="image/webp"/>
                    @endif
                    @if(!empty($req[1]['mob']['type']))
                        <source media="(max-width: 576px)" srcset="{{ $req[1]['mob']['src'] }}" type="{{ $req[1]['mob']['type'] }}"/>
                    @endif
                    @if(!empty($req[1]['desk']['src_webp']))
                        <source srcset="{{ $req[1]['desk']['src_webp'] }}" type="image/webp"/>
                    @endif
                    @if(!empty($req[1]['desk']['type']))
                        <source srcset="{{ $req[1]['desk']['src'] }}" type="{{ $req[1]['desk']['type'] }}"/>
                    @endif
                    <img src="{{ $req[1]['desk']['src'] }}" alt="{{ $req[1]['desk']['alt'] ?? '' }}" title="{{ $req[1]['desk']['title'] ?? '' }}"/>
                </picture>
            </div>
        </div>
        <div class="about-offer">
            <div class="offer-text">
                <p>{!! trans('portrait.tab2__inner_block2_text') !!}</p>
            </div>
            <div class="offer-img">
                <picture>
                    @if(!empty($req[2]['mob']['src_webp']))
                        <source media="(max-width: 576px)" srcset="{{ $req[2]['mob']['src_webp'] }}" type="image/webp"/>
                    @endif
                    @if(!empty($req[2]['mob']['type']))
                        <source media="(max-width: 576px)" srcset="{{ $req[2]['mob']['src'] }}" type="{{ $req[2]['mob']['type'] }}"/>
                    @endif
                    @if(!empty($req[2]['desk']['src_webp']))
                        <source srcset="{{ $req[2]['desk']['src_webp'] }}" type="image/webp"/>
                    @endif
                    @if(!empty($req[2]['desk']['type']))
                        <source srcset="{{ $req[2]['desk']['src'] }}" type="{{ $req[2]['desk']['type'] }}"/>
                    @endif
                    <img src="{{ $req[2]['desk']['src'] }}" alt="{{ $req[2]['desk']['alt'] ?? '' }}" title="{{ $req[2]['desk']['title'] ?? '' }}"/>
                </picture>
            </div>
        </div>
        <div class="about-offer">
            <div class="offer-text">
                <p>{!! trans('portrait.tab2__inner_block3_text') !!}</p>
            </div>
            <div class="offer-img">
                <picture>
                    @if(!empty($req[3]['mob']['src_webp']))
                        <source media="(max-width: 576px)" srcset="{{ $req[3]['mob']['src_webp'] }}" type="image/webp"/>
                    @endif
                    @if(!empty($req[3]['mob']['type']))
                        <source media="(max-width: 576px)" srcset="{{ $req[3]['mob']['src'] }}" type="{{ $req[3]['mob']['type'] }}"/>
                    @endif
                    @if(!empty($req[3]['desk']['src_webp']))
                        <source srcset="{{ $req[3]['desk']['src_webp'] }}" type="image/webp"/>
                    @endif
                    @if(!empty($req[3]['desk']['type']))
                        <source srcset="{{ $req[3]['desk']['src'] }}" type="{{ $req[3]['desk']['type'] }}"/>
                    @endif
                    <img src="{{ $req[3]['desk']['src'] }}" alt="{{ $req[3]['desk']['alt'] ?? '' }}" title="{{ $req[3]['desk']['title'] ?? '' }}"/>
                </picture>
            </div>
        </div>
    </div>
    <div class="about--notes">
        <div class="about--note">
            <span>*</span>
            <p>{!! trans('portrait.tab2__inner_mob1_hint') !!}</p>
        </div>
        <div class="about--note">
            <span>*</span>
            <p>{!! trans('portrait.tab2__inner_mob2_hint') !!}</p>
        </div>
    </div>
    <div class="hidden-trigger">
        <a href="#examples" class="anchor ellipse-arrow ellipse-arrow_white" aria-label="anchor link">
            <i class="fa-arrow-down"></i>
        </a>
        <span>{{ trans('portrait.tabs_show_btn_text') }}</span>
        <span>{{ trans('portrait.tabs_hide_btn_text') }}</span>
    </div>
</div>
