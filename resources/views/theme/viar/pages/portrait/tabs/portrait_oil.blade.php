<div class="about__block @if ($is_hidden ?? true) hidden-block @endif">
	<div class="about-image__content text-center mb-6">
		<div class="page-title">{!! trans('pages.portrait_oil.tab_portrait_oil.title1') !!}</div>
		<div><b><i>{!! trans('pages.portrait_oil.tab_portrait_oil.title2') !!}</i></b></div>
	</div>

	<div class="about-image hb_about hb_image_right">
		<div class="about-image__grid">
			<div class="about-image__content">
				<div class="page-title">
					<div class="hb_number"><span>1</span></div> @lang('pages.portrait_oil.tab_portrait_oil_tab.about__block_t11')
				</div>
				<div>
					@lang('pages.portrait_oil.tab_portrait_oil_tab.about__block_t12')
				</div>
				<br>
				<div>
					@lang('pages.portrait_oil.tab_portrait_oil_tab.about__block_t13')
				</div>

				<picture>
					<source media="(max-width: 1200px)" srcset="{{ ver_asset('images/sharj/v1Min.svg') }}">
					<source srcset="{{ ver_asset('images/sharj/v1.svg') }} ">
					<img src="{{ ver_asset('images/sharj/v1.svg') }}" width="69" height="98" alt="">
				</picture>

			</div>
			<div class="about-image__img">
				<div class="badge">
					<img src="{{ ver_asset(env('THEME') . 'images/oil/badge-bg.svg') }}" alt="" class="badge__shape">
					<img src="{{ ver_asset(env('THEME') . 'images/oil/badge-frame.svg') }}" alt="" class="badge__shape2">
					<div class="badge__content">
						<div>
							@lang('pages.portrait_oil.tab_portrait_oil_tab.about__block_t14')
							<br>
							<img src="{{ ver_asset(env('THEME') . 'images/oil/shot.png') }}" width="67" height="67" alt=""
								class="badge__icon">
						</div>

					</div>
				</div>

				<div class="hb_first_slide">
					<picture>
						<source media="(max-width: 576px)" srcset="{{ ver_asset(env('THEME') . 'images/oil/1.webp') }}" type="image/webp">
						<source srcset="{{ ver_asset(env('THEME') . 'images/oil/1.webp') }}" type="image/webp">
						<img width="800" height="549" src="{{ ver_asset(env('THEME') . 'images/oil/1.jpg') }}" alt="">
					</picture>

					<picture>
						<source media="(max-width: 576px)" srcset="{{ ver_asset(env('THEME') . 'images/oil/2.webp') }}" type="image/webp">
						<source srcset="{{ ver_asset(env('THEME') . 'images/oil/2.webp') }}" type="image/webp">
						<img width="549" height="800" src="{{ ver_asset(env('THEME') . 'images/oil/2.jpg') }}" alt="">
					</picture>
				</div>

			</div>
		</div>
	</div>

	<div class="about-image hb_about hb_image_left">
		<div class="about-image__grid">
			<div class="about-image__content">
				<div class="page-title">
					<div class="hb_number"><span>2</span></div> @lang('pages.portrait_oil.tab_portrait_oil_tab.about__block_t21')
				</div>
				<div>
					@lang('pages.portrait_oil.tab_portrait_oil_tab.about__block_t22')
				</div>
				<br>

				<div class="checked">
					<div><img src="{{ ver_asset(env('THEME') . 'images/oil/checked.svg') }}"></div>
					<div>@lang('pages.portrait_oil.tab_portrait_oil_tab.about__block_t23')</div>
				</div>
				<div class="checked">
					<div><img src="{{ ver_asset(env('THEME') . 'images/oil/checked.svg') }}"></div>
					<div>@lang('pages.portrait_oil.tab_portrait_oil_tab.about__block_t24')</div>
				</div>
			</div>
			<div class="about-image__img">
				<div class="badge two_bage">
					<div class="two_arrow">
						<picture>
							<source media="(max-width: 1200px)" srcset="https://viarcanvas.com/images/sharj/v1.svg?1691276006">
							<source srcset="https://viarcanvas.com/images/sharj/v1.svg?1691276006 ">
							<img src="https://viarcanvas.com/images/sharj/v1.svg?1691276006" width="69" height="98" alt="">
						</picture>
					</div>

					<img src="{{ ver_asset(env('THEME') . 'images/oil/badge-bg.svg') }}" alt="" class="badge__shape">
					<img src="{{ ver_asset(env('THEME') . 'images/oil/badge-frame.svg') }}" alt="" class="badge__shape2">
					<div class="badge__content">
						<div>
							@lang('pages.portrait_oil.tab_portrait_oil_tab.about__block_t25')
							<br>
							<img src="{{ ver_asset(env('THEME') . 'images/oil/mail.png') }}" width="49" height="41" alt=""
								class="badge__icon_mail">
						</div>
					</div>
				</div>
				<div class="hb_two_slide">
					<picture>
						<source media="(max-width: 576px)" srcset="{{ ver_asset(env('THEME') . 'images/oil/3.webp') }}"
							type="image/webp">
						<source srcset="{{ ver_asset(env('THEME') . 'images/oil/3.webp') }}" type="image/webp">
						<img width="636" height="900" src="{{ ver_asset(env('THEME') . 'images/oil/3.jpg') }}" alt="">
					</picture>
				</div>
			</div>
		</div>
	</div>


	<div class="about-image hb_about hb_image_right">
		<div class="about-image__grid">
			<div class="about-image__content">
				<div class="page-title">
					<div class="hb_number"><span>3</span></div> @lang('pages.portrait_oil.tab_portrait_oil_tab.about__block_t31')
				</div>
				<div>
					@lang('pages.portrait_oil.tab_portrait_oil_tab.about__block_t32')
				</div>

				<div class="checked">
					<div><img src="{{ ver_asset(env('THEME') . 'images/oil/checked.svg') }}"></div>
					<div>@lang('pages.portrait_oil.tab_portrait_oil_tab.about__block_t33')</div>
				</div>
			</div>
			<div class="about-image__img">
				<div class="badge two_bage">
					<div class="two_arrow">
						<picture>
							<source media="(max-width: 1200px)" srcset="https://viarcanvas.com/images/sharj/v1.svg?1691276006">
							<source srcset="https://viarcanvas.com/images/sharj/v1.svg?1691276006 ">
							<img src="https://viarcanvas.com/images/sharj/v1.svg?1691276006" width="69" height="98" alt="">
						</picture>
					</div>

					<img src="{{ ver_asset(env('THEME') . 'images/oil/badge-bg.svg') }}" alt="" class="badge__shape">
					<img src="{{ ver_asset(env('THEME') . 'images/oil/badge-frame.svg') }}" alt="" class="badge__shape2">
					<div class="badge__content">
						<div>
							@lang('pages.portrait_oil.tab_portrait_oil_tab.about__block_t34')
							<br>
							<img src="{{ ver_asset(env('THEME') . 'images/oil/colors.png') }}" width="49" height="41"
								alt="" class="badge__icon_mail">
						</div>
					</div>
				</div>
				<div class="hb_two_slide">
					<picture>
						<source media="(max-width: 576px)" srcset="{{ ver_asset(env('THEME') . 'images/oil/4.webp') }}"
							type="image/webp">
						<source srcset="{{ ver_asset(env('THEME') . 'images/oil/4.webp') }}" type="image/webp">
						<img width="636" height="900" src="{{ ver_asset(env('THEME') . 'images/oil/4.jpg') }}" alt="">
					</picture>
				</div>
			</div>
		</div>
	</div>

	<div class="about-image hb_about hb_image_left">
		<div class="about-image__grid">
			<div class="about-image__content">
				<div class="page-title">
					<div class="hb_number"><span>4</span></div> @lang('pages.portrait_oil.tab_portrait_oil_tab.about__block_t41')
				</div>
				<div>
					@lang('pages.portrait_oil.tab_portrait_oil_tab.about__block_t42')
				</div>
				<br>

				<div>
					@lang('pages.portrait_oil.tab_portrait_oil_tab.about__block_t43')
				</div>
			</div>
			<div class="about-image__img">
				<div class="badge two_bage">
					<div class="two_arrow">
						<picture>
							<source media="(max-width: 1200px)" srcset="https://viarcanvas.com/images/sharj/v1.svg?1691276006">
							<source srcset="https://viarcanvas.com/images/sharj/v1.svg?1691276006 ">
							<img src="https://viarcanvas.com/images/sharj/v1.svg?1691276006" width="69" height="98" alt="">
						</picture>
					</div>

					<img src="{{ ver_asset(env('THEME') . 'images/oil/badge-bg.svg') }}" alt="" class="badge__shape">
					<img src="{{ ver_asset(env('THEME') . 'images/oil/badge-frame.svg') }}" alt="" class="badge__shape2">
					<div class="badge__content">
						<div>
							@lang('pages.portrait_oil.tab_portrait_oil_tab.about__block_t44')
							<br>
							<img src="{{ ver_asset(env('THEME') . 'images/oil/defense.png') }}" width="64" height="64"
								alt="" class="badge__icon">
						</div>
					</div>
				</div>
				<div class="hb_two_slide">
					<picture>
						<source media="(max-width: 576px)" srcset="{{ ver_asset(env('THEME') . 'images/oil/5.webp') }}"
							type="image/webp">
						<source srcset="{{ ver_asset(env('THEME') . 'images/oil/5.webp') }}" type="image/webp">
						<img width="636" height="900" src="{{ ver_asset(env('THEME') . 'images/oil/5.jpg') }}" alt="">
					</picture>
				</div>
			</div>
		</div>
	</div>

	<div class="about-image hb_about hb_image_right">
		<div class="about-image__grid">
			<div class="about-image__content">
				<div class="page-title">
					<div class="hb_number"><span>5</span></div> @lang('pages.portrait_oil.tab_portrait_oil_tab.about__block_t51')
				</div>
				<div>
					@lang('pages.portrait_oil.tab_portrait_oil_tab.about__block_t52')
				</div>
				<br>
				<div>
					@lang('pages.portrait_oil.tab_portrait_oil_tab.about__block_t53')
				</div>
			</div>
			<div class="about-image__img">
				<div class="badge two_bage">
					<div class="two_arrow">
						<picture>
							<source media="(max-width: 1200px)" srcset="https://viarcanvas.com/images/sharj/v1.svg?1691276006">
							<source srcset="https://viarcanvas.com/images/sharj/v1.svg?1691276006 ">
							<img src="https://viarcanvas.com/images/sharj/v1.svg?1691276006" width="69" height="98" alt="">
						</picture>
					</div>

					<img src="{{ ver_asset(env('THEME') . 'images/oil/badge-bg.svg') }}" alt="" class="badge__shape">
					<img src="{{ ver_asset(env('THEME') . 'images/oil/badge-frame.svg') }}" alt="" class="badge__shape2">
					<div class="badge__content">
						<div>
							@lang('pages.portrait_oil.tab_portrait_oil_tab.about__block_t54')
							<br>
							<img src="{{ ver_asset(env('THEME') . 'images/oil/baget.png') }}" width="108" height="48"
								alt=""class="badge__icon_baget">
						</div>
					</div>
				</div>
				<div class="hb_five_slide">
					<picture>
						<source media="(max-width: 576px)" srcset="{{ ver_asset(env('THEME') . 'images/oil/6.webp') }}"
							type="image/webp">
						<source srcset="{{ ver_asset(env('THEME') . 'images/oil/6.webp') }}" type="image/webp">
						<img width="636" height="900" src="{{ ver_asset(env('THEME') . 'images/oil/6.jpg') }}" alt="">
					</picture>
				</div>
			</div>
		</div>
	</div>


	<div class="about-image hb_about hb_image_left">
		<div class="about-image__grid">
			<div class="about-image__content">
				<div class="page-title">
					<div class="hb_number"><span>6</span></div> @lang('pages.portrait_oil.tab_portrait_oil_tab.about__block_t61')
				</div>
				<div>
					@lang('pages.portrait_oil.tab_portrait_oil_tab.about__block_t62')
				</div>
				<br>
			</div>
			<div class="about-image__img">
				<div class="badge six_bage">
					<div class="six_arrow">
						<picture>
							<source media="(max-width: 1200px)" srcset="https://viarcanvas.com/images/sharj/v1.svg?1691276006">
							<source srcset="https://viarcanvas.com/images/sharj/v1.svg?1691276006 ">
							<img src="https://viarcanvas.com/images/sharj/v1.svg?1691276006" width="69" height="98" alt="">
						</picture>
					</div>

					<img src="{{ ver_asset(env('THEME') . 'images/oil/badge-bg.svg') }}" alt="" class="badge__shape">
					<img src="{{ ver_asset(env('THEME') . 'images/oil/badge-frame.svg') }}" alt="" class="badge__shape2">
					<div class="badge__content">
						<div>
							@lang('pages.portrait_oil.tab_portrait_oil_tab.about__block_t63')
							<br>
							<img src="{{ ver_asset(env('THEME') . 'images/oil/delivery.png') }}" width="64" height="64"
								alt="" class="badge__icon_baget">
						</div>
					</div>
				</div>
				<div class="hb_six_slide">
					<picture>
						<source media="(max-width: 576px)" srcset="{{ ver_asset(env('THEME') . 'images/oil/7.webp') }}"
							type="image/webp">
						<source srcset="{{ ver_asset(env('THEME') . 'images/oil/7.webp') }}" type="image/webp">
						<img width="636" height="900" src="{{ ver_asset(env('THEME') . 'images/oil/7.jpg') }}" alt="">
					</picture>
				</div>
			</div>
		</div>
	</div>

</div>
