@include(env('THEME_RESOURCES') . 'account.breads',["page" => $page])

<div class="main-cabinet">
	<div class="section-frame">
		<div class="main-cabinet__inner">
			<div class="cabinet-top__block">
				<div class="page-title cabinet-title">
					@lang("account_new.menu.my_settings") 
				</div>
				<p>
					@lang("account_new.settings.title_text") 
					<a class="activeSpan" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">@lang("account_new.btn.logout")</a>
				</p>
			</div>
			<div class="cabinet-container">

				@include(env('THEME_RESOURCES') . 'account.menu')

				<div class="cabinet-content">
					<div class="cabinet-settings">
						<form action="#" class="" id="AccountFormChangePasswordAndContact">
							<div class="setting-grid">
								<div class="set-group" id="ChangePassword">
									<div class="set-title">
										@lang("account_new.settings.change_pass")
									</div>
									<div class="input-group">
										<div class="input-item">
											<label for="old_password">@lang("account_new.settings.old_pass")</label>
											<input class="setInput" name="old_password" type="password" value="">
											<div class="error" name="old_password"></div>
										</div>
										<div class="input-item">
											<label for="password">@lang("account_new.settings.new_pass")</label>
											<input class="setInput" name="password" type="password" value="">
											<div class="error" name="password"></div>
										</div>
										<div class="input-item">
											<label for="password_confirmation">@lang("account_new.settings.retry_pass")</label>
											<input class="setInput" name="password_confirmation" type="password" value="">
											<div class="error" name="password_confirmation"></div>
										</div>
									</div>
									<button>@lang("account_new.btn.save")</button>
								</div>
								<div class="set-group">
									<div class="set-title">
										@lang("account_new.settings.contact_details")
									</div>
									<div class="input-group">
										<div class="input-item">
											<label for="last_name">@lang("account_new.settings.surname")</label>
											<input class="setInput" name="last_name" type="text" value="{{ Auth::user()->last_name }}">
										</div>
										<div class="input-item">
											<label for="first_name">@lang("account_new.settings.name")</label>
											<input class="setInput" name="first_name" type="text" value="{{ Auth::user()->first_name }}">
										</div>
										<div class="input-item">
											<label for="phone">@lang("account_new.settings.phone")</label>
											<input class="setInput" name="phone" type="text" value="{{ Auth::user()->phone }}">
										</div>
									</div>
									<button>@lang("account_new.btn.save")</button>
								</div>
							

								<div class="set-group">
									<div class="set-title">
										@lang("account_new.settings.permanent_address")
									</div>
									<div class="input-group">
										<div class="input-item">
											<label for="country">Страна</label>
											<div class="select-wrapper">
												<div class="arrow-box">
													<svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
														<path d="M7.78156 9.13746C7.8684 9.04582 7.96827 9 8.08116 9C8.19406 9 8.29392 9.04582 8.38076 9.13746L13.5 14.5395L18.6192 9.13746C18.7061 9.04582 18.8059 9 18.9188 9C19.0317 9 19.1316 9.04582 19.2184 9.13746L19.8697 9.82474C19.9566 9.91638 20 10.0218 20 10.1409C20 10.26 19.9566 10.3654 19.8697 10.457L13.7996 16.8625C13.7128 16.9542 13.6129 17 13.5 17C13.3871 17 13.2872 16.9542 13.2004 16.8625L7.13026 10.457C7.04342 10.3654 7 10.26 7 10.1409C7 10.0218 7.04342 9.91638 7.13026 9.82474L7.78156 9.13746Z" fill="#FA7846"/>
													</svg>                                                            
												</div>
												{{-- {{ dd(Auth::user()->country ) }} --}}
												<select name="country">
                                                    @foreach ($c_tels as $c_tel)
														<option value="{{ $c_tel['country_code'] }}" @if(Auth::user()->country == $c_tel['country_code']) selected @endif>{{ $c_tel['country_name'] }}</option>
                                                    @endforeach
												</select>
											</div>
										</div>

										{{-- <div class="input-item">
											<label for="cityAddress">Город</label>
											<div class="select-wrapper">
												<div class="arrow-box">
													<svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
														<path d="M7.78156 9.13746C7.8684 9.04582 7.96827 9 8.08116 9C8.19406 9 8.29392 9.04582 8.38076 9.13746L13.5 14.5395L18.6192 9.13746C18.7061 9.04582 18.8059 9 18.9188 9C19.0317 9 19.1316 9.04582 19.2184 9.13746L19.8697 9.82474C19.9566 9.91638 20 10.0218 20 10.1409C20 10.26 19.9566 10.3654 19.8697 10.457L13.7996 16.8625C13.7128 16.9542 13.6129 17 13.5 17C13.3871 17 13.2872 16.9542 13.2004 16.8625L7.13026 10.457C7.04342 10.3654 7 10.26 7 10.1409C7 10.0218 7.04342 9.91638 7.13026 9.82474L7.78156 9.13746Z" fill="#FA7846"/>
													</svg>                                                            
												</div>
												<select name="cityAddress" id="">
													<option value="Тернополь">Тернополь</option>
													<option value="Тернополь">Тернополь</option>
												</select>
											</div>
										</div> --}}
										<div class="input-item">
											<label for="phoneNumber">@lang("cart_new.step_3_address")</label>
											<input class="setInput" name="address" type="text" value="{{ Auth::user()->address }}">
										</div>
									</div>
									<button>@lang("account_new.btn.save")</button>
								</div>
<!--
								<div class="set-group">
									<div class="set-title">
										@lang("account_new.settings.delivery_address")
									</div>
									<div class="input-group">
										<div class="input-item">
											<label for="city">Город</label>
											<div class="select-wrapper">
												<div class="arrow-box">
													<svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
														<path d="M7.78156 9.13746C7.8684 9.04582 7.96827 9 8.08116 9C8.19406 9 8.29392 9.04582 8.38076 9.13746L13.5 14.5395L18.6192 9.13746C18.7061 9.04582 18.8059 9 18.9188 9C19.0317 9 19.1316 9.04582 19.2184 9.13746L19.8697 9.82474C19.9566 9.91638 20 10.0218 20 10.1409C20 10.26 19.9566 10.3654 19.8697 10.457L13.7996 16.8625C13.7128 16.9542 13.6129 17 13.5 17C13.3871 17 13.2872 16.9542 13.2004 16.8625L7.13026 10.457C7.04342 10.3654 7 10.26 7 10.1409C7 10.0218 7.04342 9.91638 7.13026 9.82474L7.78156 9.13746Z" fill="#FA7846"/>
													</svg>                                                            
												</div>
												<select name="city" id="">
													<option value="Тернополь">Тернополь</option>
													<option value="Тернополь">Тернополь</option>
												</select>
											</div>
										</div>
										<div class="input-item">
											<label for="pickUp">Пик-Ап пункт</label>
											{{-- <div class="select-wrapper">
												<div class="arrow-box">
													<svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
														<path d="M7.78156 9.13746C7.8684 9.04582 7.96827 9 8.08116 9C8.19406 9 8.29392 9.04582 8.38076 9.13746L13.5 14.5395L18.6192 9.13746C18.7061 9.04582 18.8059 9 18.9188 9C19.0317 9 19.1316 9.04582 19.2184 9.13746L19.8697 9.82474C19.9566 9.91638 20 10.0218 20 10.1409C20 10.26 19.9566 10.3654 19.8697 10.457L13.7996 16.8625C13.7128 16.9542 13.6129 17 13.5 17C13.3871 17 13.2872 16.9542 13.2004 16.8625L7.13026 10.457C7.04342 10.3654 7 10.26 7 10.1409C7 10.0218 7.04342 9.91638 7.13026 9.82474L7.78156 9.13746Z" fill="#FA7846"/>
													</svg>                                                            
												</div>
												<select name="pickUp" id="">
													<option value="Paku Skapis RIMI Tilta
													Tilta iela 32, 1005, Rīga, LV"></option>
												</select>
											</div> --}}
											
											<input class="setInput" name="pickUp" type="text" value="Paku Skapis RIMI Tilta
											Tilta iela 32, 1005, Rīga, LV">
										</div>
									</div>
									<button>@lang("account_new.btn.save")</button>
								</div>
							-->								
								<div class="set-bottom">
									<div class="set-col">
										<div class="checkbox-row">
											<div class="checkbox">
												<input name="news" type="checkbox" @if(Auth::user()->news == 'YES') checked @endif>
												<svg width="19" height="17" viewBox="0 0 19 17" fill="none" xmlns="http://www.w3.org/2000/svg">
													<g clip-path="url(#clip0_1722_1457)">
													<path d="M7.76452 17.0003C7.35142 17.0003 6.95257 16.8099 6.6475 16.4667L0.534166 9.58647C-0.137707 8.8304 -0.181628 7.55089 0.43564 6.72648C1.0541 5.90644 2.09752 5.85118 2.77058 6.6058L7.65768 12.1077L16.1226 0.666272C16.7351 -0.1625 17.7809 -0.225021 18.4575 0.525236C19.1329 1.27549 19.1852 2.555 18.5715 3.38377L8.98956 16.3373C8.69279 16.7357 8.28088 16.9742 7.84049 17.0003C7.81437 17.0003 7.78944 17.0003 7.76452 17.0003Z" fill="#FC8C5F"/>
													</g>
													<defs>
													<clipPath id="clip0_1722_1457">
													<rect width="19" height="17" fill="white"/>
													</clipPath>
													</defs>
												</svg>   
												<span></span>                                                 
											</div>
											<p>@lang('account.index46')</p>
										</div>
										<div class="checkbox-row">
											<div class="checkbox">
												<input name="ad" type="checkbox" @if(Auth::user()->ad == 'YES') checked @endif>
												<svg width="19" height="17" viewBox="0 0 19 17" fill="none" xmlns="http://www.w3.org/2000/svg">
													<g clip-path="url(#clip0_1722_1457)">
													<path d="M7.76452 17.0003C7.35142 17.0003 6.95257 16.8099 6.6475 16.4667L0.534166 9.58647C-0.137707 8.8304 -0.181628 7.55089 0.43564 6.72648C1.0541 5.90644 2.09752 5.85118 2.77058 6.6058L7.65768 12.1077L16.1226 0.666272C16.7351 -0.1625 17.7809 -0.225021 18.4575 0.525236C19.1329 1.27549 19.1852 2.555 18.5715 3.38377L8.98956 16.3373C8.69279 16.7357 8.28088 16.9742 7.84049 17.0003C7.81437 17.0003 7.78944 17.0003 7.76452 17.0003Z" fill="#FC8C5F"/>
													</g>
													<defs>
													<clipPath id="clip0_1722_1457">
													<rect width="19" height="17" fill="white"/>
													</clipPath>
													</defs>
												</svg>   
												<span></span>                                                    
											</div>
											<p>@lang('account.index47')</p>
										</div>
									</div>
									<div class="set-col">
										<div class="checkbox-row">
											<div class="checkbox">
												<input name="client_data" type="checkbox" @if(Auth::user()->client_data == 'YES') checked
												@endif>
												<svg width="19" height="17" viewBox="0 0 19 17" fill="none" xmlns="http://www.w3.org/2000/svg">
													<g clip-path="url(#clip0_1722_1457)">
													<path d="M7.76452 17.0003C7.35142 17.0003 6.95257 16.8099 6.6475 16.4667L0.534166 9.58647C-0.137707 8.8304 -0.181628 7.55089 0.43564 6.72648C1.0541 5.90644 2.09752 5.85118 2.77058 6.6058L7.65768 12.1077L16.1226 0.666272C16.7351 -0.1625 17.7809 -0.225021 18.4575 0.525236C19.1329 1.27549 19.1852 2.555 18.5715 3.38377L8.98956 16.3373C8.69279 16.7357 8.28088 16.9742 7.84049 17.0003C7.81437 17.0003 7.78944 17.0003 7.76452 17.0003Z" fill="#FC8C5F"/>
													</g>
													<defs>
													<clipPath id="clip0_1722_1457">
													<rect width="19" height="17" fill="white"/>
													</clipPath>
													</defs>
												</svg>     
												<span></span>                                                   
											</div>
											<p>@lang('account.index48')</p>
										</div>
									</div>
								</div>
							</div>
							<button class="btn">@lang("account_new.btn.save_settings")</button>
						</form>
					</div>
					<div class="cabinet-content__icon">
						<img src="{{ asset(config('theme.current') . '/images')}}/cabinet/settings.svg" width="82" height="82"
							alt="Viar Cabinet Peding Orders">
					</div>
				</div>
			</div>
		</div>
	</div>
</div>


@include(config('theme.resource') . 'account.special_offers')
@include(config('theme.resource') . 'pages.gallery.zpart_viarcanvas_is')

@section('script')
<script>
$('body').on('click', '#AccountFormChangePasswordAndContact button', function () {
	var inputs = {}
	var data = $(this).parent().find('input, select')

	for (obj in data) {
		if ($.isNumeric(obj)) {
			var key = $(data[obj]).attr('name')
			var type = $(data[obj]).attr('type')
			var value = $(data[obj]).val()

			value = (type === 'checkbox' && $(data[obj]).is(':checked')) ?
				'YES' :
				(type === 'checkbox') ? 'NO' : value

			inputs[key] = value
		}
	}

	$.ajax({
		method: 'POST',
		url: "{{ route('new_account.ajax_change_information') }}",
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
		data: inputs,
		dataType: 'json',
		success: function (msg) {

			if (msg.status === 'ok') {
				location.reload();
			}
			else
			{
				$('#AccountFormChangePasswordAndContact div.error').html('')

				for (key in msg.errors) {
					console.log(key);
					for (key2 in msg.errors[key]) {
						$('#AccountFormChangePasswordAndContact div.error[name=' + key +
							']').append('<p style="color: red;">' + msg.errors[key][
							key2
							] + '</p>')
					}
				}				
			}
		},
		error: function (jqXHR, exception) {
			var msg = JSON.parse(jqXHR.responseText)
			console.log(msg)
			$('#AccountFormChangePasswordAndContact div.error').html('')

			for (key in msg.errors) {
				for (key2 in msg.errors[key]) {
					$('#AccountFormChangePasswordAndContact div.error[name=' + key +
						']').append('<p style="color: red;">' + msg.errors[key][
						key2
						] + '</p>')
				}
			}
		}
	})

	return false
})
</script>
@endsection