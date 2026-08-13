@extends('layots.common')

@section('content')
<section class="viar-password-reset">
    <div class="section-frame">
        <div class="viar-password-reset__card">
            <div class="viar-password-reset__accent"></div>
            <div class="viar-password-reset__content">
                <div class="viar-password-reset__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" focusable="false">
                        <path d="M7 10V8a5 5 0 0 1 10 0v2m-11 0h12a1 1 0 0 1 1 1v9H5v-9a1 1 0 0 1 1-1Zm6 4v3" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h1>@lang('passwords.reset_page_title')</h1>
                <p class="viar-password-reset__intro">@lang('passwords.reset_page_intro')</p>

                @php
                    $resetLocale = app()->getLocale();
                    $resetAction = $resetLocale === config('app.locale', 'ru')
                        ? route('password.update')
                        : route('password.update.localized', ['locale' => $resetLocale]);
                @endphp
                <form method="POST" action="{{ $resetAction }}" class="viar-password-reset__form">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <label for="email" class="viar-password-reset__field">
                        <span>@lang('passwords.email_label')</span>
                        <input id="email" type="email" name="email" value="{{ $email ?? old('email') }}"
                            class="@error('email') is-invalid @enderror" required autocomplete="email" autofocus>
                        @error('email')
                            <small role="alert">{{ $message }}</small>
                        @enderror
                    </label>

                    <label for="password" class="viar-password-reset__field">
                        <span>@lang('passwords.new_password')</span>
                        <input id="password" type="password" name="password"
                            class="@error('password') is-invalid @enderror" required autocomplete="new-password">
                        @error('password')
                            <small role="alert">{{ $message }}</small>
                        @enderror
                    </label>

                    <label for="password-confirm" class="viar-password-reset__field">
                        <span>@lang('passwords.confirm_password')</span>
                        <input id="password-confirm" type="password" name="password_confirmation"
                            required autocomplete="new-password">
                    </label>

                    <button type="submit" class="viar-password-reset__submit">@lang('passwords.reset_action')</button>

                    <div class="viar-password-reset__security">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M12 3 5.5 5.7v5.2c0 4.3 2.7 8.2 6.5 10.1 3.8-1.9 6.5-5.8 6.5-10.1V5.7L12 3Z" fill="none" stroke="currentColor" stroke-width="1.6"/>
                            <path d="m9 12 2 2 4-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>@lang('passwords.secure_note')</span>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<style>
.viar-password-reset { padding: 240px 20px 140px; background: linear-gradient(180deg, #fff 0, #fff 58%, #fff7f0 100%); }
.viar-password-reset__card { position: relative; max-width: 680px; margin: 0 auto; overflow: hidden; border: 1px solid #f4dfd3; border-radius: 18px; background: #fffaf6; box-shadow: 0 22px 60px rgba(30,37,51,.1); }
.viar-password-reset__accent { height: 7px; background: linear-gradient(90deg, #fc8c5f, #fa7846); }
.viar-password-reset__content { padding: 44px 54px 48px; }
.viar-password-reset__icon { display: flex; width: 54px; height: 54px; margin-bottom: 18px; align-items: center; justify-content: center; border-radius: 50%; background: #fff0e8; color: #fa7846; }
.viar-password-reset__icon svg { width: 29px; height: 29px; }
.viar-password-reset h1 { margin: 0 0 10px; color: #1e2533; font: 600 34px/1.25 Inter, Arial, sans-serif; }
.viar-password-reset__intro { margin: 0 0 30px; color: #6d7481; font: 400 16px/1.6 Inter, Arial, sans-serif; }
.viar-password-reset__form { display: grid; gap: 19px; }
.viar-password-reset__field { display: block; margin: 0; color: #1e2533; font: 600 15px/1.4 Inter, Arial, sans-serif; }
.viar-password-reset__field span { display: block; margin-bottom: 8px; }
.viar-password-reset__field input { width: 100%; height: 54px; padding: 0 16px; border: 1.5px solid #d9dce2; border-radius: 8px; background: #fff; color: #1e2533; font: 400 16px Inter, Arial, sans-serif; transition: border-color .2s, box-shadow .2s; }
.viar-password-reset__field input:focus { border-color: #fa7846; box-shadow: 0 0 0 3px rgba(250,120,70,.13); }
.viar-password-reset__field input.is-invalid { border-color: #c62828; }
.viar-password-reset__field small { display: block; margin-top: 7px; color: #c62828; font-weight: 400; }
.viar-password-reset__submit { width: 100%; min-height: 56px; margin-top: 3px; border: 0; border-radius: 8px; background: linear-gradient(90deg, #fc8c5f, #fa7846); box-shadow: 0 3px 0 #e76d40, 0 16px 30px rgba(250,120,70,.2); color: #fff; font: 700 16px Inter, Arial, sans-serif; cursor: pointer; transition: transform .2s, box-shadow .2s; }
.viar-password-reset__submit:hover { transform: translateY(-1px); box-shadow: 0 4px 0 #e76d40, 0 18px 34px rgba(250,120,70,.26); }
.viar-password-reset__security { display: flex; margin-top: 4px; align-items: center; gap: 9px; color: #6d7481; font: 400 13px/1.5 Inter, Arial, sans-serif; }
.viar-password-reset__security svg { width: 21px; height: 21px; flex: 0 0 21px; color: #fa7846; }
@media (max-width: 700px) {
    .viar-password-reset { padding: 150px 15px 80px; }
    .viar-password-reset__content { padding: 32px 22px 36px; }
    .viar-password-reset h1 { font-size: 27px; }
    .phone-mobile-btn { display: none !important; }
}
</style>
@endsection
