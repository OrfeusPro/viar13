<section class="popular">
    <div class="popular-content">
        <div class="title">
            <h3>{{ $mod_head['pop_items_title'] }}</h3>
            <ul>
                <li><i class="icon-icon6"></i></li>
                <li><i class="icon-icon6"></i></li>
                <li><i class="icon-icon6"></i></li>
                <li><i class="icon-icon6"></i></li>
                <li><i class="icon-icon6"></i></li>
            </ul>
        </div>
        <div class="popular-slider">
            @foreach($pop_mod_items->chunk(2) as $pop_mod_item)
            <div class="popular-item">
                @foreach($pop_mod_item as $item)
                <div>
                    @if($item->images)
                    <div class="img">
                        <img alt="{{ $item['name'] }}" title="{{ $item['name'] }}"
                            src="{{ Voyager::image( json_decode($item->images)[0] ) }}">
                    </div>
                    @endif
                    <h5>{{ $item['name'] }}</h5>
                    <i>{{ trans('gl.pic_size') }}
                        <span> {{ App\Models\GalleryItem::getSizeByItemId($item['id']) }}</span></i>
                    <p>{{ trans('gl.price_text') }}
                        <span>{{ trans('gl.price_from_text') }} {{ $item['price_from'] }} €</span></p>
                    <a href="{{ App\Models\GalleryItem::getItemSingleUrlById($item['id']) }}">
                        <span>{{ $mod_head['pop_items_order_text'] }}<i></i></span></a>
                </div>
                @endforeach
            </div>
            @endforeach
        </div>
    </div>
</section>
