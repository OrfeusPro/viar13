@php
    use App\Models\SiteImage;

    /** @var \Illuminate\Support\Collection|SiteImage[] $siteImages */
    $siteImages = $siteImages ?? SiteImage::where('is_show', true)->get();

    // дефолтные размеры (как у тебя в примере: у #4 другой width)
    $sizes = [
        1 => [415, 433],
        2 => [415, 433],
        3 => [415, 433],
        4 => [255, 433],
        5 => [415, 433],
        6 => [415, 433],
        7 => [415, 433],
    ];

    // собираем данные для 1..7
    $collage = [];
    for ($i = 1; $i <= 7; $i++) {
        $collage[$i] = site_image_pair(
            'collage_order_content_'.$i,
            'collage_order_content_'.$i.'_mob',
            "images/collage/ord{$i}.svg",
            "images/collage/ord{$i}M.svg",
            ['collection' => $siteImages]
        ) + ['w' => $sizes[$i][0], 'h' => $sizes[$i][1]];
    }
@endphp


    <div class="collage-order__screen">
      <div class="section-frame">
        <div class="collage-order--inner">
          <div class="page-title--row">
            <h2 class="page-title page-collage-title">
              {!! trans('collage_new.z2_order_screen_title') !!}
            </h2>
            <div class="col-subtitle">
              {!! trans('collage_new.z2_order_screen_subtitle') !!}
            </div>
          </div>
          <div class="collage-order--content">
            <div class="o-abs-el">
              @for ($i = 1; $i <= 7; $i++)
                <div class="o-abs{{ $i }}">
                  <picture>
                    @if(!empty($collage[$i]['mob']['src_webp']))
                        <source media="(max-width: 700px)"
                                srcset="{{ $collage[$i]['mob']['src_webp'] }}"
                                type="image/webp">
                    @endif
                    @if(!empty($collage[$i]['mob']['type']))
                        <source media="(max-width: 700px)"
                                srcset="{{ $collage[$i]['mob']['src'] }}"
                                type="{{ $collage[$i]['mob']['type'] }}">
                    @endif
                    @if(!empty($collage[$i]['desk']['src_webp']))
                        <source srcset="{{ $collage[$i]['desk']['src_webp'] }}"
                                type="image/webp">
                    @endif
                    @if(!empty($collage[$i]['desk']['type']))
                        <source srcset="{{ $collage[$i]['desk']['src'] }}"
                                type="{{ $collage[$i]['desk']['type'] }}">
                    @endif
                    <img loading="lazy"
                        width="{{ $collage[$i]['w'] }}"
                        height="{{ $collage[$i]['h'] }}"
                        src="{{ $collage[$i]['desk']['src'] }}" alt="{{ $collage[$i]['desk']['alt'] ?? '' }}" title="{{ $collage[$i]['desk']['title'] ?? '' }}">
                  </picture>
                  <p>{!! trans('collage_new.z2_order_screen_text'.$i) !!}</p>
                </div>
              @endfor
            </div>
            <div class="collage-order-img">
			@if($ACollageOrderScreen)
              <picture>
			          @if($webpSrc = image_webp_url("storage/".$ACollageOrderScreen['image']))
                    <source srcset="{{ $webpSrc }}" type="image/webp">
                @endif
                <source srcset="{{ Voyager::image($ACollageOrderScreen['image']) }}" type="image/jpeg">
                <img width="589" height="616" loading="lazy" src="{{ Voyager::image($ACollageOrderScreen['image']) }}" @altAttrs(['type' => \App\Models\ACollageOrderScreen::class, 'id' => data_get($ACollageOrderScreen, 'id')], 'image', data_get($ACollageOrderScreen, 'image'))>
              </picture>
			@endif
            </div>
          </div>
          <a href="#collage-generator" class="c-order-btn">
            {!! trans('collage_new.z2_order_screen_btn') !!}
          </a>
        </div>
      </div>
    </div>



    <!-- ellipse -->

    <div class="ellipse ellipse_black">
      <img src="https://viarcanvas.com/theme/viar/images/icon/ellipse-black.svg" alt="img" loading="lazy">
    </div>
