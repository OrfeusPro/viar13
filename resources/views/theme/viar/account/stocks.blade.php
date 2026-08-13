@include(env('THEME_RESOURCES') . 'account.breads', ['page' => $page])
<script src="{{ ver_asset(env('THEME') . 'js/intlTelInput.min.js') }}"></script>

<div class="main-cabinet">
	<div class="section-frame">
		<div class="main-cabinet__inner">
			<div class="cabinet-top__block">
				<div class="page-title cabinet-title">
					@lang('account_new.menu.my_bonuses')
				</div>
				<p>
					@lang('account_new.stocks.p1')
					<a class="activeSpan" href="{{ route('logout') }}"
						onclick="event.preventDefault(); document.getElementById('logout-form').submit();">@lang('account_new.btn.logout')</a>
				</p>
			</div>
			<div class="cabinet-container">

				@include(env('THEME_RESOURCES') . 'account.menu')

				<div class="cabinet-content">
					<div class="cabinet-bonus">
						<div class="cabinet-bonus__block">
							<div class="bonus-card">
								<p>@lang('account_new.stocks.bonus.title')</p>
								<div class="bonus-card__row">
									<span>
										@lang('account.index27')
									</span>
									<div class="box">


										<p>@lang('account_new.stocks.bonuses')</p>
										<span>
											@if (Auth::user()->bonuses == null)
												0€
											@else
												{{ Auth::user()->bonuses }}€
											@endif
										</span>
									</div>
								</div>
							</div>
							<div class="stock-container">
								<div class="stock-block__title">
									@lang('account_new.stocks.title')
								</div>
								<div class="stock-container__inner">
									<div class="stock-method__block">
										<div class="sm-item">
											<span class="num">1.</span>
											<div class="sm-img">
												<span class="sm-add">
													<svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
														<circle cx="15" cy="15" r="15" fill="#C80B0B"></circle>
														<path
															d="M14.0455 16.5114V16.3182C14.0492 15.6553 14.108 15.1269 14.2216 14.733C14.339 14.339 14.5095 14.0208 14.733 13.7784C14.9564 13.536 15.2254 13.3163 15.5398 13.1193C15.7746 12.9678 15.9848 12.8106 16.1705 12.6477C16.3561 12.4848 16.5038 12.3049 16.6136 12.108C16.7235 11.9072 16.7784 11.6837 16.7784 11.4375C16.7784 11.1761 16.7159 10.947 16.5909 10.75C16.4659 10.553 16.2973 10.4015 16.0852 10.2955C15.8769 10.1894 15.6458 10.1364 15.392 10.1364C15.1458 10.1364 14.9129 10.1913 14.6932 10.3011C14.4735 10.4072 14.2936 10.5663 14.1534 10.7784C14.0133 10.9867 13.9375 11.2462 13.9261 11.5568H11.608C11.6269 10.7992 11.8087 10.1742 12.1534 9.68182C12.4981 9.18561 12.9545 8.81629 13.5227 8.57386C14.0909 8.32765 14.7178 8.20455 15.4034 8.20455C16.1572 8.20455 16.8239 8.32955 17.4034 8.57955C17.983 8.82576 18.4375 9.18371 18.767 9.65341C19.0966 10.1231 19.2614 10.6894 19.2614 11.3523C19.2614 11.7955 19.1875 12.1894 19.0398 12.5341C18.8958 12.875 18.6932 13.178 18.4318 13.4432C18.1705 13.7045 17.8617 13.9413 17.5057 14.1534C17.2064 14.3314 16.9602 14.517 16.767 14.7102C16.5777 14.9034 16.4356 15.1269 16.3409 15.3807C16.25 15.6345 16.2027 15.947 16.1989 16.3182V16.5114H14.0455ZM15.1705 20.1477C14.7917 20.1477 14.4678 20.0152 14.1989 19.75C13.9337 19.4811 13.803 19.1591 13.8068 18.7841C13.803 18.4129 13.9337 18.0947 14.1989 17.8295C14.4678 17.5644 14.7917 17.4318 15.1705 17.4318C15.5303 17.4318 15.8466 17.5644 16.1193 17.8295C16.392 18.0947 16.5303 18.4129 16.5341 18.7841C16.5303 19.0341 16.464 19.2633 16.3352 19.4716C16.2102 19.6761 16.0455 19.8409 15.8409 19.9659C15.6364 20.0871 15.4129 20.1477 15.1705 20.1477Z"
															fill="white"></path>
													</svg>
												</span>
												<picture>
													<source srcset="{{ asset(config('theme.current') . '/images') }}/stock/sm1.avif" type="image/avif">
													<source srcset="{{ asset(config('theme.current') . '/images') }}/stock/sm1.webp" type="image/webp">
													<source srcset="{{ asset(config('theme.current') . '/images') }}/stock/sm1.jpg" type="image/jpeg">
													<img width="150" height="150" src="{{ asset(config('theme.current') . '/images') }}/stock/sm1.jpg"
														alt="Viar">
												</picture>
											</div>
											<div class="sm-title">
												@lang('stock.text_3_1')
											</div>
											<p class="sm-inf">
												@lang('stock.text_3_2')
											</p>
											<hr>
											<p class="sm-text">
												@lang('stock.text_3_3')
											</p>
											<p class="sm-disc">
												@lang('stock.text_3_4')
											</p>
											<a href="#" class="sm-btn js-popup-msg">
												@lang('stock.text_3_5')
											</a>
										</div>
										<div class="sm-item">
											<span class="num">2.</span>
											<div class="sm-img">
												<span class="sm-add">
													<svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
														<circle cx="15" cy="15" r="15" fill="#C80B0B"></circle>
														<path
															d="M14.0455 16.5114V16.3182C14.0492 15.6553 14.108 15.1269 14.2216 14.733C14.339 14.339 14.5095 14.0208 14.733 13.7784C14.9564 13.536 15.2254 13.3163 15.5398 13.1193C15.7746 12.9678 15.9848 12.8106 16.1705 12.6477C16.3561 12.4848 16.5038 12.3049 16.6136 12.108C16.7235 11.9072 16.7784 11.6837 16.7784 11.4375C16.7784 11.1761 16.7159 10.947 16.5909 10.75C16.4659 10.553 16.2973 10.4015 16.0852 10.2955C15.8769 10.1894 15.6458 10.1364 15.392 10.1364C15.1458 10.1364 14.9129 10.1913 14.6932 10.3011C14.4735 10.4072 14.2936 10.5663 14.1534 10.7784C14.0133 10.9867 13.9375 11.2462 13.9261 11.5568H11.608C11.6269 10.7992 11.8087 10.1742 12.1534 9.68182C12.4981 9.18561 12.9545 8.81629 13.5227 8.57386C14.0909 8.32765 14.7178 8.20455 15.4034 8.20455C16.1572 8.20455 16.8239 8.32955 17.4034 8.57955C17.983 8.82576 18.4375 9.18371 18.767 9.65341C19.0966 10.1231 19.2614 10.6894 19.2614 11.3523C19.2614 11.7955 19.1875 12.1894 19.0398 12.5341C18.8958 12.875 18.6932 13.178 18.4318 13.4432C18.1705 13.7045 17.8617 13.9413 17.5057 14.1534C17.2064 14.3314 16.9602 14.517 16.767 14.7102C16.5777 14.9034 16.4356 15.1269 16.3409 15.3807C16.25 15.6345 16.2027 15.947 16.1989 16.3182V16.5114H14.0455ZM15.1705 20.1477C14.7917 20.1477 14.4678 20.0152 14.1989 19.75C13.9337 19.4811 13.803 19.1591 13.8068 18.7841C13.803 18.4129 13.9337 18.0947 14.1989 17.8295C14.4678 17.5644 14.7917 17.4318 15.1705 17.4318C15.5303 17.4318 15.8466 17.5644 16.1193 17.8295C16.392 18.0947 16.5303 18.4129 16.5341 18.7841C16.5303 19.0341 16.464 19.2633 16.3352 19.4716C16.2102 19.6761 16.0455 19.8409 15.8409 19.9659C15.6364 20.0871 15.4129 20.1477 15.1705 20.1477Z"
															fill="white"></path>
													</svg>
												</span>
												<picture>
													<source srcset="{{ asset(config('theme.current') . '/images') }}/stock/sm2.avif" type="image/avif">
													<source srcset="{{ asset(config('theme.current') . '/images') }}/stock/sm2.webp" type="image/webp">
													<source srcset="{{ asset(config('theme.current') . '/images') }}/stock/sm2.jpg" type="image/jpeg">
													<img width="150" height="150" src="{{ asset(config('theme.current') . '/images') }}/stock/sm2.jpg"
														alt="Viar">
												</picture>
											</div>
											<div class="sm-title">
												@lang('stock.text_4_1')
											</div>
											<p class="sm-inf">
												@lang('stock.text_4_2')
											</p>
											<hr>
											<p class="sm-text">
												@lang('stock.text_4_3')
											</p>
											<p class="sm-disc">
												@lang('stock.text_4_4')
											</p>
											<a href="#" class="sm-btn js-popup-friend">
												@lang('stock.text_4_5')
											</a>
										</div>
										<div class="sm-item">
											<span class="num">3.</span>
											<div class="sm-img">
												<span class="sm-add">
													<svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
														<circle cx="15" cy="15" r="15" fill="#C80B0B"></circle>
														<path
															d="M14.0455 16.5114V16.3182C14.0492 15.6553 14.108 15.1269 14.2216 14.733C14.339 14.339 14.5095 14.0208 14.733 13.7784C14.9564 13.536 15.2254 13.3163 15.5398 13.1193C15.7746 12.9678 15.9848 12.8106 16.1705 12.6477C16.3561 12.4848 16.5038 12.3049 16.6136 12.108C16.7235 11.9072 16.7784 11.6837 16.7784 11.4375C16.7784 11.1761 16.7159 10.947 16.5909 10.75C16.4659 10.553 16.2973 10.4015 16.0852 10.2955C15.8769 10.1894 15.6458 10.1364 15.392 10.1364C15.1458 10.1364 14.9129 10.1913 14.6932 10.3011C14.4735 10.4072 14.2936 10.5663 14.1534 10.7784C14.0133 10.9867 13.9375 11.2462 13.9261 11.5568H11.608C11.6269 10.7992 11.8087 10.1742 12.1534 9.68182C12.4981 9.18561 12.9545 8.81629 13.5227 8.57386C14.0909 8.32765 14.7178 8.20455 15.4034 8.20455C16.1572 8.20455 16.8239 8.32955 17.4034 8.57955C17.983 8.82576 18.4375 9.18371 18.767 9.65341C19.0966 10.1231 19.2614 10.6894 19.2614 11.3523C19.2614 11.7955 19.1875 12.1894 19.0398 12.5341C18.8958 12.875 18.6932 13.178 18.4318 13.4432C18.1705 13.7045 17.8617 13.9413 17.5057 14.1534C17.2064 14.3314 16.9602 14.517 16.767 14.7102C16.5777 14.9034 16.4356 15.1269 16.3409 15.3807C16.25 15.6345 16.2027 15.947 16.1989 16.3182V16.5114H14.0455ZM15.1705 20.1477C14.7917 20.1477 14.4678 20.0152 14.1989 19.75C13.9337 19.4811 13.803 19.1591 13.8068 18.7841C13.803 18.4129 13.9337 18.0947 14.1989 17.8295C14.4678 17.5644 14.7917 17.4318 15.1705 17.4318C15.5303 17.4318 15.8466 17.5644 16.1193 17.8295C16.392 18.0947 16.5303 18.4129 16.5341 18.7841C16.5303 19.0341 16.464 19.2633 16.3352 19.4716C16.2102 19.6761 16.0455 19.8409 15.8409 19.9659C15.6364 20.0871 15.4129 20.1477 15.1705 20.1477Z"
															fill="white"></path>
													</svg>
												</span>
												<span class="sm-fc">
													<svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
														<rect width="40" height="40" rx="20" fill="#2656FF"></rect>
														<path
															d="M23.2676 20.875L23.6504 18.3594H21.2168V16.7188C21.2168 16.0078 21.5449 15.3516 22.6387 15.3516H23.7598V13.1914C23.7598 13.1914 22.748 13 21.791 13C19.7949 13 18.4824 14.2305 18.4824 16.418V18.3594H16.2402V20.875H18.4824V27H21.2168V20.875H23.2676Z"
															fill="white"></path>
													</svg>
												</span>
												<picture>
													<source srcset="{{ asset(config('theme.current') . '/images') }}/stock/sm3.avif" type="image/avif">
													<source srcset="{{ asset(config('theme.current') . '/images') }}/stock/sm3.webp" type="image/webp">
													<source srcset="{{ asset(config('theme.current') . '/images') }}/stock/sm3.jpg" type="image/jpeg">
													<img width="150" height="150" src="{{ asset(config('theme.current') . '/images') }}/stock/sm3.jpg"
														alt="Viar">
												</picture>
											</div>
											<div class="sm-title">
												@lang('stock.text_5_1')
											</div>
											<p class="sm-inf">
												@lang('stock.text_5_2')
											</p>
											<hr>
											<p class="sm-text">
												@lang('stock.text_5_3')
											</p>
											<p class="sm-disc">
												@lang('stock.text_5_4')
											</p>
											<a href="#" class="sm-btn js-popup-bonus">
												@lang('stock.text_5_5')
											</a>
										</div>
									</div>


									<div class="sb-block">
										<div class="sb-item">
											<div class="sb-left sb-side">
												<div class="sb-title b-title">
													@lang('stock.text_6_2')
												</div>
												<div class="sb-subtitle">
													@lang('stock.text_6_3')
												</div>
											</div>
											<div class="sb-center">
												<div class="sb-center__inner">
													<div class="sb1-img">
														<p>
															@lang('stock.text_6_4')
														</p>
														<div class="sb1-image">
															<picture>
																<source srcset="{{ asset(env('THEME') . 'images') }}/stock/b1.avif" type="image/avif">
																<source srcset="{{ asset(env('THEME') . 'images') }}/stock/b1.webp" type="image/webp">
																<source srcset="{{ asset(env('THEME') . 'images') }}/stock/b1.jpg" type="image/jpeg">
																<img width="144" height="186" src="{{ asset(env('THEME') . 'images') }}/stock/b1.jpg"
																	alt="Viar">
															</picture>
															<img width="120" height="150" src="{{ asset(env('THEME') . 'images') }}/stock/v1.svg"
																alt="Viar">
														</div>
													</div>
													<div class="sb2-img">
														<p>
															@lang('stock.text_6_5')
														</p>
														<div class="sb2-image">
															<picture>
																<source srcset="{{ asset(env('THEME') . 'images') }}/stock/b2.avif" type="image/avif">
																<source srcset="{{ asset(env('THEME') . 'images') }}/stock/b2.webp" type="image/webp">
																<source srcset="{{ asset(env('THEME') . 'images') }}/stock/b2.png" type="image/png">
																<img width="195" height="217" src="{{ asset(env('THEME') . 'images') }}/stock/b2.png"
																	alt="Viar">
															</picture>
														</div>
													</div>
												</div>
											</div>
											<div class="sb-right sb-side">
												<ul>
													<li>
														<span>1.</span>
														<p>
															@lang('stock.text_6_6')
														</p>
													</li>
													<li>
														<span>2.</span>
														<p>
															@lang('stock.text_6_7')
														</p>
													</li>
													<li>
														<span>3.</span>
														<p>
															@lang('stock.text_6_8')
														</p>
													</li>
												</ul>



												<a href="#" class="sb-btn js-popup-screen_bonus">
													@lang('stock.text_6_9')
												</a>



											</div>
										</div>
										<div class="sb-item">
											<div class="sb-left sb-side">
												<div class="sb-title w-title">
													@lang('stock.text_7_1')
												</div>
											</div>
											<div class="sb-center">
												<div class="sb-center__inner">
													<div class="sb3-img">
														<div class="sb3-image">
															<picture>
																<source srcset="{{ asset(env('THEME') . 'images') }}/stock/b3.avif" type="image/avif">
																<source srcset="{{ asset(env('THEME') . 'images') }}/stock/b3.webp" type="image/webp">
																<source srcset="{{ asset(env('THEME') . 'images') }}/stock/b3.png" type="image/png">
																<img width="215" height="327" src="{{ asset(env('THEME') . 'images') }}/stock/b3.png"
																	alt="Viar">
															</picture>
															<img width="120" height="150" src="{{ asset(env('THEME') . 'images') }}/stock/v2.svg"
																alt="Viar">
														</div>
														<p>
															@lang('stock.text_7_2')
														</p>
													</div>
													<div class="sb4-img">
														<p>
															@lang('stock.text_7_3')
														</p>
														<div class="sb4-image">
															<picture>
																<source srcset="{{ asset(env('THEME') . 'images') }}/stock/b4.avif" type="image/avif">
																<source srcset="{{ asset(env('THEME') . 'images') }}/stock/b4.webp" type="image/webp">
																<source srcset="{{ asset(env('THEME') . 'images') }}/stock/b4.png" type="image/png">
																<img width="220" height="190" src="{{ asset(env('THEME') . 'images') }}/stock/b4.png"
																	alt="Viar">
															</picture>
														</div>
													</div>
												</div>
											</div>
											<div class="sb-right sb-side">
												<div class="sb-title w-title sb-txt-c">
													@lang('stock.text_7_4')
												</div>
												@auth
													<a href="#" user_id="{{ $user_id }}" coloumn="is_40_60" class="sb-btn js-popup-activation">
														@lang('stock.modal_40_60_button')
													</a>
												@else
													<a href="#" class="sm-btn js-popup-login">
														@lang('stock.modal_40_60_button')
													</a>
												@endauth

											</div>
										</div>
										<div class="sb-item">
											<div class="sb-left sb-side">
												<div class="sb-title w-title">
													@lang('stock.text_8_1')
												</div>
											</div>
											<div class="sb-center">
												<div class="sb-center__inner">
													<div class="sb5-img">
														<div class="sb5-image">
															<picture>
																<source srcset="{{ asset(env('THEME') . 'images') }}/stock/b5.avif" type="image/avif">
																<source srcset="{{ asset(env('THEME') . 'images') }}/stock/b5.webp" type="image/webp">
																<source srcset="{{ asset(env('THEME') . 'images') }}/stock/b5.png" type="image/png">
																<img width="350" height="350" src="{{ asset(env('THEME') . 'images') }}/stock/b5.png"
																	alt="Viar">
															</picture>
														</div>
														<img src="{{ asset(env('THEME') . 'images') }}/stock/v3.svg" alt="Viar">
														<div class="sb-abs">
															<p>
																@lang('stock.text_8_2')
															</p>
														</div>
													</div>
												</div>
											</div>
											<div class="sb-right sb-side">
												<div class="sb-title w-title sb-txt-c">
													@lang('stock.modal_3_1_podarok_button')
												</div>

												@auth
													<a href="#" user_id="{{ $user_id }}" coloumn="is_1free" class="sb-btn js-popup-activation">
														@lang('stock.modal_3_1_podarok_button')
													</a>
												@else
													<a href="#" class="sm-btn js-popup-login">
														@lang('stock.modal_3_1_podarok_button')
													</a>
												@endauth



											</div>
										</div>
									</div>

								</div>
							</div>
						</div>
					</div>
					<div class="cabinet-content__icon">
						<img src="{{ asset(config('theme.current') . '/images') }}/cabinet/gift-card.svg" width="82"
							height="82" alt="Viar Cabinet Peding Orders">
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@include(config('theme.resource') . 'account.special_offers')

@include(config('theme.resource') . 'pages.gallery.zpart_viarcanvas_is')
