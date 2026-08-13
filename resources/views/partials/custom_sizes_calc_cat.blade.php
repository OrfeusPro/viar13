@php
if (!function_exists('get_string_between')) {
    function get_string_between($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) {
            return '';
        }
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }
}
@endphp

@php
$custom_sizes = explode(',', $item['custom_size_prices']);
$custom_sizes_sale = explode(',', $item['custom_size_prices_sale']);
if (is_array($custom_sizes_sale) && !empty($custom_sizes_sale) && $custom_sizes_sale[0] != '' && $item['sale_end'] >= \Carbon\Carbon::now()) {
    $custom_sizes_saved = $custom_sizes_sale;
}
@endphp
<div class="size">
    {{-- sales --}}

    <ul>
        {{-- default --}}
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
                    <li>
                        <a class="calcSize js_size" href="javascript:void(0)"
                            data-size="{{ $size_clear_vals[0] }}x{{ $size_clear_vals[1] }}"
                            data-price="<?php if (!isset($price_saved)): ?>{{ floatval($price) * $contry_mult }}<?php else: ?>{{ floatval($price_saved) * $contry_mult }}<?php endif; ?>"><strong>
                                {{ $size_clear_vals[0] }}
                                <span>{{ trans('gl.cm') }}</span></strong> х
                            <strong> {{ $size_clear_vals[1] }}<span>{{ trans('gl.cm') }}</span></strong> -
                            <strong>
                                <?php if (isset($price_saved) && $price_saved != $price): ?>
                                <strike>{{ $price * $contry_mult }} €</strike>
                                <span style="color:#e2761d"> {{ $price_saved * $contry_mult }}</span> €
                                <?php else: ?>
                                {{ floatval($price) * $contry_mult }} €
                                <?php endif; ?>
                            </strong>
                        </a>
                    </li>
                    @endisset
                    @endisset
                @endif

            @endforeach
        @endif
    </ul>
</div>
