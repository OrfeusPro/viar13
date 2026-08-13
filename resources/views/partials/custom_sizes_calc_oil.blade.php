@php
if (!function_exists('get_string_between')){
    function get_string_between($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }
}
@endphp

@php
    $cspf1 = rtrim($item['oil_sizes_calc_form1'], ',');
    $custom_sizes = explode(',', $cspf1);


@endphp
<div class="size">
    {{-- sales --}}
<ul>
    {{-- default --}}
    @if(is_array($custom_sizes) && !empty($custom_sizes) && $custom_sizes[0] != "")
        @foreach($custom_sizes as $size)
            @php
                $price = get_string_between($size, '[', ']');
                $size_clear = substr($size, 0, strpos($size, "["));
                $size_clear_vals = explode('x', $size_clear);
                if(!isset($size_clear_vals[0]) || !isset($size_clear_vals[1])){
                    echo '<script>window.location = "/err_sizes?size='.$size_clear.'?page=custom_sizes_calc_oil";</script>';
                    die();
                }
            @endphp

            @if(is_array($size_clear_vals) && !empty($size_clear_vals))
                <li>
                    <a class="calcSize js_size" href="javascript:void(0)"
                    data-size="{{ $size_clear_vals[0] }}x{{ $size_clear_vals[1] }}"
                    data-price="<?php if(!isset($price_saved)) : ?>{{ $price*$contry_mult }}<?php else : ?>{{ $price_saved*$contry_mult }}<?php endif; ?>"
                    ><strong>
                        {{ $size_clear_vals[0] }}
                        <span>{{ trans('gl.cm') }}</span></strong> х
                        <strong> {{ $size_clear_vals[1] }}<span>{{ trans('gl.cm') }}</span></strong> -
                        <strong>
                            {{ $price*$contry_mult }} €
                        </strong>
                    </a>
                </li>
            @endif

        @endforeach
    @endif
</ul>

</div>

{{-- форма-2 --}}
@php
    $cspf2 = rtrim($item['oil_sizes_calc_form2'], ',');
    $custom_sizes2 = explode(',', $cspf2);
@endphp

<div class="size" style="display:none;">
    {{-- sales --}}

<ul>
    {{-- default --}}
    @if(is_array($custom_sizes2) && !empty($custom_sizes2) && $custom_sizes2[0] != "")
        @foreach($custom_sizes2 as $size)
            @php
                $price = get_string_between($size, '[', ']');
                $size_clear = substr($size, 0, strpos($size, "["));
                $size_clear_vals = explode('x', $size_clear);
            @endphp

            @if(is_array($size_clear_vals) && !empty($size_clear_vals))
                <li>
                    <a class="calcSize js_size" href="javascript:void(0)"
                    data-size="{{ $size_clear_vals[0] }}x{{ $size_clear_vals[1] }}"
                    data-price="<?php if(!isset($price_saved)) : ?>{{ $price }}<?php else : ?>{{ $price_saved }}<?php endif; ?>"
                    ><strong>
                        {{ $size_clear_vals[0] }}
                        <span>{{ trans('gl.cm') }}</span></strong> х
                        <strong> {{ $size_clear_vals[1] }}<span>{{ trans('gl.cm') }}</span></strong> -
                        <strong>
                            {{ $price}} €
                        </strong>
                    </a>
                </li>
            @endif

        @endforeach
    @endif
</ul>

</div>
{{-- форма-3 --}}
@php
    $cspf3 = rtrim($item['oil_sizes_calc_form3'], ',');
    $custom_sizes3 = explode(',', $cspf3);
@endphp

<div class="size" style="display:none;">
    {{-- sales --}}

<ul>
    {{-- default --}}
    @if(is_array($custom_sizes3) && !empty($custom_sizes3) && $custom_sizes3[0] != "")
        @foreach($custom_sizes3 as $size)
            @php
                $price = get_string_between($size, '[', ']');
                $size_clear = substr($size, 0, strpos($size, "["));
                $size_clear_vals = explode('x', $size_clear);
            @endphp

            @if(is_array($size_clear_vals) && !empty($size_clear_vals))
                <li>
                    <a class="calcSize js_size" href="javascript:void(0)"
                    data-size="{{ $size_clear_vals[0] }}x{{ $size_clear_vals[1] }}"
                    data-price="<?php if(!isset($price_saved)) : ?>{{ $price }}<?php else : ?>{{ $price_saved }}<?php endif; ?>"
                    ><strong>
                        {{ $size_clear_vals[0] }}
                        <span>{{ trans('gl.cm') }}</span></strong> х
                        <strong> {{ $size_clear_vals[1] }}<span>{{ trans('gl.cm') }}</span></strong> -
                        <strong>
                            {{ $price}} €
                        </strong>
                    </a>
                </li>
            @endif

        @endforeach
    @endif
</ul>

</div>



<script>
    $(document).on('click', '.calcForm', function(e){
        setTimeout(() => {
            var form_id = $(this).data('id');
            form_id=form_id-1;
            $('.size>.size').hide(0);
            $('.size>.size').eq(form_id).show(0);

        }, 200);
    });
</script>
