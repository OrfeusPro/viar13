@if(isset($items) && $items)
	@foreach($items as $item)
				@php
                        $custom_sizes = explode(',', $item['custom_size_prices']);
                        $custom_sizes_sale = explode(',', $item['custom_size_prices_sale']);

                        if(is_array($custom_sizes_sale) && !empty($custom_sizes_sale) && $custom_sizes_sale[0] != ""){
                            $custom_sizes_saved = $custom_sizes_sale;
                        }
				@endphp
				@if (is_array($custom_sizes) && !empty($custom_sizes) && $custom_sizes[0] != '' && $type)
                    @if(isset($route) && $route)
                        @if($item->slug == 'portrait-caricature')
                            @continue
                        @endif
                        @if($item->slug == 'simpsons-portrait')
                            @continue
                        @endif
                    @endif
<url>
	@if(isset($route) && $route)
		<loc>{{ route($route, $item->slug) }}</loc>
	@else
		<loc>{{ route('hb.gallery.item.single', ['type' => $type, 'item' => $item->id]) }}</loc>
	@endif
	<lastmod>{{str_replace(" ","T",$item->updated_at)."+00:00"}}</lastmod><changefreq>daily</changefreq><priority>0.9</priority>

    @if(Route::currentRouteName() == 'sitemap.images')
    @if(!empty(json_decode($item->images)))
    @foreach(json_decode($item->images) as $image)
    <image:image>
        <image:loc>{{ Voyager::image($image) }}</image:loc>
    </image:image>
    @endforeach
    @endif

    @if(!empty($item->add_image1))
    <image:image>
        <image:loc>{{ Voyager::image($item->add_image1) }}</image:loc>
    </image:image>
    @endif

    @if(!empty($item->add_image_bg))
    <image:image>
        <image:loc>{{ Voyager::image($item->add_image_bg) }}</image:loc>
    </image:image>
    @endif

    @if(!empty($item->add_image2_inner))
    <image:image>
        <image:loc>{{ Voyager::image($item->add_image2_inner) }}</image:loc>
    </image:image>
    @endif

    @if(!empty($item->add_image3_inner))
    <image:image>
        <image:loc>{{ Voyager::image($item->add_image3_inner) }}</image:loc>
    </image:image>
    @endif

    @if(!empty($item->etc_style_image))
    <image:image>
        <image:loc>{{ Voyager::image($item->etc_style_image) }}</image:loc>
    </image:image>
    @endif

    @if(!empty($item->new_image1))
    <image:image>
        <image:loc>{{ Voyager::image($item->new_image1) }}</image:loc>
    </image:image>
    @endif

    @if(!empty($item->new_image2))
    <image:image>
        <image:loc>{{ Voyager::image($item->new_image2) }}</image:loc>
    </image:image>
    @endif

    @if(!empty($item->new_main_image))
    <image:image>
        <image:loc>{{ Voyager::image($item->new_main_image) }}</image:loc>
    </image:image>
    @endif
    @endif
</url>
    @elseif (!isset($type))
    	<url>
            @php
                $alias = $item->alias ? $item->alias : $item->url;
            @endphp
            <loc>{{ route($route, $alias) }}</loc>
            <lastmod>{{str_replace(" ","T",$item->updated_at)."+00:00"}}</lastmod><changefreq>daily</changefreq><priority>0.9</priority>
        </url>
	@endif

	@endforeach
@endif
