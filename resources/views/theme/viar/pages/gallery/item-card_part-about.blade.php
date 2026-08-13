@php
    /** @var \Illuminate\Support\Collection|\App\Models\SiteImage[] $siteImages */
    $siteImages = $siteImages ?? \App\Models\SiteImage::where("is_show",true)->get();
    $canvas_1 = site_image('mcard_about_block_1', env('THEME').'images/mcard/5.jpg', ['collection' => $siteImages]);
    $canvas_2 = site_image('mcard_about_block_2', env('THEME').'images/mcard/6.jpg', ['collection' => $siteImages]);
    $canvas_3 = site_image('mcard_about_block_3', env('THEME').'images/mcard/7.jpg', ['collection' => $siteImages]);
@endphp


<style>
    .audio_player
    {
     display: flex;
     width: 100%;
        justify-content: center;
        margin-top: 20px;
    }
    .audio_player audio
    {  width: 100%;}

    .mca-review
    {
    flex-wrap: wrap;
    }

</style>
@php
	if(!isset($info_block)) {
		$info_block = false;
	}
	if(!isset($first_block)) {
		$first_block = false;
	}
@endphp

<div class="mcard-about">
	<div class="section-frame">
		<div class="mcard-about__inner">
			<div class="mcard-tabs">
				@if($first_block==false)
				<div class="mcard-tab active">
					@lang('gallery.photo_item_block_1_t0_1')
				</div>
				@endif
				@if($item->description && $info_block==false)
				<div class="mcard-tab">
					@lang('gallery.photo_item_block_1_t0_2')
				</div>
				@endif
				@if($item->seo && $info_block==false)
				<div class="mcard-tab">
					@lang('gallery.info')
				</div>
				@endif
				<div class="mcard-tab @if($first_block) active @endif">
					@lang('gl.our_w_title')
				</div>
			</div>
			<div class="mcard-about__blocks">
				@if($first_block==false)
				<div class="mcard-about__block">
					<div class="mca-item">
						<div class="mca-img">
							<div class="mca-title mca-mtitle">
								@lang('gallery.photo_item_block_1_t1_1')
							</div>
							<picture>
                                @if(!empty($canvas_1['src_webp']))
                                    <source loading="lazy" class="lozad" srcset="{{ $canvas_1['src_webp'] }}" type="image/webp">
                                @endif
								@if(!empty($canvas_1['type']))
                                    <source loading="lazy" class="lozad" srcset="{{ $canvas_1['src'] }}" type="{{ $canvas_1['type'] }}">
                                @endif
								<img loading="lazy" class="lozad" width="545" height="310" src="{{ $canvas_1['src'] }}" alt="{{ $canvas_1['alt'] ?? '' }}" title="{{ $canvas_1['title'] ?? '' }}">
							</picture>
						</div>
						<div class="mca-content">
							<div class="mca-title">
								@lang('gallery.photo_item_block_1_t1_1')
							</div>
							<ul>
								<li>
									<span>@lang('gallery.photo_item_block_1_t1_2')</span>
									<p>@lang('gallery.photo_item_block_1_t1_3')</p>
								</li>
								<li>
									<span>@lang('gallery.photo_item_block_1_t1_4')</span>
									<p>@lang('gallery.photo_item_block_1_t1_5')</p>
								</li>
							</ul>
						</div>
					</div>
					<div class="mca-item">
						<div class="mca-img">
							<div class="mca-title mca-mtitle">
								@lang('gallery.photo_item_block_1_t2_1')
							</div>
							<picture>
                                @if(!empty($canvas_2['src_webp']))
                                    <source loading="lazy" class="lozad" srcset="{{ $canvas_2['src_webp'] }}" type="image/webp">
                                @endif
								@if(!empty($canvas_2['type']))
                                    <source loading="lazy" class="lozad" srcset="{{ $canvas_2['src'] }}" type="{{ $canvas_2['type'] }}">
                                @endif
								<img loading="lazy" class="lozad" width="545" height="310" src="{{ $canvas_2['src'] }}" alt="{{ $canvas_2['alt'] ?? '' }}" title="{{ $canvas_2['title'] ?? '' }}">
							</picture>
						</div>
						<div class="mca-content">
							<div class="mca-title">
								@lang('gallery.photo_item_block_1_t2_1')
							</div>
							<ul>
								<li>
									<span>@lang('gallery.photo_item_block_1_t2_2')</span>
									<p>@lang('gallery.photo_item_block_1_t2_3')</p>
								</li>
								<li>
									<span>@lang('gallery.photo_item_block_1_t2_4')</span>
									<p>@lang('gallery.photo_item_block_1_t2_5')</p>
								</li>
							</ul>
						</div>
					</div>
					<div class="mca-item">
						<div class="mca-img">
							<div class="mca-title mca-mtitle">
								@lang('gallery.photo_item_block_1_t3_1')
							</div>
							<picture>
                                @if(!empty($canvas_3['src_webp']))
                                    <source loading="lazy" class="lozad" srcset="{{ $canvas_3['src_webp'] }}" type="image/webp">
                                @endif
								@if(!empty($canvas_3['type']))
                                    <source loading="lazy" class="lozad" srcset="{{ $canvas_3['src'] }}" type="{{ $canvas_3['type'] }}">
                                @endif
								<img loading="lazy" class="lozad" width="545" height="310" src="{{ $canvas_3['src'] }}" alt="{{ $canvas_3['alt'] ?? '' }}" title="{{ $canvas_3['title'] ?? '' }}">
							</picture>
						</div>
						<div class="mca-content">
							<div class="mca-title">
								@lang('gallery.photo_item_block_1_t3_1')
							</div>
							<ul>
								<li>
									<span>@lang('gallery.photo_item_block_1_t3_2')</span>
									<p>@lang('gallery.photo_item_block_1_t3_3')</p>
								</li>
								<li>
									<span>@lang('gallery.photo_item_block_1_t3_4')</span>
									<p>@lang('gallery.photo_item_block_1_t3_5')</p>
								</li>
							</ul>
						</div>
					</div>
				</div>
				@endif
				@if($item->description && $info_block==false)
				<div class="mcard-about__block hidden-block">
					<div class="mcard-about__desc">
						{!! render_content_images($item->description) !!}
					</div>
				</div>
				@endif
				@if($item->seo && $info_block==false)
				<div class="mcard-about__block hidden-block">
					<div class="mcard-about__desc">
						{!! render_content_images($item->seo) !!}
					</div>
				</div>
				@endif
				<div class="mcard-about__block mcreviews-block @if($first_block)  @else hidden-block @endif">
					<p>
						@lang('collage_new.z7_reviews_subtext')
					</p>
					<div class="mca-reviews-wrapper">
						<div class="mca-reviews">
								@if($revs)
								@foreach($revs as $item)

									<div class="mca-review" >
										<div class="mreview-img" style="text-align: center">
											<picture>
												@if ($webpSrc = image_webp_url("storage/".$item->img))
													<source loading="lazy" class="lozad" srcset="{{ $webpSrc }}" type="image/webp">
												@endif
												<source loading="lazy" class="lozad" srcset="{{ Voyager::image($item->img) }}" type="image/jpeg">
												<img loading="lazy" class="lozad" width="545" height="310" src="{{ Voyager::image($item->img) }}" style="max-height:310px; width: auto;" alt="" loading="lazy">
											</picture>
										</div>
										<div class="mreview-content">
											<div class="mca-user">
												<picture>
													@if ($webpSrc = image_webp_url("storage/".$item->avatar))
														<source loading="lazy" class="lozad" srcset="{{ $webpSrc }}" type="image/webp">
													@endif
													<source loading="lazy" class="lozad" srcset="{{ Voyager::image($item->avatar) }}" type="image/jpeg">
													<img loading="lazy" class="lozad" width="70" height="70" src="{{ Voyager::image($item->avatar) }}" style="width: 70px; aspect-ratio: 1 / 1; border-radius: 35px;" alt="">
												</picture>
												<div class="muser-info">
													<p>{{ $item->name }}</p>
													<p>{{ $item->city }}</p>
												</div>
											</div>
											<p>{!! $item->text !!}</p>
										</div>

                                        @if ($item->a_player)
                                            @php
                                                if (isset(json_decode($item->a_player)[0])) {
                                                    $file = json_decode($item->a_player)[0]->download_link;
                                                } else {
                                                    $file = '';
                                                }
                                            @endphp
											@if($file)
                                            	<audio class="audio_player" controls src="{{ Voyager::image($file) }}"></audio>
											@endif
                                        @endif

									</div>


								@endforeach
							@endif

						</div>
					</div>
					<div class="mca-arrows">
						<div class="mca-left">
							<svg width="9" height="14" viewBox="0 0 9 14" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M8 12.6663L2 6.99967L8 1.33301" stroke="white" stroke-width="2" />
							</svg>
						</div>
						<div class="mca-right">
							<svg width="9" height="14" viewBox="0 0 9 14" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M1 1.33366L7 7.00033L1 12.667" stroke="white" stroke-width="2" />
							</svg>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
