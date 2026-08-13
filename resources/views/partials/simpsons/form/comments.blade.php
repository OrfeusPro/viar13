<div class="formalization-item">
    <div class="formalization-box">
        <div class="formalization-tab">
            <img width="25" height="25" src="{{ asset(config('theme.current') . '/images/sharj/new/format6.svg') }}" alt="">

            @lang("simpson.formalization-items.formalization-item6.formalization-tab")

            <span class="tab-icon"></span>
        </div>
        <div class="formalization-content">
            <div class="formalization-content--inner">
                <div class="input-group input-comments">
                    <label for="comments1">{{ trans('portrait_buy_form.step_comment_ex') }}</label>
                    <div class="textarea-wrapper">
                        <textarea class="js_comment" id="comments1" name="userComment"></textarea>
                    </div>
                </div>
                <div class="vz-art kviz-input">
                    <p class="vz-art kviz-input__title">
                       {{ trans('portrait_buy_form.step_comment_label') }}</p>
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
                        <input type="file" class="file-input" name="photo_ex" accept="image/*,image/heif,image/heic"
                               aria-label="file input"/>
                    </div>
                </div>
{{--                <a href="#" class="file-add">--}}
{{--                    <svg>--}}
{{--                        <use xlink:href="{{ asset(env('THEME').'sprite.svg#plus') }}"></use>--}}
{{--                    </svg>--}}
{{--                    <span>Добавить еще фото</span>--}}
{{--                </a>--}}
            </div>
        </div>
    </div>
    <div class="formalization-prompt">
        <div class="formalization-prompt--wrapper">
            <div class="formalization-prompt--inner">
                <img src="{{ asset('images/prompt9.png') }}" alt=""/>
                <p>
                     {{ trans('portrait_buy_form.step_comment_bot_desc') }} </p>
            </div>
        </div>
    </div>
</div>
