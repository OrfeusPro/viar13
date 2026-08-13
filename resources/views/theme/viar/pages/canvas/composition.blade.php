<section class="composition__screen section-p" id="composition">
    <div class="composition-title">
        <div class="section-frame">
            <h1 class="page-title">{{ trans('canvas.comp_title') }}</h1>
            <div class="page-subtitle">
                {!! trans('canvas.comp_desc') !!}
            </div>
        </div>
    </div>
    <div class="composition-inner">
        <picture>
            <source media="(max-width: 980px)" srcset="{{ asset('images/canvas/composition-min.jpg') }}">
            <source media="(max-width: 980px)" srcset="{{ asset('images/canvas/composition-min.webp') }}">
            <source srcset="{{ asset('images/canvas/composition.webp') }}" type="image/webp">
            <source srcset="{{ asset('images/canvas/composition.jpg') }}" type="image/jpeg">
            <img src="{{ asset('images/canvas/composition.jpg') }}" alt="">
        </picture>
        <div class="section-frame relative">
            <div class="notes-block top-note">
                <div class="note-block">
                    <div class="note-block-inner">
                        <div class="note-round">1</div>
                        <p>{!! trans('canvas.comp_step1_title') !!}</p>
                    </div>
                </div>
            </div>
            <div class="notes-block bottom-note">
                <div class="note-block">
                    <div class="note-block-inner">
                        <div class="note-round">2</div>
                        <p>{!! trans('canvas.comp_step2_title') !!}</p>
                    </div>
                </div>
            </div>
            <div class="form-block">
                <div class="why-form-composition">
                    <div class="why-composition-inner">
                        <div class="form-title">{{ trans('canvas.comp_form_title') }}</div>
                        <div class="form-subtitle">

                            {{ trans('canvas.comp_form_get_test') }}
                        </div>
                        <form action="{{ route('send_all_styles_form') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" id="new_name" name="new_name" value="Композиция из фотокартин">
                            <input type="hidden" name="form_name" value="Композиция из фотокартин">
                            <div class="f__errs">
                                @if ($errors)
                                <ul>
                                    @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                @endif
                            </div>
                            <div class="kviz-input">
                                <p class="kviz-input__title">{{ trans('canvas.comp_form_enter_email') }}</p>
                                <div class="page-input__item">
                                    <input type="email" name="email" placeholder="E-mail" required="">
                                    <svg class="kviz-input__icon">
                                        <use xlink:href="{{ asset(env('THEME').'sprite.svg#mail') }}"></use>
                                    </svg>
                                </div>
                            </div>



                            <div class="kviz-input">
                                <p class="kviz-input__title">{{ trans('canvas.enter_num') }}</p>
                                <div class="page-input__item phone-input">
                                    <div class="country-item country-item-active">
                                        <img src="{{ asset('images/flag/lv.svg') }}" alt="img" loading="lazy">
                                    </div>
                                    <div class="country-list">
										@foreach ($c_tels as $tel)
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
                                    <input type="text" name="phone" class="input-mask input-counter" autocomplete="off" placeholder="+371" required="" data-mask="## ##-##-##" data-mask-raw-value="" data-mask-inited="true">
                                    <svg class="kviz-input__icon">
                                        <use xlink:href="{{ asset(env('THEME').'sprite.svg#phone') }}"></use>
                                    </svg>
                                </div>
                            </div>




                            <div class="input-wrapper">
                                <div class="vz-art kviz-input">
                                    <p class="vz-art kviz-input__title">{{ trans('portrait_buy_form.step1_desc') }}</p>
                                    <div class="file-save file-save__popup">
                                        <div class="abs-close">
                                            X
                                        </div>
                                        <div class="file-save__item js-file-preview">
                                            <svg>
                                                <use xlink:href="{{ asset(env('THEME').'sprite.svg#save') }}"></use>
                                            </svg>
                                            <div class="file-save__title">
                                                <p>{{ trans('homepage_new.load_photo') }}</p>
                                                <span>{{ trans('homepage_new.pree_to_add_photo') }}</span>
                                            </div>
                                        </div>
                                        <div class="file-save__item js-file-upload">
                                            <svg>
                                                <use xlink:href="{{ asset(env('THEME').'sprite.svg#picture') }}"></use>
                                            </svg>
                                            <div class="file-save__title">
                                                <p>photo_34567.jpg</p>
                                                <span>2 Mb</span>
                                            </div>
                                        </div>
                                        <div class="file-save__item js-file-multiple">
                                            <svg>
                                                <use xlink:href="{{ asset(env('THEME').'sprite.svg#check') }}"></use>
                                            </svg>
                                            <div class="file-save__title">
                                                <p class="file-title_green">{{ trans('portrait.form_files_loaded') }}</p>
                                            </div>
                                        </div>
                                        <input type="file" class="file-input" name="file[]" multiple="" accept="image/*,image/heif,image/heic" aria-label="file input">
                                    </div>
                                </div>
                            </div>
                            <button class="comp-btn" type="submit">{{ trans('canvas.comp_form_btn_title') }}</button>
                            <div class="comp-politics">
                                <svg width="24" height="25" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15.1672 9.34306V7.16299C15.1672 5.71392 13.8112 4.46936 12.0653 4.46936C10.3194 4.46936 8.96418 5.7101 8.96418 7.16299V9.34306H15.1672ZM6.30566 9.34306V18.2045H17.8257V9.34306H6.30566Z" stroke="#FC8C5F" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M12.0647 15.076V12.4175" stroke="#FC8C5F" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <p>{!! trans('canvas.comp_form_bot_desc') !!}</p>
                            </div>
                            <div class="loading-bar">
                                <span>
                                    {{ trans('portrait_buy_form.loading') }}
                                </span>
                                <div class="l-progress">
                                    <span></span><span></span><span></span><span></span><span></span><span></span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
