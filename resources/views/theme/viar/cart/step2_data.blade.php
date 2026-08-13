<div class="vz-art cart-page-global">
	<div class="section-frame">

		@include(config('theme.resource') . 'cart.bread', ['step' => 2])

		<div class="cart-page-global__wrapper">
			<div class="cart-page-global__content">
				<div class="cart-data-page">
					<div class="cart-data-page__wrapper">
						<div class="cart-data-page__form">
							<div class="cart-data-page__row">
								<label for="" class="cart-data-page__input name">
									<span>@lang('cart_new.step_2_name')</span>
									<input type="text" class="input-grey" required
										@isset($user['first_name']) value="{{ $user['first_name'] }}" @endisset>
								</label>
								<label for="" class="cart-data-page__input surname">
									<span>@lang('cart_new.step_2_surname')</span>
									<input type="text" class="input-grey" required
										@isset($user['last_name']) value="{{ $user['last_name'] }}" @endisset>
								</label>
							</div>
							<label for="" class="cart-data-page__input email">
								<span>@lang('cart_new.step_2_email')</span>
								<input type="email" class="input-grey" required
									@isset($user['email']) value="{{ $user['email'] }}" @endisset>
							</label>

							@if (!$user)
								<div class="cart-inline-login js-cart-inline-login" hidden>
									<p class="cart-inline-login__message">
										@lang('cart_new.existing_user_login_message')
										<strong class="js-cart-inline-login-email"></strong>
									</p>
									<form class="js-cart-inline-login-form"
										data-login-url="{{ route('custom_login_ajax') }}"
										action="{{ route('custom_login_ajax') }}" method="POST">
										@csrf
										<label class="cart-data-page__input cart-inline-login__password">
											<span>@lang('cart_new.existing_user_password')</span>
											<input type="password" name="password" class="input-grey"
												autocomplete="current-password" required>
										</label>
										<div class="cart-inline-login__password-tools">
											<button type="button" class="js-cart-inline-reset-password cart-inline-login__reset"
												data-reset-url="{{ route('forget_email_reset') }}">
												@lang('cart_new.existing_user_reset_password')
											</button>
										</div>
										<div class="cart-inline-login__error js-cart-inline-login-error" role="alert"></div>
										<div class="cart-inline-login__actions">
											<button type="submit" class="btn--orange cart-inline-login__submit">
												@lang('cart_new.existing_user_login')
											</button>
											<a class="cart-inline-login__google"
												href="{{ route('google_redirect', ['return' => url()->full()]) }}">
												<svg class="cart-inline-login__google-icon" viewBox="0 0 48 48"
													aria-hidden="true" focusable="false">
													<path fill="#EA4335" d="M24 9.5c3.54 0 6.72 1.22 9.24 3.23l6.9-6.9C36.06 2.39 30.39 0 24 0 14.61 0 6.5 5.38 2.56 13.22l8.04 6.24C12.33 13.53 17.73 9.5 24 9.5z"/>
													<path fill="#4285F4" d="M46.5 24c0-1.6-.14-3.14-.4-4.64H24v9.02h12.65c-.55 2.9-2.2 5.36-4.68 7.02l7.19 5.58C43.73 36.74 46.5 30.93 46.5 24z"/>
													<path fill="#FBBC05" d="M10.6 28.46c-.5-1.49-.78-3.08-.78-4.74s.28-3.25.78-4.74l-8.04-6.24C.92 16.2 0 19.98 0 23.72s.92 7.52 2.56 10.98l8.04-6.24z"/>
													<path fill="#34A853" d="M24 48c6.39 0 11.76-2.11 15.68-5.72l-7.19-5.58c-2 1.34-4.56 2.13-8.49 2.13-6.27 0-11.67-4.03-13.4-9.96l-8.04 6.24C6.5 42.62 14.61 48 24 48z"/>
												</svg>
												@lang('cart_new.existing_user_google')
											</a>
										</div>
									</form>
								</div>
							@endif

							{{-- <div class="cart-data-page__input surname">
								<span>@lang("cart_new.step_2_phone_number")</span>
								<input id="phone" class="input-grey" type="text" name="phone" style="width: 100%;" />
							</div> --}}


							@php
								$hasRecipientPhone = (string) session('phone_rec') !== '';
							@endphp
							<div class="kviz-input cart-data-page__input">
								<span>@lang('cart_new.step_2_phone_number')</span>
								<div class="page-input__item phone-input">
									<div class="country-item country-item-active">
										@php $cur_country = ''; @endphp

										@if (isset($user['country']) && $user['country'])
											@php $cur_country = $user['country']; @endphp

											<img src="{{ asset('/images/flag') }}/{{ strtolower($user['country']) }}.svg" data-country='{{ $user['country'] }}'
												alt="img" loading="lazy" />
										@elseif (isset($user['settings']['locale']) && $user['settings']['locale'])
											@php $cur_country = $user['settings']['locale']; @endphp

											<img src="{{ asset('/images/flag') }}/{{ strtolower($user['settings']['locale']) }}.svg"
												data-country='{{ $user['settings']['locale'] }}' alt="img" loading="lazy" />
										@else
											@foreach ($c_tels as $c_tel)
												@if (\Session::has('basket_country'))
													@php
														$sel_ct = session()->get('basket_country');
													@endphp

													@if ($sel_ct == $c_tel['country_code'])
														@php $cur_country = $c_tel['country_code']; @endphp

														<img src="{{ asset('/images/flag') }}/{{ strtolower($c_tel['country_code']) }}.svg"
															data-country='{{ $c_tel['country_code'] }}' alt="img" loading="lazy" />
													@endif
												@else
													@php
														$sel_ct = strtoupper(session()->get('locale'));

														if ($position = \Location::get(request()->ip())) {
                                                            if ($c_tels->contains('country_code', $position->countryCode)) {
                                                                $sel_ct = $position->countryCode;
                                                            } else {
                                                                $sel_ct = 'LV';
                                                            }

															if ($sel_ct == 'FI') {
																$sel_ct = 'FIN';
															}
														}

														if($sel_ct == 'RU' || $sel_ct == 'UA')
														{
															$sel_ct = "LV";
														}

														if($sel_ct == 'EN') { $sel_ct = "US"; }
													@endphp
													@if ($sel_ct == $c_tel['country_code'])
														{{-- @if($c_tel['country_code'] == 'RU' || $c_tel['country_code'] == 'UA') @continue @endif --}}

														@php $cur_country = $c_tel['country_code']; @endphp
														<img src="{{ asset('/images/flag') }}/{{ strtolower($c_tel['country_code']) }}.svg"
															data-country='{{ $c_tel['country_code'] }}' alt="img" loading="lazy" />
													@endif
												@endif
											@endforeach
									@endif

								@php
									$selectedCountryTel = $c_tels->first(function ($tel) use ($cur_country) {
										return strtoupper((string) $tel['country_code']) === strtoupper((string) $cur_country);
									});
									$usedCountryFallback = false;

									// GeoIP, locale and profile data are hints only. A mismatch must not remove
									// the required phone field from the checkout form.
									if (!$selectedCountryTel) {
										$selectedCountryTel = $c_tels->firstWhere('country_code', 'LV') ?: $c_tels->first();
										$cur_country = $selectedCountryTel ? $selectedCountryTel['country_code'] : '';
										$usedCountryFallback = true;
									}
								@endphp
								@if ($usedCountryFallback && $selectedCountryTel)
									<img src="{{ asset('/images/flag') }}/{{ strtolower($selectedCountryTel['country_code']) }}.svg"
										data-country="{{ $selectedCountryTel['country_code'] }}" alt="img" loading="lazy" />
								@endif
								</div>
								<div class="country-list">
										@foreach ($c_tels as $tel)
											@if($tel['country_code'] == 'RU' || $tel['country_code'] == 'UA') @continue @endif

											<div class="country-item">
												<img src="{{ asset('images/flag') }}/{{ strtolower($tel['country_code']) }}.svg" alt="img"
													loading="lazy" />
												<p>{{ $tel['country_name'] }}</p>
												<span data-mask="{{ $tel['mask'] }}"
													data-placeholder="{{ $tel['placeholder'] }}"
													data-country="{{ $tel['country_code'] }}">{{ $tel['phone_code'] }}</span>
											</div>
										@endforeach
									</div>


									@if ($selectedCountryTel)
										<input type="text" name="phone" class="input-mask input-counter js_get_country"
											style="border: 1.5px solid #c4c4c4;"
											autocomplete="off"
											data-mask="{{ $selectedCountryTel['mask'] }}"
											placeholder="{{ $selectedCountryTel['placeholder'] }}"
											data-country="{{ $selectedCountryTel['country_code'] }}"
											required=""
											@isset($user['phone']) value="{{ $user['phone'] }}" @endisset />
									@else
										<input type="tel" name="phone" class="input-mask input-counter js_get_country"
											style="border: 1.5px solid #c4c4c4;" autocomplete="tel" required />
									@endif

									<svg class="kviz-input__icon">
										<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#phone') }}"></use>
									</svg>
								</div>
							</div>


							<div class="ur_checkers pt-5 recipient-toggle">
								<div class="cart-checkbox" style="float: left;">
									<input type="checkbox" class="js_recipient_toggle" @if($hasRecipientPhone) checked @endif>
									<svg class="vz-art kviz-input__icon">
										<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#log') }}"></use>
									</svg>
								</div>
								<div style="float: left;">@lang('cart_new.step_2_recipient_phone')</div>
								<br>
							</div>

							<div class="kviz-input cart-data-page__input recipient-phone js_recipient_phone_block" @if(!$hasRecipientPhone) style="display:none;" @endif>
								<div class="page-input__item phone-input">
									@if ($selectedCountryTel)
											<input type="text" name="phone_rec"
												class="input-mask js_recipient_phone"
												style="border: 1.5px solid #c4c4c4;"
												autocomplete="off"
												data-mask="{{ $selectedCountryTel['mask'] }}"
												placeholder="{{ $selectedCountryTel['placeholder'] }}"
												data-country="{{ $selectedCountryTel['country_code'] }}"
												value="{{ session('phone_rec') }}">
									@else
										<input type="tel" name="phone_rec" class="input-mask js_recipient_phone"
											style="border: 1.5px solid #c4c4c4;" autocomplete="tel"
											value="{{ session('phone_rec') }}">
									@endif

									<svg class="kviz-input__icon">
										<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#phone') }}"></use>
									</svg>
								</div>
							</div>


							<div class="ur_checkers pt-5">
								<div class="cart-checkbox" style="float: left;">
									<input type="checkbox" name="ur_name" class="js_ur" @if(\Session::has('ur_name')) checked @endif>
									<svg class="vz-art kviz-input__icon">
										<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#log') }}"></use>
									</svg>
								</div>

								<div style="float: left;">{{ $data['ur_lico'] }}</div>
								<br>
								<div class="ur_info" @if(\Session::has('ur_name')) style="display:block;" @else style="display:none;" @endif>
									<label for="ur_name_l" class="cart-data-page__input pt-5">
										<span>{{ $data['ur_name_l'] }}</span>
										<input type="text" name="ur_name_l" class="input-grey" placeholder="{{ $data['ur_name_l'] }}" @if(\Session::has('ur_name_l') && \Session::has('ur_name')) value="{{ session()->get('ur_name_l') }}" @endif>
									</label>

									<label for="ur_reg_num" class="cart-data-page__input pt-5">
										<span>{{ $data['ur_reg_num'] }}</span>
										<input type="text" name="ur_reg_num" class="input-grey" placeholder="{{ $data['ur_reg_num'] }}" @if(\Session::has('ur_reg_num') && \Session::has('ur_name')) value="{{ session()->get('ur_reg_num') }}" @endif>
									</label>

									<label for="ur_legal_addr" class="cart-data-page__input pt-5">
										<span>{{ $data['ur_addr'] }}</span>
										<input type="text" name="ur_legal_addr" class="input-grey" placeholder="{{ $data['ur_addr'] }}" @if(\Session::has('ur_legal_addr') && \Session::has('ur_name')) value="{{ session()->get('ur_legal_addr') }}" @endif>
									</label>

									<label for="ur_pnr_nr" class="cart-data-page__input pt-5">
										<span>{{ $data['ur_pnr_nr'] }}</span>
										<input type="text" name="ur_pnr_nr" class="input-grey" placeholder="{{ $data['ur_pnr_nr'] }}" @if(\Session::has('ur_pnr_nr') && \Session::has('ur_name')) value="{{ session()->get('ur_pnr_nr') }}" @endif>
									</label>

									<label for="ur_bank_name" class="cart-data-page__input pt-5">
										<span>{{ $data['ur_bank_name'] }}</span>
										<input type="text" name="ur_bank_name" class="input-grey" placeholder="{{ $data['ur_bank_name'] }}" @if(\Session::has('ur_bank_name') && \Session::has('ur_name')) value="{{ session()->get('ur_bank_name') }}" @endif>
									</label>

									<label for="ur_bank_code" class="cart-data-page__input pt-5">
										<span>{{ $data['ur_bank_code'] }}</span>
										<input type="text" name="ur_bank_code" class="input-grey" placeholder="{{ $data['ur_bank_code'] }}" @if(\Session::has('ur_bank_code') && \Session::has('ur_name')) value="{{ session()->get('ur_bank_code') }}" @endif>
									</label>

									<label for="ur_bank_acc_code" class="cart-data-page__input pt-5">
										<span>{{ $data['ur_bank_acc_code'] }}</span>
										<input type="text" name="ur_bank_acc_code" class="input-grey" placeholder="{{ $data['ur_bank_acc_code'] }}" @if(\Session::has('ur_bank_acc_code') && \Session::has('ur_name')) value="{{ session()->get('ur_bank_acc_code') }}" @endif>
									</label>
								</div>
							</div>

						</div>
						<div class="cart-data-page__bottom">
							<a href="{{ route('cart.index') }}" rel="nofollow" class="cart-data-page__btn-back">
								@lang('cart_new.general_return')
							</a>
						</div>
					</div>
				</div>
			</div>

			@include(config('theme.resource') . 'cart.sidebar', [
			    'basket' => $basket,
			    'btn' => trans('cart_new.step_2_go_to_delivery'),
			    'btn_class' => 'cart_send_userdata',
			    'action' => route('cart.step3'),
			    'step' => 2,
			])

			<div class="cart-data-page__bottom tablet">
				<a href="{{ route('cart.index') }}" rel="nofollow" class="cart-data-page__btn-back">
					@lang('cart_new.general_return')
				</a>
			</div>
		</div>
	</div>
</div>

<style>
.cart-inline-login {
	margin: 16px 0 22px;
	padding: 24px;
	border: 1px solid #f28b62;
	border-radius: 12px;
	background: #fff8f4;
	scroll-margin-top: 96px;
}
.cart-inline-login[hidden] { display: none !important; }
.cart-inline-login__message { margin: 0 0 20px; color: #333; line-height: 1.45; }
.cart-inline-login__message strong { overflow-wrap: anywhere; }
.cart-inline-login__password { margin-bottom: 0; }
.cart-inline-login__error { margin: 14px 0 0; color: #c62828; font-size: 14px; line-height: 1.4; }
.cart-inline-login__error:empty { display: none; }
.cart-inline-login__error.is-success { color: #27863a; }
.cart-inline-login__password-tools { display: flex; justify-content: flex-end; margin: 10px 0 0; }
.cart-inline-login__reset {
	padding: 0;
	border: 0;
	border-bottom: 1px solid currentColor;
	background: transparent;
	color: #fa7846;
	cursor: pointer;
}
.cart-inline-login__reset[disabled] { opacity: .65; cursor: wait; }
.cart-inline-login__actions { display: flex; flex-wrap: wrap; gap: 18px 12px; align-items: center; margin-top: 18px; }
.cart-inline-login__submit,
.cart-inline-login__google {
	display: inline-flex;
	min-height: 48px;
	align-items: center;
	justify-content: center;
	padding: 10px 18px;
	border-radius: 8px;
	font-weight: 600;
	text-align: center;
}
.cart-inline-login__submit { border: 0; cursor: pointer; }
.cart-inline-login__submit[disabled] { opacity: .65; cursor: wait; }
.cart-inline-login__google { gap: 8px; border: 1px solid #c4c4c4; background: #fff; color: #333; }
.cart-inline-login__google-icon { width: 24px; height: 24px; flex: 0 0 24px; }
@media (max-width: 575px) {
	.cart-inline-login { padding: 18px 14px; scroll-margin-top: 72px; }
	.cart-inline-login__message { margin-bottom: 18px; }
	.cart-inline-login__actions { gap: 14px; margin-top: 16px; }
	.cart-inline-login__actions > * { width: 100%; }
}
</style>

{{-- Modal windows for cart step 2 --}}
@include(config('theme.resource') . 'cart.modals.alternative_size_modal')
@include(config('theme.resource') . 'cart.modals.recommendation_modal')

<script>
document.addEventListener("DOMContentLoaded", function() {
    const urCheckbox = document.querySelector(".js_ur");
    const urInfo = document.querySelector(".ur_info");
    const recipientToggle = document.querySelector(".js_recipient_toggle");
    const recipientBlock = document.querySelector(".js_recipient_phone_block");
    const recipientInput = document.querySelector(".js_recipient_phone");
	const emailInput = document.querySelector(".cart-data-page__input.email input");
	const inlineLogin = document.querySelector(".js-cart-inline-login");
	const inlineLoginForm = document.querySelector(".js-cart-inline-login-form");
	const inlineLoginError = document.querySelector(".js-cart-inline-login-error");
	const resetPasswordButton = document.querySelector(".js-cart-inline-reset-password");

    if (urCheckbox) {
        urCheckbox.addEventListener("change", function() {
            urInfo.style.display = urCheckbox.checked ? "block" : "none";
        });
    }

    if (recipientToggle && recipientBlock) {
        recipientToggle.addEventListener("change", function() {
            const isActive = recipientToggle.checked;
            recipientBlock.style.display = isActive ? "block" : "none";
            if (!isActive && recipientInput) {
                recipientInput.value = "";
            }
        });
    }

	if (emailInput && inlineLogin) {
		emailInput.addEventListener("input", function() {
			inlineLogin.hidden = true;
			if (inlineLoginError) {
				inlineLoginError.textContent = "";
				inlineLoginError.classList.remove("is-success");
			}
			const passwordInput = inlineLogin.querySelector('input[name="password"]');
			if (passwordInput) {
				passwordInput.value = "";
				passwordInput.classList.remove("error");
				const passwordWrapper = passwordInput.closest(".cart-data-page__input");
				if (passwordWrapper) passwordWrapper.classList.remove("error");
			}
		});
	}

	if (inlineLoginForm && emailInput) {
		inlineLoginForm.addEventListener("submit", function(event) {
			event.preventDefault();

			const passwordInput = inlineLoginForm.querySelector('input[name="password"]');
			const submitButton = inlineLoginForm.querySelector('button[type="submit"]');
			const email = String(emailInput.value || "").trim();
			const password = passwordInput ? passwordInput.value : "";

			if (!email || !password) return;
			if (inlineLoginError) {
				inlineLoginError.textContent = "";
				inlineLoginError.classList.remove("is-success");
			}
			if (submitButton) submitButton.disabled = true;

			fetch(inlineLoginForm.dataset.loginUrl, {
				method: "POST",
				credentials: "same-origin",
				headers: {
					"Accept": "application/json",
					"Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
					"X-CSRF-TOKEN": inlineLoginForm.querySelector('input[name="_token"]').value,
					"X-Requested-With": "XMLHttpRequest"
				},
				body: new URLSearchParams({ email: email, password: password }).toString()
			})
				.then(function(response) { return response.json(); })
				.then(function(data) {
					if (data.status === true) {
						window.location.reload();
						return;
					}
					if (inlineLoginError) {
						inlineLoginError.textContent = data.errors || @json(trans('cart_new.existing_user_login_error'));
					}
				})
				.catch(function() {
					if (inlineLoginError) inlineLoginError.textContent = @json(trans('cart_new.existing_user_login_error'));
				})
				.finally(function() {
					if (submitButton) submitButton.disabled = false;
				});
		});
	}

	if (resetPasswordButton && emailInput && inlineLoginForm) {
		resetPasswordButton.addEventListener("click", function() {
			const email = String(emailInput.value || "").trim();
			if (!email) return;

			resetPasswordButton.disabled = true;
			if (inlineLoginError) {
				inlineLoginError.textContent = "";
				inlineLoginError.classList.remove("is-success");
			}

			fetch(resetPasswordButton.dataset.resetUrl, {
				method: "POST",
				credentials: "same-origin",
				headers: {
					"Accept": "application/json",
					"Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
					"X-CSRF-TOKEN": inlineLoginForm.querySelector('input[name="_token"]').value,
					"X-Requested-With": "XMLHttpRequest"
				},
				body: new URLSearchParams({ email: email, locale: @json(app()->getLocale()) }).toString()
			})
				.then(function(response) { return response.json(); })
				.then(function(data) {
					if (inlineLoginError) {
						inlineLoginError.textContent = data.msg || @json(trans('cart_new.existing_user_reset_error'));
						inlineLoginError.classList.toggle("is-success", String(data.error) === "false");
					}
				})
				.catch(function() {
					if (inlineLoginError) inlineLoginError.textContent = @json(trans('cart_new.existing_user_reset_error'));
				})
				.finally(function() {
					resetPasswordButton.disabled = false;
				});
		});
	}
});
</script>
