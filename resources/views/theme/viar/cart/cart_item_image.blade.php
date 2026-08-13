@php
$was_act_img = 0;
@endphp
{{-- {{ dd($product) }} --}}
@if(isset($product['orig_images']))
	@if(count($product['orig_images'])>=1)
		@foreach ($product['orig_images'] as $key_img=>$img)

			@if($key_img==0)
			<div class="cart-page-item__photo" id="cart_img_{{ $basketIndex }}_{{ $key_img }}">
				@php
					$imgSrc = order_image_url($img);
				@endphp
				<img src="{{ $imgSrc }}">

                <span class="zoom-icon" data-action="zoom"></span>
                <div class="popup-basket-image popup p__mod">
                    <span class="close-popup"></span>
                    <div class="popup-content">
                        <span class="close-content" style="display: flex; justify-content: center;">
                            <img class="popup-img" src="{{ $imgSrc }}" alt="">
                        </span>
                    </div>
                </div>

				<div class="cart-page-item__photo--btns">
					<a href="#" class="cart-page-item__photo--btn cart-page-item-edit" data-type="upload" data-cartid="{{ $basketIndex }}" data-cartimgid="{{ $key_img }}" data-curid="cart_img_{{ $basketIndex }}_{{ $key_img }}" data-img="{{ $img }}">
						@lang("cart_new.general_change")
						<input type="file" accept="image/*,image/heif,image/heic">
					</a>
					{{-- <a href="#" class="cart-page-item__photo--btn cart-page-item-remove" data-type="delete" data-cartid="{{ $basketIndex }}" data-cartimgid="{{ $key_img }}" data-curid="cart_img_{{ $basketIndex }}_{{ $key_img }}" data-img="{{ $img }}">
						@lang("cart_new.general_delete")
					</a> --}}
				</div>
			</div>
			@endif
		@endforeach
	@endif
@elseif (isset($product['basketType']) && $product['basketType'] == 5)
	<img class="two" src="{{ asset(env('THEME') . 'images/gift/cart') }}/{{  Config::get('app.locale') }}.png" alt="">

    {{-- <span class="zoom-icon" data-action="zoom"></span>
    <div class="popup-basket-image popup p__mod">
        <span class="close-popup"></span>
        <div class="popup-content">
            <span class="close-content" style="display: flex; justify-content: center;">
                <img class="popup-img" src="{{ asset($product['activeImage']) }}" alt="">
            </span>
        </div>
    </div> --}}
@elseif (!isset($product['is_port_product']))
	<div class="cart-page-item__photo" id="cart_img_{{ $basketIndex }}">
		@if(isset($product['activeImage']) && $product['activeImage'] && !isset($product['save_active_img']))
            @php
                $src = order_image_url($product['activeImage']);
                $was_act_img = 1;
            @endphp

            <img src="{{ $src }}" alt="">
		@else
            @php
                $src = order_image_url($product['savedImage'] ?? null);
            @endphp

            <img src="{{ $src }}">
		@endif

        <span class="zoom-icon" data-action="zoom"></span>
        <div class="popup-basket-image popup p__mod">
            <span class="close-popup"></span>
            <div class="popup-content">
                <span class="close-content" style="display: flex; justify-content: center;">
                    <img class="popup-img" src="{{ $src }}" alt="">
                </span>
            </div>
        </div>

		<div class="cart-page-item__photo--btns">
			<a href="#" class="cart-page-item__photo--btn cart-page-item-edit" data-type="upload" data-cartid="{{ $basketIndex }}" data-cartimgid="savedImage" data-curid="cart_img_{{ $basketIndex }}" data-img="{{ $product['savedImage'] }}">
				@lang("cart_new.general_change")
				<input type="file" accept="image/*,image/heif,image/heic">
			</a>
			{{-- <a href="#" class="cart-page-item__photo--btn cart-page-item-remove" data-type="delete" data-cartid="{{ $basketIndex }}" data-cartimgid="savedImage" data-curid="cart_img_{{ $basketIndex }}" data-img="{{ $product['savedImage'] }}">
				@lang("cart_new.general_delete")
			</a> --}}
		</div>
	</div>
@elseif (isset($product['image_uploads']))
	<img class="two" src="{{ order_image_url($product['activeImage'] ?? null) }}" alt="">

    <span class="zoom-icon" data-action="zoom"></span>
    <div class="popup-basket-image popup p__mod">
        <span class="close-popup"></span>
        <div class="popup-content">
            <span class="close-content" style="display: flex; justify-content: center;">
                <img class="popup-img" src="{{ order_image_url($product['activeImage'] ?? null) }}" alt="">
            </span>
        </div>
    </div>
@else
	@if (isset($product['activeImage']))

		@if (isset($product['is_construct']) || isset($product['is_canvas_inter']) || isset($product['is_modular_inter']) || isset($product['is_oil_portrait']) || (isset($product['is_gall_with_img']) && $product['is_gall_with_img'] == 1))
			@php
				$svg1 = str_replace('.jpeg', '.svg', $product['activeImage']);
				$file = str_replace('.png', '.svg', $svg1);
				$file = str_replace('.jpg', '.svg', $file);
				$file = str_replace('.heic', '.svg', $file);
				$file = str_replace('/storage/', '/uploads/', $file);
				$file = strstr($file, 'uploads/');
				$svgLocal = order_image_local_path($file);
			@endphp
			@if (!$svgLocal || !file_exists($svgLocal))
				@if ($was_act_img == 0)
					@php
						$activeSrc = order_image_url($product['activeImage']);
					@endphp
					<a href="{{ $activeSrc }}">
						<img class="two" src="{{ $activeSrc }}" alt="">
					</a>

                    <span class="zoom-icon" data-action="zoom"></span>
                    <div class="popup-basket-image popup p__mod">
                        <span class="close-popup"></span>
                        <div class="popup-content">
                            <span class="close-content" style="display: flex; justify-content: center;">
                                <img class="popup-img" src="{{ $activeSrc }}" alt="">
                            </span>
                        </div>
                    </div>
				@endif
			@else
				@php
					try {
						$svg_file = file_get_contents($svgLocal);
					} catch (\Throwable $th) {
						$svg_file = '';
					}
					if (!isset($product['file_hash'])) {
						$file = str_replace('id="image"', 'id="image' . $loop->index . '"', $svg_file);
					} else {
						$file = $svg_file;
					}

					$file = str_replace('style="','style="max-width:180px;max-height:180px;',$file);

					echo "<div class='two' style='max-width: 180px; max-height: 180px;'>" . $file . '</div>';
				@endphp
			@endif
		@else
			{{-- <a href="{{ Voyager::image($product['activeImage']) }}"> --}}
				<img class="two" src="{{ order_image_url($product['activeImage'] ?? null) }}" alt="">

                <span class="zoom-icon" data-action="zoom"></span>
                <div class="popup-basket-image popup p__mod">
                    <span class="close-popup"></span>
                    <div class="popup-content">
                        <span class="close-content" style="display: flex; justify-content: center;">
                            <img class="popup-img" src="{{ order_image_url($product['activeImage'] ?? null) }}" alt="">
                        </span>
                    </div>
                </div>
			{{-- </a> --}}
		@endif
	@endif

@endif


