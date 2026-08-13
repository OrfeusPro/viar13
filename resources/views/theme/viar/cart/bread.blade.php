<div class="cart-page-global__header">
	<a href="#{{-- route('cart.index') --}}" onclick="event.preventDefault()" class="cart-page-global__header--item @if($step == 1) js-active @endif">
		<svg>
			<use xlink:href="{{ asset(env('THEME') . 'img/cart/sprite.svg') }}#cart"></use>
		</svg>
		<span>@lang("cart_new.breadcrumbs_step_1_cart")</span>
	</a>
	<div class="cart-page-global__header--arrow">
		<svg>
			<use xlink:href="{{ asset(env('THEME') . 'img/cart/sprite.svg') }}#arrow-right"></use>
		</svg>
	</div>
	<a href="#{{-- route('cart.step2') --}}" onclick="event.preventDefault()" class="cart-page-global__header--item @if($step == 2) js-active @endif">
		<svg>
			<use xlink:href="{{ asset(env('THEME') . 'img/cart/sprite.svg') }}#resume"></use>
		</svg>
		<span>@lang("cart_new.breadcrumbs_step_2_data")</span>
	</a>
	<div class="cart-page-global__header--arrow">
		<svg>
			<use xlink:href="{{ asset(env('THEME') . 'img/cart/sprite.svg') }}#arrow-right"></use>
		</svg>
	</div>
	<a href="#{{-- route('cart.step3') --}}" onclick="event.preventDefault()" class="cart-page-global__header--item  @if($step == 3) js-active @endif delivery">
		<svg>
			<use xlink:href="{{ asset(env('THEME') . 'img/cart/sprite.svg') }}#delivery"></use>
		</svg>
		<span>@lang("cart_new.breadcrumbs_step_3_delivery")</span>
	</a>
	<div class="cart-page-global__header--arrow">
		<svg>
			<use xlink:href="{{ asset(env('THEME') . 'img/cart/sprite.svg') }}#arrow-right"></use>
		</svg>
	</div>
	<a href="#{{-- route('cart.step4') --}}" onclick="event.preventDefault()" class="cart-page-global__header--item  @if($step == 4) js-active @endif">
		<svg>
			<use xlink:href="{{ asset(env('THEME') . 'img/cart/sprite.svg') }}#card"></use>
		</svg>
		<span>@lang("cart_new.breadcrumbs_step_4_payment")</span>
	</a>
</div>