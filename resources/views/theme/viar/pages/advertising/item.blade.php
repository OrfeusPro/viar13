@if(isset($items) && $items)
{{-- {{ dd($module) }} --}}
	@foreach($items as $item)

				@php
					$custom_sizes = explode(',', $item['custom_size_prices']);
					$custom_sizes_sale = explode(',', $item['custom_size_prices_sale']);
					
					if(is_array($custom_sizes_sale) && !empty($custom_sizes_sale) && $custom_sizes_sale[0] != ""){
						$custom_sizes_saved = $custom_sizes_sale;
					}
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
										{{-- <title>{{$item->getTranslatedAttribute('name', App::getLocale(),'ru')}}</title> --}}
										<title>{{$item->name}}</title>
										<link>{{ route('hb.gallery.item.single', ['type' => $type, 'item' => $item->id]) }}?size={{ $size_clear_vals[0] }}x{{ $size_clear_vals[1] }}</link>
										{{-- @if($item->getTranslatedAttribute('description', App::getLocale(),'ru'))
											<description>{{ \Illuminate\Support\Str::limit($item->getTranslatedAttribute('description', App::getLocale(),'ru'), 5000, '...') }}</description>
										@endif --}}
										@if($item->description)
											<description>{{ \Illuminate\Support\Str::limit($item->description, 5000, '...') }}</description>
										@endif
										
										@php
										$images = json_decode($item->images, true);
										@endphp
										@if ($images)
											@foreach ($images as $image)
												@php $src = '/storage/' . $image; @endphp
												@if($loop->iteration == 1)
													<g:image_link>{{ asset($src) }}</g:image_link>
												@else
													<g:additional_image_link>{{ asset($src) }}</g:additional_image_link>
												@endif
											@endforeach
										@endif
										<g:id>{{$item->id}}_{{ $size_clear_vals[0] }}x{{ $size_clear_vals[1] }}</g:id>
										@if (isset($price_saved) && $price_saved != $price)
											<g:price>{{ (float)$price_saved * $contry_mult }} EUR</g:price>
											<g:sale_price>{{ floatval($price) * $contry_mult }} EUR</g:sale_price>
										@else
											<g:price>{{ floatval($price) * $contry_mult }} EUR</g:price>
										@endif
										<g:condition>new</g:condition>
										<g:identifier_exists>no</g:identifier_exists>
										<g:availability>In stock</g:availability>
										<g:item_group_id>{{$item->id}}</g:item_group_id>
										<g:brand>ViarCanvas</g:brand>
										
									</item>
									

								@endisset
							@endisset
						@endif

					@endforeach
				@endif

				{{-- {{ dd(\DB::table('gallery_sizes')->where('id', $item->id)->get()) }} --}}
				{{-- {{ dd($item->getSizeByItemId($item->id)) }} --}}
				{{-- {{ dd($item) }} --}}
				{{-- 
				<g:price>{{$itm->price}} UAH</g:price>
				@if($itm->price_old)<g:sale_price>{{$itm->price_old}} UAH</g:sale_price>@endif

				<g:item_group_id>{{$itm->category->title}}</g:item_group_id>
				--}}

	@endforeach
@endif