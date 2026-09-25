@php $true_image = false; @endphp

@if (isset($product['image_uploads']))
    @php $true_image = true; @endphp
    <img class="def_img image_offset" style="max-width: 100%;" src="{{ order_image_url($product['activeImage'] ?? null) }}"  @frontendAlt('theme/viar/account/basket_img.blade.php', (order_image_url($product['activeImage'] ?? null)), '', '')>
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
                @php $true_image = true; @endphp
                <a style="position:relative;display:block;" href="javascript:void(0);" data-file="{{ $file }}">
                    <img class="def_img_some" style="max-width: 100%;" src="{{ $activeSrc }}"  @frontendAlt('theme/viar/account/basket_img.blade.php', ($activeSrc), '', '')>
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
                @php $true_image = true; @endphp
                <img  class="def_img_some" style="max-width: 100%;" src="{{ $activeSrc }}" @frontendAlt('theme/viar/account/basket_img.blade.php', ($activeSrc), '', '')>
            @endif
        @else
            @php $true_image = true; @endphp
            @if (isset($product['savedImage']) && $product['savedImage'] && !$product['activeImage'])
                <img class="def_img" style="max-width: 100%;" src="{{ $savedSrc }}"  @frontendAlt('theme/viar/account/basket_img.blade.php', ($savedSrc), '', '')>
            @else
                @if($product['activeImage'])
                    @isset($product['savedImage'])<a href="{{ $savedSrc }}">@endisset
                        <img class="def_img" style="max-width: 100%;" src="{{ $activeSrc }}"  @frontendAlt('theme/viar/account/basket_img.blade.php', ($activeSrc), '', '')>
                    @isset($product['savedImage'])</a>@endisset
                @endif
            @endif
        @endisset

    @endif

    @isset($product['is_gift_card'])
        @php $true_image = true; @endphp
        <img  style="max-width: 100%;" class="def_img card__img" src="{{ asset('img/benefits-img1.png') }}"
              @frontendAlt('theme/viar/account/basket_img.blade.php', (asset('img/benefits-img1.png')), 'Gift', '')>
    @endisset
@endif


@if($true_image == false)
    <img class="def_img" style="max-width: 100%;" src="{{ order_image_placeholder() }}"  @frontendAlt('theme/viar/account/basket_img.blade.php', (order_image_placeholder()), '', '')>
@endif
