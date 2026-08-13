@php
use App\Models\SiteImage;

/** @var \Illuminate\Support\Collection|SiteImage[] $siteImages */
$siteImages = $siteImages ?? SiteImage::where('is_show', true)->get();

/* mapping: индекс -> дефолтный относительный путь */
$map = [
    1 => 'images/three-days.jpg',
    2 => 'images/one-day.jpg',
    3 => 'images/on-date.jpg',
    4 => 'images/van.jpg',
    5 => 'images/on-adress.jpg',
    6 => 'images/abroad.jpg',
    7 => 'images/venipak.png',
    8 => 'images/dpd.png',
    9 => 'images/omniva-logo-41B019A1E9-seeklogo.com.png',
];

/* итоговый набор для 1..9 */
$dl = [];
for ($i=1; $i<=9; $i++){
    $key = 'canvas_about_tab_about_deadline_'.$i;
    $dl[$i] = site_image($key, $map[$i], ['collection' => $siteImages]);
}
@endphp


<div class="about__block about__fifth brief-v">
    <div class="about-deadline">
        <div class="deadline-block">
            <div class="deadline-title">
                <img src="{{ ver_asset('images/clock.png') }}" alt=""/>
                <p>{!! trans('canvas.tab5__inner__title') !!}</p>
            </div>
            <div class="deadline-items">
                <div class="deadline-item">
                    <div class="deadline-img">
                        <picture>
                            @if(!empty($dl[1]['src_webp']))
                                <source srcset="{{ $dl[1]['src_webp'] }}" type="image/webp"/>
                            @endif
                            @if(!empty($dl[1]['type']))
                                <source srcset="{{ $dl[1]['src'] }}" type="{{ $dl[1]['type'] }}"/>
                            @endif
                            <img src="{{ $dl[1]['src'] }}" alt="{{ $dl[1]['alt'] ?? '' }}" title="{{ $dl[1]['title'] ?? '' }}"/>
                        </picture>
                    </div>
                    <div class="deadline-content">
                        <div class="deadline-desc">
                            {!! trans('canvas.tab5__inner__block1_title') !!}
                        </div>
                        <div class="deadline-text">
                            {!! trans('canvas.tab5__inner__block1_text') !!}
                        </div>
                    </div>
                </div>
                <div class="deadline-item">
                    <div class="deadline-img">
                        <picture>
                            @if(!empty($dl[2]['src_webp']))
                                <source srcset="{{ $dl[2]['src_webp'] }}" type="image/webp"/>
                            @endif
                            @if(!empty($dl[2]['type']))
                                <source srcset="{{ $dl[2]['src'] }}" type="{{ $dl[2]['type'] }}"/>
                            @endif
                            <img src="{{ $dl[2]['src'] }}" alt="{{ $dl[2]['alt'] ?? '' }}" title="{{ $dl[2]['title'] ?? '' }}"/>
                        </picture>
                    </div>
                    <div class="deadline-content">
                        <div class="deadline-desc">
                             {!! trans('canvas.tab5__inner__block2_title') !!}
                        </div>
                        <div class="deadline-text">
                             {!! trans('canvas.tab5__inner__block2_text') !!}
                        </div>
                    </div>
                </div>
                <div class="deadline-item">
                    <div class="deadline-img">
                        <picture>
                            @if(!empty($dl[3]['src_webp']))
                                <source srcset="{{ $dl[3]['src_webp'] }}" type="image/webp"/>
                            @endif
                            @if(!empty($dl[3]['type']))
                                <source srcset="{{ $dl[3]['src'] }}" type="{{ $dl[3]['type'] }}"/>
                            @endif
                            <img src="{{ $dl[3]['src'] }}" alt="{{ $dl[3]['alt'] ?? '' }}" title="{{ $dl[3]['title'] ?? '' }}"/>
                        </picture>
                    </div>
                    <div class="deadline-content">
                        <div class="deadline-desc">
                            {!! trans('canvas.tab5__inner__block3_title') !!}
                        </div>
                        <div class="deadline-text">
                            {!! trans('canvas.tab5__inner__block3_title_after') !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="deadline-block">
            <div class="deadline-title">
                <img src="{{ ver_asset('images/delivery.png') }}" alt=""/>
                <p>{!! trans('canvas.tab5__delivery__title') !!}</p>
            </div>
            <div class="deadline-items">
                <div class="deadline-item">
                    <div class="deadline-img">
                        <picture>
                            @if(!empty($dl[4]['src_webp']))
                                <source srcset="{{ $dl[4]['src_webp'] }}" type="image/webp"/>
                            @endif
                            @if(!empty($dl[4]['type']))
                                <source srcset="{{ $dl[4]['src'] }}" type="{{ $dl[4]['type'] }}"/>
                            @endif
                            <img src="{{ $dl[4]['src'] }}" alt="{{ $dl[4]['alt'] ?? '' }}" title="{{ $dl[4]['title'] ?? '' }}"/>
                        </picture>
                    </div>
                    <div class="deadline-content">
                        <div class="deadline-text">
                            {!! trans('canvas.tab5__delivery__block1_title') !!}
                        </div>
						<div class="deadline-desc">{!! trans('canvas.tab5__delivery__block1_title_after') !!}</div>
                    </div>
                </div>
                <div class="deadline-item">
                    <div class="deadline-img">
                        <picture>
                            @if(!empty($dl[5]['src_webp']))
                                <source srcset="{{ $dl[5]['src_webp'] }}" type="image/webp"/>
                            @endif
                            @if(!empty($dl[5]['type']))
                                <source srcset="{{ $dl[5]['src'] }}" type="{{ $dl[5]['type'] }}"/>
                            @endif
                            <img src="{{ $dl[5]['src'] }}" alt="{{ $dl[5]['alt'] ?? '' }}" title="{{ $dl[5]['title'] ?? '' }}"/>
                        </picture>
                    </div>
                    <div class="deadline-content">
                        <div class="deadline-text">
                            {!! trans('canvas.tab5__delivery__block2_title') !!}
                        </div>
                        <div class="deadline-desc">{!! trans('canvas.tab5__delivery__block2_title_after') !!}</div>
                    </div>
                </div>
                <div class="deadline-item">
                    <div class="deadline-img">
                        <picture>
                            @if(!empty($dl[6]['src_webp']))
                                <source srcset="{{ $dl[6]['src_webp'] }}" type="image/webp"/>
                            @endif
                            @if(!empty($dl[6]['type']))
                                <source srcset="{{ $dl[6]['src'] }}" type="{{ $dl[6]['type'] }}"/>
                            @endif
                            <img src="{{ $dl[6]['src'] }}" alt="{{ $dl[6]['alt'] ?? '' }}" title="{{ $dl[6]['title'] ?? '' }}"/>
                        </picture>
                    </div>
                    <div class="deadline-content">
                        <div class="deadline-text">
                            {!! trans('canvas.tab5__delivery__block3_title') !!}
                        </div>
                        <div class="deadline-desc">{!! trans('canvas.tab5__delivery__block3_title_after') !!}</div>
                    </div>
                </div>
            </div>
            <div class="courier-info">
                <div class="courier-text">
                    {!! trans('canvas.tab5__delivery__after_text') !!}
                </div>
                <div class="courier-items">
                    <div class="courier-item">
                        <div class="courier-inner">
                            <div class="courier-logo">
                                <picture>
                                    @if(!empty($dl[7]['src_webp']))
                                        <source srcset="{{ $dl[7]['src_webp'] }}" type="image/webp"/>
                                    @endif
                                    @if(!empty($dl[7]['type']))
                                        <source srcset="{{ $dl[7]['src'] }}" type="{{ $dl[7]['type'] }}"/>
                                    @endif
                                    <img src="{{ $dl[7]['src'] }}" alt="{{ $dl[7]['alt'] ?? '' }}" title="{{ $dl[7]['title'] ?? '' }}"/>
                                </picture>
                            </div>
                            <div class="courier-txt">
                                {!! trans('canvas.tab5__delivery__after_block1_text') !!}
                            </div>
                        </div>
                    </div>
                    <div class="courier-item">
                        <div class="courier-inner">
                            <div class="courier-logo">
                                <picture>
                                    @if(!empty($dl[8]['src_webp']))
                                        <source srcset="{{ $dl[8]['src_webp'] }}" type="image/webp"/>
                                    @endif
                                    @if(!empty($dl[8]['type']))
                                        <source srcset="{{ $dl[8]['src'] }}" type="{{ $dl[8]['type'] }}"/>
                                    @endif
                                    <img src="{{ $dl[8]['src'] }}" alt="{{ $dl[8]['alt'] ?? '' }}" title="{{ $dl[8]['title'] ?? '' }}"/>
                                </picture>

                                <picture>
                                    @if(!empty($dl[9]['src_webp']))
                                        <source srcset="{{ $dl[9]['src_webp'] }}" type="image/webp"/>
                                    @endif
                                    @if(!empty($dl[9]['type']))
                                        <source srcset="{{ $dl[9]['src'] }}" type="{{ $dl[9]['type'] }}"/>
                                    @endif
                                    <img src="{{ $dl[9]['src'] }}" style="max-width:50px;" alt="{{ $dl[9]['alt'] ?? '' }}" title="{{ $dl[9]['title'] ?? '' }}"/>
                                </picture>
                            </div>
                            <div class="courier-txt">
                                {!! trans('canvas.tab5__delivery__after_block2_text') !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="hidden-trigger">
        <a href="#examples" class="anchor ellipse-arrow ellipse-arrow_white" aria-label="anchor link">
            <i class="fa-arrow-down"></i>
        </a>
        <span>{{ trans('canvas.tabs_show_btn_text') }}</span>
        <span>{{ trans('canvas.tabs_hide_btn_text') }}</span>
    </div>
</div>
