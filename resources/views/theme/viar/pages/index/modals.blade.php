	@include((config('theme.resource') ?: 'theme.viar.') . 'modals.quiz_form')
	@include((config('theme.resource') ?: 'theme.viar.') . 'modals.popup_add_to_cart')

	{{-- thanks --}}
	<div class="vz-artjs-popup thanks">
		<div class="vz-art kviz-thanks">
			<img src="{{ asset('images/icon/check-done.svg') }}" class="kviz-thanks__icon" alt="img" loading="lazy">
			<div class="vz-art kviz-thanks__title">
				<div class="h3_old">{{ trans('portrait_buy_form.popup_thanks_text1') }}</div>
				<p>{{ trans('portrait_buy_form.popup_thanks_text2') }}</p>
			</div>
			<div class="vz-art kviz-thanks__info">
				<p>{{ trans('portrait_buy_form.popup_thanks_text3') }}</p>
				<a href="#" target="_blank" rel="noopener noreferrer">
					<svg>
						<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#wh') }}"></use>
					</svg>
					{{ trans('portrait_buy_form.popup_thanks_text4') }}
				</a>
			</div>
		</div>
	</div>


	@if (isset($is_portrait_page))
		<form data-portrait-by-photo-header="1" action="{{ route('send_photo_portrait_form') }}" method="POST"
			enctype="multipart/form-data" class="vz-art js-popup target-box popup-works-ex popup-def submit-form">
			@csrf
			<i class="vz-art fa-close popup-close"></i>
			<div class="vz-art page-title popup-photo-title h2_old">{{ trans('homepage_new.order_photo_portrait_title') }}</div>
			<div class="vz-art popup-group">
				<div class="vz-art kviz-input">
					<p class="vz-art kviz-input__title">{{ trans('homepage_new_login_reg.create_acc_enter_your_email') }}</p>
					<div class="vz-art page-input__item">
						<input type="email" name="email" placeholder="E-mail" required="" />
						<svg class="vz-art kviz-input__icon">
							<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#mail') }}"></use>
						</svg>
					</div>
				</div>
				<div class="vz-art kviz-input">
					<p class="vz-art kviz-input__title">{{ trans('homepage_new_login_reg.create_acc_enter_your_phone') }}</p>
					<div class="vz-art page-input__item phone-input" style="width:100%;">
						<input type="text" id="phone4" name="phone" required="" style="width:100%;">
						<svg class="vz-art kviz-input__icon">
							<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#phone') }}"></use>
						</svg>
					</div>
				</div>
			</div>
			<div class="vz-art popup-grid">
				<div class="vz-art popup-grid__file">
					<div class="vz-art kviz-input">
						<p class="vz-art kviz-input__title">{{ trans('homepage_new.photo_for_portrait') }}</p>
						<div class="file-save file-save__popup">
							<div class="file-save__item js-file-preview">
								<svg>
									<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#save') }}"></use>
								</svg>
								<div class="file-save__title">
									<p>{{ trans('homepage_new.load_photo') }}</p>
									<span>{{ trans('homepage_new.pree_to_add_photo') }}</span>
								</div>
							</div>
							<div class="file-save__item js-file-upload">
								<svg>
									<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#picture') }}"></use>
								</svg>
								<div class="file-save__title">

								</div>
							</div>
							<input type="file" id="#file1" class="file-input" name="file" accept="image/*,image/heif,image/heic" aria-label="file input" />
						</div>
						<div class="file-save file-save__popup file-save_hide">
							<div class="file-save__item js-file-preview">
								<svg>
									<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#save') }}"></use>
								</svg>
								<div class="file-save__title">
									<p>{{ trans('homepage_new.load_photo') }}</p>
									<span>{{ trans('homepage_new.pree_to_add_photo') }}</span>
								</div>
							</div>
							<div class="file-save__item js-file-upload">
								<svg>
									<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#picture') }}"></use>
								</svg>
								<div class="file-save__title">
									<p>{{ trans('homepage_new.load_photo') }}</p>
									<span>{{ trans('homepage_new.pree_to_add_photo') }}</span>
								</div>
							</div>
							<input type="file" id="#file2" class="file-input file-input_hide" name="file2" accept="image/*,image/heif,image/heic"
								aria-label="file input" />
						</div>
						<div class="file-save file-save__popup file-save_hide">
							<div class="file-save__item js-file-preview">
								<svg>
									<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#save') }}"></use>
								</svg>
								<div class="file-save__title">
									<p>{{ trans('homepage_new.load_photo') }}</p>
									<span>{{ trans('homepage_new.pree_to_add_photo') }}</span>
								</div>
							</div>
							<div class="file-save__item js-file-upload">
								<svg>
									<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#picture') }}"></use>
								</svg>
								<div class="file-save__title">

								</div>
							</div>
							<input type="file" class="file-input file-input_hide" name="file3" accept="image/*,image/heif,image/heic" aria-label="file input" />
						</div>
						<div class="file-save file-save__popup file-save_hide">
							<div class="file-save__item js-file-preview">
								<svg>
									<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#save') }}"></use>
								</svg>
								<div class="file-save__title">
									<p>{{ trans('homepage_new.load_photo') }}</p>
									<span>{{ trans('homepage_new.pree_to_add_photo') }}</span>
								</div>
							</div>
							<div class="file-save__item js-file-upload">
								<svg>
									<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#picture') }}"></use>
								</svg>
								<div class="file-save__title">

								</div>
							</div>
							<input type="file" class="file-input file-input_hide" name="file4" accept="image/*,image/heif,image/heic" aria-label="file input" />
						</div>
						<div class="file-save file-save__popup file-save_hide">
							<div class="file-save__item js-file-preview">
								<svg>
									<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#save') }}"></use>
								</svg>
								<div class="file-save__title">
									<p>{{ trans('homepage_new.load_photo') }}</p>
									<span>{{ trans('homepage_new.pree_to_add_photo') }}</span>
								</div>
							</div>
							<div class="file-save__item js-file-upload">
								<svg>
									<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#picture') }}"></use>
								</svg>
								<div class="file-save__title">
								</div>
							</div>
							<input type="file" class="file-input file-input_hide" name="file5" accept="image/*,image/heif,image/heic" aria-label="file input" />
						</div>
						<div class="file-save file-save__popup file-save_hide">
							<div class="file-save__item js-file-preview">
								<svg>
									<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#save') }}"></use>
								</svg>
								<div class="file-save__title">
									<p>{{ trans('homepage_new.load_photo') }}</p>
									<span>{{ trans('homepage_new.pree_to_add_photo') }}</span>
								</div>
							</div>
							<div class="file-save__item js-file-upload">
								<svg>
									<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#picture') }}"></use>
								</svg>
								<div class="file-save__title">

								</div>
							</div>
							<input type="file" class="file-input file-input_hide" name="file6" accept="image/*,image/heif,image/heic" aria-label="file input" />
						</div>
						<div class="file-save file-save__popup file-save_hide">
							<div class="file-save__item js-file-preview">
								<svg>
									<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#save') }}"></use>
								</svg>
								<div class="file-save__title">
									<p>{{ trans('homepage_new.load_photo') }}</p>
									<span>{{ trans('homepage_new.pree_to_add_photo') }}</span>
								</div>
							</div>
							<div class="file-save__item js-file-upload">
								<svg>
									<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#picture') }}"></use>
								</svg>
								<div class="file-save__title">

								</div>
							</div>
							<input type="file" class="file-input file-input_hide" name="file7" accept="image/*,image/heif,image/heic" aria-label="file input" />
						</div>
						<div class="file-save file-save__popup file-save_hide">
							<div class="file-save__item js-file-preview">
								<svg>
									<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#save') }}"></use>
								</svg>
								<div class="file-save__title">
									<p>{{ trans('homepage_new.load_photo') }}</p>
									<span>{{ trans('homepage_new.pree_to_add_photo') }}</span>
								</div>
							</div>
							<div class="file-save__item js-file-upload">
								<svg>
									<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#picture') }}"></use>
								</svg>
								<div class="file-save__title">

								</div>
							</div>
							<input type="file" class="file-input file-input_hide" name="file8" accept="image/*,image/heif,image/heic" aria-label="file input" />
						</div>
						<div class="file-save file-save__popup file-save_hide">
							<div class="file-save__item js-file-preview">
								<svg>
									<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#save') }}"></use>
								</svg>
								<div class="file-save__title">
									<p>{{ trans('homepage_new.load_photo') }}</p>
									<span>{{ trans('homepage_new.pree_to_add_photo') }}</span>
								</div>
							</div>
							<div class="file-save__item js-file-upload">
								<svg>
									<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#picture') }}"></use>
								</svg>
								<div class="file-save__title">

								</div>
							</div>
							<input type="file" class="file-input file-input_hide" name="file9" accept="image/*,image/heif,image/heic" aria-label="file input" />
						</div>
						<div class="file-save file-save__popup file-save_hide">
							<div class="file-save__item js-file-preview">
								<svg>
									<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#save') }}"></use>
								</svg>
								<div class="file-save__title">
									<p>{{ trans('homepage_new.load_photo') }}</p>
									<span>{{ trans('homepage_new.pree_to_add_photo') }}</span>
								</div>
							</div>
							<div class="file-save__item js-file-upload">
								<svg>
									<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#picture') }}"></use>
								</svg>
								<div class="file-save__title">

								</div>
							</div>
							<input type="file" class="file-input file-input_hide" name="file10" accept="image/*,image/heif,image/heic" aria-label="file input" />
						</div>
					</div>
					<a href="#" class="file-add">
						<svg>
							<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#plus') }}"></use>
						</svg>
						<span>{{ trans('homepage_new.load_more_photo') }}</span>
					</a>
					<div class="kviz-input kviz-input_mob">
						<p class="vz-art kviz-input__title">{{ trans('homepage_new.set_need_portrait_size') }}</p>
						@php
							$s_items = $item->getMedia('our_works_new');
						@endphp
						<select name="size" class="select-page select-size">
							@if (isset($is_portrait_page))
								@foreach ($s_items as $s_item)
									@if ($s_item->getCustomProperty('size'))
										<option value="{{ str_trans($s_item->getCustomProperty('size')) }}">
											{{ str_trans($s_item->getCustomProperty('size')) }}</option>
									@endif
								@endforeach
							@endif
						</select>
					</div>
					<div class="kviz-input kviz-input_mob">
						<p class="vz-art kviz-input__title">{{ trans('homepage_new.set_need_style') }}</p>
						@php
							$s_items = $item->getMedia('our_works_new');
						@endphp
						<select name="styles" class="select-page select-style">
							@if (isset($is_portrait_page))
								@foreach ($s_items as $s_item)
									@if ($s_item->getCustomProperty('name'))
										<option value="{{ str_trans($s_item->getCustomProperty('name')) }}">
											{{ str_trans($s_item->getCustomProperty('name')) }}</option>
									@endif
								@endforeach
							@endif
						</select>
					</div>
					<div class="box">
						<div class="h3_old">{{ trans('homepage_new.you_feel_nice_2_text') }}</div>
						<div class="box-list">
							<div class="kviz-radio box-radio kviz-radio_active js-checkbox">
								<div class="vz-art check"></div>
								<label>
									<span>{{ trans('homepage_new.set_nopack') }}</span>
									<input type="radio" checked="checked" name="box" value="{{ trans('homepage_new.set_nopack') }}" />
								</label>
							</div>
							<div class="kviz-radio box-radio js-checkbox">
								<div class="vz-art check"></div>
								<label>
									<span>{{ trans('homepage_new.set_pack_paper') }}</span>
									<input type="radio" name="box" value="{{ trans('homepage_new.set_pack_paper') }}" />
								</label>
							</div>
							<div class="kviz-radio box-radio js-checkbox">
								<div class="vz-art check"></div>
								<label>
									<span>{{ trans('homepage_new.set_pack_case') }}</span>
									<input type="radio" name="box" value="{{ trans('homepage_new.set_pack_case') }}" />
								</label>
							</div>
						</div>
					</div>
					<label class="popup-photo__submit"> <input type="submit" />{{ trans('homepage_new.send') }} </label>
					<div class="kviz-politics">
						<svg>
							<use xlink:href="{{ asset(env('THEME') . 'sprite.svg#lock') }}"></use>
						</svg>
						<p>
							{!! trans('homepage_new_login_reg.create_acc_policy_text') !!}
						</p>
					</div>
				</div>
				<div class="vz-art popup-grid__select kviz-input_pc">
					<div class="vz-art kviz-input">
						<p class="vz-art kviz-input__title">{{ trans('homepage_new.set_need_portrait_size') }}</p>
						<select name="size" class="select-page select-size">
							@if (isset($is_portrait_page))
								@foreach ($s_items as $s_item)
									@if ($s_item->getCustomProperty('size'))
										<option value="{{ str_trans($s_item->getCustomProperty('size')) }}">
											{{ str_trans($s_item->getCustomProperty('size')) }}</option>
									@endif
								@endforeach
							@endif
						</select>
					</div>
					<div class="vz-art kviz-input">
						<p class="vz-art kviz-input__title">{{ trans('homepage_new.set_need_style') }}</p>
						<select name="styles" class="select-page select-style">
							@if (isset($is_portrait_page))
								@foreach ($s_items as $s_item)
									@if ($s_item->getCustomProperty('name'))
										<option value="{{ str_trans($s_item->getCustomProperty('name')) }}">
											{{ str_trans($s_item->getCustomProperty('name')) }}</option>
									@endif
								@endforeach
							@endif
						</select>
					</div>
				</div>
			</div>
			<picture>
				<source srcset="{{ asset('images/portrait-form.webp') }}" type="image/webp" />
				<source srcset="{{ asset('images/portrait-form.png') }}" />
				<img src="{{ asset('images/portrait-form.png') }}" class="vz-art photo-mokap" alt="img" loading="lazy" />
			</picture>
		</form>

        <div class="vz-art js-popup target-box popup-sizes">
            <div class="popup-sizes__inner">
                <i class="vz-art fa-close popup-close"></i>
                <div class="ps-title ps-input-title">
                    Размеры портретов
                </div>
                <div class="sizes-images">
                    <div class="rtop">
                        <div class="sizes-image">
                            <div class="img">
                                <picture>
                                    <source media="(max-width: 576px)"
                                            srcset="{{ asset(config('theme.current') . '/images/sizesprices/z1Min.webp') }}"
                                            type="image/webp">
                                    <source srcset="{{ asset(config('theme.current') . '/images/sizesprices/z1.webp') }}"
                                            type="image/webp">
                                    <img width="200" height="270"
                                         src="{{ asset(config('theme.current') . '/images/sizesprices/z1.webp') }}" alt="">
                                </picture>
                            </div>
                            <div class="row">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                          d="M0.666667 0H0V0.666667V7.33333H1.33333V2.27614L6.86193 7.80473L7.80473 6.86193L2.27614 1.33333H7.33333V0H0.666667ZM19.3333 0H20V0.666667V7.33333H18.6667V2.27614L13.1381 7.80473L12.1953 6.86193L17.7239 1.33333H12.6667V0H19.3333ZM20 20H19.3333H12.6667V18.6667H17.7239L12.1953 13.1381L13.1381 12.1953L18.6667 17.7239V12.6667H20V19.3333V20ZM0.666667 20H0V19.3333V12.6667H1.33333V17.7239L6.86193 12.1953L7.80473 13.1381L2.27614 18.6667H7.33333V20H0.666667Z"
                                          fill="#FA7846"></path>
                                </svg>
                                <span>30x40cm</span>
                            </div>
                        </div>
                        <div class="sizes-image">
                            <div class="img">
                                <picture>
                                    <source media="(max-width: 576px)"
                                            srcset="{{ asset(config('theme.current') . '/images/sizesprices/z2Min.webp') }}"
                                            type="image/webp">
                                    <source srcset="{{ asset(config('theme.current') . '/images/sizesprices/z2.webp') }}"
                                            type="image/webp">
                                    <img width="200" height="270"
                                         src="{{ asset(config('theme.current') . '/images/sizesprices/z2.webp') }}" alt="">
                                </picture>
                            </div>
                            <div class="row">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                          d="M0.666667 0H0V0.666667V7.33333H1.33333V2.27614L6.86193 7.80473L7.80473 6.86193L2.27614 1.33333H7.33333V0H0.666667ZM19.3333 0H20V0.666667V7.33333H18.6667V2.27614L13.1381 7.80473L12.1953 6.86193L17.7239 1.33333H12.6667V0H19.3333ZM20 20H19.3333H12.6667V18.6667H17.7239L12.1953 13.1381L13.1381 12.1953L18.6667 17.7239V12.6667H20V19.3333V20ZM0.666667 20H0V19.3333V12.6667H1.33333V17.7239L6.86193 12.1953L7.80473 13.1381L2.27614 18.6667H7.33333V20H0.666667Z"
                                          fill="#FA7846"></path>
                                </svg>
                                <span>40x60cm</span>
                            </div>
                        </div>
                        <div class="sizes-image">
                            <div class="img">
                                <picture>
                                    <source media="(max-width: 576px)"
                                            srcset="{{ asset(config('theme.current') . '/images/sizesprices/z3Min.webp') }}"
                                            type="image/webp">
                                    <source srcset="{{ asset(config('theme.current') . '/images/sizesprices/z3.webp') }}"
                                            type="image/webp">
                                    <img width="200" height="270"
                                         src="{{ asset(config('theme.current') . '/images/sizesprices/z3.webp') }}" alt="">
                                </picture>
                            </div>
                            <div class="row">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                          d="M0.666667 0H0V0.666667V7.33333H1.33333V2.27614L6.86193 7.80473L7.80473 6.86193L2.27614 1.33333H7.33333V0H0.666667ZM19.3333 0H20V0.666667V7.33333H18.6667V2.27614L13.1381 7.80473L12.1953 6.86193L17.7239 1.33333H12.6667V0H19.3333ZM20 20H19.3333H12.6667V18.6667H17.7239L12.1953 13.1381L13.1381 12.1953L18.6667 17.7239V12.6667H20V19.3333V20ZM0.666667 20H0V19.3333V12.6667H1.33333V17.7239L6.86193 12.1953L7.80473 13.1381L2.27614 18.6667H7.33333V20H0.666667Z"
                                          fill="#FA7846"></path>
                                </svg>
                                <span>50x70cm</span>
                            </div>
                        </div>
                        <div class="sizes-image">
                            <div class="img">
                                <picture>
                                    <source media="(max-width: 576px)"
                                            srcset="{{ asset(config('theme.current') . '/images/sizesprices/z4Min.webp') }}"
                                            type="image/webp">
                                    <source srcset="{{ asset(config('theme.current') . '/images/sizesprices/z4.webp') }}"
                                            type="image/webp">
                                    <img width="230" height="270"
                                         src="{{ asset(config('theme.current') . '/images/sizesprices/z4.webp') }}" alt="">
                                </picture>
                            </div>
                            <div class="row">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                          d="M0.666667 0H0V0.666667V7.33333H1.33333V2.27614L6.86193 7.80473L7.80473 6.86193L2.27614 1.33333H7.33333V0H0.666667ZM19.3333 0H20V0.666667V7.33333H18.6667V2.27614L13.1381 7.80473L12.1953 6.86193L17.7239 1.33333H12.6667V0H19.3333ZM20 20H19.3333H12.6667V18.6667H17.7239L12.1953 13.1381L13.1381 12.1953L18.6667 17.7239V12.6667H20V19.3333V20ZM0.666667 20H0V19.3333V12.6667H1.33333V17.7239L6.86193 12.1953L7.80473 13.1381L2.27614 18.6667H7.33333V20H0.666667Z"
                                          fill="#FA7846"></path>
                                </svg>
                                <span>55x80cm</span>
                            </div>
                        </div>
                    </div>
                    <div class="rbottom">
                        <div class="sizes-image">
                            <div class="img">
                                <picture>
                                    <source media="(max-width: 576px)"
                                            srcset="{{ asset(config('theme.current') . '/images/sizesprices/z5Min.webp') }}"
                                            type="image/webp">
                                    <source srcset="{{ asset(config('theme.current') . '/images/sizesprices/z5.webp') }}"
                                            type="image/webp">
                                    <img width="240" height="270"
                                         src="{{ asset(config('theme.current') . '/images/sizesprices/z5.webp') }}" alt="">
                                </picture>
                            </div>
                            <div class="row">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                          d="M0.666667 0H0V0.666667V7.33333H1.33333V2.27614L6.86193 7.80473L7.80473 6.86193L2.27614 1.33333H7.33333V0H0.666667ZM19.3333 0H20V0.666667V7.33333H18.6667V2.27614L13.1381 7.80473L12.1953 6.86193L17.7239 1.33333H12.6667V0H19.3333ZM20 20H19.3333H12.6667V18.6667H17.7239L12.1953 13.1381L13.1381 12.1953L18.6667 17.7239V12.6667H20V19.3333V20ZM0.666667 20H0V19.3333V12.6667H1.33333V17.7239L6.86193 12.1953L7.80473 13.1381L2.27614 18.6667H7.33333V20H0.666667Z"
                                          fill="#FA7846"></path>
                                </svg>
                                <span>60x90cm</span>
                            </div>
                        </div>
                        <div class="sizes-image">
                            <div class="img">
                                <picture>
                                    <source media="(max-width: 576px)"
                                            srcset="{{ asset(config('theme.current') . '/images/sizesprices/z6Min.webp') }}"
                                            type="image/webp">
                                    <source srcset="{{ asset(config('theme.current') . '/images/sizesprices/z6.webp') }}"
                                            type="image/webp">
                                    <img width="230" height="270"
                                         src="{{ asset(config('theme.current') . '/images/sizesprices/z6.webp') }}" alt="">
                                </picture>
                            </div>
                            <div class="row">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                          d="M0.666667 0H0V0.666667V7.33333H1.33333V2.27614L6.86193 7.80473L7.80473 6.86193L2.27614 1.33333H7.33333V0H0.666667ZM19.3333 0H20V0.666667V7.33333H18.6667V2.27614L13.1381 7.80473L12.1953 6.86193L17.7239 1.33333H12.6667V0H19.3333ZM20 20H19.3333H12.6667V18.6667H17.7239L12.1953 13.1381L13.1381 12.1953L18.6667 17.7239V12.6667H20V19.3333V20ZM0.666667 20H0V19.3333V12.6667H1.33333V17.7239L6.86193 12.1953L7.80473 13.1381L2.27614 18.6667H7.33333V20H0.666667Z"
                                          fill="#FA7846"></path>
                                </svg>
                                <span>70x100cm</span>
                            </div>
                        </div>
                        <div class="sizes-image">
                            <div class="img">
                                <picture>
                                    <source media="(max-width: 576px)"
                                            srcset="{{ asset(config('theme.current') . '/images/sizesprices/z1Min.webp') }}"
                                            type="image/webp">
                                    <source srcset="{{ asset(config('theme.current') . '/images/sizesprices/z7.webp') }}"
                                            type="image/webp">
                                    <img width="290" height="270"
                                         src="{{ asset(config('theme.current') . '/images/sizesprices/z7.webp') }}" alt="">
                                </picture>
                            </div>
                            <div class="row">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                          d="M0.666667 0H0V0.666667V7.33333H1.33333V2.27614L6.86193 7.80473L7.80473 6.86193L2.27614 1.33333H7.33333V0H0.666667ZM19.3333 0H20V0.666667V7.33333H18.6667V2.27614L13.1381 7.80473L12.1953 6.86193L17.7239 1.33333H12.6667V0H19.3333ZM20 20H19.3333H12.6667V18.6667H17.7239L12.1953 13.1381L13.1381 12.1953L18.6667 17.7239V12.6667H20V19.3333V20ZM0.666667 20H0V19.3333V12.6667H1.33333V17.7239L6.86193 12.1953L7.80473 13.1381L2.27614 18.6667H7.33333V20H0.666667Z"
                                          fill="#FA7846"></path>
                                </svg>
                                <span>80x120cm</span>
                            </div>
                        </div>
                        <div class="sizes-image">
                            <div class="img">
                                <picture>
                                    <source media="(max-width: 576px)"
                                            srcset="{{ asset(config('theme.current') . '/images/sizesprices/z8Min.webp') }}"
                                            type="image/webp">
                                    <source srcset="{{ asset(config('theme.current') . '/images/sizesprices/z8.webp') }}"
                                            type="image/webp">
                                    <img width="350" height="270"
                                         src="{{ asset(config('theme.current') . '/images/sizesprices/z8.webp') }}" alt="">
                                </picture>
                            </div>
                            <div class="row">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                          d="M0.666667 0H0V0.666667V7.33333H1.33333V2.27614L6.86193 7.80473L7.80473 6.86193L2.27614 1.33333H7.33333V0H0.666667ZM19.3333 0H20V0.666667V7.33333H18.6667V2.27614L13.1381 7.80473L12.1953 6.86193L17.7239 1.33333H12.6667V0H19.3333ZM20 20H19.3333H12.6667V18.6667H17.7239L12.1953 13.1381L13.1381 12.1953L18.6667 17.7239V12.6667H20V19.3333V20ZM0.666667 20H0V19.3333V12.6667H1.33333V17.7239L6.86193 12.1953L7.80473 13.1381L2.27614 18.6667H7.33333V20H0.666667Z"
                                          fill="#FA7846"></path>
                                </svg>
                                <span>90x140cm</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
	@endif
