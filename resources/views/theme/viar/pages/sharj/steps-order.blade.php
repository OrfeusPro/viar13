@php
    use App\Models\SiteImage;

    /** @var \Illuminate\Support\Collection|SiteImage[] $siteImages */
    $siteImages = $siteImages ?? SiteImage::where('is_show', true)->get();

    $base = asset(env('THEME').'images').'/sharj/new/categories/';

    // дефолт для каждого шага (десктоп). Если нужно — добавь мобильные дефолты.
    $defaults = [
        1 => $base.'step1.webp',
        2 => $base.'step2.webp',
        3 => $base.'step3.webp',
        4 => $base.'step4.webp',
        5 => $base.'step5.webp',
    ];

    // собираем данные по шагам (поддержим и *_mob, если появится)
    $steps = [];
    for ($i = 1; $i <= 5; $i++) {
        $key = 'caricature_ordering_step_'.$i;
        $steps[$i] = site_image_pair(
            $key,
            $key.'_mob',
            $defaults[$i],
            $defaults[$i],
            ['collection' => $siteImages]
        );
    }
@endphp



<div class="title-row">
        <h2 class="page-title mb-2">
            @if ($h2_titles->third_block_h2!='')
                {!!   $h2_titles->third_block_h2 !!}
            @else
                @lang('sharj.translate45')
            @endif
        </h2>
    {{-- <p>@lang("sharj.translate46")</p> --}}
    {{-- <div class="section-badge">
        <div class="pmo-block pmo-block1">
            <p>@lang("sharj.translate47")</p>
            <img width="52" height="80" src="{{ asset(env('THEME') . 'images') }}/sharj/new/pngwing.webp" alt="">
            <svg width="130" height="69" viewBox="0 0 130 69" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M129.461 56.6926C129.568 56.4378 129.447 56.145 129.193 56.0386L125.04 54.305C124.785 54.1986 124.492 54.3189 124.386 54.5737C124.28 54.8286 124.4 55.1214 124.655 55.2278L128.346 56.7688L126.805 60.46C126.699 60.7149 126.819 61.0077 127.074 61.1141C127.329 61.2204 127.621 61.1001 127.728 60.8453L129.461 56.6926ZM1 1C0.508262 1.09052 0.508383 1.09118 0.508575 1.09221C0.508734 1.09307 0.508997 1.09449 0.509316 1.0962C0.509953 1.09962 0.510877 1.10456 0.512093 1.111C0.514525 1.12389 0.518124 1.1428 0.522924 1.16762C0.532524 1.21725 0.54693 1.29051 0.566418 1.38645C0.605393 1.57834 0.664701 1.86097 0.746561 2.22685C0.910274 2.95857 1.16422 4.02335 1.52614 5.36105C2.24993 8.03621 3.40596 11.8043 5.13655 16.1838C8.59643 24.9394 14.3599 36.1568 23.573 45.9673C32.7911 55.7831 45.4619 64.1889 62.7158 67.3045C79.9652 70.4193 101.751 68.2389 129.19 56.9625L128.81 56.0375C101.499 67.2611 79.9098 69.3932 62.8935 66.3205C45.8819 63.2486 33.3964 54.9669 24.302 45.2827C15.2026 35.5932 9.49732 24.4981 6.06657 15.8162C4.35185 11.477 3.2071 7.74504 2.49144 5.09988C2.13364 3.77743 1.88318 2.72698 1.72243 2.00851C1.64206 1.64928 1.58412 1.37308 1.54641 1.1874C1.52755 1.09456 1.51375 1.02436 1.50473 0.977723C1.50022 0.954406 1.4969 0.936983 1.49475 0.92557C1.49367 0.919864 1.49289 0.91566 1.49239 0.912974C1.49214 0.91163 1.49197 0.910758 1.49185 0.910084C1.49176 0.909592 1.49174 0.909481 1 1Z" fill="#FA7846"></path>
            </svg>
        </div>
    </div> --}}
</div>

<div class="steps-order__top">
    <div class="steps-order__top-item">
        <img width="100" height="100" src="{{ asset(env('THEME') . 'images') }}/sharj/new/express.svg" alt="">
        <p>@lang("sharj.translate48")</p>
    </div>
    <div class="steps-order__top-item">
        <img width="100" height="100" src="{{ asset(env('THEME') . 'images') }}/sharj/new/standart.svg" alt="">
        <p>@lang("sharj.translate49")</p>
    </div>
</div>
<div class="steps-order__items full-mode">
  @for ($i = 1; $i <= 5; $i++)
    @php
      $titleKey = 'sharj.translate' . (49 + 2 * $i); // 51,53,55,57,59
      $descKey  = 'sharj.translate' . (50 + 2 * $i); // 52,54,56,58,60
    @endphp

    <div class="steps-order__item">
      <div class="img">
        @if ($i === 1)
          <p>@lang('sharj.translate50')</p>
        @endif
        <span>{{ $i }}</span>

        <picture>
          @if(!empty($steps[$i]['mob']['src_webp']))
            <source media="(max-width: 576px)"
                    srcset="{{ $steps[$i]['mob']['src_webp'] }}"
                    type="image/webp">
          @endif
          @if(!empty($steps[$i]['mob']['type']))
            <source media="(max-width: 576px)"
                    srcset="{{ $steps[$i]['mob']['src'] }}"
                    type="{{ $steps[$i]['mob']['type'] }}">
          @endif
          @if(!empty($steps[$i]['desk']['src_webp']))
            <source srcset="{{ $steps[$i]['desk']['src_webp'] }}"
                    type="image/webp">
          @endif
          @if(!empty($steps[$i]['desk']['type']))
            <source srcset="{{ $steps[$i]['desk']['src'] }}"
                    type="{{ $steps[$i]['desk']['type'] }}">
          @endif
          <img width="163" height="215" loading="lazy"
               src="{{ $steps[$i]['desk']['src'] }}" alt="{{ $steps[$i]['desk']['alt'] ?? '' }}" title="{{ $steps[$i]['desk']['title'] ?? '' }}">
        </picture>
      </div>

      <div class="name orange">@lang($titleKey)</div>
      <p>@lang($descKey)</p>
    </div>
  @endfor
</div>

{{-- <div class="steps-order__items full-mode" >
    <div class="steps-order__item">
        <div class="img">
            <p>@lang("sharj.translate50")</p>
            <span>1</span>
            <img width="163" height="215" src="{{ asset(env('THEME') . 'images') }}/sharj/new/categories/step1.webp" type="image/webp" alt="">
        </div>
        <div class="name orange">@lang("sharj.translate51")</div>
        <p>@lang("sharj.translate52")</p>
    </div>
    <div class="steps-order__item">
        <div class="img">
            <span>2</span>
            <img width="163" height="215" src="{{ asset(env('THEME') . 'images') }}/sharj/new/categories/step2.webp" type="image/webp" alt="">
        </div>
        <div class="name orange">@lang("sharj.translate53")</div>
        <p>@lang("sharj.translate54")
        </p>
    </div>
    <div class="steps-order__item">
        <div class="img">
            <span>3</span>
            <img width="163" height="215" src="{{ asset(env('THEME') . 'images') }}/sharj/new/categories/step3.webp?v=1" type="image/webp" alt="">
        </div>
        <div class="name orange">@lang("sharj.translate55")</div>
        <p>@lang("sharj.translate56")  </p>
    </div>
    <div class="steps-order__item">
        <div class="img">
            <span>4</span>
            <img width="163" height="215" src="{{ asset(env('THEME') . 'images') }}/sharj/new/categories/step4.webp" type="image/webp" alt="">
        </div>
        <div class="name orange">@lang("sharj.translate57")</div>
        <p>@lang("sharj.translate58")</p>
    </div>
    <div class="steps-order__item">
        <div class="img">
            <span>5</span>
            <img width="163" height="215" src="{{ asset(env('THEME') . 'images') }}/sharj/new/categories/step5.webp" type="image/webp" alt="">
        </div>
        <div class="name orange">@lang("sharj.translate59")</div>
        <p>@lang("sharj.translate60")</p>
    </div>
</div> --}}
