@php
    /** @var \Illuminate\Support\Collection|\App\Models\SiteImage[] $siteImages */
    $siteImages = $siteImages ?? \App\Models\SiteImage::where('is_show', true)->get();
    $banner1 = site_image('home_banner_2', env('THEME').'images/bg/work-bg.jpg', ['collection' => $siteImages]);
    $bgJpg  = $banner1['src'];
    $bgWebp = $banner1['src_webp'] ?? $bgJpg;
@endphp

<style>
  .work{
    background-image: url('{{ $bgJpg }}');
    background-position: center bottom;
    background-repeat: no-repeat;
    background-size: 100% auto;
  }

  .work{
    background-image: -webkit-image-set(
      url('{{ $bgWebp }}') type("image/webp") 1x, url('{{ $bgJpg }}')  type("image/jpeg") 1x
    );
  }
  .work{
    background-image: image-set(
      url('{{ $bgWebp }}') type("image/webp") 1x, url('{{ $bgJpg }}')  type("image/jpeg") 1x
    );
  }
</style>

<section class="work">
    <div class="section-frame">
        <div class="work-title">
            @if (Route::currentRouteName() == 'delivery_page')
                <h2 class="page-title">{!! trans('homepage_new.how_we_work_title') !!}</h2>
            @elseif (Route::currentRouteName() == 'home')
                <h2 class="page-title">{!! trans('homepage_new.how_we_work_title') !!}</h2>
            @elseif (Route::currentRouteName() == 'about')
                <h2 class="page-title">{!! trans('homepage_new.how_we_work_title') !!}</h2>
            @else
                <h2 class="page-title">{!! trans('homepage_new.how_we_work_title') !!}</h2>
            @endif
            <p>{!! trans('homepage_new.how_we_work_sub_title') !!}</p>
        </div>
        <div class="work-list">
            <div class="work-item">
                <img data-src="{{ asset('images/work/work-1.svg') }}" class="work-photo lozad"
                    src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                    alt="img" loading="lazy">
                <div class="h3_old">{!! trans('homepage_new.how_we_work_step1_title') !!}</div>
                <p>{!! trans('homepage_new.how_we_work_step1_desc') !!}</p>
                <svg class="work-arrow">
                    <use xlink:href="{{ asset(env('THEME').'sprite.svg#work-arrow') }}"></use>
                </svg>
            </div>
            <div class="work-item">
                <img data-src="{{ asset('images/work/work-2.svg') }}" class="work-photo lozad"
                    src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                    alt="img" loading="lazy">
                <div class="h3_old">{!! trans('homepage_new.how_we_work_step2_title') !!}</div>
                <p>{!! trans('homepage_new.how_we_work_step2_desc') !!}</p>
                <svg class="work-arrow">
                    <use xlink:href="{{ asset(env('THEME').'sprite.svg#work-arrow') }}"></use>
                </svg>
            </div>
            <div class="work-item">
                <img data-src="{{ asset('images/work/work-3.svg') }}" class="work-photo lozad"
                    src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                    alt="img" loading="lazy">
                <div class="h3_old">{!! trans('homepage_new.how_we_work_step3_title') !!}</div>
                <p>{!! trans('homepage_new.how_we_work_step3_desc') !!}</p>
            </div>
        </div>
        <div class="work-time">
            <div class="work-time__item">
                <img src="{{ asset('images/work/work-4.svg') }}" alt="img" loading="lazy">
                <p>{!! trans('homepage_new.how_we_work_express') !!}</p>
            </div>
            <div class="work-time__item">
                <img src="{{ asset('images/work/work-5.svg') }}" alt="img" loading="lazy">
                <p>{!! trans('homepage_new.how_we_work_standart') !!}</p>
            </div>
        </div>
    </div>
</section>
