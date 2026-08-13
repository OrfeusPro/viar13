        <form action="{{ route('send_all_styles_form') }}" method="POST" enctype="multipart/form-data"
            class="why-form submit-form">
            @csrf
            <div class="f__errs">
                @if ($errors)
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <input type="hidden" id="new_name" name="new_name" value="{{ $top_form->getTranslatedAttribute('f_title') }}">
            <div class="why-form__title h3_old">{{ $top_form->getTranslatedAttribute('f_title') }}</div>
            <div class="why-form__group">
                <div class="kviz-input">
                    <p class="kviz-input__title"></p>
                    <div class="file-save">
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
                <div class="kviz-input">
                    <p class="kviz-input__title">{!! $top_form->getTranslatedAttribute('f_email') !!}</p>
                    <div class="page-input__item">
                        <input type="email" name="email" placeholder="E-mail" required="">
                        <svg class="kviz-input__icon">
                            <use xlink:href="{{ asset(env('THEME').'sprite.svg#mail') }}"></use>
                        </svg>
                    </div>
                </div>
                <div class="kviz-input">
                    <p class="kviz-input__title">{!! $top_form->getTranslatedAttribute('f_phone') !!}</p>
                    <div class="page-input__item phone-input">
                        <div class="banner__input-item">
                            <input type="text" id="phone2" name="phone" class="banner__input phone" required>
                            <img src="{{ asset('img/icons/phone.svg') }}" alt="" class="img-svg img-svg__posa">
                        </div>
                    </div>
                </div>
                <label class="why-sub">
                    <input type="submit">{!! $bot_form['send'] !!}
                </label>
            </div>
            <div class="loading-bar">
                    <span>
                        {{ trans('portrait_buy_form.loading') }}
                    </span>
                    <div class="l-progress">
                        <span></span><span></span><span></span><span></span><span></span><span></span>
                    </div>
                </div>
            <picture>
                <source srcset="{{ asset('images/pinned-photo.webp') }}" type="image/webp">
                <source srcset="{{ asset('images/pinned-photo.png') }}">
                <img src="{{ asset('images/pinned-photo.png') }}" class="why-form__photo" alt="img"
                    loading="lazy">
            </picture>
        </form>
