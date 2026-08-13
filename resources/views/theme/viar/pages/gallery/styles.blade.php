<div class="br-object">
	<div class="section-frame">
		<div class="breadcrumbs breadcrumbs__block">
			<div>
				<a href="{{ route('home') }}" class="breadcrumbs__link breadcrumbs__link_main">
					<span>@lang('account.index1')</span>
				</a>
			</div>
			<svg xmlns="http://www.w3.org/2000/svg" width="6" height="11" viewBox="0 0 6 11" fill="none"
				class="img-svg breadcrumbs__arrow replaced-svg">
				<path
					d="M0.24775 1.4341C0.180786 1.50106 0.147304 1.57807 0.147304 1.66513C0.147304 1.75218 0.180786 1.82919 0.24775 1.89615L4.1953 5.8437L0.247751 9.79124C0.180787 9.85821 0.147304 9.93522 0.147304 10.0223C0.147304 10.1093 0.180787 10.1863 0.247751 10.2533L0.749984 10.7555C0.816948 10.8225 0.893957 10.856 0.98101 10.856C1.06806 10.856 1.14507 10.8225 1.21204 10.7555L5.89284 6.07473C5.9598 6.00776 5.99329 5.93075 5.99329 5.8437C5.99329 5.75664 5.9598 5.67964 5.89284 5.61267L1.21204 0.931868C1.14507 0.864903 1.06806 0.831421 0.981009 0.831421C0.893956 0.831421 0.816947 0.864903 0.749983 0.931868L0.24775 1.4341Z"
					fill="#FA7846"></path>
			</svg>
			<div>
				<a href="{{ route('hb.gallery.index') }}" class="breadcrumbs__link breadcrumbs__link_main">
					<span>{{ trans('breadcrumbs.gallery') }}</span>
				</a>
			</div>
			<svg xmlns="http://www.w3.org/2000/svg" width="6" height="11" viewBox="0 0 6 11" fill="none"
				class="img-svg breadcrumbs__arrow replaced-svg">
				<path
					d="M0.24775 1.4341C0.180786 1.50106 0.147304 1.57807 0.147304 1.66513C0.147304 1.75218 0.180786 1.82919 0.24775 1.89615L4.1953 5.8437L0.247751 9.79124C0.180787 9.85821 0.147304 9.93522 0.147304 10.0223C0.147304 10.1093 0.180787 10.1863 0.247751 10.2533L0.749984 10.7555C0.816948 10.8225 0.893957 10.856 0.98101 10.856C1.06806 10.856 1.14507 10.8225 1.21204 10.7555L5.89284 6.07473C5.9598 6.00776 5.99329 5.93075 5.99329 5.8437C5.99329 5.75664 5.9598 5.67964 5.89284 5.61267L1.21204 0.931868C1.14507 0.864903 1.06806 0.831421 0.981009 0.831421C0.893956 0.831421 0.816947 0.864903 0.749983 0.931868L0.24775 1.4341Z"
					fill="#FA7846"></path>
			</svg>
			<div>
				<a href="{{ route('hb.gallery.module', $page->url) }}" class="breadcrumbs__link breadcrumbs__link_main">
					<span>{{ $page->name }}</span>
				</a>
			</div>
			<svg xmlns="http://www.w3.org/2000/svg" width="6" height="11" viewBox="0 0 6 11" fill="none"
				class="img-svg breadcrumbs__arrow replaced-svg">
				<path
					d="M0.24775 1.4341C0.180786 1.50106 0.147304 1.57807 0.147304 1.66513C0.147304 1.75218 0.180786 1.82919 0.24775 1.89615L4.1953 5.8437L0.247751 9.79124C0.180787 9.85821 0.147304 9.93522 0.147304 10.0223C0.147304 10.1093 0.180787 10.1863 0.247751 10.2533L0.749984 10.7555C0.816948 10.8225 0.893957 10.856 0.98101 10.856C1.06806 10.856 1.14507 10.8225 1.21204 10.7555L5.89284 6.07473C5.9598 6.00776 5.99329 5.93075 5.99329 5.8437C5.99329 5.75664 5.9598 5.67964 5.89284 5.61267L1.21204 0.931868C1.14507 0.864903 1.06806 0.831421 0.981009 0.831421C0.893956 0.831421 0.816947 0.864903 0.749983 0.931868L0.24775 1.4341Z"
					fill="#FA7846"></path>
			</svg>
			<div>
				<a href="{{ $linkroute }}" class="breadcrumbs__link">
					<span>{{ $linktext }}</span>
				</a>
			</div>
		</div>		
	</div>
</div>


<div class="reproduction">

	<div class="reproduction-main">
		<div class="section-frame">
			<div class="reproduction-inner">
				<div class="reproduction-content">
					<div class="reproduction-title">
						{{ $linktext }}
					</div>
				</div>
				<picture>
					<source media="(max-width: 525px)" srcset="{{ asset(env('THEME').'images') }}/reproduction/1Min.webp" type="image/webp">
					<source media="(max-width: 525px)" srcset="{{ asset(env('THEME') . 'images') }}/reproduction/1Min.jpg"
						type="image/jpeg">
					<source srcset="{{ asset(env('THEME').'images') }}/reproduction/1.webp" type="image/webp">
					<source srcset="{{ asset(env('THEME') . 'images') }}/reproduction/1.jpg" type="image/jpeg">
					<img width="1350" height="381" src="{{ asset(env('THEME') . 'images') }}/reproduction/1.jpg" alt="">
				</picture>
				<div></div>
			</div>
		</div>
	</div>

    @include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.reproduction_tabs')    
</div>

<div class="pop-painters" style="padding-top: 0;">
	<div class="section-frame">
		@if(!$items->isEmpty())

		<div class="pop-painters__inner">
			<div class="pp-list2">
				<div class="pp-list-wrapper">


                        
                        @foreach ($items as $item)
                            <div class="cp-item">
                                <div class="cp-item-inner">
                                    <div class="cp-item-inner-b">
                                        <a href="{{ route($rout, ['alias' => $item->alias]) }}"
                                            class="mm-btn">@lang('gallery.see')</a>
                                    </div>
                                    <picture>
                                        @if ($item->image)
                                            @php
                                                $galleryStyleImageSources = image_picture_sources($item->image, true);
                                            @endphp
                                            @if(!empty($galleryStyleImageSources['src_webp']))
                                                <source srcset="{{ $galleryStyleImageSources['src_webp'] }}" type="image/webp">
                                            @endif
                                            @if(!empty($galleryStyleImageSources['src']) && !empty($galleryStyleImageSources['type']))
                                                <source srcset="{{ $galleryStyleImageSources['src'] }}" type="{{ $galleryStyleImageSources['type'] }}">
                                            @endif
                                            <img width="433" height="583" src="{{ $galleryStyleImageSources['src'] }}" @altAttrs($item, 'image', data_get($item, 'image')) loading="lazy">
                                        @else
                                            <source srcset="{{ asset(env('THEME') . 'images') }}/gallery/7.jpg" type="image/jpeg">
                                            <img width="433" height="583" src="{{ asset(env('THEME') . 'images') }}/gallery/7.jpg"
                                                alt="{{ $item->name }}" title="{{ $item->name }}" loading="lazy">
                                        @endif
                                    </picture>
                                </div>
                                <p>{{ $item->name }}</p>
                            </div>
                        @endforeach




				</div>

			</div>
		</div>

		@else
			<div style="width:100%;text-align:center; font-size:18px;">
				@lang('gallery.painter_not_found')
			</div>
		@endif

	</div>
</div>
