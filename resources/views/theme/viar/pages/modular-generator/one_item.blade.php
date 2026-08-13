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



	<div class="mc-item swiper-slide">
		{{-- <div class="bs-discount">
			-50 %
		</div> --}}
		<div class="mc-item__inner">
			<div class="mc-item__img">
				<picture>
					@if ($webpSrc = image_webp_url($image))
						<source srcset="{{ $webpSrc }}" type="image/webp">
					@endif
					<img width="515" height="527" alt="{{ App\Models\GalleryItem::getTransName($item->id) }}" title="{{ App\Models\GalleryItem::getTransName($item->id) }}" class="" src="{{ $image }}" data-src="{{ $image }}" loading="lazy"/>
				</picture>
			</div>
			<div class="mc-item__content">
				<div class="mc-item__title">
					<a href="{{ route('hb.gallery.item.single', ['type' => $type, 'item' => $item->id]) }}" alt="{{ App\Models\GalleryItem::getTransName($item->id) }}" title="{{ App\Models\GalleryItem::getTransName($item->id) }}">{{ App\Models\GalleryItem::getTransName($item->id) }}</a>
				</div>
				<div class="mc-item__info">
					<p class="mc-item-size">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path fill-rule="evenodd" clip-rule="evenodd" d="M5.46667 5H5V5.46667V10.1333H5.93333V6.5933L9.80335 10.4633L10.4633 9.80335L6.5933 5.93333H10.1333V5H5.46667ZM18.5333 5H19V5.46667V10.1333H18.0667V6.5933L14.1966 10.4633L13.5367 9.80335L17.4067 5.93333H13.8667V5H18.5333ZM19 19H18.5333H13.8667V18.0667H17.4067L13.5367 14.1966L14.1966 13.5367L18.0667 17.4067V13.8667H19V18.5333V19ZM5.46667 19H5V18.5333V13.8667H5.93333V17.4067L9.80335 13.5367L10.4633 14.1966L6.5933 18.0667H10.1333V19H5.46667Z" fill="#FA7846"></path>
						</svg>     
						<span>
							@include(env('THEME_RESOURCES') . 'pages.gallery.custom_sizes_calc_cat', ['current_first_size' => true])&nbsp;
						</span>
					</p>
					<p class="mc-avail">
						<svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
							<g clip-path="url(#clip0_214_3)">
							<path d="M14.8315 2.21218C14.6062 1.98689 14.2409 1.98689 14.0156 2.21218L4.66372 11.5641L0.985349 7.88568C0.760084 7.66036 0.394781 7.66036 0.169458 7.88568C-0.0558351 8.11097 -0.0558351 8.47625 0.169458 8.70157L4.25582 12.7879C4.48103 13.0131 4.84645 13.0132 5.07171 12.7879L14.8315 3.02807C15.0568 2.80275 15.0568 2.43748 14.8315 2.21218Z" fill="#1F9750"></path>
							</g>
							<defs>
							<clipPath id="clip0_214_3">
							<rect width="15" height="15" fill="white"></rect>
							</clipPath>
							</defs>
						</svg>
						<span>@lang('cart_new.in_stock')</span>
					</p>
				</div>
				<div class="mc-item__info">
					<div class="mc-price">
						<p>
						@if($item->minSumPrice)
							@lang('gl.price_from_text') <span class="cats-m-price">@include(env('THEME_RESOURCES') . 'pages.gallery.custom_sizes_calc_cat', ['full_current_price' => true])</span>
						@endif
					</p>
					</div>
					<a href="{{ route('hb.gallery.item.single', ['type' => $type, 'item' => $item->id]) }}" tabindex="{{ $item->id }}" class="mc-btn mm-btn">
						@lang('collage.index12')
					</a>
				</div>
			</div>
		</div>
	</div>

@endforeach
