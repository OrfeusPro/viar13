

<div class="popup-wrapper">
    <div class="popup-item calculator-block simpson-calculator">

        <form class="formalization simpson-formalization">
            <div class="formalization__block">
                <div class="formalization__block--top">
                    <div class="formalization__block--top-inner">
                        <div class="simpson-titleBlock">

                            @lang("simpson.popup-wrapper.simpson-titleBlock")

                        </div>

                        <div class="formalization__subrow">

                            <div class="text">

                                @lang("simpson.popup-wrapper.formalization__subrow.text")



                                <svg width="115" height="52" viewBox="0 0 115 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M114.231 40.4564C114.336 40.2012 114.215 39.9088 113.96 39.8032L109.801 38.0829C109.546 37.9774 109.254 38.0986 109.148 38.3538C109.043 38.609 109.164 38.9014 109.419 39.007L113.115 40.5361L111.586 44.2323C111.481 44.4875 111.602 44.7799 111.857 44.8855C112.112 44.991 112.405 44.8698 112.51 44.6146L114.231 40.4564ZM1.00022 0.530259C0.51664 0.657361 0.516794 0.657944 0.517027 0.658829C0.517211 0.659522 0.517523 0.660702 0.517889 0.662081C0.518621 0.664839 0.519669 0.668776 0.521038 0.673883C0.523775 0.684097 0.527792 0.698984 0.533113 0.718457C0.543755 0.757397 0.559612 0.814662 0.580881 0.889512C0.623422 1.03922 0.687614 1.25928 0.77503 1.54375C0.949857 2.11269 1.21761 2.93941 1.59086 3.9764C2.33729 6.05016 3.50609 8.96614 5.19808 12.3438C8.5809 19.0967 14.0621 27.7089 22.453 35.1217C30.8486 42.5387 42.1519 48.7497 57.1588 50.6954C72.1616 52.6406 90.832 50.3186 113.96 40.7271L113.577 39.8034C90.5712 49.3442 72.0787 51.6215 57.2873 49.7037C42.5 47.7864 31.3781 41.6721 23.115 34.3723C14.8473 27.0683 9.43659 18.5721 6.09217 11.8959C4.42055 8.55891 3.2669 5.68011 2.53177 3.63774C2.16424 2.61666 1.90145 1.80498 1.73092 1.25003C1.64566 0.972555 1.58347 0.759299 1.5428 0.616175C1.52246 0.544613 1.50751 0.49059 1.49774 0.454837C1.49286 0.436968 1.48927 0.423657 1.48695 0.415013C1.48579 0.410694 1.48495 0.407539 1.48443 0.405555C1.48416 0.404567 1.48401 0.403969 1.48387 0.403468C1.48379 0.403165 1.48379 0.403158 1.00022 0.530259Z" fill="#FA7846"/>
                                </svg>
                            </div>

                            <picture>
                                <!-- <source media="(max-width: 576px)" srcset="images/sharj/new/subRow1Min.webp" type="image/webp"> -->
                                <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/subRow2.webp') }}" type="image/webp">
                                <img width="194" height="236" src="{{ asset(config('theme.current') . '/images/sharj/new/subRow2.webp') }}" alt="">
                            </picture>
                        </div>


                        <div class="formalization__col">
                            <div class="formalization-items">
                                <div class="formalization-item">
                                    <div class="formalization-box">
                                        <div class="formalization-tab">
                                            <img width="25" height="25" src="{{ asset(config('theme.current') . '/images/sharj/new/format4.svg') }}" alt="">

                                            @lang("simpson.popup-wrapper.formalization-item1.formalization-tab")


                                            <span class="tab-icon"></span>
                                        </div>
                                        <div class="formalization-content">
                                            <div class="formalization-content--inner">
                                                <div class="kviz-row full-width">
                                                    <div class="
                                  kviz-group__item
                                  kviz-c-group kviz-custom-flexcol
                                ">
                                                        <div class="
                                    kviz-radio
                                    js-checkbox
                                    kviz-radio_active
                                    kviz-types
                                  " data-stock="1" data-count="1">
                                                            <em class="check"></em>
                                                            <label>

                                                                @lang("simpson.popup-wrapper.formalization-item1.label1")


                                                                <input type="radio" name="count" value="Personal" />
                                                            </label>
                                                        </div>

                                                        <div class="kviz-radio js-checkbox kviz-types" data-stock="2" data-count="2">
                                                            <em class="check"></em>
                                                            <label>

                                                                @lang("simpson.popup-wrapper.formalization-item1.label2")



                                                                <input type="radio" name="count" value="Group" />
                                                            </label>
                                                        </div>


                                                        <div class="input-group input_disabled">

                                                            <label for="count1">
                                                                @lang("simpson.popup-wrapper.formalization-item1.count_for_count1")

                                                            </label>

                                                            <input id="count1" class="kviz-input" type="text" disabled />
                                                        </div>

                                                    </div>
                                                    <div class="
                                  kviz-group__item
                                  kviz-c-group
                                  input_disabled
                                ">
                                                        <div class="kviz-radio js-checkbox kviz-types" data-stock="1">
                                                            <em class="check"></em>
                                                            <label>
                                                                <span>
                                                                    @lang("simpson.formalization-item1.kviz-radio1.span")
                                                                </span>

                                                                <input type="radio" name="size" value="60x90 см - 65€ 42€" disabled />
                                                            </label>
                                                        </div>

                                                        <div class="kviz-radio js-checkbox kviz-types" data-stock="2">
                                                            <em class="check"></em>
                                                            <label>
                                                                <span>
                                                                    @lang("simpson.formalization-item1.kviz-radio2.span")
                                                                </span>

                                                                <input type="radio" name="size" value="100x70 см - 75€ 52€" disabled />
                                                            </label>
                                                        </div>


                                                        <div class="kviz-radio js-checkbox kviz-types" data-stock="1">
                                                            <em class="check"></em>
                                                            <label>
                                                                <span>
                                                                     @lang("simpson.formalization-item1.kviz-radio3.span")
                                                                </span>

                                                                <input type="radio" name="size" value="120x80 см - 88€ 60€" disabled />

                                                            </label>
                                                        </div>


                                                        <div class="kviz-radio js-checkbox kviz-types" data-stock="2">
                                                            <em class="check"></em>
                                                            <label>
                                                                <span>
                                                                    @lang("simpson.formalization-item1.kviz-radio4.span")
                                                                </span>

                                                                <input type="radio" name="size" value="140x80 см - 88€ 60€" disabled />
                                                            </label>
                                                        </div>


                                                        <div class="kviz-radio js-checkbox kviz-types" data-stock="2">
                                                            <em class="check"></em>
                                                            <label>
                                                                <span>
                                                                    @lang("simpson.formalization-item1.kviz-radio5.span")
                                                                </span>

                                                                <input type="radio" name="size" value="100x70 см - 75€ 52€" disabled />
                                                            </label>
                                                        </div>


                                                        <div class="kviz-radio js-checkbox kviz-types" data-stock="1">
                                                            <em class="check"></em>
                                                            <label>
                                                                <span>
                                                                    @lang("simpson.formalization-item1.kviz-radio6.span")
                                                                </span>

                                                                <input type="radio" name="size" value="120x80 см - 88€ 60€" disabled />
                                                            </label>
                                                        </div>


                                                        <div class="kviz-radio js-checkbox kviz-types" data-stock="2">
                                                            <em class="check"></em>
                                                            <label>
                                                                <span>
                                                                    @lang("simpson.formalization-item1.kviz-radio7.span")
                                                                </span>

                                                                <input type="radio" name="size" value="140x80 см - 88€ 60€" disabled />

                                                            </label>
                                                        </div>


                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="formalization-prompt">
                                        <div class="formalization-prompt--wrapper">
                                            <div class="formalization-prompt--inner">
                                                <img width="56" height="58" src="{{ asset(config('theme.current') . '/images/sharj/new/ficon4.webp') }}" alt="" />

                                                @lang("simpson.formalization-item1.formalization-prompt--inner")

                                            </div>
                                        </div>
                                    </div>

                                </div>



                                <div class="formalization-item">   <!--formalization-item2--->
                                    <div class="formalization-box">
                                        <div class="formalization-tab">
                                            <img width="25" height="25" src="{{ asset(config('theme.current') . '/images/sharj/new/format1.svg') }}" alt="">

                                            @lang("simpson.popup-wrapper.formalization-item2.formalization-tab")

                                            <span class="tab-icon"></span>
                                        </div>
                                        <div class="formalization-content">
                                            <div class="formalization-content--inner">
                                                <div class="kviz-c-group kviz-full">
                                                    <div class="kviz-group__item kviz-grid">

                                                        <div class="kviz-radio js-checkbox kviz-radio_active">
                                                            <em class="check"></em>
                                                            <label>

                                                                <span>
                                                                    @lang("simpson.popup-wrapper.formalization-item2.kviz-radio1.span")
                                                                </span>

                                                                <input type="radio" name="types" value="Эконом (плотность 280г/м2)">
                                                                <picture class="kviz-image">
                                                                    <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/simpsonHolst.webp') }}" type="image/webp">
                                                                    <img width="112" height="119" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/simpsonHolst.webp') }}" alt="">
                                                                </picture>
                                                            </label>
                                                        </div>

                                                        <div class="kviz-radio js-checkbox">
                                                            <em class="check"></em>
                                                            <label>

                                                                <span>
                                                                    @lang("simpson.popup-wrapper.formalization-item2.kviz-radio2.span")
                                                                </span>

                                                                <input type="radio" name="types" value="Эконом (плотность 280г/м2)">
                                                                <picture class="kviz-image">
                                                                    <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/simpson/simpsonPaper.webp') }}" type="image/webp">
                                                                    <img width="112" height="119" src="{{ asset(config('theme.current') . '/images/sharj/new/simpson/simpsonPaper.webp') }}" alt="">
                                                                </picture>
                                                            </label>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="formalization-prompt">
                                        <div class="formalization-prompt--wrapper">
                                            <div class="formalization-prompt--inner">
                                                <img width="56" height="58" src="{{ asset(config('theme.current') . '/images/sharj/new/ficon1.webp') }}" alt="" />

                                                <p>
                                                    @lang("simpson.popup-wrapper.formalization-item2.formalization-prompt--inner.p")
                                                </p>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="formalization-item">  <!---formalization-item3--->
                                    <div class="formalization-box">
                                        <div class="formalization-tab">
                                            <img width="25" height="25" src="{{ asset(config('theme.current') . '/images/sharj/new/format2.svg') }}" alt="">

                                            @lang("simpson.popup-wrapper.formalization-item3.formalization-tab")

                                            <span class="tab-icon"></span>
                                        </div>
                                        <div class="formalization-content">
                                            <div class="formalization-content--inner">
                                                <div class="vz-art kviz-input">
                                                    <p class="vz-art kviz-input__title">
                                                        @lang("simpson.popup-wrapper.formalization-item3.kviz-input__title")
                                                    </p>
                                                    <div class="file-save file-save__popup">
                                                        <div class="abs-close"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x"><line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/></svg></div>
                                                        <div class="file-save__item js-file-preview">
                                                            <svg>
                                                                <use xlink:href="{{ asset(config('theme.current') . '/sprite.svg#save') }}"></use>
                                                            </svg>
                                                            <div class="file-save__title">

                                                                <p>
                                                                    @lang("simpson.load_image")
                                                                </p>

                                                                <span>
                                                                    @lang("simpson.click_to_add_photo")
                                                                </span>

                                                            </div>
                                                        </div>
                                                        <div class="file-save__item js-file-upload">
                                                            <svg>
                                                                <use xlink:href="sprite.svg#picture"></use>
                                                            </svg>
                                                            <div class="file-save__title">
                                                                <p>photo_34567.jpg</p>
                                                                <span>2 Mb</span>
                                                            </div>
                                                        </div>
                                                        <div class="file-save__item js-file-multiple">
                                                            <svg>
                                                                <use xlink:href="sprite.svg#check"></use>
                                                            </svg>
                                                            <div class="file-save__title">
                                                                <p class="file-title_green">
                                                                    @lang("simpson.popup-wrapper.formalization-item3.file-title_green")
                                                                    Файлы загружены
                                                                </p>

                                                            </div>
                                                        </div>
                                                        <input type="file" class="file-input" name="file[]" multiple="" accept="image/*,image/heif,image/heic" aria-label="file input">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="images-container"></div>
                                        </div>
                                    </div>
                                    <div class="formalization-prompt">
                                        <div class="formalization-prompt--wrapper">
                                            <div class="formalization-prompt--inner">
                                                <img width="56" height="58" src="{{ asset(config('theme.current') . '/images/sharj/new/ficon2.webp') }}" alt="" />

                                                <p>
                                                    @lang("simpson.popup-wrapper.formalization-item3.formalization-prompt--inner.p")
                                                </p>

                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div class="formalization-item">   <!--formalization-item4--->
                                    <div class="formalization-box">
                                        <div class="formalization-tab">
                                            <img width="25" height="25" src="{{ asset(config('theme.current') . '/images/sharj/new/format3.svg') }}" alt="">

                                            @lang("simpson.popup-wrapper.formalization-item4.formalization-tab")

                                            <span class="tab-icon"></span>
                                        </div>
                                        <div class="formalization-content">
                                            <div class="formalization-content--inner">
                                                <p class="underline-text sizesPopup-js">Посмотреть как портреты выглядят в руках</p>
                                                <div class="kviz-row kviz-c-group full-width">
                                                    <div class="kviz-group__item">

                                                        <div class="kviz-radio js-checkbox kviz-radio_active" data-stock="1">
                                                            <em class="check"></em>
                                                            <label>
                                                                <span>
                                                                    @lang("simpson.popup-wrapper.formalization-item4.kviz-radio1.span")
                                                                </span>

                                                                <input type="radio" name="size" value="30x40 см - 25€ 15€" />
                                                            </label>
                                                        </div>


                                                        <div class="kviz-radio js-checkbox" data-stock="2">
                                                            <em class="check"></em>
                                                            <label>
                                                                <span>
                                                                    @lang("simpson.popup-wrapper.formalization-item4.kviz-radio2.span")
                                                                </span>

                                                                <input type="radio" name="size" value="40x60 см - 30€ 20€" />
                                                            </label>
                                                        </div>


                                                        <div class="kviz-radio js-checkbox" data-stock="1">
                                                            <em class="check"></em>
                                                            <label>
                                                                <span>
                                                                    @lang("simpson.popup-wrapper.formalization-item4.kviz-radio3.span")
                                                                </span>

                                                                <input type="radio" name="size" value="50x70 см - 37€ 27€" />
                                                            </label>
                                                        </div>


                                                        <div class="kviz-radio js-checkbox" data-stock="2">
                                                            <em class="check"></em>
                                                            <label>
                                                                <span>
                                                                    @lang("simpson.popup-wrapper.formalization-item4.kviz-radio4.span")
                                                                </span>

                                                                <input type="radio" name="size" value="55x80 см - 50€ 37€" />
                                                            </label>
                                                        </div>

                                                    </div>


                                                    <div class="kviz-group__item">

                                                        <div class="kviz-radio js-checkbox" data-stock="1">
                                                            <em class="check"></em>
                                                            <label>
                                                                <span>
                                                                    @lang("simpson.popup-wrapper.formalization-item4.kviz-radio5.span")
                                                                </span>

                                                                <input type="radio" name="size" value="60x90 см - 65€ 42€" />
                                                            </label>
                                                        </div>


                                                        <div class="kviz-radio js-checkbox" data-stock="2">
                                                            <em class="check"></em>
                                                            <label>
                                                                <span>
                                                                    @lang("simpson.popup-wrapper.formalization-item4.kviz-radio6.span")
                                                                </span>

                                                                <input type="radio" name="size" value="100x70 см - 75€ 52€" />
                                                            </label>
                                                        </div>


                                                        <div class="kviz-radio js-checkbox" data-stock="1">
                                                            <em class="check"></em>
                                                            <label>
                                                                <span>
                                                                    @lang("simpson.popup-wrapper.formalization-item4.kviz-radio7.span")
                                                                </span>

                                                                <input type="radio" name="size" value="120x80 см - 88€ 60€" />
                                                            </label>
                                                        </div>


                                                        <div class="kviz-radio js-checkbox" data-stock="2">
                                                            <em class="check"></em>
                                                            <label>
                                                                <span>
                                                                    @lang("simpson.popup-wrapper.formalization-item4.kviz-radio8.span")
                                                                </span>

                                                                <input type="radio" name="size" value="140x80 см - 88€ 60€" />
                                                            </label>
                                                        </div>


                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="formalization-prompt">
                                        <div class="formalization-prompt--wrapper">
                                            <div class="formalization-prompt--inner">
                                                <img width="56" height="58" src="{{ asset(config('theme.current') . '/images/sharj/new/ficon3.webp') }}" alt="" />

                                                <p>
                                                    @lang("simpson.popup-wrapper.formalization-item4.formalization-prompt--inner.p")
                                                </p>

                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div class="formalization-item opened">   <!---formalization-item5--->
                                    <div class="formalization-box">
                                        <div class="formalization-tab">
                                            <img width="25" height="25" src="{{ asset(config('theme.current') . '/images/sharj/new/format5.svg') }}" alt="">

                                            @lang("simpson.popup-wrapper.formalization-item5.formalization-tab")

                                            <span class="tab-icon"></span>
                                        </div>
                                        <div class="formalization-content">
                                            <div class="formalization-content--inner no-padding">
                                                <div class="frame-outerContainer">
                                                    <div class="frame-trigger">

                                                        <div class="frame-trigger-tab kviz-radio_active">
                                                            <em class="check"></em>

                                                            @lang("simpson.popup-wrapper.formalization-item5.frame-trigger-tab1")

                                                        </div>

                                                        <div class="frame-trigger-tab">
                                                            <em class="check"></em>

                                                            @lang("simpson.popup-wrapper.formalization-item5.frame-trigger-tab2")

                                                        </div>

                                                    </div>
                                                    <div class="frame-blocks">
                                                        <div class="frame-wrapper">

                                                            <div class="frame-info__row">

                                                                @lang("simpson.popup-wrapper.formalization-item5.frame-wrapper1.frame-info__row")

                                                            </div>




                                                            <div class="frame-block">
                                                                <div class="frames-list">
                                                                    <div class="frame-item frame-standard active" data-code="18" data-price="20" data-src="images/sharj/new/ram1.webp') }}" data-width="10">
                                                                        <picture>
                                                                            <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/ram1.webp') }}" type="image/jpeg">
                                                                            <img width="150" height="150" src="{{ asset(config('theme.current') . '/images/sharj/new/ram1.webp') }}" alt="Viar" loading="lazy">
                                                                        </picture>
                                                                        <div class="frame-info">
                                                                            <ul>
                                                                                <li>
                                                                                    <svg width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                        <g clip-path="url(#clip0_261_1144)">
                                                                                            <path d="M7.90987 1.17996C7.78973 1.0598 7.5949 1.0598 7.47473 1.17996L2.48706 6.16764L0.525259 4.20583C0.405118 4.08565 0.210289 4.08565 0.0901174 4.20583C-0.0300391 4.32598 -0.0300391 4.5208 0.0901174 4.64097L2.26951 6.82036C2.38962 6.94047 2.58451 6.9405 2.70465 6.82036L7.90987 1.6151C8.03002 1.49493 8.03002 1.30012 7.90987 1.17996Z" fill="#1F9750"></path>
                                                                                        </g>
                                                                                        <defs>
                                                                                            <clipPath id="clip0_261_1144">
                                                                                                <rect width="8" height="8" fill="white"></rect>
                                                                                            </clipPath>
                                                                                        </defs>
                                                                                    </svg>

                                                                                    <span>@lang('cart_new.in_stock')</span>
                                                                                </li>

                                                                                <li>
                                                                                    <span>@lang("gallery.code"):</span>
                                                                                    <span>18</span>
                                                                                </li>

                                                                                    <li>
                                                                                        <span>@lang("gallery.material"):</span>
                                                                                        <span>Дерево</span>
                                                                                    </li>


                                                                                    <li>
                                                                                        <span>@lang("gallery.shade"):</span>
                                                                                        <span>Золотий</span>
                                                                                    </li>

                                                                                <li>
                                                                                    <span>@lang("modular_pictures.index28"):</span>
                                                                                    <span>5.2 cm</span>
                                                                                </li>

                                                                                <li>
                                                                                    <span>@lang("modular_pictures.index30"):</span>
                                                                                    <span>5.2 cm</span>
                                                                                </li>

                                                                            </ul>




                                                                        </div>


                                                                        <a href="#" class="mf-popup frame-js-popup">
                                                                            @lang("gl.read_more")
                                                                        </a>


                                                                        <div class="frame-bottom">
                                                                            <div class="frame-radio"></div>

                                                                            <p>
                                                                                @lang("gallery.color")

                                                                                <span class="orange">
                                                                                    Черный
                                                                                </span>

                                                                            </p>

                                                                        </div>

                                                                    </div>
                                                                    <div class="frame-item frame-standard" data-code="18" data-price="20" data-src="images/sharj/new/ram1.webp') }}" data-width="10">
                                                                        <picture>
                                                                            <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/ram1.webp') }}" type="image/jpeg">
                                                                            <img width="150" height="150" src="images/sharj/new/ram1.webp') }}" alt="Viar" loading="lazy">
                                                                        </picture>
                                                                        <div class="frame-info">
                                                                            <ul>
                                                                                <li>
                                                                                    <svg width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                        <g clip-path="url(#clip0_261_1144)">
                                                                                            <path d="M7.90987 1.17996C7.78973 1.0598 7.5949 1.0598 7.47473 1.17996L2.48706 6.16764L0.525259 4.20583C0.405118 4.08565 0.210289 4.08565 0.0901174 4.20583C-0.0300391 4.32598 -0.0300391 4.5208 0.0901174 4.64097L2.26951 6.82036C2.38962 6.94047 2.58451 6.9405 2.70465 6.82036L7.90987 1.6151C8.03002 1.49493 8.03002 1.30012 7.90987 1.17996Z" fill="#1F9750"></path>
                                                                                        </g>
                                                                                        <defs>
                                                                                            <clipPath id="clip0_261_1144">
                                                                                                <rect width="8" height="8" fill="white"></rect>
                                                                                            </clipPath>
                                                                                        </defs>
                                                                                    </svg>



                                                                                    <span>@lang('cart_new.in_stock')</span>
                                                                                </li>

                                                                                <li>
                                                                                    <span>@lang("gallery.code"):</span>
                                                                                    <span>18</span>
                                                                                </li>

                                                                                <li>
                                                                                    <span>@lang("gallery.material"):</span>
                                                                                    <span>Дерево</span>
                                                                                </li>


                                                                                <li>
                                                                                    <span>@lang("gallery.shade"):</span>
                                                                                    <span>Золотий</span>
                                                                                </li>

                                                                                <li>
                                                                                    <span>@lang("modular_pictures.index28"):</span>
                                                                                    <span>5.2 cm</span>
                                                                                </li>

                                                                                <li>
                                                                                    <span>@lang("modular_pictures.index30"):</span>
                                                                                    <span>5.2 cm</span>
                                                                                </li>

                                                                            </ul>



                                                                        </div>



                                                                        <a href="#" class="mf-popup frame-js-popup">
                                                                            @lang("gl.read_more")
                                                                        </a>


                                                                        <div class="frame-bottom">
                                                                            <div class="frame-radio"></div>

                                                                            <p>
                                                                                @lang("gallery.color")

                                                                                <span class="orange">
                                                                                    Черный
                                                                                </span>

                                                                            </p>

                                                                        </div>




                                                                    </div>
                                                                    <div class="frame-item frame-standard" data-code="18" data-price="20" data-src="images/sharj/new/ram1.webp" data-width="10">
                                                                        <picture>
                                                                            <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/ram1.webp') }}" type="image/jpeg">
                                                                            <img width="150" height="150" src="{{ asset(config('theme.current') . '/images/sharj/new/ram1.webp') }}" alt="Viar" loading="lazy">
                                                                        </picture>
                                                                        <div class="frame-info">
                                                                            <ul>
                                                                                <li>
                                                                                    <svg width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                        <g clip-path="url(#clip0_261_1144)">
                                                                                            <path d="M7.90987 1.17996C7.78973 1.0598 7.5949 1.0598 7.47473 1.17996L2.48706 6.16764L0.525259 4.20583C0.405118 4.08565 0.210289 4.08565 0.0901174 4.20583C-0.0300391 4.32598 -0.0300391 4.5208 0.0901174 4.64097L2.26951 6.82036C2.38962 6.94047 2.58451 6.9405 2.70465 6.82036L7.90987 1.6151C8.03002 1.49493 8.03002 1.30012 7.90987 1.17996Z" fill="#1F9750"></path>
                                                                                        </g>
                                                                                        <defs>
                                                                                            <clipPath id="clip0_261_1144">
                                                                                                <rect width="8" height="8" fill="white"></rect>
                                                                                            </clipPath>
                                                                                        </defs>
                                                                                    </svg>



                                                                                    <span>@lang('cart_new.in_stock')</span>
                                                                                </li>

                                                                                <li>
                                                                                    <span>@lang("gallery.code"):</span>
                                                                                    <span>18</span>
                                                                                </li>

                                                                                <li>
                                                                                    <span>@lang("gallery.material"):</span>
                                                                                    <span>Дерево</span>
                                                                                </li>


                                                                                <li>
                                                                                    <span>@lang("gallery.shade"):</span>
                                                                                    <span>Золотий</span>
                                                                                </li>

                                                                                <li>
                                                                                    <span>@lang("modular_pictures.index28"):</span>
                                                                                    <span>5.2 cm</span>
                                                                                </li>

                                                                                <li>
                                                                                    <span>@lang("modular_pictures.index30"):</span>
                                                                                    <span>5.2 cm</span>
                                                                                </li>

                                                                            </ul>



                                                                        </div>


                                                                        <a href="#" class="mf-popup frame-js-popup">
                                                                            @lang("gl.read_more")
                                                                        </a>


                                                                        <div class="frame-bottom">
                                                                            <div class="frame-radio"></div>

                                                                            <p>
                                                                                @lang("gallery.color")

                                                                                <span class="orange">
                                                                                    Черный
                                                                                </span>

                                                                            </p>

                                                                        </div>



                                                                    </div>
                                                                    <div class="frame-item frame-standard" data-code="18" data-price="20" data-src="images/sharj/new/ram1.webp') }}" data-width="10">
                                                                        <picture>
                                                                            <source srcset="{{ asset(config('theme.current') . '/images/sharj/new/ram1.webp') }}" type="image/jpeg">
                                                                            <img width="150" height="150" src="{{ asset(config('theme.current') . '/images/sharj/new/ram1.webp') }}" alt="Viar" loading="lazy">
                                                                        </picture>
                                                                        <div class="frame-info">
                                                                            <ul>
                                                                                <li>
                                                                                    <svg width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                        <g clip-path="url(#clip0_261_1144)">
                                                                                            <path d="M7.90987 1.17996C7.78973 1.0598 7.5949 1.0598 7.47473 1.17996L2.48706 6.16764L0.525259 4.20583C0.405118 4.08565 0.210289 4.08565 0.0901174 4.20583C-0.0300391 4.32598 -0.0300391 4.5208 0.0901174 4.64097L2.26951 6.82036C2.38962 6.94047 2.58451 6.9405 2.70465 6.82036L7.90987 1.6151C8.03002 1.49493 8.03002 1.30012 7.90987 1.17996Z" fill="#1F9750"></path>
                                                                                        </g>
                                                                                        <defs>
                                                                                            <clipPath id="clip0_261_1144">
                                                                                                <rect width="8" height="8" fill="white"></rect>
                                                                                            </clipPath>
                                                                                        </defs>
                                                                                    </svg>



                                                                                    <span>@lang('cart_new.in_stock')</span>
                                                                                </li>

                                                                                <li>
                                                                                    <span>@lang("gallery.code"):</span>
                                                                                    <span>18</span>
                                                                                </li>

                                                                                <li>
                                                                                    <span>@lang("gallery.material"):</span>
                                                                                    <span>Дерево</span>
                                                                                </li>


                                                                                <li>
                                                                                    <span>@lang("gallery.shade"):</span>
                                                                                    <span>Золотий</span>
                                                                                </li>

                                                                                <li>
                                                                                    <span>@lang("modular_pictures.index28"):</span>
                                                                                    <span>5.2 cm</span>
                                                                                </li>

                                                                                <li>
                                                                                    <span>@lang("modular_pictures.index30"):</span>
                                                                                    <span>5.2 cm</span>
                                                                                </li>

                                                                            </ul>



                                                                        </div>

                                                                        <a href="#" class="mf-popup frame-js-popup">
                                                                            @lang("gl.read_more")
                                                                        </a>

                                                                        <div class="frame-bottom">
                                                                            <div class="frame-radio"></div>
                                                                            <p>
                                                                                @lang("gallery.color"):

                                                                                <span class="orange">Черный</span>

                                                                            </p>

                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="frame-wrapper hidden-block">
                                                            <div class="rc-filter-box">
                                                                <div class="filter-item mc-js-filter">
                                                                    <a href="#">

                                                                        <span>
                                                                            @lang("gallery.color")
                                                                        </span>

                                                                        <svg width="13" height="8" viewBox="0 0 13 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                            <path d="M12.2184 0.137457C12.1316 0.0458193 12.0317 4.23244e-08 11.9188 4.72591e-08C11.8059 5.21938e-08 11.7061 0.0458193 11.6192 0.137457L6.5 5.53952L1.38076 0.137458C1.29392 0.0458198 1.19405 5.16054e-07 1.08116 5.20989e-07C0.968269 5.25924e-07 0.868403 0.0458198 0.781562 0.137458L0.130261 0.824744C0.0434205 0.916381 -3.05028e-07 1.02176 -2.99821e-07 1.14089C-2.94614e-07 1.26002 0.0434205 1.36541 0.130261 1.45705L6.2004 7.86254C6.28724 7.95418 6.38711 8 6.5 8C6.61289 8 6.71276 7.95418 6.7996 7.86254L12.8697 1.45705C12.9566 1.36541 13 1.26002 13 1.14089C13 1.02176 12.9566 0.916381 12.8697 0.824743L12.2184 0.137457Z" fill="#FC8C5F"></path>
                                                                        </svg>
                                                                    </a>
                                                                    <div class="filter-item--wrapper filter-color">

                                                                        <p>
                                                                            @lang("gallery.select_color"):
                                                                        </p>

                                                                        <ul class="color-grid" id="ram_colors">
                                                                            <li data-ramcolorid="1" style="background: #d4b725;"></li>
                                                                            <li data-ramcolorid="2" style="background: #ffea00;"></li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                                <div class="filter-item mc-js-filter">
                                                                    <a href="#">
                                                                        <span>
                                                                            @lang("gallery.material")
                                                                        </span>

                                                                        <svg width="13" height="8" viewBox="0 0 13 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                            <path d="M12.2184 0.137457C12.1316 0.0458193 12.0317 4.23244e-08 11.9188 4.72591e-08C11.8059 5.21938e-08 11.7061 0.0458193 11.6192 0.137457L6.5 5.53952L1.38076 0.137458C1.29392 0.0458198 1.19405 5.16054e-07 1.08116 5.20989e-07C0.968269 5.25924e-07 0.868403 0.0458198 0.781562 0.137458L0.130261 0.824744C0.0434205 0.916381 -3.05028e-07 1.02176 -2.99821e-07 1.14089C-2.94614e-07 1.26002 0.0434205 1.36541 0.130261 1.45705L6.2004 7.86254C6.28724 7.95418 6.38711 8 6.5 8C6.61289 8 6.71276 7.95418 6.7996 7.86254L12.8697 1.45705C12.9566 1.36541 13 1.26002 13 1.14089C13 1.02176 12.9566 0.916381 12.8697 0.824743L12.2184 0.137457Z" fill="#FC8C5F"></path>
                                                                        </svg>
                                                                    </a>

                                                                    <div class="filter-item--wrapper">

                                                                        <p>
                                                                            @lang("gallery.select_material")
                                                                        </p>

                                                                        <ul class="filter-material" id="ram_materials">
                                                                            <li data-rammaterialid="1">
                                                                                <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                    <g class="clip0_309_246">
                                                                                        <path d="M14.8315 2.21218C14.6062 1.98689 14.2409 1.98689 14.0156 2.21218L4.66372 11.5641L0.985349 7.88568C0.760084 7.66036 0.394781 7.66036 0.169458 7.88568C-0.0558351 8.11097 -0.0558351 8.47625 0.169458 8.70157L4.25582 12.7879C4.48103 13.0131 4.84645 13.0132 5.07171 12.7879L14.8315 3.02807C15.0568 2.80275 15.0568 2.43748 14.8315 2.21218Z" fill="#FA7846"></path>
                                                                                    </g>
                                                                                    <defs>
                                                                                        <clipPath class="clip0_309_246">
                                                                                            <rect width="15" height="15" fill="white"></rect>
                                                                                        </clipPath>
                                                                                    </defs>
                                                                                </svg>

                                                                                <span>
                                                                                    @lang("simpson.plastic")
                                                                                </span>

                                                                            </li>
                                                                            <li data-rammaterialid="2">
                                                                                <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                    <g class="clip0_309_246">
                                                                                        <path d="M14.8315 2.21218C14.6062 1.98689 14.2409 1.98689 14.0156 2.21218L4.66372 11.5641L0.985349 7.88568C0.760084 7.66036 0.394781 7.66036 0.169458 7.88568C-0.0558351 8.11097 -0.0558351 8.47625 0.169458 8.70157L4.25582 12.7879C4.48103 13.0131 4.84645 13.0132 5.07171 12.7879L14.8315 3.02807C15.0568 2.80275 15.0568 2.43748 14.8315 2.21218Z" fill="#FA7846"></path>
                                                                                    </g>
                                                                                    <defs>
                                                                                        <clipPath class="clip0_309_246">
                                                                                            <rect width="15" height="15" fill="white"></rect>
                                                                                        </clipPath>
                                                                                    </defs>
                                                                                </svg>

                                                                                <span>
                                                                                    @lang("simpson.wood")
                                                                                </span>

                                                                            </li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="rc-row">
                                                                <div class="rc-f-selected-container">
                                                                    <div class="mc-f-selected" data-id="1">

                                                                        <div>
                                                                            @lang("simpson.gold")
                                                                        </div>

                                                                        <svg width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                            <rect width="10.4429" height="0.870241" transform="matrix(0.707093 -0.707121 0.707093 0.707121 0.00146484 7.38428)" fill="#1E2533"></rect>
                                                                            <rect width="10.4429" height="0.870241" transform="matrix(-0.707092 -0.707121 0.707092 -0.707121 7.38428 8)" fill="#1E2533"></rect>
                                                                        </svg>
                                                                    </div>
                                                                    <div class="mc-f-selected" data-id="2">

                                                                        <div>
                                                                            @lang("simpson.wood")
                                                                        </div>

                                                                        <svg width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                            <rect width="10.4429" height="0.870241" transform="matrix(0.707093 -0.707121 0.707093 0.707121 0.00146484 7.38428)" fill="#1E2533"></rect>
                                                                            <rect width="10.4429" height="0.870241" transform="matrix(-0.707092 -0.707121 0.707092 -0.707121 7.38428 8)" fill="#1E2533"></rect>
                                                                        </svg>
                                                                    </div>
                                                                </div>
                                                                <div class="mc-search">
                                                                    <div class="mc-input">
                                                                        <input id="ram_search" data-route="https://viarcanvas.com/get/ram_search" type="search" name="search_id" placeholder="Поиск по артикулу">
                                                                    </div>
                                                                    <button>
                                                                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                            <g clip-path="url(#clip0_461_7169)">
                                                                                <path d="M18 17.2046L11.8996 11.1042C12.8959 9.92765 13.5 8.40895 13.5 6.75001C13.5 3.02783 10.4722 0 6.75001 0C3.02786 0 0 3.02783 0 6.75001C0 10.4722 3.02783 13.5 6.75001 13.5C8.40895 13.5 9.92765 12.8959 11.1042 11.8996L17.2046 18L18 17.2046ZM6.75001 12.375C3.64857 12.375 1.12501 9.85145 1.12501 6.75001C1.12501 3.64857 3.64857 1.12501 6.75001 1.12501C9.85145 1.12501 12.375 3.64857 12.375 6.75001C12.375 9.85145 9.85145 12.375 6.75001 12.375Z" fill="white"></path>
                                                                            </g>
                                                                            <defs>
                                                                                <clipPath id="clip0_461_7169">
                                                                                    <rect width="18" height="18" fill="white"></rect>
                                                                                </clipPath>
                                                                            </defs>
                                                                        </svg>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <div class="frame-block">
                                                                <div class="frames-list">
                                                                    <div class="frame-item" data-code="17" data-price="10" data-src="https://viarcanvas.com/storage/canvas-rams/January2023/ECxyswCt5jI5o9cZ5ghL.jpg" data-width="10">
                                                                        <picture>
                                                                            <source srcset="https://viarcanvas.com/storage/canvas-rams/January2023/ECxyswCt5jI5o9cZ5ghL.jpg" type="image/jpeg">
                                                                            <img width="150" height="150" src="https://viarcanvas.com/storage/canvas-rams/January2023/ECxyswCt5jI5o9cZ5ghL.jpg" alt="Viar" loading="lazy">
                                                                        </picture>
                                                                        <div class="fi-info">
                                                                            <p>
                                                                                @lang("gallery.code")

                                                                                <span>17</span>
                                                                            </p>

                                                                            <b>+10 €</b>
                                                                        </div>
                                                                        <div class="frame-info">
                                                                            <ul>
                                                                                <li>
                                                                                    <svg width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                        <g clip-path="url(#clip0_261_1144)">
                                                                                            <path d="M7.90987 1.17996C7.78973 1.0598 7.5949 1.0598 7.47473 1.17996L2.48706 6.16764L0.525259 4.20583C0.405118 4.08565 0.210289 4.08565 0.0901174 4.20583C-0.0300391 4.32598 -0.0300391 4.5208 0.0901174 4.64097L2.26951 6.82036C2.38962 6.94047 2.58451 6.9405 2.70465 6.82036L7.90987 1.6151C8.03002 1.49493 8.03002 1.30012 7.90987 1.17996Z" fill="#1F9750"></path>
                                                                                        </g>
                                                                                        <defs>
                                                                                            <clipPath id="clip0_261_1144">
                                                                                                <rect width="8" height="8" fill="white"></rect>
                                                                                            </clipPath>
                                                                                        </defs>
                                                                                    </svg>

                                                                                    <span>@lang('cart_new.in_stock')</span>
                                                                                </li>

                                                                                <li>
                                                                                    <span>@lang("gallery.code"):</span>
                                                                                    <span>18</span>
                                                                                </li>

                                                                                <li>
                                                                                    <span>@lang("gallery.material"):</span>
                                                                                    <span>Дерево</span>
                                                                                </li>


                                                                                <li>
                                                                                    <span>@lang("gallery.shade"):</span>
                                                                                    <span>Золотий</span>
                                                                                </li>

                                                                                <li>
                                                                                    <span>@lang("modular_pictures.index28"):</span>
                                                                                    <span>5.2 cm</span>
                                                                                </li>

                                                                                <li>
                                                                                    <span>@lang("modular_pictures.index30"):</span>
                                                                                    <span>5.2 cm</span>
                                                                                </li>


                                                                                <li>
                                                                                    <span>@lang("gallery.ram_total_price"):</span>
                                                                                    <span>10 €</span>
                                                                                </li>
                                                                            </ul>

                                                                        </div>
                                                                        <div class="frame-js-popup">
                                                                            <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                <circle cx="14" cy="14" r="14" fill="#FA7846"></circle>
                                                                                <g clip-path="url(#clip0_877_8)">
                                                                                    <path d="M22.6 21.9106L17.313 16.6236C18.1764 15.604 18.7 14.2878 18.7 12.85C18.7 9.62412 16.0759 7 12.85 7C9.62414 7 7 9.62412 7 12.85C7 16.0759 9.62412 18.7 12.85 18.7C14.2878 18.7 15.604 18.1764 16.6236 17.313L21.9106 22.6L22.6 21.9106ZM12.85 17.725C10.1621 17.725 7.97501 15.5379 7.97501 12.85C7.97501 10.1621 10.1621 7.97501 12.85 7.97501C15.5379 7.97501 17.725 10.1621 17.725 12.85C17.725 15.5379 15.5379 17.725 12.85 17.725Z" fill="white"></path>
                                                                                    <rect width="6.26573" height="0.68813" transform="matrix(-2.0104e-05 -1 1 2.0104e-05 12.4766 15.9268)" fill="white"></rect>
                                                                                    <rect width="6.26574" height="0.658255" transform="matrix(-1 -2.03991e-05 -2.05255e-05 -1 15.9277 13.0557)" fill="white"></rect>
                                                                                </g>
                                                                                <defs>
                                                                                    <clipPath id="clip0_877_8">
                                                                                        <rect width="15" height="15" fill="white" transform="translate(7 7)"></rect>
                                                                                    </clipPath>
                                                                                </defs>
                                                                            </svg>
                                                                        </div>
                                                                        <div class="frame-radio"></div>
                                                                        <a href="#" class="mf-popup frame-js-popup"> Подробнее </a>
                                                                    </div>
                                                                    <div class="frame-item active" data-code="18" data-price="20" data-src="https://viarcanvas.com/storage/canvas-rams/February2023/SHVhNNe38XFNZrJNzq4a.webp" data-width="10">
                                                                        <picture>
                                                                            <source srcset="https://viarcanvas.com/storage/canvas-rams/February2023/SHVhNNe38XFNZrJNzq4a.webp" type="image/jpeg">
                                                                            <img width="150" height="150" src="https://viarcanvas.com/storage/canvas-rams/February2023/SHVhNNe38XFNZrJNzq4a.webp" alt="Viar" loading="lazy">
                                                                        </picture>
                                                                        <div class="fi-info">
                                                                            <p>
                                                                                @lang("gallery.code"):

                                                                                <span>18</span>
                                                                            </p>
                                                                            <b>+20 €</b>
                                                                        </div>
                                                                        <div class="frame-info">
                                                                            <ul>
                                                                                <li>
                                                                                    <svg width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                        <g clip-path="url(#clip0_261_1144)">
                                                                                            <path d="M7.90987 1.17996C7.78973 1.0598 7.5949 1.0598 7.47473 1.17996L2.48706 6.16764L0.525259 4.20583C0.405118 4.08565 0.210289 4.08565 0.0901174 4.20583C-0.0300391 4.32598 -0.0300391 4.5208 0.0901174 4.64097L2.26951 6.82036C2.38962 6.94047 2.58451 6.9405 2.70465 6.82036L7.90987 1.6151C8.03002 1.49493 8.03002 1.30012 7.90987 1.17996Z" fill="#1F9750"></path>
                                                                                        </g>
                                                                                        <defs>
                                                                                            <clipPath id="clip0_261_1144">
                                                                                                <rect width="8" height="8" fill="white"></rect>
                                                                                            </clipPath>
                                                                                        </defs>
                                                                                    </svg>




                                                                                    <span>@lang('cart_new.in_stock')</span>
                                                                                </li>

                                                                                <li>
                                                                                    <span>@lang("gallery.code"):</span>
                                                                                    <span>18</span>
                                                                                </li>

                                                                                <li>
                                                                                    <span>@lang("gallery.material"):</span>
                                                                                    <span>Дерево</span>
                                                                                </li>


                                                                                <li>
                                                                                    <span>@lang("gallery.shade"):</span>
                                                                                    <span>Золотий</span>
                                                                                </li>

                                                                                <li>
                                                                                    <span>@lang("modular_pictures.index28"):</span>
                                                                                    <span>5.2 cm</span>
                                                                                </li>

                                                                                <li>
                                                                                    <span>@lang("modular_pictures.index30"):</span>
                                                                                    <span>5.2 cm</span>
                                                                                </li>


                                                                                <li>
                                                                                    <span>@lang("gallery.ram_total_price"):</span>
                                                                                    <span>10 €</span>
                                                                                </li>
                                                                            </ul>





                                                                        </div>
                                                                        <div class="frame-js-popup">
                                                                            <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                <circle cx="14" cy="14" r="14" fill="#FA7846"></circle>
                                                                                <g clip-path="url(#clip0_877_8)">
                                                                                    <path d="M22.6 21.9106L17.313 16.6236C18.1764 15.604 18.7 14.2878 18.7 12.85C18.7 9.62412 16.0759 7 12.85 7C9.62414 7 7 9.62412 7 12.85C7 16.0759 9.62412 18.7 12.85 18.7C14.2878 18.7 15.604 18.1764 16.6236 17.313L21.9106 22.6L22.6 21.9106ZM12.85 17.725C10.1621 17.725 7.97501 15.5379 7.97501 12.85C7.97501 10.1621 10.1621 7.97501 12.85 7.97501C15.5379 7.97501 17.725 10.1621 17.725 12.85C17.725 15.5379 15.5379 17.725 12.85 17.725Z" fill="white"></path>
                                                                                    <rect width="6.26573" height="0.68813" transform="matrix(-2.0104e-05 -1 1 2.0104e-05 12.4766 15.9268)" fill="white"></rect>
                                                                                    <rect width="6.26574" height="0.658255" transform="matrix(-1 -2.03991e-05 -2.05255e-05 -1 15.9277 13.0557)" fill="white"></rect>
                                                                                </g>
                                                                                <defs>
                                                                                    <clipPath id="clip0_877_8">
                                                                                        <rect width="15" height="15" fill="white" transform="translate(7 7)"></rect>
                                                                                    </clipPath>
                                                                                </defs>
                                                                            </svg>
                                                                        </div>

                                                                        <a href="#" class="mf-popup frame-js-popup">
                                                                            @lang("gl.read_more")
                                                                        </a>

                                                                        <div class="frame-radio"></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="formalization-prompt">
                                        <div class="formalization-prompt--wrapper">
                                            <div class="formalization-prompt--inner">
                                                <img src="{{ asset(config('theme.current') . '/images/prompt7.png') }}" alt="" />

                                                <p>
                                                    @lang("simpson.popup-wrapper.formalization-item5.formalization-prompt--inner.p")
                                                </p>

                                            </div>
                                        </div>
                                    </div>
                                </div>



                                <div class="formalization-item">   <!---formalization-item6----->
                                    <div class="formalization-box">
                                        <div class="formalization-tab">
                                            <img width="25" height="25" src="{{ asset(config('theme.current') . '/images/sharj/new/format6.svg') }}" alt="">

                                            @lang("simpson.popup-wrapper.formalization-item6.formalization-tab")

                                            <span class="tab-icon"></span>
                                        </div>
                                        <div class="formalization-content">
                                            <div class="formalization-content--inner">
                                                <div class="input-group input-comments">

                                                    <label for="comments1">
                                                        @lang("simpson.popup-wrapper.formalization-item6.comments1")
                                                    </label>

                                                    <div class="textarea-wrapper">
                              <textarea id="comments1" name="comments">
                                </textarea>
                                                    </div>
                                                </div>
                                                <div class="vz-art kviz-input">

                                                    <p class="vz-art kviz-input__title">
                                                        @lang("portrait_buy_form.step_comment_label")
                                                    </p>


                                                    <div class="file-save file-save__popup">
                                                        <div class="abs-close"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x"><line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/></svg></div>
                                                        <div class="file-save__item js-file-preview">
                                                            <svg>
                                                                <use xlink:href="{{ asset(config('theme.current') . '/sprite.svg#save') }}"></use>
                                                            </svg>
                                                            <div class="file-save__title">



                                                                <p>
                                                                    @lang("simpson.load_image")
                                                                </p>

                                                                <span>
                                                                    @lang("simpson.click_to_add_photo")
                                                                </span>

                                                            </div>
                                                        </div>
                                                        <div class="file-save__item js-file-upload">
                                                            <svg>
                                                                <use xlink:href="sprite.svg#picture"></use>
                                                            </svg>
                                                            <div class="file-save__title">
                                                                <p>photo_34567.jpg</p>
                                                                <span>2 Mb</span>
                                                            </div>
                                                        </div>
                                                        <div class="file-save__item js-file-multiple">
                                                            <svg>
                                                                <use xlink:href="sprite.svg#check"></use>
                                                            </svg>
                                                            <div class="file-save__title">
                                                                <p class="file-title_green">
                                                                    @lang("simpson.popup-wrapper.formalization-item6.file-title_green")
                                                                </p>

                                                            </div>
                                                        </div>
                                                        <input type="file" class="file-input" name="file[]" multiple="" accept="image/*,image/heif,image/heic" aria-label="file input">
                                                    </div>
                                                </div>
                                                <div class="images-container"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="formalization-prompt">
                                        <div class="formalization-prompt--wrapper">
                                            <div class="formalization-prompt--inner">
                                                <img src="{{ asset(config('theme.current') . '/images/prompt9.png') }}" alt="" />

                                                <p>
                                                    @lang("simpson.popup-wrapper.formalization-item6.formalization-prompt--inner.p")
                                                </p>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
                <div class="formalization__block--bottom">  <!----не потрібно переводити----->
                    <div class="formalization__final">
                        <div class="formalizaton__submit">
                            <div class="formalization-price">
                                Стоимость услуги: <span>15€</span>
                            </div>
                            <div class="formalization-btn">Добавить в корзину</div>
                        </div>
                        <div class="formalization-bottom--check">
                            <div class="kviz-wrap">
                                <p>Выберите упаковку:</p>
                                <div class="kviz-c-row kviz-c-group">
                                    <div class="kviz-radio js-checkbox kviz-radio_active" data-stock="1">
                                        <div class="check check-border"></div>
                                        <label>
                        <span>Эксклюзивная упаковка
                          <img src="{{ asset(config('theme.current') . '/images/icon/info.svg') }}" alt="" /></span>
                                            <input type="radio" name="equipment" value="Эксклюзивная упаковка" />
                                        </label>
                                    </div>
                                    <div class="kviz-radio js-checkbox" data-stock="2">
                                        <div class="check check-border"></div>
                                        <label>
                        <span>Подарочная бумага<img src="{{ asset(config('theme.current') . '/images/icon/info.svg') }}" alt="" />
                        </span>
                                            <input type="radio" name="equipment" value="Подарочная бумага" />
                                        </label>
                                    </div>
                                    <div class="kviz-radio js-checkbox" data-stock="1">
                                        <div class="check check-border"></div>
                                        <label>
                                            <span>Обычная упаковка<img src="{{ asset(config('theme.current') . '/images/icon/info.svg') }}" alt="" /></span>
                                            <input type="radio" name="equipment" value="Обычная упаковка" />
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="kviz-wrap">
                                <p>Срок изготовления:</p>
                                <div class="kviz-c-row kviz-c-group">
                                    <div class="kviz-radio js-checkbox kviz-radio_active" data-stock="1">
                                        <div class="check check-border"></div>
                                        <label>
                                            <span>Стандарт - 3 рабочих дня 0 €</span>
                                            <input type="radio" name="equipment" value="Стандарт - 3 рабочих дня 0 €" />
                                        </label>
                                    </div>
                                    <div class="kviz-radio js-checkbox" data-stock="2">
                                        <div class="check check-border"></div>
                                        <label>
                                            <span>Экспресс - 1 рабочие сутки 5 € </span>
                                            <input type="radio" name="equipment" value="Экспресс - 1 рабочие сутки 5 €" />
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="close-btn">
                    <img width="30" height="30" src="{{ asset(config('theme.current') . '/images/sharj/new/cancel.svg') }}" alt="">
                </div>
            </div>
        </form>
    </div>


    <div class="popup-layout"></div>
</div>
