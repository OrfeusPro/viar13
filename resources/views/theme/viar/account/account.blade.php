@include(env('THEME_RESOURCES') . 'account.breads',["page" => $page])

<div class="main-cabinet">
	<div class="section-frame">
		<div class="main-cabinet__inner">
			<div class="cabinet-top__block">
				<div class="page-title cabinet-title">
					@lang("account_new.main.title")
				</div>
				<p>
					@lang("account_new.menu.my_account") 
					<a class="activeSpan" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">@lang("account_new.btn.logout")</a>
				</p>
				
			</div>
			<div class="cabinet-container">

				@include(env('THEME_RESOURCES') . 'account.menu')

				<div class="cabinet-content">

					
					<div class="cabinet-initialState">
						<p class="title">@lang("account_new.main.title_text")</p>
						<div class="cabinet-initialState__info">
							<div class="cabinet-initialState__item">
								<p>@lang("account_new.main.fio") <span>{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</span></p>
								<a href="{{ route('new_account.settings') }}">@lang("account_new.btn.change")</a>
							</div>
							<div class="cabinet-initialState__item">
								<p>@lang("account_new.main.email") <span>{{ Auth::user()->email }}</span></p>
								<a href="{{ route('new_account.settings') }}">@lang("account_new.btn.change")</a>
							</div>
							<div class="cabinet-initialState__item">
								<p>@lang("account_new.main.address") <span>{{ Auth::user()->address }}</span></p>
								<a href="{{ route('new_account.settings') }}">@lang("account_new.btn.change")</a>
							</div>
							<div class="cabinet-initialState__item">
								<p>@lang("account_new.main.phone") <span>{{ Auth::user()->phone }}</span></p>
								<a href="{{ route('new_account.settings') }}">@lang("account_new.btn.change")</a>
							</div>
						</div>
					</div>
					<div class="cabinet-content__icon">
						<img src="{{ asset(config('theme.current') . '/images')}}/cabinet/resume.svg" width="82" height="82" alt="Viar Cabinet Resume">
					</div>


				</div>
			</div>
		</div>
	</div>
</div>

@include(config('theme.resource') . 'account.special_offers')
@include(config('theme.resource') . 'pages.gallery.zpart_viarcanvas_is')