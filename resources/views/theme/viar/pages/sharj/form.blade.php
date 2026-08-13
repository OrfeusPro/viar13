<section class="sharj-form">
    <div class="section-frame">
        <div class="section-inner">
            <form data-portrait-by-photo-header="1" id="fastorder_form" action="{{ route('send_photo_portrait_form') }}"
                method="POST" enctype="multipart/form-data"
                class="vz-art popup-photo-canvas submit-form sharj-form__inner why-form">
                @csrf
                <input type="hidden" id="new_catid" name="new_catid" value="1">
                <input type="hidden" id="new_size"name="new_size" value="15x15">
                <input type="hidden" id="new_price" name="new_price" value="0">
                <input type="hidden" id="new_overall_price" name="overall_price" value="0">
                <input type="hidden" id="new_styles" name="styles" value="Sharj">
                <input type="hidden" id="new_count" name="count" name="styles" value="1">
                <input type="hidden" id="new_name" name="new_name" value="Шарж">



                <div class="why-form-inner">
                    <div class="why-form__title h3_old">
                        @lang('sharj.translate31')
                    </div>
                    <p class="why-form__subtitle">
                        @lang('sharj.translate32')
                    </p>
                    <div class="form-inner">
                        <img src="{{ asset(config('theme.current') . '/images/sharj/men.webp') }}" width="250"
                            height="525" alt="">

                        {{--                    <img src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/homer.webp') }}" width="250" height="525" alt=""> --}}
                        <div class="form-grid">
                            <div class="kviz-input">

                                <p class="kviz-input__title">
                                    @lang('simpson.simpson-form.kviz-input1.kviz-input__title')
                                </p>

                                <div class="page-input__item">
                                    <input type="email" name="email" placeholder="E-mail" required="">
                                    <svg class="kviz-input__icon">
                                        <use xlink:href=" {{ asset(env('THEME') . '/images') }}/sprite.svg#mail"></use>
                                    </svg>
                                </div>
                            </div>
                            <div class="kviz-input">

                                <p class="kviz-input__title">
                                    @lang('simpson.simpson-form.kviz-input2.kviz-input__title')
                                </p>

                                <div class="file-save">
                                    <div class="abs-close" style="display: none;">
                                        X
                                    </div>
                                    <div class="file-save__item js-file-preview" style="">
                                        <svg>
                                            <use xlink:href="{{ asset(config('theme.current') . '/sprite.svg#save') }}">
                                            </use>
                                        </svg>
                                        <div class="file-save__title">

                                            <p>
                                                @lang('simpson.load_image')
                                            </p>

                                            <span>
                                                @lang('simpson.click_to_add_photo')
                                            </span>

                                        </div>
                                    </div>
                                    <div class="file-save__item js-file-upload" style="display: none;">
                                        <svg>
                                            <use xlink:href="{{ asset(env('THEME') . 'sprite.svg#picture') }}"></use>
                                        </svg>
                                        <div class="file-save__title">
                                            <p>photo_34567.jpg</p>
                                            <span>2 Mb</span>
                                        </div>
                                    </div>
                                    <div class="file-save__item js-file-multiple" style="display: none;">
                                        <svg>
                                            <use xlink:href="{{ asset(env('THEME') . 'sprite.svg#check') }}"></use>
                                        </svg>

                                        <div class="file-save__title">
                                            <p class="file-title_green">
                                                @lang('portrait.form_files_loaded')
                                            </p>
                                        </div>

                                    </div>
                                    <input type="file" class="file-input file-input_save" name="file[]"
                                        multiple="" accept="image/*,image/heif,image/heic" aria-label="file input">
                                </div>
                            </div>

                            <div class="kviz-input">

                                <p class="kviz-input__title">
                                    @lang('simpson.simpson-form.kviz-input3.kviz-input__title')
                                </p>

                                <div class="page-input__item phone-input">
                                    <div class="banner__input-item">
                                        <input type="text" id="phone2" name="phone" class="banner__input phone"
                                            required>
                                        <img src="https://viarcanvas.com/img/icons/phone.svg" alt=""
                                            class="img-svg img-svg__posa">
                                    </div>
                                </div>
                            </div>

                            <div class="kviz-input">
                                <p class="kviz-input__title">
                                    @lang('simpson.simpson-form.kviz-input4.kviz-input__title')
                                </p>
                                <div class="page-input__item">
                                    <textarea name="comment"></textarea>
                                </div>
                            </div>

                            <button type="submit" class="default-btn">
                                @lang('stock.submit')
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
