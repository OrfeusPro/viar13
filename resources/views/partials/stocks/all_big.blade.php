@if(count($items_big_sale)>0)
<div class="other-slider">
    @foreach($items_big_sale as $item)
    <div>
        <div class="other-items clearfix">
            @php
            $images = json_decode($item['images'], true);
            $image = '/storage/' . $images[0];
            @endphp
            <img src="{{ $image }}" alt="">
            <div class="text">
                <strong>{{ $cat_name }}</strong>
                <h3>{{ $item['name'] }}</h3>
                <div class="time js__count" data-end="{{ $item['sale_end']}}">
                    <ul>
                        <li class="js_dd"><span></span> <i>{{ trans('gl.dd_text') }}</i></li>
                        <li class="js_hh"><span></span> <i>{{ trans('gl.hh_text') }}</i></li>
                        <li class="js_mm"><span></span> <i>{{ trans('gl.mm_text') }}</i></li>
                        <li class="js_ss"><span></span> <i>{{ trans('gl.ss_text') }}</i></li>
                    </ul>
                </div>

                @php
                    $custom_sizes = explode(',', $item['custom_size_prices']);
                    $custom_sizes_sale = explode(',', $item['custom_size_prices_sale']);
                    if(is_array($custom_sizes_sale) && !empty($custom_sizes_sale) && $custom_sizes_sale[0] != ""){
                        $custom_sizes_saved = $custom_sizes_sale;
                    }
                @endphp

                @include('partials.sale_sizes_list_big')
                <a href="{{ App\Models\GalleryItem::getItemSingleUrlById($item['id']) }}"><span>{{ trans('gl.order_btn') }}</span></a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
