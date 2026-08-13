@include(env('THEME_RESOURCES') . 'account.breads', ['page' => $page])

<div class="main-cabinet">
	<div class="section-frame">
		<div class="main-cabinet__inner">
			<div class="cabinet-top__block">
				<div class="page-title cabinet-title">
					@lang('account_new.orders_success.title')
				</div>
				<p>@lang('account_new.orders_success.count') 20
					<a class="activeSpan" href="{{ route('logout') }}"
						onclick="event.preventDefault(); document.getElementById('logout-form').submit();">@lang('account_new.btn.logout')</a>
				</p>
			</div>
			<div class="cabinet-container">

				@include(env('THEME_RESOURCES') . 'account.menu')
				
				<div class="cabinet-content">

					@include(env('THEME_RESOURCES') . 'account.order')

					<div class="cabinet-content__icon">
						<img src="{{ asset(config('theme.current') . '/images')}}/cabinet/free-icon-order-delivery.svg" width="82" height="82"
							alt="Viar Cabinet Peding Orders">
					</div>


				</div>
			</div>
		</div>
	</div>
</div>


@include(config('theme.resource') . 'account.special_offers')
@include(config('theme.resource') . 'pages.gallery.zpart_viarcanvas_is')
