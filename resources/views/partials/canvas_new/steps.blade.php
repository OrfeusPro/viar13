<section class="formalization__screen section-p" id="formalizaton">
    <div class="section-frame">
        <div class="formalization__screen--inner">
            <div class="section-title">
                <div class="page-title h2_old">{{ trans('canvas.order_steps_title') }}</div>
                <div class="section-subtitle">
                    {{ trans('canvas.order_steps_desc') }}
                </div>
            </div>
            <div class="stages__wrapper">
                <div class="stages-row">
                    <div class="stage-item">
                        <div class="stage-img">
                            <picture>
                                <source srcset="{{ asset('images/photo.webp') }}" type="image/webp">
                                <source srcset="{{ asset('images/photo.png') }}">
                                <img src="{{ asset('images/photo.png') }}" alt="" />
                            </picture>
                            <div class="stage-num">1</div>
                        </div>
                        <div class="stage-name">{{ trans('canvas.order_step1_title') }}</div>
                        <div class="stage-text">
                            {!! trans('canvas.order_step1_desc') !!}
                        </div>
                    </div>
                    <div class="stage-item">
                        <div class="stage-img">
                            <picture>
                                <source srcset="{{ asset('images/conversation.webp') }}" type="image/webp">
                                <source srcset="{{ asset('images/conversation.png') }}">
                                <img src="{{ asset('images/conversation.png') }}" alt="" />
                            </picture>
                            <div class="stage-num">2</div>
                        </div>
                        <div class="stage-name">{{ trans('canvas.order_step2_title') }}</div>
                        <div class="stage-text">
                            {!! trans('canvas.order_step2_desc') !!}
                        </div>
                    </div>
                    <div class="stage-item">
                        <div class="stage-img">
                            <picture>
                                <source srcset="{{ asset('images/portrait.webp') }}" type="image/webp">
                                <source srcset="{{ asset('images/portrait.png') }}">
                                <img src="{{ asset('images/portrait.png') }}" alt="" />
                            </picture>
                            <div class="stage-num">3</div>
                        </div>
                        <div class="stage-name">{{ trans('canvas.order_step3_title') }}</div>
                        <div class="stage-text">
                            {!! trans('canvas.order_step3_desc') !!}
                        </div>
                    </div>
                    <div class="stage-item">
                        <div class="stage-img">
                            <picture>
                                <source srcset="{{ asset('images/canvas.webp') }}" type="image/webp">
                                <source srcset="{{ asset('images/canvas.png') }}">
                                <img src="{{ asset('images/canvas.png') }}" alt="" />
                            </picture>
                            <div class="stage-num">4</div>
                        </div>
                        <div class="stage-name">{{ trans('canvas.order_step4_title') }}</div>
                        <div class="stage-text">
                            {!! trans('canvas.order_step4_desc') !!}
                        </div>
                    </div>
                    <div class="stage-item">
                        <div class="stage-img">
                            <picture>
                                <source srcset="{{ asset('images/delivery1.webp') }}" type="image/webp">
                                <source srcset="{{ asset('images/delivery1.png') }}">
                                <img src="{{ asset('images/delivery1.png') }}" alt="" />
                            </picture>
                            <div class="stage-num">5</div>
                        </div>
                        <div class="stage-name">{{ trans('canvas.order_step5_title') }}</div>
                        <div class="stage-text">
                            {!! trans('canvas.order_step5_desc') !!}
                        </div>
                    </div>
                </div>
                <div class="examples-arrow">
                    <a href="#" class="examples-prev examples-prev4 c-ex-prev" aria-label="examples prev">
                        <i class="fa-arrow-prev"></i>
                    </a>
                    <a href="#" class="examples-next examples-next4 c-ex-next" aria-label="examples next">
                        <i class="fa-arrow-next"></i>
                    </a>
                </div>
            </div>
            <div class="work-time">
                <div class="work-time__item">
                    <img src="{{ asset('images/work/work-4.svg') }}" alt="img" loading="lazy" />
                    <p>{!! trans('homepage_new.how_we_work_express') !!}</p>
                </div>
                <div class="work-time__item">
                    <img src="{{ asset('images/work/work-5.svg') }}" alt="img" loading="lazy" />
                    <p>{!! trans('homepage_new.how_we_work_standart') !!}</p>
                </div>
            </div>
            <form action="{{ route('send_all_styles_form') }}" method="POST" enctype="multipart/form-data" class="why-form submit-form portait-why-form">
                <input type="hidden" id="new_name" name="new_name" value="{{ $top_form->getTranslatedAttribute('f_title') }}">
                @csrf
                <div class="why-form__title h3_old">{{ $top_form->getTranslatedAttribute('f_title') }}</div>

                <div class="f__errs">
                    @if ($errors)
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    @endif
                </div>
                <div class="why-form__group">
                    <div class="kviz-input">
                        <p class="kviz-input__title">{!! $top_form->getTranslatedAttribute('f_email') !!}</p>
                        <div class="page-input__item">
                            <input type="email" name="email" placeholder="E-mail" required="" />
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
                    <div class="kviz-input">
                        <p class="kviz-input__title">{{ trans('portrait.form_add_comment') }}</p>
                        <div class="page-input__item">
                            <div class="kviz-textarea">
                                <textarea name="comments"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="kviz-input">
                        <p class="kviz-input__title">{{ trans('portrait.form_photo') }}</p>
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
                </div>
                <label class="why-sub"> <input type="submit" />{!! $bot_form['send'] !!}</label>

                <div class="loading-bar">
                    <span>
                        {{ trans('portrait_buy_form.loading') }}
                    </span>
                    <div class="l-progress">
                        <span></span><span></span><span></span><span></span><span></span><span></span>
                    </div>
                </div>
            </form>
            <div class="canvas-features">
                <div class="canvas-feature__image">
                    <picture>
                        <source media="(max-width: 550px)" srcset="{{ asset('images/canvas/advantagesMin.webp') }}" type="image/webp">
                        <source media="(max-width: 550px)" srcset="{{ asset('images/canvas/advantagesMin.png') }}" type="image/png">
                        <source srcset="{{ asset('images/canvas/advantages.webp') }}" type="image/webp">
                        <source srcset="{{ asset('images/canvas/advantages.png') }}" type="image/png">
                        <img src="{{ asset('images/canvas/advantages.png') }}" alt="">
                    </picture>
                </div>
                <div class="canvas-feature__content">
                    <div class="canvas-feature--title page-title">
                        {!! trans('canvas.photo_sec_title') !!}
                    </div>
                    <ul>
                        <li>
                            <img src="{{ asset('images/canvas/tree.svg') }}" alt="">
                            <p>{!! trans('canvas.photo_sec_step1') !!}</p>
                        </li>
                        <li>
                            <img src="{{ asset('images/canvas/leaf.svg') }}" alt="">
                            <p>{!! trans('canvas.photo_sec_step2') !!}</p>
                        </li>
                        <li>
                            <img src="{{ asset('images/canvas/brush.svg') }}" alt="">
                            <p>{!! trans('canvas.photo_sec_step3') !!}</p>
                        </li>
                        <li>
                            <img src="{{ asset('images/canvas/shield.svg') }}" alt="">
                            <p>{!! trans('canvas.photo_sec_step4') !!}</p>
                        </li>
                        <li>
                            <img src="{{ asset('images/canvas/picture.svg') }}" alt="">
                            <p>{!! trans('canvas.photo_sec_step5') !!}</p>
                        </li>
                        <li>
                            <img src="{{ asset('images/canvas/clock.svg') }}" alt="">
                            <p>{!! trans('canvas.photo_sec_step6') !!}</p>
                        </li>
                        <li>
                            <img src="{{ asset('images/canvas/package.svg') }}" alt="">
                            <p>{!! trans('canvas.photo_sec_step7') !!}</p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
