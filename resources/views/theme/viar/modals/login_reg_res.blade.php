{{-- login --}}
<form data-redirect="{{ url(Request::url()) }}" action="{{ route('custom_login_ajax') }}" method="POST"
	class="vz-art js-popup target-box popup-login js_login_form" onsubmit="return false;">
	@csrf
	<i class="vz-art fa-close popup-close"></i>
	<div class="vz-art page-title popup-photo-title h2_old">{!! trans('homepage_new_login_reg.enter') !!}</div>
	<div class="vz-art popup-log-group">
		<div class="vz-art kviz-input">
			<p class="vz-art kviz-input__title">{!! trans('homepage_new_login_reg.enter_email') !!}</p>
			<div class="vz-art page-input__item">
				<input type="email" name="email" placeholder="E-mail" required="">
				<svg class="vz-art kviz-input__icon">
					<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#mail') }}"></use>
				</svg>
			</div>
			<div class="vz-art error" data-name="email"></div>
		</div>
		<div class="vz-art kviz-input">
			<p class="vz-art kviz-input__title">{!! trans('homepage_new_login_reg.enter_password') !!}</p>
			<div class="vz-art page-input__item">
				<input type="password" name="password" required="">
				<svg class="vz-art kviz-input__icon" style="width: 24px; height: 24px;">
					<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#lock-popup') }}"></use>
				</svg>
			</div>
			<div class="vz-art error" data-name="password"></div>
		</div>
	</div>
	<div class="vz-art popup-log-save">
		<div class="vz-art popup-log-check log-check_active js-checkbox">
			<span class="vz-art log-check">
				<svg class="vz-art kviz-input__icon">
					<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#log') }}"></use>
				</svg>
			</span>
			<p>{!! trans('homepage_new_login_reg.remember_me') !!}</p>
			<input type="checkbox" checked="checked" name="save">
		</div>
		<a class="js_forget_btn" href="#">{!! trans('homepage_new_login_reg.forget_password') !!}</a>
	</div>
	<div class="vz-art js_spinner"></div>
	<label class="vz-art log-sub">
		<input type="submit">{!! trans('homepage_new_login_reg.enter_btn') !!}
	</label>
	<div class="vz-art log-creat">
		<p>{!! trans('homepage_new_login_reg.still_no_reg') !!}</p>
		<a href="#" class="vz-art js-popup-registration ">{!! trans('homepage_new_login_reg.create_acc') !!}</a>
	</div>
		<div class="vz-art log-social">
			<h3>
				<span>{!! trans('homepage_new_login_reg.or') !!}</span>
			</h3>
			<p>{!! trans('homepage_new_login_reg.enter_with') !!}</p>
			<ul>
				@if(config('services.facebook.enable'))
				<li>
					<a href="{{ route('facebook_redirect', ['return' => url()->full()]) }}">
						<i class="fa-facebook"></i>
					</a>
				</li>
				@endif
				<li>
					<a href="{{ route('google_redirect', ['return' => url()->full()]) }}">
						<i class="fa-google"></i>
					</a>
				</li>
			</ul>
		</div>
</form>
{{-- register --}}
<form action="{{ route('custom_register_ajax') }}" method="POST" class="vz-art js-popup target-box popup-registration"
	id="reg_form"
      @php
          $cartRoutes = [
              'cart.index',
              'cart.step2',
              'cart.step3',
              'cart.step4',
              'delimage',
              'updateimg',
              'setmaking',
              'setcoupon',
              'setdelivery',
              'setuser',
              'setpay',
              'clearcart'
          ];
      @endphp

      @if (in_array(Route::currentRouteName(), $cartRoutes))
      data-redirect="{{ route('review') }}"
      @else
      data-redirect="{{ url(config('auth.auth_redirect_to')) }}"
      @endif

      >
	@csrf
    {{-- <input type="hidden" name="g-recaptcha-response" class="g-recaptcha-response" value="{{ env('RECAPTCHA_SITE_KEY') }}"> --}}
	<i class="vz-art fa-close popup-close"></i>
	<div class="vz-art page-title popup-photo-title h2_old">{!! trans('homepage_new_login_reg.create_acc') !!}</div>
	<div class="vz-art registration-group">
		<div class="vz-art kviz-input">
			<p class="vz-art kviz-input__title">{!! trans('homepage_new_login_reg.create_acc_enter_your_name') !!}<span>*</span>
			</p>
			<div class="page-input__item vz-artpage-input__item">
				<input type="text" name="name" placeholder="" required="">
				<svg class="vz-art kviz-input__icon" style="width: 24px; height: 24px;">
					<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#user-popup') }}"></use>
				</svg>
			</div>
			<span class="err_msg err_name"></span>
		</div>
		<div class="vz-art kviz-input">
			<p class="vz-art kviz-input__title">{!! trans('homepage_new_login_reg.create_acc_enter_your_fio') !!}</p>
			<div class="vz-art page-input__item">
				<input type="text" name="surname" placeholder="">
				<svg class="vz-artkviz-input__icon" style="width: 24px; height: 24px;">
					<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#user-popup') }}"></use>
				</svg>
			</div>
			<span class="err_msg err_surname"></span>
		</div>
		<div class="vz-art kviz-input">
			<p class="vz-art kviz-input__title">{!! trans('homepage_new_login_reg.create_acc_enter_your_email') !!}<span>*</span>
			</p>
			<div class="vz-art page-input__item">
				<input type="email" name="email" placeholder="" required="">
				<svg class="vz-art kviz-input__icon">
					<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#mail') }}"></use>
				</svg>
			</div>
			<span class="err_msg err_email"></span>
		</div>
		<div class="vz-art kviz-input">
			<p class="vz-art kviz-input__title">{!! trans('homepage_new_login_reg.create_acc_enter_your_phone') !!}<span>*</span>
			</p>
			<div class="vz-art page-input__item phone-input" style="width:100%;">
				<input type="text" id="phone3" name="phone" required="" style="width:100%;">
				<svg class="vz-art kviz-input__icon">
					<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#phone') }}"></use>
				</svg>
			</div>
			<span class="err_msg err_phone"></span>
		</div>
		<div class="vz-art kviz-input">
			<p class="vz-art kviz-input__title">{!! trans('homepage_new_login_reg.create_acc_enter_pass') !!}<span>*</span>
			</p>
			<div class="vz-art page-input__item">
				<input type="password" name="password" required="">
				<svg class="vz-art kviz-input__icon" style="width: 24px; height: 24px;">
					<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#lock-popup') }}"></use>
				</svg>
			</div>
			<span class="err_msg err_pass"></span>
		</div>
		<div class="vz-art kviz-input">
			<p class="vz-art kviz-input__title">{!! trans('homepage_new_login_reg.create_acc_repeat_pass') !!}<span>*</span>
			</p>
			<div class="page-input__item vz-artpage-input__item">
				<input type="password" name="password_confirmation" required="">
				<svg class="vz-art kviz-input__icon" style="width: 24px; height: 24px;">
					<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#lock-popup"') }}"></use>
				</svg>
			</div>
		</div>
		<label class="vz-art registration-sub">
			<input type="submit">{!! trans('homepage_new_login_reg.create_acc_reg_btn') !!}
		</label>
       <div class="vz-art registration-recaptcha">
           <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>
       </div>
	</div>
    <div class="vz-art kviz-politics registration-politics">
		<svg>
			<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#lock') }}"></use>
		</svg>
		<p>{!! storefront_html(trans('homepage_new_login_reg.create_acc_policy_text')) !!}</p>
	</div>
		<div class="vz-art log-social">
			<h3>
				<span>{!! trans('homepage_new_login_reg.create_acc_or_text') !!}</span>
			</h3>
			<p>{!! trans('homepage_new_login_reg.create_acc_reg_with') !!}</p>
			<ul>
                @if(config('services.facebook.enable'))
				<li>
					<a href="{{ route('facebook_redirect') }}">
						<i class="fa-facebook"></i>
					</a>
				</li>
                @endif
                <li>
					<a href="{{ route('google_redirect') }}">
						<i class="fa-google"></i>
					</a>
				</li>
			</ul>
		</div>
</form>
{{-- forgert pass --}}
<form action="{{ route('forget_email_reset') }}" method="POST"
	class="vz-art js-popup target-box popup-forget js_forget_pass_new"
	data-msg="{{ trans('homepage_new_login_reg.restore_pass_msg') }}">
	@csrf
	<input type="hidden" name="locale" value="{{ app()->getLocale() }}">
	<i class="vz-art fa-close popup-close"></i>
	<div class="vz-art page-title popup-photo-title h2_old">{!! trans('homepage_new_login_reg.forget_password') !!}</div>
	<div class="vz-artregistration-group--block">
		<div class="vz-artkviz-input">
			<p class="vz-artkviz-input__title pass__text">{!! trans('homepage_new_login_reg.create_acc_enter_your_email') !!}<span>*</span>
			</p>
			<div class="vz-artpage-input__item">
				<input class="vz-art js__mail_forget page-input__item" type="email" name="email" placeholder="" required="">
				{{-- <svg class="vz-art kviz-input__icon">
                <use xlink:href="{{ asset(env('THEME').'sprite.svg#mail') }}"></use>
            </svg> --}}
			</div>
		</div>
		<label class="vz-art registration-sub">
			<input type="submit">{!! trans('homepage_new_login_reg.restore_text') !!}
		</label>
	</div>
</form>

{{-- portrait modals --}}

<form action="{{ route('cart.set-email') }}" method="POST"
      class="vz-art js-popup target-box popup-repair-basket">
    @csrf
    <i class="vz-art fa-close popup-close"></i>
    <div class="vz-art page-title popup-photo-title h2_old">
        @lang('popup.please_enter_email')
    </div>
    <div class="vz-art popup-log-group">
        <div class="vz-art kviz-input">
            <p class="vz-art kviz-input__title">{!! trans('homepage_new_login_reg.enter_email') !!}</p>
            <div class="vz-art page-input__item">
                <input type="email" name="email" placeholder="E-mail" required="">
                <svg class="vz-art kviz-input__icon">
                    <use xlink:href="{{ asset(env('THEME') . 'sprite.svg#mail') }}"></use>
                </svg>
            </div>
            <div class="vz-art error" data-name="email"></div>
        </div>
    </div>
    <div class="vz-art js_spinner"></div>
    <label class="vz-art log-sub">
        <input type="submit">@lang('account_new.btn.accept')
    </label>
</form>
