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
				<svg xmlns="http://www.w3.org/2000/svg" width="6" height="11" viewBox="0 0 6 11" fill="none" class="img-svg breadcrumbs__arrow replaced-svg">
					<path d="M0.24775 1.4341C0.180786 1.50106 0.147304 1.57807 0.147304 1.66513C0.147304 1.75218 0.180786 1.82919 0.24775 1.89615L4.1953 5.8437L0.247751 9.79124C0.180787 9.85821 0.147304 9.93522 0.147304 10.0223C0.147304 10.1093 0.180787 10.1863 0.247751 10.2533L0.749984 10.7555C0.816948 10.8225 0.893957 10.856 0.98101 10.856C1.06806 10.856 1.14507 10.8225 1.21204 10.7555L5.89284 6.07473C5.9598 6.00776 5.99329 5.93075 5.99329 5.8437C5.99329 5.75664 5.9598 5.67964 5.89284 5.61267L1.21204 0.931868C1.14507 0.864903 1.06806 0.831421 0.981009 0.831421C0.893956 0.831421 0.816947 0.864903 0.749983 0.931868L0.24775 1.4341Z" fill="#FA7846"></path>
				</svg>
				<div itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
					<a href="{{ route('blog') }}" class="breadcrumbs__link breadcrumbs__link_main" itemprop="item">
						<span itemprop="name">{{ $data['title'] }}</span>
					</a>
					<meta itemprop="position" content="2" />
				</div>
				<svg xmlns="http://www.w3.org/2000/svg" width="6" height="11" viewBox="0 0 6 11" fill="none" class="img-svg breadcrumbs__arrow replaced-svg">
					<path d="M0.24775 1.4341C0.180786 1.50106 0.147304 1.57807 0.147304 1.66513C0.147304 1.75218 0.180786 1.82919 0.24775 1.89615L4.1953 5.8437L0.247751 9.79124C0.180787 9.85821 0.147304 9.93522 0.147304 10.0223C0.147304 10.1093 0.180787 10.1863 0.247751 10.2533L0.749984 10.7555C0.816948 10.8225 0.893957 10.856 0.98101 10.856C1.06806 10.856 1.14507 10.8225 1.21204 10.7555L5.89284 6.07473C5.9598 6.00776 5.99329 5.93075 5.99329 5.8437C5.99329 5.75664 5.9598 5.67964 5.89284 5.61267L1.21204 0.931868C1.14507 0.864903 1.06806 0.831421 0.981009 0.831421C0.893956 0.831421 0.816947 0.864903 0.749983 0.931868L0.24775 1.4341Z" fill="#FA7846"></path>
				</svg>
				<div itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
					<a href="{{ route('blog_inner', $blog_item->slug) }}" class="breadcrumbs__link" itemprop="item">
						<span itemprop="name">{{ $blog_item->title }}</span>
					</a>
					<meta itemprop="position" content="3" />
				</div>
			</div>

		</div>
	</div>


	<div class="article__screen">
		<div class="section-frame">
			<div class="article__screen--inner" itemscope="" itemtype="https://schema.org/Article">
				<h1 class="article-title page-title" itemprop="headline">
					{{ $blog_item->title }}
				</h1>
				<div class="article__screen--image">
					@php
						$blogImage = image_picture_sources(Voyager::image($blog_item['image']));
					@endphp
					<picture>
						@if($blogImage['src_webp'])
							<source srcset="{{ $blogImage['src_webp'] }}" type="image/webp">
						@endif
						<source srcset="{{ $blogImage['src'] }}" type="{{ $blogImage['type'] ?? 'image/jpeg' }}">
						<img @altAttrs(['type' => \App\Models\BlogPost::class, 'id' => $blog_item['id'] ?? null], 'image', $blog_item['image'] ?? null, null, $blog_item['title'] ?? null) class="img"
							src="{{ $blogImage['src'] }}" data-src="{{ $blogImage['src'] }}" itemprop="image">
					</picture>
				</div>
				<div class="article__screen--row">
					<div class="article-author">
							@if(isset($blog_author->image))
								<div class="article-author--img">
									@php
										$authorImage = image_picture_sources(Voyager::image($blog_author->image));
									@endphp
									<picture>
										@if($authorImage['src_webp'])
											<source srcset="{{ $authorImage['src_webp'] }}" type="image/webp">
										@endif
										<source srcset="{{ $authorImage['src'] }}" type="{{ $authorImage['type'] ?? 'image/jpeg' }}">
										<img src="{{ $authorImage['src'] }}" @altAttrs(['type' => \App\Models\BlogAuthor::class, 'id' => $blog_author->id ?? null], 'image', $blog_author->image ?? null)>
									</picture>
								</div>
							@endif
							@if(isset($blog_author->name))
								<b itemprop="author">{{ $blog_author->getTranslatedAttribute('name') }}</b>
							@else
								<b itemprop="author">Admin</b>
							@endif
						<p>@lang("blog.article_author")</p>
						<div class="a-date">
							{{ date('d M, Y', strtotime($blog_item->created_at)) }}
							<span class="date-published" itemprop="datePublished" style="display: none;"><?php echo date('Y-m-d\TH:i:sP', strtotime($blog_item->created_at)); ?></span>
							<span class="date-modified" itemprop="dateModified" style="display: none;"><?php echo date('Y-m-d\TH:i:sP', strtotime($blog_item->updated_at)); ?></span>
							<a href="{{ route('blog_inner', $blog_item->slug) }}" itemprop="url" style="display: none;"><?php echo date('Y-m-d\TH:i:sP', strtotime($blog_item->updated_at)); ?>{{ $blog_item->title }}</a>
						</div>
					</div>
					<div class="article-content">
						<div class="article-info">
							<p>@lang('blog.reading_time') —
								@php
									$cnt_round_string = round((int) str_word_count(strip_tags($blog_item->text)) / 100, 0, PHP_ROUND_HALF_UP);
								@endphp

								@if ($cnt_round_string > 0)
									{{ $cnt_round_string }} @lang('blog.min')
								@else
									1 @lang('blog.min')
								@endif
							</p>
							<p>
								<svg width="19" height="13" viewBox="0 0 19 13" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path
										d="M1 6.47326L0.433212 6.15506L0.263953 6.45655L0.419355 6.76541L1 6.47326ZM18 6.49126L18.5668 6.80946L18.736 6.50797L18.5806 6.19911L18 6.49126ZM9.3399 12V12.65V12ZM1.56679 6.79146C2.06301 5.90756 3.05392 4.60883 4.45169 3.53722C5.84491 2.46911 7.61018 1.65 9.67327 1.65V0.35C7.25111 0.35 5.21515 1.31384 3.66074 2.50552C2.1109 3.69371 1.00779 5.1316 0.433212 6.15506L1.56679 6.79146ZM9.67327 1.65C13.7962 1.65 16.5054 4.96699 17.4194 6.78341L18.5806 6.19911C17.5752 4.20083 14.5208 0.35 9.67327 0.35V1.65ZM17.4332 6.17306C16.9358 7.05907 15.947 8.36696 14.5524 9.44697C13.1621 10.5236 11.401 11.35 9.3399 11.35V12.65C11.7641 12.65 13.7976 11.6757 15.3483 10.4748C16.8947 9.27727 17.9934 7.8308 18.5668 6.80946L17.4332 6.17306ZM9.3399 11.35C5.21905 11.35 2.49604 8.00044 1.58064 6.18111L0.419355 6.76541C1.42332 8.76078 4.49024 12.65 9.3399 12.65V11.35Z"
										fill="#848484" />
									<circle cx="9.5" cy="6.5" r="1.5" fill="#848484" />
								</svg>
								<span>{{ $blog_item->count_viewed }}</span>
							</p>
						</div>
						<div class="article-block" itemprop="articleBody">
							{!! storefront_html(render_content_images($blog_item->text)) !!}
						</div>
						{{-- <div class="article-block">
						<div class="article-banner orange-banner">
							<div class="article-banner--inner">
								<h1>
									Фото картина
								</h1>
								<p>cкидка -25%</p>
								<a href="#">
									Смотреть подробно
								</a>
								<div class="banner-discount">
									100х70 см
								</div>
							</div>
							<picture>
								<source srcset="images/blog/ab1.png" type="image/jpeg">
								<img src="images/blog/ab1.png" alt="">
							</picture>
						</div>

						<div class="article-banner blue-banner">
							<div class="article-banner--inner">
								<h1>
									Бесплатная доставка
								</h1>
								<p>До 20 апреля</p>
								<a href="#">
									Код скидки <b>FREE20</b>
								</a>
							</div>
							<picture>
								<source srcset="images/blog/ab2.png" type="image/jpeg">
								<img src="images/blog/ab2.png" alt="">
							</picture>
						</div>
						--}}
					</div>
					<div class="article-sidebar">

						{{-- <div class="article-social">
							<h3>@lang("blog.share_on_social_networks")</h3>
							<ul>
								<li>
									<a href="#">
										<svg width="20" height="16" viewBox="0 0 20 16" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path
												d="M19.6 1.89999C18.9 2.19999 18.1 2.4 17.3 2.5C18.1 2 18.8 1.2 19.1 0.300003C18.3 0.800003 17.5 1.1 16.5 1.3C15.8 0.500003 14.7 0 13.6 0C11.4 0 9.59999 1.8 9.59999 4C9.59999 4.3 9.6 4.59999 9.7 4.89999C6.4 4.69999 3.39999 3.1 1.39999 0.699997C1.09999 1.3 0.899994 2 0.899994 2.7C0.899994 4.1 1.6 5.3 2.7 6C2 6 1.39999 5.8 0.899994 5.5C0.899994 7.4 2.29999 9.09999 4.09999 9.39999C3.79999 9.49999 3.4 9.5 3 9.5C2.7 9.5 2.5 9.49999 2.2 9.39999C2.7 11 4.2 12.2 6 12.2C4.6 13.3 2.9 13.9 1 13.9C0.7 13.9 0.4 13.9 0 13.8C1.8 14.9 3.9 15.6 6.2 15.6C13.6 15.6 17.6 9.5 17.6 4.2V3.7C18.4 3.4 19.1 2.69999 19.6 1.89999Z"
												fill="url(#paint0_linear_3920_559)" />
											<defs>
												<linearGradient id="paint0_linear_3920_559" x1="0" y1="7.8" x2="19.6" y2="7.8"
													gradientUnits="userSpaceOnUse">
													<stop stop-color="#FC8C5F" />
													<stop offset="1" stop-color="#FA7846" />
												</linearGradient>
											</defs>
										</svg>
									</a>
								</li>
								<li>
									<a href="#">
										<svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path
												d="M17 2.81865C17 1.32124 15.6788 0 14.1813 0H2.81865C1.32124 0 0 1.32124 0 2.81865V14.1813C0 15.6787 1.32124 17 2.81865 17H8.54404V10.5699H6.43005V7.7513H8.54404V6.60622C8.54404 4.66839 9.95337 2.99481 11.715 2.99481H14.0052V5.81346H11.715C11.4508 5.81346 11.1865 6.07772 11.1865 6.60622V7.7513H14.0052V10.5699H11.1865V17H14.1813C15.6788 17 17 15.6787 17 14.1813V2.81865Z"
												fill="url(#paint0_linear_3920_556)" />
											<defs>
												<linearGradient id="paint0_linear_3920_556" x1="0" y1="8.5" x2="17" y2="8.5" gradientUnits="userSpaceOnUse">
													<stop stop-color="#FC8C5F" />
													<stop offset="1" stop-color="#FA7846" />
												</linearGradient>
											</defs>
										</svg>
									</a>
								</li>
								<li>
									<a href="#">
										<svg width="20" height="18" viewBox="0 0 20 18" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path
												d="M0.350701 8.37408L4.9488 10.0886L6.74126 15.8557C6.8192 16.2454 7.2868 16.3233 7.59854 16.0895L10.1703 13.9853C10.4041 13.7515 10.7938 13.7515 11.1055 13.9853L15.7036 17.3364C16.0154 17.5702 16.483 17.4144 16.5609 17.0247L19.99 0.658655C20.0679 0.268988 19.6782 -0.120677 19.2886 0.0351905L0.350701 7.36095C-0.1169 7.51681 -0.1169 8.21821 0.350701 8.37408ZM6.50747 9.23135L15.5477 3.69807C15.7036 3.62013 15.8595 3.85394 15.7036 3.93187L8.29993 10.868C8.06613 11.1018 7.83233 11.4135 7.83233 11.8032L7.59854 13.6736C7.59854 13.9074 7.20886 13.9853 7.13093 13.6736L6.19572 10.2445C5.96192 9.85482 6.1178 9.38721 6.50747 9.23135Z"
												fill="url(#paint0_linear_3920_553)" />
											<defs>
												<linearGradient id="paint0_linear_3920_553" x1="0" y1="8.72314" x2="20" y2="8.72314"
													gradientUnits="userSpaceOnUse">
													<stop stop-color="#FC8C5F" />
													<stop offset="1" stop-color="#FA7846" />
												</linearGradient>
											</defs>
										</svg>
									</a>
								</li>
								<li>
									<a href="#">
										<svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path
												d="M15 8.7444V14.2601H11.7713V9.08072C11.7713 7.80269 11.3004 6.92825 10.157 6.92825C9.28251 6.92825 8.74439 7.53363 8.5426 8.07175C8.47534 8.27354 8.40807 8.5426 8.40807 8.87892V14.2601H5.17937C5.17937 14.2601 5.24664 5.5157 5.17937 4.64126H8.40807V5.98655C8.81166 5.3139 9.61883 4.3722 11.3004 4.3722C13.3856 4.3722 15 5.78475 15 8.7444ZM1.81614 0C0.739908 0 0 0.73991 0 1.68161C0 2.62332 0.672645 3.36323 1.74888 3.36323C2.89238 3.36323 3.56502 2.62332 3.56502 1.68161C3.63229 0.672646 2.95964 0 1.81614 0ZM0.201793 14.2601H3.43049V4.64126H0.201793V14.2601Z"
												fill="url(#paint0_linear_3920_562)" />
											<defs>
												<linearGradient id="paint0_linear_3920_562" x1="0" y1="7.13004" x2="15" y2="7.13004"
													gradientUnits="userSpaceOnUse">
													<stop stop-color="#FC8C5F" />
													<stop offset="1" stop-color="#FA7846" />
												</linearGradient>
											</defs>
										</svg>
									</a>
								</li>
							</ul>
						</div> --}}
						<div class="article-sidebar--content">
							<h2 class="blog__sidebar--title">
								@lang('blog.popular')
							</h2>
							<div class="blog__sidebar--content">
								@foreach ($popular_posts as $popular_post)
									<a href="{{ route('blog_inner', $popular_post->getTranslatedAttribute('slug')) }}"
										class="blog__sidebar--item">
										@if (!$popular_post->categoryes()->get()->isEmpty())
											<div class="blog-tags">
												@foreach ($popular_post->categoryes()->get()->translate(App::getLocale()) as $cat_item)
													<p>{{ $cat_item->title }}</p>
												@endforeach
											</div>
										@endif

										<div class="blog__s-title">
											{{ $popular_post->getTranslatedAttribute('title') }}
										</div>
										<div class="blog__screen--info">
											<div class="b-time">
												<svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
													<path d="M7.12854 4.15771V7.82557L9.72854 10.472" stroke="#848484" stroke-width="1.3"
														stroke-miterlimit="10" />
													<path
														d="M7.5 14C11.0899 14 14 11.0899 14 7.5C14 3.91015 11.0899 1 7.5 1C3.91015 1 1 3.91015 1 7.5C1 11.0899 3.91015 14 7.5 14Z"
														stroke="#848484" stroke-width="1.3" stroke-miterlimit="10" />
												</svg>
												<span>
													@php
														$cnt_round_string = round((int) str_word_count(strip_tags($popular_post->getTranslatedAttribute('text'))) / 100, 0, PHP_ROUND_HALF_UP);
													@endphp

													@if ($cnt_round_string > 0)
														{{ $cnt_round_string }} @lang('blog.min')
													@else
														1 @lang('blog.min')
													@endif
												</span>
											</div>
											<div class="b-view">
												<svg width="19" height="13" viewBox="0 0 19 13" fill="none" xmlns="http://www.w3.org/2000/svg">
													<path
														d="M1 6.47326L0.433212 6.15506L0.263953 6.45655L0.419355 6.76541L1 6.47326ZM18 6.49126L18.5668 6.80946L18.736 6.50797L18.5806 6.19911L18 6.49126ZM9.3399 12V12.65V12ZM1.56679 6.79146C2.06301 5.90756 3.05392 4.60883 4.45169 3.53722C5.84491 2.46911 7.61018 1.65 9.67327 1.65V0.35C7.25111 0.35 5.21515 1.31384 3.66074 2.50552C2.1109 3.69371 1.00779 5.1316 0.433212 6.15506L1.56679 6.79146ZM9.67327 1.65C13.7962 1.65 16.5054 4.96699 17.4194 6.78341L18.5806 6.19911C17.5752 4.20083 14.5208 0.35 9.67327 0.35V1.65ZM17.4332 6.17306C16.9358 7.05907 15.947 8.36696 14.5524 9.44697C13.1621 10.5236 11.401 11.35 9.3399 11.35V12.65C11.7641 12.65 13.7976 11.6757 15.3483 10.4748C16.8947 9.27727 17.9934 7.8308 18.5668 6.80946L17.4332 6.17306ZM9.3399 11.35C5.21905 11.35 2.49604 8.00044 1.58064 6.18111L0.419355 6.76541C1.42332 8.76078 4.49024 12.65 9.3399 12.65V11.35Z"
														fill="#848484" />
													<circle cx="9.5" cy="6.5" r="1.5" fill="#848484" />
												</svg>
												<span>{{ $popular_post->count_viewed }}</span>
											</div>
										</div>
									</a>
								@endforeach
							</div>
						</div>
						@if(isset($blog_right_banner) && $blog_right_banner)
							{!! $blog_right_banner !!}
						<!-- <div class="article-adds">
							<div class="a-adds-title">
								Хотите узнать мнение нашего художника?
							</div>
							<picture>
								<source srcset="https://viarcanvas.com/theme/viar/img/add1.webp" type="image/webp">
								<source srcset="https://viarcanvas.com/theme/viar/img/add1.png" type="image/png">
								<img src="https://viarcanvas.com/theme/viar/img/add1.png" alt="">
							</picture>
							<a href="#" class="a-adds-btn">
								<span>Спросить в чате</span>
								<svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M17.2266 0.0644531C12.5918 0.539062 8.65435 3.39258 7.45317 7.14844C7.35356 7.45312 7.25395 7.81055 7.23052 7.93359C7.19536 8.13867 7.20122 8.16211 7.30083 8.13281C8.25591 7.85156 9.02935 7.66406 9.62114 7.56445C13.0196 7.00195 16.3477 7.4707 19.3067 8.92969C20.8184 9.67383 21.8262 10.3828 22.9512 11.4902C25.0723 13.5703 26.2208 15.9844 26.4551 18.8496L26.4961 19.3359H27.9141C29.5196 19.3359 29.625 19.3125 29.8653 18.8906C30.0235 18.6211 30.0352 18.3457 29.9063 18.0938C29.8536 17.9883 29.3379 17.3613 28.7579 16.6875L27.709 15.4629L27.9375 15.2051C28.7872 14.2383 29.5313 12.709 29.836 11.291C30.0176 10.4355 30.0176 8.90039 29.836 8.04492C28.9102 3.73828 24.8614 0.527344 19.7754 0.0585938C19.0254 -0.0117188 17.9415 -0.00585938 17.2266 0.0644531Z" fill="white"/>
									<path d="M11.2499 9.12305C8.95298 9.29883 6.4979 10.1309 4.71665 11.3262C3.45103 12.1758 2.27915 13.3477 1.51157 14.5312C-0.0353005 16.9043 -0.398582 19.6348 0.468606 22.3535C0.814309 23.4434 1.55259 24.7852 2.24986 25.5996L2.51939 25.9102L2.33189 26.127C0.650246 28.0371 0.0994652 28.6816 0.0584495 28.8047C-0.0938942 29.2031 0.0936058 29.6895 0.468606 29.8828C0.697121 30 0.796731 30 7.23032 30C14.2147 30 14.4549 29.9941 15.6151 29.7012C20.0799 28.5879 23.6834 25.2129 24.5155 21.3574C24.9549 19.3477 24.703 17.4141 23.7538 15.498C23.2381 14.4434 22.7401 13.7695 21.7674 12.8027C20.8944 11.9297 20.2381 11.4258 19.2186 10.8457C16.91 9.5332 14.0096 8.90625 11.2499 9.12305ZM7.81626 17.7773C8.4315 18.0762 8.75962 18.6211 8.75962 19.3359C8.75962 19.834 8.63657 20.168 8.32603 20.5254C7.84556 21.0645 6.93736 21.2344 6.25767 20.9004C5.32017 20.4375 4.9979 19.2715 5.56626 18.3809C6.03501 17.6426 7.00767 17.3848 7.81626 17.7773ZM13.1483 17.7773C13.7635 18.0762 14.0917 18.6211 14.0917 19.3359C14.0917 19.834 13.9686 20.168 13.6581 20.5254C13.1776 21.0645 12.2694 21.2344 11.5897 20.9004C10.6522 20.4375 10.3299 19.2715 10.8983 18.3809C11.367 17.6426 12.3397 17.3848 13.1483 17.7773ZM18.4803 17.7773C19.0956 18.0762 19.4237 18.6211 19.4237 19.3359C19.4237 19.834 19.3006 20.168 18.9901 20.5254C18.5096 21.0645 17.6014 21.2344 16.9217 20.9004C15.9842 20.4375 15.662 19.2715 16.2303 18.3809C16.6991 17.6426 17.6717 17.3848 18.4803 17.7773Z" fill="white"/>
								</svg>
							</a>
						</div> -->
						@endif
					</div>
				</div>
			</div>

			{{-- @include(env('THEME_RESOURCES') . 'pages.index.faq9') --}}
		</div>
	</div>
	<style>
		.main-first {
			padding: 150px 0 92px;
			background: #fbeaead4;
		}

		.main-first {
			padding: 90px 0 92px;
		}
	</style>	
	@include(env('THEME_RESOURCES') . 'pages.faq.item',["faqs" => $faqs])
</div>
