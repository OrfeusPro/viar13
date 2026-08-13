@if(isset($product['pid']) && $product['pid'] == 5)
	<div class="cart-page-item__left--row">
        <b>@lang("gift_card.gift_cart")</b>
	</div>
@endif

@if(isset($product['whom']) && $product['whom'])
	<div class="cart-page-item__left--row">
		<span> {{ trans('gl.card_type') }}:</span>
		<b> {{ $product['whom'] }}</b>
	</div>
@endif

@if(isset($product['pid']) && $product['pid'] == 5)
	@if(isset($product['price']) && $product['price'])
		<div class="cart-page-item__left--row">
			<span> {{ trans('gl.nominal') }}:</span>
			<b> {{ $product['price'] }}€</b>
		</div>
	@endif
@endif

@if(isset($product['name']) && $product['name'] && isset($product['pid']) && $product['pid'] != 5)
	<div class="cart-page-item__left--row">
		<span>@lang("cart_new.general_selected_style")</span>
        <b>{!! $product['name'] !!}</b>
	</div>
@endif

@isset($product['savedImage'])
	<div class="cart-page-item__left--row">
		<span>
			<a href="{{ $product['savedImage'] }}" target="_blank">{{ trans('gl.orig_image') }}</a>
		</span>
	</div>
@endisset

@isset($product['photo_ex'])
	@if ($product['photo_ex'] != '')
		<div class="cart-page-item__left--row">
			<span><a href="{{ $product['photo_ex'] }}" target="_blank">Фотопример</a></span>
		</div>
	@endif
@endisset

{{-- @isset($product['orig_images'])--}}
{{--	<div class="cart-page-item__left--row">--}}
{{--		<span>{{ trans('gl.orig_images') }}:</span>--}}
{{--		@foreach ($product['orig_images'] as $img)--}}
{{--			<b><a href="{{ $img }}" target="_blank">#{{ $loop->index }}</a></b>--}}
{{--		@endforeach--}}
{{--	</div>--}}
{{--@endisset --}}

@isset($product['sizeId'])
	@if ($product['sizeId'])
		<div class="cart-page-item__left--row">
			<span> {{ trans('cart.size') }}:</span>
			<b>{{ $product['sizeId'] }}</b>
		</div>
	@endif
@endisset

@isset($product['size_name'])
	@if ($product['size_name'])
		<div class="cart-page-item__left--row">
			<span> {{ trans('gl.size_text') }}:</span>
			<b> {{ $product['size_name'] }}</b>
		</div>
	@endif
@endisset

@isset($product['compl_id'])
	@if ($product['compl_id'] != 'undefined' && $product['compl_id'])

		@php
			$pname = \App\Models\GalleryBox::getNameById($product['compl_id'])['name'];
		@endphp

		<div class="cart-page-item__left--row">
			<span>{{ trans('gl.pack') }}:</span>
			<b>{{ $pname }}</b>
		</div>

	@endif
@endisset


@isset($product['formIndex'])
	@if ($product['formIndex'])
		<div class="cart-page-item__left--row">
			<span>{{ trans('gl.form_num_text') }}:</span>
			<b>{{ $product['formIndex'] }}</b>
		</div>
	@endif
@endisset

@isset($product['is_gift_card'])
	<div class="text">
		<div class="cart-page-item__left--row">
			<span>{{ trans('gl.sender') }}:</span>
			<b>{{ data_get($product, 'sender', '') }}</b>
		</div>
		<div class="cart-page-item__left--row">
			<span>{{ trans('gl.reseiver') }}:</span>
			<b>{{ data_get($product, 'reseiver', '') }}</b>
		</div>
		<div class="cart-page-item__left--row">
			<span>{{ trans('gl.nominal') }}:</span>
			<b>{{ data_get($product, 'price', '') }}</b>
		</div>
		<div class="cart-page-item__left--row">
			<span>{{ trans('gl.date') }}:</span>
			<b>{{ data_get($product, 'date', '') }}</b>
		</div>
		<div class="cart-page-item__left--row">
			<span>{{ trans('gl.torjname') }}:</span>
			<b>{{ data_get($product, 'torjname', '') }}</b>
		</div>
		<div class="cart-page-item__left--row">
			<span>{{ trans('gl.torjtext') }}:</span>
			<b>{{ data_get($product, 'torjtext', '') }}</b>
		</div>
		<div class="cart-page-item__left--row">
			<span>{{ trans('gl.card_type') }}:</span>
			<b>{{ data_get($product, 'card_type', '') }}</b>
		</div>
		<div class="cart-page-item__left--row">
			<span>{{ trans('gl.nominal') }}:</span>
			<b>
				@if ((string) data_get($product, 'hide_nom', 'false') == 'false') {{ trans('gl.nom_show') }}
				@else
					{{ trans('gl.nom_hide') }} @endif
			</b>
		</div>

		@isset($is_admin_data_orders)
			@isset($product['gift_code'])
				<div class="cart-page-item__left--row">
					<b>{{ $product['gift_code'] }}</b>
				</div>
			@endisset
		@endisset

	</div>
@endisset

{{--
@isset($product['formId'])
	@if ($product['formId'] != 'undefined' && $product['formId'])
		<div class="cart-page-item__left--row">
			<span>{{ trans('gl.form_id') }}:</span>
			<b>{{ $product['formId'] }}</b>
		</div>
	@endif
@endisset
--}}

@isset($product['execution'])
	@if ($product['execution'] != 'undefined' && $product['execution'])
		<div class="cart-page-item__left--row">
			<span>{{ trans('cart.execution_type') }}:</span>
			<b>{{ $product['execution'] }}</b>
		</div>
	@endif
@endisset

@isset($product['is_port_product'])

{{--
	@isset($product['forma_id'])
		@if ($product['forma_id'] != 'undefined' && $product['forma_id'])
			<div class="cart-page-item__left--row">
				<span>{{ trans('gl.bask_form') }}</span>
				<b>{{ $product['forma_id'] }}</b>
			</div>
		@endif
	@endisset
 --}}

	@isset($product['users_count'])
		@if ($product['users_count'] != 'undefined' && $product['users_count'])
			<div class="cart-page-item__left--row">
				<span>{{ trans('gl.bask_persons') }}:</span>
				<b>{{ $product['users_count'] }}</b>
			</div>
		@endif
	@endisset

	@isset($product['type'])
		@if ($product['type'] != 'undefined' && $product['type'])
			<div class="cart-page-item__left--row">
				<span>{{ trans('gl.bask_isp') }}</span>
				<b>{{ $product['type'] }}</b>
			</div>
		@endif
	@endisset


	@isset($product['holst_id'])
		@if ($product['holst_id'] != 'undefined' && $product['holst_id'])
			<div class="cart-page-item__left--row">
				<span>{{ trans('gl.bask_holst') }}</span>
				<b>{{ App\Models\GalleryHolst::getHolstNameById($product['holst_id']) }}</b>
			</div>
		@endif
	@endisset

	@isset($product['hud_of'])
		@if ($product['hud_of'] != 'undefined' && $product['hud_of'])
			<div class="cart-page-item__left--row">
				<span>{{ trans('gl.bask_of') }}:</span>
				<b>{{ $product['hud_of'] }}</b>
			</div>
		@endif
	@endisset


@endisset


@isset($product['ram_id'])
	@if ($product['ram_id'] != 'undefined' && $product['ram_id'] && \App\Models\CanvasRam::isFramedOption($product['ram_id']))
		<div class="cart-page-item__left--row">
			<span>{{ trans('gl.bask_ram') }}:</span>
			<b>{{ App\Models\CanvasRam::getRamNameById($product['ram_id']) }}</b>
		</div>
	@endif
@endisset


@isset($product['wall_size_mod'])
	@if ($product['wall_size_mod'])
		<div class="cart-page-item__left--row">
			<span>{{ trans('gl.wall_size') }}</span>
			<b>{{ $product['wall_size_mod'] }}</b>
		</div>
	@endif
@endisset

@php
$is_mod = 0;
@endphp


@isset($product['is_canvas_inter'])
	@isset($product['wall_size'])
		@if ($product['wall_size'] != 'x' && $product['wall_size'])
			<div class="cart-page-item__left--row">
				<span>{{ trans('gl.wall_size') }}:</span>
				<b>{{ $product['wall_size'] }}</b>
			</div>
		@endif
	@endisset

	@isset($product['pic_size'])
		@if ($product['pic_size'] != 'x' && $product['pic_size'])
			<div class="cart-page-item__left--row">
				<span>{{ trans('gl.pic_size') }}:</span>
				<b>{{ $product['pic_size'] }}</b>
			</div>
		@endif
	@endisset

	@isset($product['ram_id'])
		@if ($product['ram_id'] != 'undefined' && $product['ram_id'])
			<div class="cart-page-item__left--row">
				<span>{{ trans('gl.rama') }}:</span>
				<b>{{ App\Models\GalleryItem::getRamNameById($product['ram_id']) }}</b>
			</div>
		@endif
	@endisset
@endisset

@isset($product['is_modular_inter'])
	@if ($product['wall_size'] != 'x' && $product['wall_size'])
		<div class="cart-page-item__left--row">
			<span>{{ trans('gl.wall_size') }}:</span>
			<b>{{ $product['wall_size'] }}</b>
		</div>
	@endif

	@isset($product['pic_size'])
		@if ($product['pic_size'] != 'x' && $product['pic_size'])
			<div class="cart-page-item__left--row">
				<span>{{ trans('gl.pic_size') }}</span>
				<b>{{ $product['pic_size'] }}</b>
			</div>
		@endif
	@endisset

	@isset($product['ram_id'])
		@if ($product['ram_id'] != 'undefined' && $product['ram_id'])
			<div class="cart-page-item__left--row">
				<span>{{ trans('gl.rama') }}</span>
				<b>{{ App\Models\GalleryItem::getRamNameById($product['ram_id']) }}</b>
			</div>
		@endif
	@endisset

@endisset


@php
$showed_box = 0;
@endphp

@isset($product['show'])
	@isset($product['show']['basketType'])
		@if ($product['show']['basketType'])
			<div class="cart-page-item__left--row">
				<span>@lang('basket.picture_type'):</span>
				<b>{{ $product['show']['basketType'] }}</b>
			</div>
		@endif
	@endisset

	@isset($product['show']['effect'])
		@if ($product['show']['effect'])
			<div class="cart-page-item__left--row">
				<b>{{ $product['show']['effect'] }}</b>
			</div>
		@endif
	@endisset

	@isset($product['show']['decoration'])
		@if ($product['show']['decoration'])
			<div class="cart-page-item__left--row">
				<span>{{ trans('gl.hud_of_text') }}:</span>
				<b>{{ $product['show']['decoration'] }}</b>
			</div>
		@endif
	@endisset

	@if (!isset($product['sizeId'])):
		@isset($product['show']['size'])
			@if ($product['show']['size'])
				<div class="cart-page-item__left--row">
					<span>{{ trans('cart.size') }}:</span>
					<b>{{ $product['show']['size'] }}</b>
				</div>
			@endif
		@endisset
	@endif
	@isset($product['show']['execution'])
		@if ($product['show']['execution'])
			<div class="cart-page-item__left--row">
				<span>{{ trans('cart.execution_type') }}:</span>
                <b>{{ $product['show']['execution'] }}</b>
			</div>
		@endif
	@endisset
    @isset($product['show']['canvas'])
		@if ($product['show']['canvas'])
			<div class="cart-page-item__left--row">
				<span>{{ trans('cart.canvas') }}:</span>
                <b>{{ $product['show']['canvas'] }}</b>
			</div>
		@endif
	@endisset

	@isset($product['show']['box'])
		@php
	    	$showed_box = 1;
		@endphp

		<div class="cart-page-item__left--row">
			<span>{{ trans('cart.packaging') }}:</span>
			<b>{{ implode(', ', $product['show']['box']) }}</b>
		</div>
	@endisset


 @endisset

@if(isset($product['pid']) && $product['pid'] == \App\Http\Controllers\Pages\PagePortraitRoyalController::TYPE_ID)
    @isset($product['ram_id'])
        @if ($product['ram_id'] != 'undefined' && $product['ram_id'])
            <div class="cart-page-item__left--row">
                <span>{{ trans('gl.rama') }}:</span>
                <b>{{ App\Models\GalleryItem::getRamNameById($product['ram_id']) }}</b>
            </div>
        @endif
    @endisset
@endif

@isset($product['canvas'])
	@if ($product['canvas'])
		<div class="cart-page-item__left--row">
			<span>{{ trans('cart.canvas') }}:</span>
            <b>{{ $product['canvas'] }}</b>
		</div>
	@endif
@endisset

@isset($product['pack'])
	@if ($product['pack'] != 'undefined' && $product['pack'])
		@if ($showed_box != 1 && empty($product['compl_id']))
			<div class="cart-page-item__left--row">
				<span>{{ trans('cart.packaging') }}:</span>
				<b>{{ $product['pack'] }}</b>
			</div>
		@endisset
	@endif
@endisset

{{-- Улучшение качества --}}
@isset($product['improve_photo'])

    @if ($product['improve_photo'] == 'standart')
        <div class="cart-page-item__left--row">
            <span>{{ trans('cart_new.improve_title') }}:</span>
            <b id="terms_{{ $basketIndex }}">{{ @trans('cart_new.improve_optimal') }}</b>
        </div>
    @endif

    @if ($product['improve_photo'] == 'premium')
        <div class="cart-page-item__left--row">
            <span>{{ trans('cart_new.improve_title') }}:</span>
            <b id="terms_{{ $basketIndex }}">{{ @trans('cart_new.improve_premium') }}</b>
        </div>
    @endif

@endisset

{{-- Изготовление стандарт и экспресс --}}
@isset($product['terms'])
	@if ($is_mod == 0)
		@if ($product['terms'])
			<div class="cart-page-item__left--row">
				<span>{{ trans('gl.bask_izg') }}:</span>
				<b id="terms_{{ $basketIndex }}">{{ $product['terms'] }}</b>
			</div>
		@endif
	@endif
@endisset

{{--Если есть фон - добавляем название фона--}}
@isset($product['fon'])
    <div class="cart-page-item__left--row">
        <span>{{ trans('cart_new.fon') }}:</span>
        <b id="terms_{{ $basketIndex }}">{{ $product['fon'] }}</b>
    </div>
@endisset

{{--Если есть образ - добавляем название образа--}}
@isset($product['obraz'])
    <div class="cart-page-item__left--row">
        <span>{{ trans('cart_new.obraz') }}:</span>
        <b id="terms_{{ $basketIndex }}">
			@isset($product['obraz_img'])<a href="{{ $product['obraz_img'] }}" style="color: #fa7846; text-decoration: underline;" target="_blank">@endif
			{{ $product['obraz'] }}
			@isset($product['obraz_img'])</a>@endif
		</b>
    </div>
@endisset

@isset($product['obraz_title'])
    <div class="cart-page-item__left--row">
		@if(isset($product['pid']) && $product['pid'] == "1055")
			<span>{{ trans('cart_new.fon') }}:</span>
		@else
			<span>{{ trans('cart_new.obraz') }}:</span>
		@endif
        <b id="terms_{{ $basketIndex }}">
			<a href="{{ $product['obraz_img'] }}" style="color: #fa7846; text-decoration: underline;" target="_blank">
				{{ $product['obraz_title'] }}
			</a>
		</b>
    </div>
@endisset

@if (isset($product['userComment']))
	@if ($product['userComment'])
		<div class="cart-page-item__left--row">
			<span>{{ trans('gl.comments') }}:</span>
            <b style="word-break: break-word; overflow-wrap: break-word;">{!! $product['userComment'] !!}</b>
		</div>
	@endif
@endif

@if (!empty($product['has_special_label']))
	<div class="cart-page-item__left--row">
		<b style="color: #fc7a0f; font-size: 12px;">
			@if ($product['label_type'] == 'super_deal')
				⚠️ SUPER DEAL: {{ __('cart.not_sale') }}
			@elseif ($product['label_type'] == 'hit')
				⚠️ HIT: {{ __('cart.not_sale') }}
			@elseif ($product['label_type'] == 'top')
				⚠️ TOP: {{ __('cart.not_sale') }}
			@elseif ($product['label_type'] == 'recommendation')
				⚠️ Recommendation: {{ __('cart.not_sale') }}
			@else
				⚠️ {{ __('cart.this_product_not_sale') }}
			@endif
		</b>
	</div>
@endif
