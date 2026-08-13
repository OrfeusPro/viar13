@php
  $custom_sizes = explode(',', $item['custom_size_prices']);
  $custom_sizes_sale = explode(',', $item['custom_size_prices_sale']);
  if (is_array($custom_sizes_sale) && !empty($custom_sizes_sale) && $custom_sizes_sale[0] != '' && $item['sale_end'] >= \Carbon\Carbon::now()) {
      $custom_sizes_saved = $custom_sizes_sale;
  }
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
                    $price_saved = get_string_between($custom_sizes_saved[$loop->index] ?? '', '[', ']');
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
        @endphp

        @if (is_array($size_clear_vals) && !empty($size_clear_vals))

            {{-- new --}}
            @isset($custom_sizes_saved)
                @php
                    $price_saved = get_string_between($custom_sizes_saved[$loop->index], '[', ']');
                @endphp
            @endisset
            {{-- endnew --}}

            @isset($size_clear_vals[0])
                @isset($size_clear_vals[1])
                    @if(isset($first_size) && $first_size==true)
                        @if($loop->iteration == $current_size_id)
                              {{ $size_clear_vals[0] }}х{{ $size_clear_vals[1] }}{{ $int_globs['cm'] }} -
                                    <?php if (isset($price_saved) && $price_saved != $price): ?>
                                        {{ $price_saved * $contry_mult }}
                                    <?php else: ?>
                                        {{ floatval($price) * $contry_mult }}
                                    <?php endif; ?>
                        @endif
                    @elseif(isset($current_price) && $current_price==true)
                        @if($loop->iteration == $current_size_id)
                            <?php if (isset($sale_price) && $price_saved != $price): ?>
                                  {{ $sale_price * $contry_mult }}
                            <?php else: ?>
                            {{ floatval($price) * $contry_mult }}
                            <?php endif; ?>
                        @endif
                    @elseif(isset($full_current_price) && $full_current_price==true)
                        @if($loop->iteration == $current_size_id)

                            <?php if (isset($price_saved) && $price_saved != $price): ?>

                                    {{ $price_saved * $contry_mult }}

                            <?php else: ?>

                                    {{ floatval($price) * $contry_mult }}

                            <?php endif; ?>

                        @endif
                    @elseif(isset($current_first_size) && $current_first_size==true)
                        @if($loop->iteration == $current_size_id)
                                @if(isset($size_clear_vals[0]) && isset($size_clear_vals[1]))
{{--                                    {{ $size_clear_vals[0] }}х{{ $size_clear_vals[1] }}--}}
                                @endif
                        @endif
                    @else

                    @endif
                @endisset
            @endisset
        @endif

    @endforeach
@endif
