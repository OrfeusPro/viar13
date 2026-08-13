<div class="two_tab about__block @if ($is_hidden ?? true) hidden-block @endif">

	<div class="about__block-types">
		<div class="page-title">
			@lang('pages.portrait_oil.tab_two_pictures_tab.t11')
		</div>
		<p>@lang('pages.portrait_oil.tab_two_pictures_tab.t12')</p>
		<div class="about__block-types-row">
			<div class="two_tab_left">
				<div class="flex">
					<img width="40" height="40" src="{{ ver_asset('images/sharj/i1.svg') }}" alt="">
					<p>
						@lang('pages.portrait_oil.tab_two_pictures_tab.t13')
					</p>
				</div>
				<div class="two_tab_left_subtitle">
					@lang('pages.portrait_oil.tab_two_pictures_tab.t14')
				</div>

				<div class="two_tab_arrow">
					<picture>
						<source media="(max-width: 1200px)" srcset="https://viarcanvas.com/images/sharj/v1.svg?1691276006">
						<source srcset="https://viarcanvas.com/images/sharj/v1.svg?1691276006 ">
						<img src="https://viarcanvas.com/images/sharj/v1.svg?1691276006" width="69" height="98" alt="">
					</picture>
				</div>

				<div>
					<div class="material">
						<div>
							<picture>
								<source srcset="{{ ver_asset(env('THEME') . 'images/oil/t2_1.webp') }}" type="image/webp">
								<img width="85" height="85" src="{{ ver_asset(env('THEME') . 'images/oil/t2_1.png') }}" alt=""
									class="hb_material">
							</picture>
						</div>
						<div>
							@lang('pages.portrait_oil.tab_two_pictures_tab.t15')
						</div>
					</div>
					<div class="material">
						<div>
							<picture>
								<source srcset="{{ ver_asset(env('THEME') . 'images/oil/t2_2.webp') }}" type="image/webp">
								<img width="85" height="85" src="{{ ver_asset(env('THEME') . 'images/oil/t2_2.png') }}" alt=""
									class="hb_material">
							</picture>
						</div>
						<div>
							@lang('pages.portrait_oil.tab_two_pictures_tab.t16')
						</div>
					</div>
					<div class="material">
						<div>
							<picture>
								<source srcset="{{ ver_asset(env('THEME') . 'images/oil/t2_3.webp') }}" type="image/webp">
								<img width="85" height="85" src="{{ ver_asset(env('THEME') . 'images/oil/t2_3.png') }}" alt=""
									class="hb_material">
							</picture>
						</div>
						<div>
							@lang('pages.portrait_oil.tab_two_pictures_tab.t17')
						</div>
					</div>
				</div>
			</div>

			<div class="two_tab_arrow_duwn">
				<picture>
					<source media="(max-width: 1200px)" srcset="https://viarcanvas.com/images/sharj/v1.svg?1691276006">
					<source srcset="https://viarcanvas.com/images/sharj/v1.svg?1691276006 ">
					<img src="https://viarcanvas.com/images/sharj/v1.svg?1691276006" width="69" height="98" alt="">
				</picture>
			</div>

			<div class="img imgCenter">
				<picture>
					<source media="(max-width: 576px)" srcset="{{ ver_asset(env('THEME') . 'images/oil/t2_4.webp') }}"
						type="image/webp">
					<source srcset="{{ ver_asset(env('THEME') . 'images/oil/t2_4.webp') }}" type="image/webp">
					<img width="313" height="460" src="{{ ver_asset(env('THEME') . 'images/oil/t2_3.png') }}" alt="">
				</picture>
			</div>
		</div>


		<div class="page-title center-title">
			{!! trans('homepage_new_login_reg.or') !!}
		</div>



		<div class="or_about__block-types-row">

			<div class="two_tab_left width488">
				<div class="flex">
					<img width="40" height="40" src="{{ ver_asset('images/sharj/i1.svg') }}" alt="">
					<p>
						@lang('pages.portrait_oil.tab_two_pictures_tab.t21')
					</p>
				</div>
				<div class="two_tab_left_subtitle">
					@lang('pages.portrait_oil.tab_two_pictures_tab.t22')
				</div>

				<div class="pt-3 checked">
					<img src="{{ ver_asset(env('THEME') . 'images/oil/checked.svg') }}">
					<div>
						@lang('pages.portrait_oil.tab_two_pictures_tab.t23')
					</div>
				</div>

				<div class="two_tab_left_subtitle2">
					@lang('pages.portrait_oil.tab_two_pictures_tab.t24')
				</div>

				<div class="two_tab_2_images">
					<picture>
						<source srcset="{{ ver_asset(env('THEME') . 'images/oil/t2_b4.webp') }}" type="image/webp">
						<img width="480" height="344" src="{{ ver_asset(env('THEME') . 'images/oil/t2_b4.jpg') }}" alt="">
					</picture>
					<picture>
						<source srcset="{{ ver_asset(env('THEME') . 'images/oil/t2_b3.webp') }}" type="image/webp">
						<img width="480" height="344" src="{{ ver_asset(env('THEME') . 'images/oil/t2_b3.jpg') }}" alt="">
					</picture>
				</div>

				<div class="pt-3 checked">
					<img src="{{ ver_asset(env('THEME') . 'images/oil/checked.svg') }}">
					<div>
						@lang('pages.portrait_oil.tab_two_pictures_tab.t25')
					</div>
				</div>

				<div class="two_tab_left_subtitle2">
					@lang('pages.portrait_oil.tab_two_pictures_tab.t26')					
				</div>


				<div class="pt-3 checked">
					<img src="{{ ver_asset(env('THEME') . 'images/oil/checked.svg') }}">
					<div>
						@lang('pages.portrait_oil.tab_two_pictures_tab.t27')
					</div>
				</div>

				<div class="two_tab_left_subtitle2">
					@lang('pages.portrait_oil.tab_two_pictures_tab.t28')					
				</div>

				<div class="two_tab_1_images">
					<picture>
						<source srcset="{{ ver_asset(env('THEME') . 'images/oil/t2_b5.webp') }}" type="image/webp">
						<img width="488" height="236" src="{{ ver_asset(env('THEME') . 'images/oil/t2_b5.jpg') }}"
							alt="">
					</picture>
					<picture>
				</div>

			</div>

			<div class="hb_two_image_left2">
				<picture>
					<source media="(max-width: 576px)" srcset="{{ ver_asset(env('THEME') . 'images/oil/t2_bg2.webp') }}"
						type="image/webp">
					<source srcset="{{ ver_asset(env('THEME') . 'images/oil/t2_bg2.webp') }}" type="image/webp">
					<img width="313" height="460" src="{{ ver_asset(env('THEME') . 'images/oil/t2_bg2.png') }}"
						alt="">
				</picture>
			</div>
		</div>
		<div class="hb_two_image_left">
			<picture>
				<source media="(max-width: 576px)" srcset="{{ ver_asset(env('THEME') . 'images/oil/t2_bg2.webp') }}"
					type="image/webp">
				<source srcset="{{ ver_asset(env('THEME') . 'images/oil/t2_bg2.webp') }}" type="image/webp">
				<img width="313" height="460" src="{{ ver_asset(env('THEME') . 'images/oil/t2_bg2.png') }}"
					alt="">
			</picture>
		</div>


	</div>
</div>
