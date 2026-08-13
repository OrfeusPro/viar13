@if(isset($items) && $items)
	@foreach($items as $item)
<url>

    <loc>{{ route('hb.gallery.category', ['type' => $item->type, 'category' => $item->url]) }}</loc>

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
	@endforeach
@endif
