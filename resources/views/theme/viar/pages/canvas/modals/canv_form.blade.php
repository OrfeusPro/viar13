<form data-portrait-by-photo-header="1" action="{{ route('send_photo_portrait_form') }}" method="POST" enctype="multipart/form-data" class="vz-art js-popup target-box popup-photo popup-photo-canvas submit-form">
    @csrf
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
            <a href="#" class="file-add">
                <svg>
                    <use xlink:href="{{ asset(env('THEME').'sprite.svg#plus') }}"></use>
                </svg>
                <span>{{ trans('homepage_new.load_more_photo') }}</span>
            </a>
            <div class="kviz-input kviz-input_mob">
                <p class="vz-art kviz-input__title">{{ trans('homepage_new.set_need_portrait_size') }}</p>
                <select name="size" class="select-page select-size canvas_select_sizes js_canvas_select_size">
                    @php
                    $mod_sizes = explode(',', $item['sizes_prices']);
                    @endphp
                    @if(is_array($mod_sizes) && !empty($mod_sizes))
                    @foreach ($mod_sizes as $mod_size)
                    @php
                    $size_without_price = trim(substr($mod_size, 0, strpos($mod_size, '[')));

                    $label_after_bracket = '';
                    if (preg_match('/\]([hstHST])/', $mod_size, $matches)) {
                        $label_after_bracket = strtolower($matches[1]);
                    }

                    $clean_size_name = $size_without_price;

                    $size = explode('x', $size_without_price);
                    $display_size = $size[0] . 'x' . (isset($size[1]) ? $size[1] : '');
                    @endphp
                    <option data-price="{{  get_string_between($mod_size, '[', ']') }}€" data-price-label="{{ $label_after_bracket }}" value="{{ $clean_size_name }}">{{ $display_size }}</option>
                    @endforeach
                    @endif
                </select>
            </div>
            <div class="kviz-input kviz-input_mob">
                <p class="vz-art js_cost_canvas"></p>
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
                    <use xlink:href="{{ asset(env('THEME').'sprite.svg#lock') }}"></use>
                </svg>
                <p>
                    {!! storefront_html(trans('homepage_new_login_reg.create_acc_policy_text')) !!}
                </p>
            </div>
        </div>
        <div class="vz-art popup-grid__select kviz-input_pc">
            <div class="vz-art kviz-input">
                <p class="vz-art kviz-input__title">{{ trans('homepage_new.set_need_portrait_size') }}</p>
                <select name="size" class="select-page select-size js_canvas_select_size canvas_select_sizes">
                    @if(is_array($mod_sizes) && !empty($mod_sizes))
                    @foreach ($mod_sizes as $mod_size)
                    @php
                    $size_without_price = trim(substr($mod_size, 0, strpos($mod_size, '[')));

                    $label_after_bracket = '';
                    if (preg_match('/\]([hstHST])/', $mod_size, $matches)) {
                        $label_after_bracket = strtolower($matches[1]);
                    }

                    $clean_size_name = $size_without_price;

                    $size = explode('x', $size_without_price);
                    $display_size = $size[0] . 'x' . (isset($size[1]) ? $size[1] : '');
                    @endphp
                    <option data-price="{{  get_string_between($mod_size, '[', ']') }}€" data-price-label="{{ $label_after_bracket }}" value="{{ $clean_size_name }}">{{ $display_size }}</option>
                    @endforeach
                    @endif
                </select>
            </div>
            <div class="vz-art kviz-input">
                <p class="vz-art js_cost_canvas"></p>
                <input class="js_c_price_mod" type="hidden" name="price">
            </div>
        </div>
    </div>
    <picture>
        <source srcset="{{ asset('images/portrait-form.webp') }}" type="image/webp" />
        <source srcset="{{ asset('images/portrait-form.png') }}" />
        <img src="{{ asset('images/portrait-form.png') }}" class="vz-art photo-mokap" alt="img" loading="lazy" />
    </picture>
</form>
