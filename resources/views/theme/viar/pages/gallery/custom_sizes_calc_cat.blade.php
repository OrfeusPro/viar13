@php
$custom_sizes = explode(',', $item['custom_size_prices']);
$custom_sizes_sale = explode(',', $item['custom_size_prices_sale']);
if (is_array($custom_sizes_sale) && !empty($custom_sizes_sale) && $custom_sizes_sale[0] != '' && $item['sale_end'] >= \Carbon\Carbon::now()) {
    $custom_sizes_saved = $custom_sizes_sale;
}

$recDiscount = $recommendationDiscount ?? null;
$recDiscountMultiplier = $recDiscount ? (100 - (int)$recDiscount) / 100 : 1;
@endphp

@php
$current_size = false;
$current_size_id = 1;
$i = 0;
if(isset($_GET['size']) && $_GET['size'])
{
    $current_size = $_GET['size'];
}

if($current_size)
{
    if (is_array($custom_sizes) && !empty($custom_sizes) && $custom_sizes[0] != '') {
        foreach ($custom_sizes as $size) {
            $i++;
            $price = get_string_between($size, '[', ']');
            $size_clear = substr($size, 0, strpos($size, '['));
            $size_clear_vals = explode('x', $size_clear);

            if (is_array($size_clear_vals) && !empty($size_clear_vals))
            {
                if(isset($custom_sizes_saved))
                {
                    $price_saved = get_string_between($custom_sizes_saved[$i - 1] ?? '', '[', ']');
                }

                if(isset($size_clear_vals[0]))
                {
                    if(isset($size_clear_vals[1]))
                    {
                        if($current_size == $size_clear_vals[0]."x".$size_clear_vals[1])
                        {
                            $current_size_id = $i;
                        }
                    }
                }
            }
        }

    }

}

@endphp

@if (is_array($custom_sizes) && !empty($custom_sizes) && $custom_sizes[0] != '')
    @foreach ($custom_sizes as $size)
        @php
            $price = get_string_between($size, '[', ']');
            $size_clear = substr($size, 0, strpos($size, '['));
            $size_clear_vals = explode('x', $size_clear);

            $currentPriceSaved = null;
            if (isset($custom_sizes_saved, $custom_sizes_saved[$loop->index])) {
                $currentPriceSaved = get_string_between($custom_sizes_saved[$loop->index], '[', ']');
            }

            if ($currentPriceSaved && $currentPriceSaved != $price) {
                $originalPrice = floatval($currentPriceSaved) * $contry_mult;
            } else {
                $originalPrice = floatval($price) * $contry_mult;
            }

            $finalPrice = round($originalPrice * $recDiscountMultiplier, 2);
        @endphp

        @if (is_array($size_clear_vals) && !empty($size_clear_vals))
            @isset($size_clear_vals[0])
                @isset($size_clear_vals[1])
                    @if(isset($first_size) && $first_size==true)
                        @if($loop->iteration == $current_size_id)
                            <span>
                                {{ $size_clear_vals[0] }}х{{ $size_clear_vals[1] }}{{ $int_globs['cm'] }} -
                                <span>{{ $finalPrice }}€</span>
                            </span>
                        @endif
                    @elseif(isset($current_price) && $current_price==true)
                        @if($loop->iteration == $current_size_id)
                            <span>{{ $finalPrice }}</span> €
                        @endif
                    @elseif(isset($full_current_price) && $full_current_price==true)
                        @if($loop->iteration == $current_size_id)
                            @if($currentPriceSaved && $currentPriceSaved != $price)
                                <span>{{ floatval($currentPriceSaved) * $contry_mult }}€</span>
                                <span>{{ floatval($price) * $contry_mult }}€</span>
                            @else
                                <span>{{ floatval($price) * $contry_mult }}€</span>
                            @endif
                        @endif
                    @elseif(isset($current_first_size) && $current_first_size==true)
                        @if($loop->iteration == $current_size_id)
                            <span>
                                @if(isset($size_clear_vals[0]) && isset($size_clear_vals[1]))
                                    {{ $size_clear_vals[0] }}х{{ $size_clear_vals[1] }}
                                @endif
                            </span>
                        @endif
                    @else
                        <li class="@if($loop->iteration == $current_size_id) active @endif">

                            @if(isset($merchant))
                                <div itemprop="offers" itemtype="https://schema.org/Offer" itemscope>
                                    <link itemprop="url" href="{{ url(Request::url()) }}" />
                                    <meta itemprop="availability" content="https://schema.org/InStock" />
                                    <meta itemprop="priceCurrency" content="EUR" />
                                    @php
                                        $strippedName = strip_tags($item->name);
                                        $cleanName = str_replace('"', '', $strippedName);
                                    @endphp
                                    <meta itemprop="description" content="{{ $cleanName.' '.$size_clear_vals[0] }}х{{ $size_clear_vals[1] }}{{ $int_globs['cm'] }}" >

                                    <div class="checkbox-item">
                                        <label>
                                            <input type="checkbox" class="calcSize js_size"
                                                   data-size="{{ $size_clear_vals[0] }}x{{ $size_clear_vals[1] }}"
                                                   data-price="{{ $finalPrice }}"
                                                   data-id="{{ $loop->iteration }}"
                                                   name="activities" @if($loop->iteration == $current_size_id) checked @endif>
                                            <span class="checkmark"></span>
                                            <p>{{ $size_clear_vals[0] }}х{{ $size_clear_vals[1] }}{{ $int_globs['cm'] }} -
                                                <span itemprop="price">{{ $finalPrice }}</span>€
                                            </p>
                                        </label>
                                    </div>
                                </div>
                            @else
                                <div class="checkbox-item">
                                    <label>
                                        <input type="checkbox" class="calcSize js_size"
                                               data-size="{{ $size_clear_vals[0] }}x{{ $size_clear_vals[1] }}"
                                               data-price="{{ $finalPrice }}"
                                               data-id="{{ $loop->iteration }}"
                                               name="activities" @if($loop->iteration == $current_size_id) checked @endif>
                                        <span class="checkmark"></span>
                                        <p>{{ $size_clear_vals[0] }}х{{ $size_clear_vals[1] }}{{ $int_globs['cm'] }} -
                                            <span>{{ $finalPrice }}€</span>
                                        </p>
                                    </label>
                                </div>
                            @endif
                        </li>
                    @endif
                @endisset
            @endisset
        @endif
    @endforeach
@endif
