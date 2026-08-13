@if(isset($items) && $items)
{{-- {{ dd($module) }} --}}
	@foreach($items as $item)

				@php
					$custom_sizes = explode(',', $item['custom_size_prices']);
					$custom_sizes_sale = explode(',', $item['custom_size_prices_sale']);
					
					if(is_array($custom_sizes_sale) && !empty($custom_sizes_sale) && $custom_sizes_sale[0] != ""){
						$custom_sizes_saved = $custom_sizes_sale;
					}

					$cat = $item->cats->first()->translate('lv')->name ?? "";
					$main_cat = \App\Models\GalleryType::where('id', $item->id_type)->first()->translate('lv' )->name ?? "";
					$full_cat = $main_cat ." > ". $cat;
				@endphp

				@if (is_array($custom_sizes) && !empty($custom_sizes) && $custom_sizes[0] != '')
					@foreach ($custom_sizes as $size)
						@php
							$price = get_string_between($size, '[', ']');
							$size_clear = substr($size, 0, strpos($size, '['));
							$size_clear_vals = explode('x', $size_clear);
						@endphp

						@if (is_array($size_clear_vals) && !empty($size_clear_vals))

							{{-- new --}}
							@isset($custom_sizes_saved)
								@php
									if(isset($custom_sizes_saved[$loop->index]))
									{
										$price_saved = get_string_between($custom_sizes_saved[$loop->index], '[', ']');
									}
									else {
										
									}
								@endphp
							@endisset
							{{-- endnew --}}

							@isset($size_clear_vals[0])
								@isset($size_clear_vals[1])

	<item>
		<name>{{ $main_cat }} {{$item->getTranslatedAttribute('name')}} {{ $size_clear_vals[0] }}x{{ $size_clear_vals[1] }}</name>
		<link>{{ route('hb.gallery.item.single', ['type' => $type, 'item' => $item->id]) }}?size={{ $size_clear_vals[0] }}x{{ $size_clear_vals[1] }}</link>
		@if (isset($price_saved) && $price_saved != $price)
			<price>{{ (float)$price_saved * $contry_mult }}</price>												
		@else
			<price>{{ floatval($price) * $contry_mult }}</price>
		@endif
		@php
		$images = json_decode($item->images, true);
		@endphp
		@if ($images)
			@foreach ($images as $image)
				@php $src = '/storage/' . $image; @endphp
				@if($loop->iteration == 1)
					<image>{{ asset($src) }}</image>
					@break
				@endif
			@endforeach
		@endif
		<category_full>{{ $full_cat }}</category_full>
		<manufacturer>ViarCanvas</manufacturer>
		<in_stock>5</in_stock>
		<delivery_cost_riga>5</delivery_cost_riga>
		<delivery_latvija>5</delivery_latvija>
		<delivery_venipak>5</delivery_venipak>
		<delivery_days_riga>3</delivery_days_riga>
		<delivery_days_latvija>3</delivery_days_latvija>
		<used>0</used>
		<adult>no</adult>
	</item>
									

								@endisset
							@endisset
						@endif

					@endforeach
				@endif


	@endforeach
@endif