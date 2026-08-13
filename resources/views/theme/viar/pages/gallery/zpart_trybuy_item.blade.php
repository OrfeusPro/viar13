<div class="cp-block @if(!$active) hidden-block @endif" id="trybuy_{{ $item->id }}">
	@php
		$images = json_decode($item->images, true);
		if(isset($images[1])){
			$image = '/storage/' . $images[1];
		}
		else{
			$image = '/storage/' . $images[0];
		}
	@endphp
	<div class="cp-v">
		<div class="cp-v_img">
			{{--
			<div class="bs-discount">
				-50 %
			</div>
			--}}
			<picture>
				@if($webpSrc = image_webp_url($image))
					<source srcset="{{ $webpSrc }}" type="image/webp">
				@endif
				<source srcset="https://viarcanvas.com/{{ $image }}" type="image/jpeg">
				<img width="430" height="535" src="https://viarcanvas.com/{{ $image }}"
					@altAttrs($item, 'images', $image, null, 'ViarCanvas') loading="lazy">
			</picture>
		</div>
		<div class="cp-v_content">
			<p class="cp-v-text">@lang('gallery.trybuy_t2_title'):</p>
			<div class="timer" data-endtime="{{ $item->sale_end }}">
				<div class="day">
					<span></span> @lang('gallery.trybuy_t2_days')
				</div>
				<div class="hour">
					<span></span> @lang('gallery.trybuy_t2_hours')
				</div>
				<div class="minute">
					<span></span> @lang('gallery.trybuy_t2_minutes')
				</div>
				<div class="seconds">
					<span></span> @lang('gallery.trybuy_t2_seconds')
				</div>
			</div>
			<div class="cp-v-title">
				{{ App\Models\GalleryItem::getTransName($item->id) }}
			</div>
			<p class="cp-v-size">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none"
					xmlns="http://www.w3.org/2000/svg">
					<path fill-rule="evenodd" clip-rule="evenodd"
						d="M5.46667 5H5V5.46667V10.1333H5.93333V6.5933L9.80335 10.4633L10.4633 9.80335L6.5933 5.93333H10.1333V5H5.46667ZM18.5333 5H19V5.46667V10.1333H18.0667V6.5933L14.1966 10.4633L13.5367 9.80335L17.4067 5.93333H13.8667V5H18.5333ZM19 19H18.5333H13.8667V18.0667H17.4067L13.5367 14.1966L14.1966 13.5367L18.0667 17.4067V13.8667H19V18.5333V19ZM5.46667 19H5V18.5333V13.8667H5.93333V17.4067L9.80335 13.5367L10.4633 14.1966L6.5933 18.0667H10.1333V19H5.46667Z"
						fill="#FA7846"></path>
				</svg>
				<span>@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.custom_sizes_calc_cat', ['current_first_size' => true])</span>
			</p>
			<p class="cp-v-price">
				@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.custom_sizes_calc_cat', ['full_current_price' => true])
			</p>

			<a href="{{ route('hb.gallery.item.single', ['type' => \App\Models\GalleryType::where('id', $item->id_type)->first()->url, 'item' => $item->id]) }}" class="mm-btn">@lang("gallery.trybuy_btn")</a>
		</div>
	</div>
</div>
