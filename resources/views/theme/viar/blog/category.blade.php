<div class="vz-art">
	<div class="br-object">
		<div class="section-frame">
			<div class="breadcrumbs breadcrumbs__block" itemscope itemtype="https://schema.org/BreadcrumbList">
				<div itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
					<a href="{{ storefront_url('/') }}" class="breadcrumbs__link breadcrumbs__link_main" itemprop="item">
						<span itemprop="name">@lang('breadcrumbs.home')</span>
					</a>
					<meta itemprop="position" content="1" />
				</div>
				<svg xmlns="http://www.w3.org/2000/svg" width="6" height="11" viewBox="0 0 6 11" fill="none"
					class="img-svg breadcrumbs__arrow replaced-svg">
					<path
						d="M0.24775 1.4341C0.180786 1.50106 0.147304 1.57807 0.147304 1.66513C0.147304 1.75218 0.180786 1.82919 0.24775 1.89615L4.1953 5.8437L0.247751 9.79124C0.180787 9.85821 0.147304 9.93522 0.147304 10.0223C0.147304 10.1093 0.180787 10.1863 0.247751 10.2533L0.749984 10.7555C0.816948 10.8225 0.893957 10.856 0.98101 10.856C1.06806 10.856 1.14507 10.8225 1.21204 10.7555L5.89284 6.07473C5.9598 6.00776 5.99329 5.93075 5.99329 5.8437C5.99329 5.75664 5.9598 5.67964 5.89284 5.61267L1.21204 0.931868C1.14507 0.864903 1.06806 0.831421 0.981009 0.831421C0.893956 0.831421 0.816947 0.864903 0.749983 0.931868L0.24775 1.4341Z"
						fill="#FA7846"></path>
				</svg>
				<div itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
					<a href="{{ route('blog') }}" class="breadcrumbs__link breadcrumbs__link_main" itemprop="item">
						<span itemprop="name">{{ $data['title'] }}</span>
					</a>
					<meta itemprop="position" content="2" />
				</div>
				<svg xmlns="http://www.w3.org/2000/svg" width="6" height="11" viewBox="0 0 6 11" fill="none"
					class="img-svg breadcrumbs__arrow replaced-svg">
					<path
						d="M0.24775 1.4341C0.180786 1.50106 0.147304 1.57807 0.147304 1.66513C0.147304 1.75218 0.180786 1.82919 0.24775 1.89615L4.1953 5.8437L0.247751 9.79124C0.180787 9.85821 0.147304 9.93522 0.147304 10.0223C0.147304 10.1093 0.180787 10.1863 0.247751 10.2533L0.749984 10.7555C0.816948 10.8225 0.893957 10.856 0.98101 10.856C1.06806 10.856 1.14507 10.8225 1.21204 10.7555L5.89284 6.07473C5.9598 6.00776 5.99329 5.93075 5.99329 5.8437C5.99329 5.75664 5.9598 5.67964 5.89284 5.61267L1.21204 0.931868C1.14507 0.864903 1.06806 0.831421 0.981009 0.831421C0.893956 0.831421 0.816947 0.864903 0.749983 0.931868L0.24775 1.4341Z"
						fill="#FA7846"></path>
				</svg>
				<div itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
					<a href="#" class="breadcrumbs__link" itemprop="item">
						<span itemprop="name">
							@if($category)
								{{ $category->getTranslatedAttribute('title') }}
							@else
								@lang("blog.tag_all")
							@endif
						</span>
					</a>
					<meta itemprop="position" content="3" />
				</div>
			</div>



		</div>
	</div>

	<div class="vz-art blog__filter">
		<div class="section-frame">
			<div class="blog__filter--inner">
				<div class="blog__filter-top-row">
					<h1 class="f-title">
						@if($category)
							{{ $category->getTranslatedAttribute('title') }}
						@else
							@lang("blog.tag_all")
						@endif
					</h1>
					{{-- <form class="f-search">
						<svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
							<g clip-path="url(#clip0_209_266)">
								<path
									d="M25 23.8953L16.5272 15.4225C17.911 13.7884 18.75 11.6791 18.75 9.37501C18.75 4.20531 14.5447 0 9.37501 0C4.20536 0 0 4.20531 0 9.37501C0 14.5447 4.20531 18.75 9.37501 18.75C11.6791 18.75 13.7884 17.911 15.4225 16.5272L23.8953 25L25 23.8953ZM9.37501 17.1875C5.06745 17.1875 1.56252 13.6826 1.56252 9.37501C1.56252 5.06745 5.06745 1.56252 9.37501 1.56252C13.6826 1.56252 17.1875 5.06745 17.1875 9.37501C17.1875 13.6826 13.6826 17.1875 9.37501 17.1875Z"
									fill="#FA7846" />
							</g>
							<defs>
								<clipPath id="clip0_209_266">
									<rect width="25" height="25" fill="white" />
								</clipPath>
							</defs>
						</svg>
						<div class="f-search-input">
							<input placeholder="Поиск по канвас" type="search">
						</div>
						<button class="btn-search" type="submit">
							Найти
						</button>
					</form> --}}
					<div class="search-trigger">
						<svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
							<g clip-path="url(#clip0_209_266)">
								<path
									d="M25 23.8953L16.5272 15.4225C17.911 13.7884 18.75 11.6791 18.75 9.37501C18.75 4.20531 14.5447 0 9.37501 0C4.20536 0 0 4.20531 0 9.37501C0 14.5447 4.20531 18.75 9.37501 18.75C11.6791 18.75 13.7884 17.911 15.4225 16.5272L23.8953 25L25 23.8953ZM9.37501 17.1875C5.06745 17.1875 1.56252 13.6826 1.56252 9.37501C1.56252 5.06745 5.06745 1.56252 9.37501 1.56252C13.6826 1.56252 17.1875 5.06745 17.1875 9.37501C17.1875 13.6826 13.6826 17.1875 9.37501 17.1875Z"
									fill="#FA7846" />
							</g>
							<defs>
								<clipPath id="clip0_209_266">
									<rect width="25" height="25" fill="white" />
								</clipPath>
							</defs>
						</svg>
					</div>
				</div>
				<div class="blog__filter-wrap">
					{{-- <div class="f-trigger">
						<span>Выбор рубрики</span>
						<svg width="12" height="8" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M1 0.999999L6 7L11 1" stroke="#1E2533" />
						</svg>
					</div> --}}
					<div class="f-inner">

						<a href="{{ route('blog_category_all' ) }}" @if($category == null) class="active" @endif>@lang("blog.tag_all")</a>
						@foreach ($blog_categories as $category)
							<a href="{{ route('blog_category', $category->slug ) }}" @if($category->slug == $slug) class="active" @endif>{{ $category->getTranslatedAttribute('title') }}</a>
						@endforeach

					</div>
				</div>
				<div class="blog__list">

					@foreach ($blog_posts as $blog_item)
						<a class="blog__list-item bl-mobile" href="{{ route('blog_inner', $blog_item->getTranslatedAttribute('slug')) }}">
							<div class="newone-slide-img">
								<picture>
									@if($blog_item['image_preview'])
									@php
										$blogItemImage = image_picture_sources(Voyager::image($blog_item['image_preview']));
									@endphp
									@if($blogItemImage['src_webp'])
										<source srcset="{{ $blogItemImage['src_webp'] }}" type="image/webp">
									@endif
									<source srcset="{{ $blogItemImage['src'] }}" type="{{ $blogItemImage['type'] ?? 'image/jpeg' }}">
									<img @altAttrs(['type' => \App\Models\BlogPost::class, 'id' => $blog_item['id'] ?? null], 'image_preview', $blog_item['image_preview'] ?? null, null, $blog_item['title'] ?? null) class="img"
										src="{{ $blogItemImage['src'] }}" data-src="{{ $blogItemImage['src'] }}">
									@else
									@php
										$blogItemImage = image_picture_sources(Voyager::image($blog_item['image']));
									@endphp
									@if($blogItemImage['src_webp'])
										<source srcset="{{ $blogItemImage['src_webp'] }}" type="image/webp">
									@endif
									<source srcset="{{ $blogItemImage['src'] }}" type="{{ $blogItemImage['type'] ?? 'image/jpeg' }}">
									<img @altAttrs(['type' => \App\Models\BlogPost::class, 'id' => $blog_item['id'] ?? null], 'image', $blog_item['image'] ?? null, null, $blog_item['title'] ?? null) class="img"
										src="{{ $blogItemImage['src'] }}" data-src="{{ $blogItemImage['src'] }}">
									@endif
								</picture>
							</div>
							<div class="newone-slide-content">

								@if(!$blog_item->categoryes()->get()->isEmpty())
									<div class="blog-tags">
										@foreach ($blog_item->categoryes()->get()->translate(App::getLocale()) as $cat_item)
											<p>{{ $cat_item->title }}</p>
										@endforeach
									</div>
								@endif
								<div class="h3_old">{{ $blog_item->getTranslatedAttribute('title') }}</div>
								<div class="text">
									{!! strip_tags(Str::limit($blog_item->getTranslatedAttribute('text'), 120)) !!}

								</div>
								<div class="blog__screen--info">
									<div class="b-time">
										<svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M7.12854 4.15771V7.82557L9.72854 10.472" stroke="#848484" stroke-width="1.3" stroke-miterlimit="10">
											</path>
											<path
												d="M7.5 14C11.0899 14 14 11.0899 14 7.5C14 3.91015 11.0899 1 7.5 1C3.91015 1 1 3.91015 1 7.5C1 11.0899 3.91015 14 7.5 14Z"
												stroke="#848484" stroke-width="1.3" stroke-miterlimit="10"></path>
										</svg>
										<span>
											@php
												$cnt_round_string = round(((int)str_word_count(strip_tags($blog_item->getTranslatedAttribute('text'))) / 100),0, PHP_ROUND_HALF_UP)
											@endphp

											@if($cnt_round_string > 0)
												{{ $cnt_round_string }} @lang("blog.min")
											@else
												1 @lang("blog.min")
											@endif

										</span>
									</div>
									<div class="b-view">
										<svg width="19" height="13" viewBox="0 0 19 13" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path
												d="M1 6.47326L0.433212 6.15506L0.263953 6.45655L0.419355 6.76541L1 6.47326ZM18 6.49126L18.5668 6.80946L18.736 6.50797L18.5806 6.19911L18 6.49126ZM9.3399 12V12.65V12ZM1.56679 6.79146C2.06301 5.90756 3.05392 4.60883 4.45169 3.53722C5.84491 2.46911 7.61018 1.65 9.67327 1.65V0.35C7.25111 0.35 5.21515 1.31384 3.66074 2.50552C2.1109 3.69371 1.00779 5.1316 0.433212 6.15506L1.56679 6.79146ZM9.67327 1.65C13.7962 1.65 16.5054 4.96699 17.4194 6.78341L18.5806 6.19911C17.5752 4.20083 14.5208 0.35 9.67327 0.35V1.65ZM17.4332 6.17306C16.9358 7.05907 15.947 8.36696 14.5524 9.44697C13.1621 10.5236 11.401 11.35 9.3399 11.35V12.65C11.7641 12.65 13.7976 11.6757 15.3483 10.4748C16.8947 9.27727 17.9934 7.8308 18.5668 6.80946L17.4332 6.17306ZM9.3399 11.35C5.21905 11.35 2.49604 8.00044 1.58064 6.18111L0.419355 6.76541C1.42332 8.76078 4.49024 12.65 9.3399 12.65V11.35Z"
												fill="#848484"></path>
											<circle cx="9.5" cy="6.5" r="1.5" fill="#848484"></circle>
										</svg>
										<span>{{ $blog_item->count_viewed }}</span>
									</div>
								</div>
							</div>
						</a>
					@endforeach

				</div>
				<div class="blog__list-nav">
					<a href="#">
						{{-- Смотреть больше --}}
					</a>

					@if (!is_array($blog_posts))
						{{ $blog_posts->links('theme.viar.blog.paginate') }}
					@endif
				</div>
			</div>
		</div>
	</div>

	<div class="cblog__section">
		<div class="section-frame">
			<div class="cblog__section-inner">

				@foreach ($blog_short_blocks as $short_block)

				<div class="cblog__section-item">
					<div class="page-title cblog__section-title">
						{{ $short_block->getTranslatedAttribute('title') }}
					</div>
					<p>
						{!! render_content_images($short_block->getTranslatedAttribute('text_top')) !!}
						{{-- <span>Канвас</span> — холст, на котором рисуют художники.
						Полотно для холста бывает разных видов: натуральное, синтетическое, плотное или тонкое. У
						каждого полотна своя фактура поверхности, как и у любой ткани. Каждый вид полотна
						используется по своему назначению. --}}
					</p>
					<div class="cblog__section-row">
						<div class="cblog__section-img">
							<picture>
								@php
									$shortBlockImage = image_picture_sources(Voyager::image($short_block['image']));
								@endphp
								@if($shortBlockImage['src_webp'])
									<source srcset="{{ $shortBlockImage['src_webp'] }}" type="image/webp">
								@endif
								<source srcset="{{ $shortBlockImage['src'] }}" type="{{ $shortBlockImage['type'] ?? 'image/jpeg' }}">
								<img src="{{ $shortBlockImage['src'] }}" @altAttrs(['type' => \App\Models\BlogCategoriesShortBlock::class, 'id' => $short_block['id'] ?? null], 'image', $short_block['image'] ?? null, null, 'VIARCANVAS')>
							</picture>
						</div>
						<div class="cblog__section-list">
							{!! render_content_images($short_block->getTranslatedAttribute('text_right')) !!}

							<div class="b-mobile">
								{!! render_content_images($short_block->getTranslatedAttribute('text_bottom')) !!}
							</div>
						</div>
					</div>
					<div class="b-desk">
						{!! render_content_images($short_block->getTranslatedAttribute('text_bottom')) !!}
					</div>
				</div>
				@endforeach

			</div>
			<div class="cblog__section-nav newone-arrow">
				<a href="#" class="newone-prev slick-arrow slick-disabled" aria-label="newone-prev" style="" aria-disabled="true">
					<i class="fa-arrow-prev"></i>
				</a>
				<a href="#" class="newone-next slick-arrow" aria-label="newone-next" style="" aria-disabled="false">
					<i class="fa-arrow-next"></i>
				</a>
			</div>
		</div>
	</div>



	<div class="discount__screen secondis">
		<div class="section-frame">
			<div class="discount__screen--inner">
				<div class="discount-title page-title">
					@lang("blog.discount_paintings")
				</div>
				<div class="discount-row">
					@foreach ($AMailTopSale as $top_sale)
						<a href="{{ $top_sale->link }}" class="stay discount-item">
							<div class="discount-img">
								<picture>
									@php
										$topSaleImage = image_picture_sources(Voyager::image($top_sale['image']));
									@endphp
									@if($topSaleImage['src_webp'])
										<source srcset="{{ $topSaleImage['src_webp'] }}" type="image/webp">
									@endif
									<source srcset="{{ $topSaleImage['src'] }}" type="{{ $topSaleImage['type'] ?? 'image/jpeg' }}">
									<img src="{{ $topSaleImage['src'] }}" @altAttrs(['type' => \App\Models\AMailTopSale::class, 'id' => $top_sale['id'] ?? null], 'image', $top_sale['image'] ?? null)>
								</picture>
							</div>
							{{-- <p>Скидка -35%</p> --}}
							<p>{{ $top_sale->price }}€</p>
							<span>{{ $top_sale->size }}</span>
							<div class="h4_old">{{ $top_sale->title }}</div>
						</a>
					@endforeach
				</div>
			</div>
		</div>
	</div>


	<div class="blogsoc__screen">
		<div class="section-frame">
			<div class="blogsoc__screen--inner">
				<div class="blogsoc-title page-title">
					@lang("blog.our_social_networks")
				</div>
				<ul>
					<li>
						<a href="{{ setting('sots-seti.facebook') }}" target="_blank">
							<svg width="19" height="39" viewBox="0 0 19 39" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path
									d="M4.32938 7.69898C4.32938 8.67696 4.32938 13.0421 4.32938 13.0421H0.39209V19.5756H4.32938V38.9911H12.4174V19.5762H17.8449C17.8449 19.5762 18.3532 16.4434 18.5996 13.018C17.8931 13.018 12.448 13.018 12.448 13.018C12.448 13.018 12.448 9.21697 12.448 8.55073C12.448 7.88304 13.3298 6.98492 14.2014 6.98492C15.0714 6.98492 16.9075 6.98492 18.6081 6.98492C18.6081 6.09536 18.6081 3.02176 18.6081 0.183105C16.3378 0.183105 13.755 0.183105 12.6165 0.183105C4.12946 0.182654 4.32938 6.72272 4.32938 7.69898Z"
									fill="white" />
							</svg>
						</a>
					</li>
					<li>
						<a href="{{ setting('sots-seti.instagram') }}" target="_blank">
							<svg width="33" height="33" viewBox="0 0 33 33" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path fill-rule="evenodd" clip-rule="evenodd"
									d="M0.848145 8.55908C0.848145 4.14081 4.42987 0.559082 8.84815 0.559082H24.5281C28.9464 0.559082 32.5281 4.1408 32.5281 8.55908V24.2391C32.5281 28.6574 28.9464 32.2391 24.5281 32.2391H8.84815C4.42987 32.2391 0.848145 28.6574 0.848145 24.2391V8.55908ZM16.6881 22.1991C19.8913 22.1991 22.4881 19.6023 22.4881 16.3991C22.4881 13.1958 19.8913 10.5991 16.6881 10.5991C13.4848 10.5991 10.8881 13.1958 10.8881 16.3991C10.8881 19.6023 13.4848 22.1991 16.6881 22.1991ZM16.6881 25.1991C21.5482 25.1991 25.4881 21.2592 25.4881 16.3991C25.4881 11.539 21.5482 7.59906 16.6881 7.59906C11.828 7.59906 7.88807 11.539 7.88807 16.3991C7.88807 21.2592 11.828 25.1991 16.6881 25.1991ZM25.4881 9.3591C26.4601 9.3591 27.2481 8.57112 27.2481 7.5991C27.2481 6.62708 26.4601 5.8391 25.4881 5.8391C24.5161 5.8391 23.7281 6.62708 23.7281 7.5991C23.7281 8.57112 24.5161 9.3591 25.4881 9.3591Z"
									fill="white" />
							</svg>
						</a>
					</li>
				</ul>
				@if($category->getTranslatedAttribute('seo'))
					<div class="blog_seo_desc">
						{!! $category->getTranslatedAttribute('seo') !!}
					</div>
				@elseif($category == false && $data['seo_tag_all'])
					<div class="blog_seo_desc">
						{!! $data['seo_tag_all'] !!}
					</div>
				@else

				@endif

			</div>
		</div>
	</div>

</div>
