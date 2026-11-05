<form data-portrait-by-photo-header="1" action="{{ route('send_photo_portrait_form') }}" method="POST" enctype="multipart/form-data" class="vz-art js-popup target-box popup-photo popup-photo-canvas submit-form">
    @csrf
    <input type="hidden" name="new_catid" value="">
    <input type="hidden" name="new_size" value="">
    <input type="hidden" name="new_price" value="">
    <input type="hidden" name="new_people_count" value="">
    <input type="hidden" name="new_people_count_price" value="">
    <input type="hidden" name="overall_price" value="">
    <input type="hidden" name="priceLabel" value="" class="js-price-label-field">
    <i class="vz-art fa-close popup-close"></i>
    <div class="vz-art page-title popup-photo-title h2_old">{{ trans('homepage_new.order_photo_portrait_title') }}</div>
    <div class="vz-art popup-group">
        <div class="vz-art kviz-input">
            <p class="vz-art kviz-input__title">{{ trans('homepage_new_login_reg.create_acc_enter_your_email') }}</p>
            <div class="vz-art page-input__item">
                <input type="email" name="email" placeholder="E-mail" required="" />
                <svg class="vz-art kviz-input__icon">
                    <use xlink:href="{{ asset(env('THEME').'sprite.svg#mail') }}"></use>
                </svg>
            </div>
        </div>
        <div class="vz-art kviz-input">
            <p class="vz-art kviz-input__title">{{ trans('homepage_new_login_reg.create_acc_enter_your_phone') }}</p>
            <div class="vz-art page-input__item phone-input" style="width:100%;">
                <input type="text" id="phone4" name="phone" required="" style="width:100%;">
                <svg class="vz-art kviz-input__icon">
                    <use xlink:href="{{ asset(env('THEME').'sprite.svg#phone') }}"></use>
                </svg>
            </div>
        </div>
    </div>
    <div class="vz-art popup-grid">
        <div class="vz-art popup-grid__file">
            <div class="vz-art kviz-input">
                <p class="vz-art kviz-input__title">{{ trans('homepage_new.photo_for_portrait') }}</p>
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

            @php

            $all_styles = $style;

            @endphp

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
            <div class="popup-half-mob">
                <label class="popup-photo__submit"> <input type="submit" />{{ trans('homepage_new.send') }} </label>
                <div class="loading-bar">
                    <span>
                        {{ trans('portrait_buy_form.loading') }}
                    </span>
                    <div class="l-progress">
                        <span></span><span></span><span></span><span></span><span></span><span></span>
                    </div>
                </div>
                <div class="kviz-politics">
                <div class="cart-checkbox">
                        <input type="checkbox" checked>
                        <svg class="vz-art kviz-input__icon">
                            <use xlink:href="{{ asset(env('THEME').'sprite.svg#log') }}"></use>
                        </svg>
                    </div>
                    <svg>
                        <use xlink:href="{{ asset(env('THEME').'sprite.svg#lock') }}"></use>
                    </svg>
                    <p>
                        {!! storefront_html(trans('homepage_new_login_reg.create_acc_policy_text')) !!}
                    </p>
                </div>
            </div>
        </div>
        <div class="vz-art popup-grid__select kviz-input_pc">
            <div class="vz-art kviz-input">
                <p class="vz-art kviz-input__title">{{ trans('homepage_new.set_need_portrait_size') }}</p>


                @foreach($all_styles as $style)
                @php
                $mod_sizes = explode(',', $style->custom_size_prices.','.$style->custom_size_prices_form2);
                @endphp
                <select data-catid="{{ $style->id }}" name="size" class="select-page select-size js_canvas_select_size canvas_select_sizes">
                    @if(is_array($mod_sizes) && !empty($mod_sizes))
                    @foreach ($mod_sizes as $mod_size)
                    @php
                    // Витягуємо розмір до дужки [ (наприклад, "30x40" з "30x40[45]h")
                    $size_without_price = trim(substr($mod_size, 0, strpos($mod_size, '[')));

                    // Витягуємо літеру після ] (якщо є)
                    $label_after_bracket = '';
                    if (preg_match('/\]([hstHST])/', $mod_size, $matches)) {
                        $label_after_bracket = strtolower($matches[1]);
                    }

                    // Чистий розмір БЕЗ позначки
                    $clean_size_name = $size_without_price;

                    // Для відображення - без літери
                    $size = explode('x', $size_without_price);
                    $display_size = $size[0] . 'x' . (isset($size[1]) ? $size[1] : '');

                    $style_price = get_string_between($mod_size, '[', ']');
                    if(count(explode("-",$style_price)) == 2)
                    {
                    $style_price = explode("-",$style_price)[1]*$contry_mult;

                    }
                    @endphp
                    <option data-price="{{ $style_price }}€" data-price-label="{{ $label_after_bracket }}" value="{{ $clean_size_name }}">{{ $display_size }} - {{ $style_price }}€</option>
                    @endforeach
                    @endif
                </select>
                @endforeach
            </div>
            <div class="vz-art kviz-input">
                <p class="vz-art kviz-input__title">{{ trans('homepage_new.set_need_style') }}</p>
                <select name="styles" class="select-page select-style">
                    @if($all_styles)
                    @foreach($all_styles as $style)
                    @php
                    $style_name = $style->getTranslatedAttribute('name');
                    $style_name = str_replace('<span>','', $style_name);
                        $style_name = str_replace('</span>','', $style_name);
                    @endphp
                    <option data-catid="{{ $style->id }}" value="{{ $style_name }}" @if( $style->id == $current_quiz_style_id) selected @endif>{{ $style_name }}</option>
                    @endforeach
                    @endif
                </select>
            </div>
            <div class="vz-art kviz-input  kviz-popup--portrait">
                <p class="vz-art kviz-input__title">{{ trans('portrait_buy_form.choose_the_number_of_people') }}</p>

                @if($all_styles)
                @foreach($all_styles as $style)

                @if(isset($style->custom_users_prices) && $style->custom_users_prices)
                @php
                $mod_count = explode(',', $style->custom_users_prices);
                @endphp

                <select name="count" data-catid="{{ $style->id }}" class="select-page select-count js_select_count">

                    @foreach ($mod_count as $this_cnt)
                    @php
                    $count_cnt = substr($this_cnt, 0, strpos($this_cnt, '['));
                    $count_price = get_string_between($this_cnt, '[', ']')*$contry_mult;
                    @endphp

                    <option data-count-price="{{ $count_price }}€" value="{{ $count_cnt }}">{{ $count_cnt }}@if($count_price) - {{ $count_price }}€@endif</option>
                    @endforeach

                </select>
                @endif
                @endforeach

                @endif

            </div>
            <div class="vz-art kviz-input">
                <p class="kviz-price">
                    {{ trans('portrait_buy_form.price') }}: <span></span>€
                </p>
            </div>

            <!-- <div class="kviz-input">
                <p class="vz-art js_cost_canvas"></p>
            </div> -->
        </div>


    </div>

    <div class="popup-half--pc ">
        <label class="popup-photo__submit"> <input type="submit" />{{ trans('homepage_new.send') }} </label>
        <div class="loading-bar">
            <span>
                {{ trans('portrait_buy_form.loading') }}
            </span>
            <div class="l-progress">
                <span></span><span></span><span></span><span></span><span></span><span></span>
            </div>
        </div>
        <div class="kviz-politics">
            <div class="cart-checkbox">
                <input type="checkbox" checked>
                <svg class="vz-art kviz-input__icon">
                    <use xlink:href="{{ asset(env('THEME').'/sprite.svg') }}#log"></use>
                </svg>
            </div>
            <svg>
                <use xlink:href="{{ asset(env('THEME').'sprite.svg#lock') }}"></use>
            </svg>
            <p>
                {!! storefront_html(trans('homepage_new_login_reg.create_acc_policy_text')) !!}
            </p>
        </div>

    </div>
    <!-- <picture>
        <source srcset="{{ asset('images/portrait-form.webp') }}" type="image/webp" />
        <source srcset="{{ asset('images/portrait-form.png') }}" />
        <img src="{{ asset('images/portrait-form.png') }}" class="vz-art photo-mokap" alt="img" loading="lazy" />
    </picture> -->
</form>
