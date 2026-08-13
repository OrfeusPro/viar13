@foreach ($items as $item)
@php
	$images = json_decode($item->images, true);
	$image = '/storage/' . $images[0];
	if(isset($images[0])){
		$image = '/storage/' . $images[0];
	}
	else{
		$image = "";
	}
@endphp

	<div class="cats-item /*bs-dc*/ @if(isset($slide) && $slide) swiper-slide @endif">
		{{-- <div class="bs-discount">
			-50 %
		</div> --}}
		<div class="cats-item__inner">
			<div class="ci-img">
				<a href="{{ route('hb.gallery.item.single', ['type' => $type, 'item' => $item->id]) }}">
					<picture>
						@if ($webpSrc = image_webp_url($image))
							<source srcset="{{ $webpSrc }}" type="image/webp">
						@endif
						<source srcset="{{ asset($image) }}" type="image/jpeg">
						<img width="515" height="527" @altAttrs($item, 'images', $image, null, App\Models\GalleryItem::getTransName($item->id)) class="" src="{{ $image }}" data-src="{{ $image }}" loading="lazy"/>
					</picture>
				</a>
			</div>
			<div class="ci-content">
				<p class="mc-item__title"><a href="{{ route('hb.gallery.item.single', ['type' => $type, 'item' => $item->id]) }}" alt="{{ App\Models\GalleryItem::getTransName($item->id) }}" title="{{ App\Models\GalleryItem::getTransName($item->id) }}">{{ App\Models\GalleryItem::getTransName($item->id) }}</a></p>
				<div class="ci-row">
					<span>@lang('gallery.product_code'): {{ $item->id }}</span>
					<span>
						<svg width="10" height="10" viewBox="0 0 10 10" fill="none"
							xmlns="http://www.w3.org/2000/svg">
							<g clip-path="url(#clip0_261_1578)">
								<path
									d="M9.88782 1.47471C9.73765 1.32451 9.49411 1.32451 9.34389 1.47471L3.10931 7.70931L0.657062 5.25704C0.506886 5.10682 0.26335 5.10682 0.113135 5.25704C-0.0370606 5.40723 -0.0370606 5.65075 0.113135 5.80097L2.83738 8.52521C2.98752 8.67535 3.23113 8.67538 3.38131 8.52521L9.88782 2.01863C10.038 1.86842 10.038 1.6249 9.88782 1.47471Z"
									fill="#1F9750" />
							</g>
							<defs>
								<clipPath id="clip0_261_1578">
									<rect width="10" height="10" fill="white" />
								</clipPath>
							</defs>
						</svg>
						<span>@lang('cart_new.in_stock')</span>
					</span>
				</div>
				@isset($glob)
				<div class="ci-list">
					<ul>
						<li>{{ $glob['art_jenre_text'] }}</li>
						<li>{{ $glob['art_style_text'] }}</li>
						<li>{{ $glob['art_size_text'] }}</li>
					</ul>
					<ul>
						<li>
							@php 
								$items_arr_genre = [];
								foreach ($item->get_genre as $genre) {
									$items_arr_genre[] = translated_value($genre, 'name', $genre->name);
								}	
								$items_arr_genre = implode(", ",$items_arr_genre);				
							@endphp
							{{ $items_arr_genre}}&nbsp;
						</li>
						<li>
							@php 
								$items_arr_style = [];
								foreach ($item->get_style as $style) {
									$items_arr_style[] = translated_value($style, 'name', $style->name);
								}	
								$items_arr_style = implode(", ",$items_arr_style);				
							@endphp
							{{ $items_arr_style}}&nbsp;
						
						</li>
						<li>@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.custom_sizes_calc_cat', ['current_first_size' => true])&nbsp;
						</li>
					</ul>
				</div>
				@endisset
				<div class="ci-act">
					<div class="mc-price">
						@if($item->minSumPrice)
							@lang('gl.price_from_text') <span class="cats-m-price">@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.custom_sizes_calc_cat', ['full_current_price' => true])</span>
						@endif
						{{-- <span class="mc-n-price">105€</span> --}}
						{{-- <span class="mc-o-price">150€</span> --}}
					</div>
					<a href="{{ route('hb.gallery.item.single', ['type' => $type, 'item' => $item->id]) }}" tabindex="{{ $item->id }}" class="mc-btn mm-btn">
						@lang('collage.index12')
					</a>
				</div>
			</div>
		</div>
	</div>
@endforeach
