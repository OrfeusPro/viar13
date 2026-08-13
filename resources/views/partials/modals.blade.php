<div class="vz-art popup-frame target-frame">
    @include(env('THEME_RESOURCES') . 'modals.login_reg_res')
</div>


{{-- Товар добавлен в корзину. --}}
<div class="popup-bask-add popup p__mod">
	<span class="close-popup"></span>
	<div class="popup-content">
		<span class="close-content"><i class="icon-icon4"></i></span>
		<div class="p__mod__title green__text">{{ trans('gl.suc') }}
			<span>
				<img src="{{ asset('img/checkmark_circle.1.png') }}" alt="">
			</span>
		</div>
	</div>
</div>


<div class="popup-rev-ok popup p__mod">
	<span class="close-popup"></span>
	<div class="popup-content">
		<span class="close-content"><i class="icon-icon4"></i></span>
		<div class="p__mod__title green__text">Ваш отзыв будет добавлен
			<span>
				<img src="{{ asset('img/checkmark_circle.1.png') }}" alt="">
			</span>
		</div>
	</div>
</div>

{{-- Неправильный размер. --}}
<div class="popup-inv-size popup p__mod">
    <span class="close-popup"></span>
    <div class="popup-content">
			<span class="close-content" style="display: flex; flex-direction: row-reverse; cursor: pointer ">
            <img src="{{ asset(env('THEME').'img/cross1.png') }}" alt="" style="width:15px; height: 15px">
            </span>
        <div class="p__mod__title red__text h3_old" id="err_msgs">
            <span style="display:block;">{{ trans('gl.inv_filesize_or_ext') }}</span>
        </div>
    </div>
</div>

{{-- Неправильный размер. orig --}}
<div class="popup-inv-size-orig popup p__mod">
	<span class="close-popup"></span>
	<div class="popup-content">
		<span class="close-content"><i class="icon-icon4"></i></span>
		<div class="p__mod__title red__text h3_old" id="err_msgs">
			<span style="display:block;">{{ trans('gl.inv_size') }}</span>
			<span>
				<img src="{{ asset('img/cross1.png') }}" alt="">
			</span>
		</div>
	</div>
</div>

{{-- Действие подтверждено. --}}
<div class="popup-act-act popup p__mod">
	<span class="close-popup"></span>
	<div class="popup-content">
		<span class="close-content"><i class="icon-icon4"></i></span>
		<div class="p__mod__title green__text"><span></span>
			<span>
				<img src="{{ asset('img/checkmark_circle.1.png') }}" alt="">
			</span>
		</div>
	</div>
</div>

{{-- printscreenm. --}}
<div class="popup-print popup p__mod">
	<span class="close-popup"></span>
	<div class="popup-content">
		<span class="close-content"><i class="icon-icon4"></i></span>
		<div class="p__mod__title green__text"><span class="js_mod_text">Действие подтверждено.</span>
			<span>
				<img src="{{ asset('img/checkmark_circle.1.png') }}" alt="">
			</span>
		</div>
	</div>
</div>

{{-- Неправильное фото --}}
<div class="popup-inv-foto popup p__mod">
	<span class="close-popup"></span>
	<div class="popup-content">
		<span class="close-content"><i class="icon-icon4"></i></span>
		<div class="p__mod__title red__text h3_old">{{ trans('gl.media_missing') }}
			<span>
				<img src="{{ asset('img/cross1.png') }}" alt="">
			</span>
		</div>
	</div>
</div>

{{-- Скидка. --}}
<div class="popup-act-conf popup p__mod">
	<span class="close-popup"></span>
	<div class="popup-content">
		<span class="close-content"><i class="icon-icon4"></i></span>
		<div class="p__mod__title green__text">Ваша скидка подтверждена.
			<span>
				<img src="{{ asset('img/checkmark_circle.1.png') }}" alt="">
			</span>
		</div>
	</div>
</div>

{{-- inv size --}}
<div class="popup-inv-size__sel popup p__mod">
	<span class="close-popup"></span>
	<div class="popup-content">
		<span class="close-content"><i class="icon-icon4"></i></span>
		<div class="p__mod__title red__text h3_old">Нужно выбрать размер
			<span>
				<img src="{{ asset('img/cross1.png') }}" alt="">
			</span>
		</div>
	</div>
</div>

<div class="popup-registration popup">
	<span class="close-popup"></span>
	<div class="popup-content">
		<span class="close-content"><i class="icon-icon4"></i></span>
		<div class="h3_old">{{ __('header.reg') }}</div>
		<form method="POST" action="{{ route('register') }}" id="reg_form"
			data-redirect="{{ url(config('auth.auth_redirect_to')) }}">
			@csrf
			<div class="input">
				<label>{{ __('header.email_reg') }}</label>
				<input id="email_reg" type="email" class="form-control @error('email') is-invalid @enderror" name="email"
					value="{{ old('email') }}" required autocomplete="email">

				<div class="error" data-name="email"></div>
			</div>
			<div class="input">
				<label>{{ __('Password') }}</label>
				<input id="password_reg" type="password" class="form-control @error('password') is-invalid @enderror"
					name="password" required autocomplete="new-password">

				<div class="error" data-name="password"></div>
			</div>
			<div class="input">
				<label>{{ __('Confirm Password') }}</label>
				<input id="password-confirm" type="password" class="form-control" name="password_confirmation" required
					autocomplete="new-password">


			</div>
			<div class="invited">
				<p class="invited_js">{{ __('header.invited') }}</p>
				<div class="input">
					<label>{{ __('Invited') }}</label>
					<input id="name" type="text" class="form-control @error('invited') is-invalid @enderror" name="invited"
						value="{{ old('invited') }}" autocomplete="off">

					<div class="error" data-name="invited"></div>
				</div>
			</div>
			<div class="js_spinner"></div>
			<button type="submit" class="btn btn-primary">
				<span>
					{{ __('Register') }}
				</span>
			</button>
		</form>
	</div>
</div>

<div class="popup-login popup">
	<span class="close-popup"></span>
	<div class="popup-content">
		<span class="close-content"><i class="icon-icon4"></i></span>
		<div class="h3_old">{{ __('header.popup_login_title') }}</div>
		@if (\Session::has('error'))
			<div class="alert alert-error">
				<h1 style="text-align:center;max-width: 320px; margin: 0 auto; font-size: 16px;  padding: 5px;">
					{!! \Session::get('error') !!}</h1>
			</div>
		@endif
		<form class="js_login_form" method="POST" action="{{ route('custom_login_ajax') }}">
			@csrf
			<div class="input">
				<label>{{ __('E-Mail Address') }}</label>
				<input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email"
					value="{{ old('email') }}" required autocomplete="email">

				<div class="error" data-name="email"></div>
			</div>
			<div class="input">
				<label>{{ __('Password') }}</label>
				<input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password"
					required autocomplete="current-password">

				<div class="error" data-name="password"></div>
			</div>
			<p class="forgotYour_js">{{ __('header.popup_login_forgot') }}</p>
			<div class="js_spinner"></div>
			<button><span>{{ __('header.popup_login_button') }}</span></button>
		</form>
	</div>
</div>

<div class="popup-forgotYour popup">
	<span class="close-popup"></span>
	<div class="popup-content">
		<span class="close-content"><i class="icon-icon4"></i></span>
		<div class="h3_old">{{ __('header.popup_forgot_title') }}</div>
		<form action="{{ route('forget_email_reset') }}" class="js_forget_pass">
			<input type="hidden" name="locale" value="{{ app()->getLocale() }}">
			<div class="input">
				<label>{{ __('header.popup_forgot_email') }}</label>
				<input required type="email" name="email" class="js__mail_forget page-input__item">
			</div>
			<div class="js_spinner"></div>
			<button type="submit"><span>{{ __('header.popup_forgot_button') }}</span></button>
		</form>
	</div>
</div>

<style>
  .date .message1,.date .message2 {
    color:red;
  }
  .show{
      display: block;
  }
  .hide{
       display: none;
  }


</style>
<div class="popup-dates popup">
	@php
		$date_form_now_10d = Carbon::now()->addDays(20);
		$date_form_now_10d = $date_form_now_10d->format('Y-m-d');
	@endphp
	<span class="close-popup"></span>
	<div class="popup-content">
		<span class="close-content"><i class="icon-icon4"></i></span>
		<div class="h3_old">{{ __('header.popup_dates_title') }}</div>
		<p>{{ __('header.popup_dates_p') }}</p>
		<strong><i>*</i> {{ __('header.popup_dates_strong') }}</strong>
		<form action="#" method="POST" class="js_dates_form">
			<div class="date">
				<div class="input">
                                        <span class="message1 hide"></span>
					<input min="{{ $date_form_now_10d }}" required class="js_date1" type="date" name="date1" value="{{$user_data->date1 ?? ''}}">
                                        <input required class="js_torj1" name="torj1" type="text" value="{{$user_data->torj1  ?? ''}}">
{{--                                         <input class="hide coupon-code1" value="">--}}
				</div>
				<div class="input">
                                         <span class="message2 hide"></span>
					<input min="{{ $date_form_now_10d }}" required class="js_date2" type="date" name="date2" value="{{$user_data->date2  ?? ''}}">
					<input required class="js_torj2" name="torj2" type="text" value="{{$user_data->torj2  ?? ''}}">
                                        <input class="hide coupon-code2" value="">
				</div>
			</div>
			<div class="js_spinner" style="text-align:center;"></div>
			<button type="submit"><span>{{ __('header.popup_dates_button') }}</span></button>
		</form>
	</div>
</div>

@auth
	<div class="popup-lnvite-friend popup">
		<span class="close-popup"></span>
		<div class="popup-content">
			<span class="close-content"><i class="icon-icon4"></i></span>
			<div class="h3_old">{{ __('header.popup_lnvite_friend_title') }}</div>
			<p>{{ __('header.popup_lnvite_friend_p') }}</p>
			<form action="?">
				<div class="date">
					<input class="js_code_cup" type="text" placeholder="" @if (Auth::user()->inv_sale_code == 'alredy_used') disabled @endif
						@if (Auth::user()->inv_sale_code == 'alredy_used') value="You have already invited a friend"
            @else
            value="{{ Auth::user()->inv_sale_code }}" @endif
						name="invite_code">
				</div>
				<button @if (Auth::user()->inv_sale_code == 'alredy_used') disabled @endif type="button"
					class="js_user_copy"><span>{{ __('header.popup_lnvite_friend_button') }}</span></button>
			</form>
		</div>
	</div>
@endauth

@auth
	<div class="popup-print-screen popup">
		<span class="close-popup"></span>
		<div class="popup-content">
			<span class="close-content"><i class="icon-icon4"></i></span>
			<div class="h3_old">{{ __('header.popup-print-screen_title') }}</div>
			<p>{{ __('header.popup-print-screen_p') }} <strong>{{ __('header.popup-print-screen_strong') }}</strong>
			</p>
			<form action="?" method="POST" class="js_print_sale" enctype="multipart/form-data">
				{{ csrf_field() }}
				<div class="file">
					<input id="js_print_sale" required type="file" accept=".jpg, .jpeg, .png, .heic, .heif" name="sale_img" class="js_sale_file">
				</div>
				<div class="js_spinner" style="text-align:center;">
					<div class="bounce1"></div>
					<div class="bounce2"></div>
					<div class="bounce3"></div>
				</div>
				<button><span>{{ __('header.popup-print-screen_button') }}</span></button>
			</form>
		</div>
	</div>
@endauth

<div class="popup-photo popup">
	<span class="close-popup"></span>
	<div class="popup-content">
		<span class="close-content"><i class="icon-icon4"></i></span>
		<div class="h3_old">{{ __('header.popup-photo_title') }}</div>
		<p>{{ __('header.popup-photo_p') }} <strong>{{ __('header.popup-photo_strong') }}</strong></p>
		<strong><i>*</i>{{ __('header.popup-photo_strong2') }}</strong>
		<form action="?" method="POST" class="js_free_image" enctype="multipart/form-data">
			{{ csrf_field() }}
			<div class="file">
				<input id="js_free_img" name="file" required type="file" accept=".jpg, .jpeg, .png, .heic, .heif" multiple>
			</div>
			<div class="js_spinner" style="text-align:center;"></div>
			<button><span>{{ __('header.popup-photo_button') }}</span></button>
		</form>
	</div>
</div>
