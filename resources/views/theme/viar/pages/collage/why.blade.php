@php
    /** @var \Illuminate\Support\Collection|\App\Models\SiteImage[] $siteImages */
    $siteImages = $siteImages ?? \App\Models\SiteImage::where("is_show",true)->get();
    $banner1 = site_image('collage_portait-why', env('THEME').'images/bg/portrait-why.webp', ['collection' => $siteImages]);
    $bgJpg  = $banner1['src'];
    $bgWebp = $banner1['src_webp'] ?? $bgJpg;
@endphp

<style>
  .portait-why{
    background-image: url('{{ $bgJpg }}');
  }

  .portait-why{
    background-image: -webkit-image-set(
      url('{{ $bgWebp }}') type("image/webp") 1x, url('{{ $bgJpg }}')  type("image/jpeg") 1x
    );
  }
  .portait-why{
    background-image: image-set(
      url('{{ $bgWebp }}') type("image/webp") 1x, url('{{ $bgJpg }}')  type("image/jpeg") 1x
    );
  }
</style>

<section class="lozad portait-why hb_collage">
	<div class="section-frame">
		<div class="top-title">
			<div class="page-title h2_old">{{ trans('portrait.des_help_title') }}</h2>
			<p>{{ trans('portrait.des_help_desc') }}</p>
		</div>
		<div class="why-list">
			<div class="why-box">
				<div class="why-item">
					<p>
						{!! trans('portrait.des_help_step1') !!}
					</p>
				</div>
				<div class="why-item">
					<p>
						{!! trans('portrait.des_help_step2') !!}
					</p>
				</div>
			</div>
			<div class="why-box">
				<div class="why-item">
					<p>
						{!! trans('portrait.des_help_step3') !!}
					</p>
				</div>
				<div class="why-item">
					<p>
						{!! trans('portrait.des_help_step4') !!}
					</p>
				</div>
			</div>
		</div>

		@include(env('THEME_RESOURCES') . 'pages.collage.why_form')
	</div>
</section>
