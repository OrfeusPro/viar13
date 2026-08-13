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
    $cspf1 = rtrim($item['custom_size_prices'], ',');
    $custom_sizes = explode(',', $cspf1);

    $cspf1_sale = rtrim($item['custom_size_prices_sale'], ',');
    $custom_sizes_sale = explode(',', $cspf1_sale);
    if(is_array($custom_sizes_sale) && !empty($custom_sizes_sale) && $custom_sizes_sale[0] != ""
        && ($item['sale_end'] >= \Carbon\Carbon::now()) )
    {
        $custom_sizes_saved = $custom_sizes_sale;
    }

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
                    echo '<script>window.location = "/err_sizes?size='.$size_clear.'?page=custom_sizes_calc";</script>';
                    die();
                }
            @endphp

            @if(is_array($size_clear_vals) && !empty($size_clear_vals))

                {{-- new --}}
                @isset($custom_sizes_saved)
                    @php
                        $price_saved = get_string_between($custom_sizes_saved[$loop->index], '[', ']');
                        @endphp
                @endisset
                {{-- endnew --}}
                @php

                @endphp

                @php
                    $price_ex =  explode("-", $price);

                    if(count($price_ex)>1){
                        $old_price = $price_ex[0];
                        $new_price = $price_ex[1];
                    }else{
                        $old_price = $price;
                        $new_price = $price;
                    }
                @endphp

                <li>
                    <a class="calcSize js_size" href="javascript:void(0)"
                    data-size="{{ $size_clear_vals[0] }}x{{ $size_clear_vals[1] }}"
                    @if($new_price != $old_price)
                    data-price="{{ $new_price*$contry_mult }}"
                    data-old-price="{{ $old_price*$contry_mult }}"
                    @else
                    data-price="{{ $old_price*$contry_mult }}"
                    data-old-price="{{ $old_price*$contry_mult }}"
                    @endif

                    ><strong>
                        {{ $size_clear_vals[0] }}
                        <span>{{ trans('gl.cm') }}</span></strong> х
                        <strong> {{ $size_clear_vals[1] }}<span>{{ trans('gl.cm') }}</span></strong> -
                        <strong>
                            <?php if(isset($old_price) && $old_price != $new_price) : ?>
                                <strike>{{ $old_price*$contry_mult }} €</strike>
                                <span style="color:#e2761d">  {{ $new_price*$contry_mult }}</span> €
                            <?php else : ?>
                                {{ $price*$contry_mult }} €
                            <?php endif; ?>
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
    $cspf2 = rtrim($item['custom_size_prices_form2'], ',');
    $custom_sizes2 = explode(',', $cspf2);
@endphp

<div class="size" style="display:none;">

    <ul>
        @if(is_array($custom_sizes2) && !empty($custom_sizes2) && $custom_sizes2[0] != "")
        @foreach($custom_sizes2 as $size)
            @php
                $price = get_string_between($size, '[', ']');
                $size_clear = substr($size, 0, strpos($size, "["));
                $size_clear_vals = explode('x', $size_clear);
            @endphp

            @if(is_array($size_clear_vals) && !empty($size_clear_vals))

                {{-- new --}}
                @isset($custom_sizes_saved)
                    @php
                        $price_saved = get_string_between($custom_sizes_saved[$loop->index], '[', ']');
                        @endphp
                @endisset
                {{-- endnew --}}
                @php

                @endphp

                @php
                $price_ex =  explode("-", $price);

                if(count($price_ex)>1){
                    $old_price = $price_ex[0];
                    $new_price = $price_ex[1];
                }else{
                    $old_price = $price;
                    $new_price = $price;
                }
                @endphp

                <li>
                    <a class="calcSize js_size" href="javascript:void(0)"
                    data-size="{{ $size_clear_vals[0] }}x{{ $size_clear_vals[1] }}"
                    @if($new_price != $old_price)
                    data-price="{{ $new_price*$contry_mult }}"
                    data-old-price="{{ $old_price*$contry_mult }}"
                    @else
                    data-price="{{ $old_price*$contry_mult }}"
                    data-old-price="{{ $old_price*$contry_mult }}"
                    @endif

                    ><strong>
                        {{ $size_clear_vals[0] }}
                        <span>{{ trans('gl.cm') }}</span></strong> х
                        <strong> {{ $size_clear_vals[1] }}<span>{{ trans('gl.cm') }}</span></strong> -
                        <strong>
                            <?php if(isset($old_price) && $old_price != $new_price) : ?>
                                <strike>{{ $old_price*$contry_mult }} €</strike>
                                <span style="color:#e2761d">  {{ $new_price*$contry_mult }}</span> €
                            <?php else : ?>
                                {{ $price*$contry_mult }} €
                            <?php endif; ?>
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
    $cspf3 = rtrim($item['custom_size_prices_form3'], ',');
    $custom_sizes3 = explode(',', $cspf3);
    // sale price?
@endphp

<div class="size" style="display:none;">

    <ul>
        @if(is_array($custom_sizes3) && !empty($custom_sizes3) && $custom_sizes3[0] != "")
        @foreach($custom_sizes3 as $size)
            @php
                $price = get_string_between($size, '[', ']');
                $size_clear = substr($size, 0, strpos($size, "["));
                $size_clear_vals = explode('x', $size_clear);
            @endphp

            @if(is_array($size_clear_vals) && !empty($size_clear_vals))

                {{-- new --}}
                @isset($custom_sizes_saved)
                    @php
                        $price_saved = get_string_between($custom_sizes_saved[$loop->index], '[', ']');
                        @endphp
                @endisset
                {{-- endnew --}}
                @php

                @endphp

                @php
                $price_ex =  explode("-", $price);

                if(count($price_ex)>1){
                    $old_price = $price_ex[0];
                    $new_price = $price_ex[1];
                }else{
                    $old_price = $price;
                    $new_price = $price;
                }
                @endphp

                <li>
                    <a class="calcSize js_size" href="javascript:void(0)"
                    data-size="{{ $size_clear_vals[0] }}x{{ $size_clear_vals[1] }}"
                    @if($new_price != $old_price)
                    data-price="{{ $new_price*$contry_mult }}"
                    data-old-price="{{ $old_price*$contry_mult }}"
                    @else
                    data-price="{{ $old_price*$contry_mult }}"
                    data-old-price="{{ $old_price*$contry_mult }}"
                    @endif

                    ><strong>
                        {{ $size_clear_vals[0] }}
                        <span>{{ trans('gl.cm') }}</span></strong> х
                        <strong> {{ $size_clear_vals[1] }}<span>{{ trans('gl.cm') }}</span></strong> -
                        <strong>
                            <?php if(isset($old_price) && $old_price != $new_price) : ?>
                                <strike>{{ $old_price}} €</strike>
                                <span style="color:#e2761d">  {{ $new_price*$contry_mult }}</span> €
                            <?php else : ?>
                                {{ $price*$contry_mult }} €
                            <?php endif; ?>
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
