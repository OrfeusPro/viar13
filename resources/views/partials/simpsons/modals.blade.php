<div class="vz-art popup-frame target-frame popup-frame--ex">

@if(isset($is_portrait_page))
<form data-portrait-by-photo-header="1" action="{{ route('send_photo_portrait_form') }}" method="POST" enctype="multipart/form-data"
          class="vz-art js-popup target-box popup-works-ex popup-def submit-form">
        @csrf
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
                <div class="vz-art page-input__item phone-input"  style="width:100%;">
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

                            </div>
                        </div>
                        <input type="file" id="#file1" class="file-input" name="file" accept="image/*,image/heif,image/heic" aria-label="file input" />
                    </div>
                    <div class="file-save file-save__popup file-save_hide">
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
                                <p>{{ trans('homepage_new.load_photo') }}</p>
                                    <span>{{ trans('homepage_new.pree_to_add_photo') }}</span>
                            </div>
                        </div>
                        <input type="file" id="#file2" class="file-input file-input_hide" name="file2" accept="image/*,image/heif,image/heic" aria-label="file input" />
                    </div>
                    <div class="file-save file-save__popup file-save_hide">
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

                            </div>
                        </div>
                        <input type="file" class="file-input file-input_hide" name="file3" accept="image/*,image/heif,image/heic" aria-label="file input" />
                    </div>
                    <div class="file-save file-save__popup file-save_hide">
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

                            </div>
                        </div>
                        <input type="file" class="file-input file-input_hide" name="file4" accept="image/*,image/heif,image/heic" aria-label="file input" />
                    </div>
                    <div class="file-save file-save__popup file-save_hide">
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
                            </div>
                        </div>
                        <input type="file" class="file-input file-input_hide" name="file5" accept="image/*,image/heif,image/heic" aria-label="file input" />
                    </div>
                    <div class="file-save file-save__popup file-save_hide">
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

                            </div>
                        </div>
                        <input type="file" class="file-input file-input_hide" name="file6" accept="image/*,image/heif,image/heic" aria-label="file input" />
                    </div>
                    <div class="file-save file-save__popup file-save_hide">
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

                            </div>
                        </div>
                        <input type="file" class="file-input file-input_hide" name="file7" accept="image/*,image/heif,image/heic" aria-label="file input" />
                    </div>
                    <div class="file-save file-save__popup file-save_hide">
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

                            </div>
                        </div>
                        <input type="file" class="file-input file-input_hide" name="file8" accept="image/*,image/heif,image/heic" aria-label="file input" />
                    </div>
                    <div class="file-save file-save__popup file-save_hide">
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

                            </div>
                        </div>
                        <input type="file" class="file-input file-input_hide" name="file9" accept="image/*,image/heif,image/heic" aria-label="file input" />
                    </div>
                    <div class="file-save file-save__popup file-save_hide">
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

                            </div>
                        </div>
                        <input type="file" class="file-input file-input_hide" name="file10"
                         accept="image/*,image/heif,image/heic" aria-label="file input" />
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
                        @php
                            $s_items = $item->getMedia('our_works_new');
                        @endphp
                    <select name="size" class="select-page select-size">
                        @if(isset($is_portrait_page))
                             @foreach( $s_items as $s_item)
                                  @if ($s_item->getCustomProperty('size'))
                                    <option value="{{ str_trans($s_item->getCustomProperty('size')) }}">{{ str_trans($s_item->getCustomProperty('size')) }}</option>
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
                        <select name="styles" class="select-page select-size">
                        @if(isset($is_portrait_page))
                             @foreach( $s_items as $s_item)
                                  @if ($s_item->getCustomProperty('name'))
                                    <option value="{{ str_trans($s_item->getCustomProperty('name')) }}">{{ str_trans($s_item->getCustomProperty('name')) }}</option>
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
                    <select name="size" class="select-page select-size">
                        @if(isset($is_portrait_page))
                             @foreach( $s_items as $s_item)
                                  @if ($s_item->getCustomProperty('size'))
                                    <option value="{{ str_trans($s_item->getCustomProperty('size')) }}">{{ str_trans($s_item->getCustomProperty('size')) }}</option>
                                  @endif
                             @endforeach
                        @endif
                    </select>
                </div>
                <div class="vz-art kviz-input">
                    <p class="vz-art kviz-input__title">{{ trans('homepage_new.set_need_style') }}</p>
                        <select name="styles" class="select-page select-size">
                        @if(isset($is_portrait_page))
                             @foreach( $s_items as $s_item)
                                  @if ($s_item->getCustomProperty('name'))
                                    <option value="{{ str_trans($s_item->getCustomProperty('name')) }}">{{ str_trans($s_item->getCustomProperty('name')) }}</option>
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
@endif


</div>



