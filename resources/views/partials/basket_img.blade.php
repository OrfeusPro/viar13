@if (isset($product['image_uploads']))
    <img class="def_img image_offset" src="{{ order_image_url($product['activeImage'] ?? null) }}" alt="">
@else
    @if (isset($product['activeImage']))
        @php
            $activeSrc = order_image_url($product['activeImage']);
            $savedSrc = isset($product['savedImage']) ? order_image_url($product['savedImage']) : $activeSrc;

            $svg1 = str_replace('.jpeg', '.svg', $product['activeImage']);
            $file = str_replace('.png', '.svg', $svg1);
            $file = str_replace('.jpg', '.svg', $file);
            $file = str_replace('.heic', '.svg', $file);
            $file = str_replace('.heif', '.svg', $file);
            $file = str_replace('/storage/', '/uploads/', $file);
            $file = strstr($file, 'uploads/');
            $svgLocal = order_image_local_path($file);
        @endphp

        @isset($product['collageSvgImage'])
            @if ($svgLocal && file_exists($svgLocal))
                <a style="position:relative;display:block;" href="javascript:void(0);" data-file="{{ $file }}">
                    <img class="def_img_some" style="max-width:223px;opacity:0;" src="{{ $activeSrc }}" alt="">
                    @php
                        try {
                            $svg_file = file_get_contents($svgLocal);
                        } catch (\Throwable $th) {
                            $svg_file = '';
                        }
                        echo "<div style='width:100%; height:100%;position:absolute;left:0;top:0;right:0;bottom:0;'>" .
                            $svg_file .
                            "</div>";
                    @endphp
                </a>
            @else
                <img alt="" class="def_img_some" style="max-width:223px;" src="{{ $activeSrc }}">
            @endif
        @else
            <img class="def_img" src="{{ $savedSrc }}" alt="">
        @endisset
    @endif

    @isset($product['is_gift_card'])
        <img alt="Gift" style="max-width:72px;" class="def_img card__img" src="{{ asset('img/benefits-img1.png') }}"
             alt="">
    @endisset
@endif
