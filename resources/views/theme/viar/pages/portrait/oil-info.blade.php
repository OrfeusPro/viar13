<section class="oilinfo-section">
	<div class="curve-top">
		<svg viewBox="0 0 1440 100" preserveAspectRatio="none">
			<path d="M0,100 C480,0 960,0 1440,100 L1440,0 L0,0 Z" fill="#fbf2ea"></path>
		</svg>
	</div>

	<div class="section-frame">
		<div class="page-title">
			@lang('pages.portrait_oil.oilinfo.t11')
		</div>
		<div class="oilinfo-bottom">
			<div class="eb-grid">
				<div class="eb-grid-content">
					<p class="pt-3">
						@lang('pages.portrait_oil.oilinfo.t12')
					</p>
					<br>
					<p>
						@lang('pages.portrait_oil.oilinfo.t13')
					</p>

					<div class="portrait-img">
						<div class="info_small_img">
							<picture>
								<source srcset="{{ ver_asset(env('THEME') . 'images/oil/oil_b2.webp') }}" type="image/webp">
								<img width="211" height="194" src="{{ ver_asset(env('THEME') . 'images/oil/oil_b2.png') }}" alt="">
							</picture>
							<div class="sub_text">
								@lang('pages.portrait_oil.oilinfo.t14')
							</div>
							<div class="six_arrow">
								<picture>
									<source media="(max-width: 1200px)" srcset="https://viarcanvas.com/images/sharj/v1.svg?1691276006">
									<source srcset="https://viarcanvas.com/images/sharj/v1.svg?1691276006 ">
									<img src="https://viarcanvas.com/images/sharj/v1.svg?1691276006" width="69" height="98" alt="">
								</picture>
							</div>
						</div>
					</div>
				</div>

				<div class="eb-grid-img">
					<picture>
						<source media="(max-width: 576px)" srcset="{{ ver_asset(env('THEME') . 'images/oil/oil_b1.webp') }}"
							type="image/webp">
						<source srcset="{{ ver_asset(env('THEME') . 'images/oil/oil_b1.webp') }}" type="image/webp">
						<img width="652" height="744" src="{{ ver_asset(env('THEME') . 'images/oil/oil_b1.png') }}" alt="">
					</picture>
				</div>

			</div>
		</div>

	</div>
</section>
