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

<ul>
    {{-- default --}}
    @if(is_array($custom_sizes) && !empty($custom_sizes) && $custom_sizes[0] != "")
        @foreach($custom_sizes as $size)
            @php
                $price = get_string_between($size, '[', ']');
                $size_clear = substr($size, 0, strpos($size, "["));
                $size_clear_vals = explode('x', $size_clear);
                if(!isset($size_clear_vals[0]) || !isset($size_clear_vals[1])){
                    echo '<script>window.location = "/err_sizes/?size='.$size_clear.'?page=custom_sizes_list";</script>';
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

                <li>
                    <a class="size__sale" href="javascript:void(0)"
                    data-size="{{ $size_clear_vals[0] }}x{{ $size_clear_vals[1] }}"
                    data-price="<?php if(!isset($price_saved)) : ?>{{ $price }}<?php else : ?>{{ $price_saved }}<?php endif; ?>"
                    >
                        {{ $size_clear_vals[0] }} х {{ $size_clear_vals[1] }} {{ trans('gl.cm') }}
                        <strong>
                            <?php if(isset($price_saved)) : ?>
                                   <span style="color:#e2761d">  {{ $price_saved }}  €</span>
                            <?php else : ?>
                                {{ $price}} €
                            <?php endif; ?>
                        </strong>
                    </a>
                </li>
            @endif

        @endforeach
    @endif
</ul>
