<div class="accordion-title h2_old">
    <span>{{ trans('canvas.form_step_comment_title') }}</span>
    <span class="tab-icon"></span>
</div>
<div class="accordion-content hidden__labels">
    <div class="input-group input-comments">
        <label>{{ trans('portrait_buy_form.step_comment_ex') }}</label>
        <div class="textarea-wrapper">
            <textarea id="userComment" name="user_comment"></textarea>
        </div>
    </div>
    <div class="vz-art kviz-input">
        <p class="vz-art kviz-input__title">
            {{ trans('portrait_buy_form.step_comment_label') }}</p>
        <div class="file-save file-save__popup">
            <div class="abs-close">
                X
            </div>
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
            <div class="file-save__item js-file-multiple">
                <svg>
                    <use xlink:href="{{ asset(env('THEME') . 'sprite.svg#check') }}"></use>
                </svg>
                <div class="file-save__title">
                    <p class="file-title_green">Файлы загружены</p>
                </div>
            </div>
            <input id="real_file_input" data-desc="Вы можете загрузить фото пример" name="photo_ex" accept="image/*,image/heif,image/heic"
                type="file" class="file-input" />
        </div>
        {{-- <div class="file-save file-save__popup file-save_hide"> --}}
        {{-- <div class="file-save__item js-file-preview"> --}}
        {{-- <svg> --}}
        {{-- <use --}}
        {{-- xlink:href="{{ asset(env('THEME').'sprite.svg#save') }}"></use> --}}
        {{-- </svg> --}}
        {{-- <div class="file-save__title"> --}}
        {{-- <p>{{ trans('homepage_new.load_photo') }}</p> --}}
        {{-- <span>{{ trans('homepage_new.pree_to_add_photo') }}</span> --}}
        {{-- </div> --}}
        {{-- </div> --}}
        {{-- <div class="file-save__item js-file-upload"> --}}
        {{-- <svg> --}}
        {{-- <use --}}
        {{-- xlink:href="{{ asset(env('THEME').'sprite.svg#picture') }}"></use> --}}
        {{-- </svg> --}}
        {{-- <div class="file-save__title"> --}}
        {{-- </div> --}}
        {{-- </div> --}}
        {{-- <input id="real_file_input1" --}}
        {{-- data-desc="Вы можете загрузить фото пример" --}}
        {{-- name="photo_ex" accept="image/*,image/heif,image/heic" type="file" --}}
        {{-- class="file-input"/> --}}
        {{-- </div> --}}
        {{-- <div class="file-save file-save__popup file-save_hide"> --}}
        {{-- <div class="file-save__item js-file-preview"> --}}
        {{-- <svg> --}}
        {{-- <use --}}
        {{-- xlink:href="{{ asset(env('THEME').'sprite.svg#save') }}"></use> --}}
        {{-- </svg> --}}
        {{-- <div class="file-save__title"> --}}
        {{-- <p>{{ trans('homepage_new.load_photo') }}</p> --}}
        {{-- <span>{{ trans('homepage_new.pree_to_add_photo') }}</span> --}}
        {{-- </div> --}}
        {{-- </div> --}}
        {{-- <div class="file-save__item js-file-upload"> --}}
        {{-- <svg> --}}
        {{-- <use --}}
        {{-- xlink:href="{{ asset(env('THEME').'sprite.svg#picture') }}"></use> --}}
        {{-- </svg> --}}
        {{-- <div class="file-save__title"> --}}
        {{-- </div> --}}
        {{-- </div> --}}
        {{-- <input id="real_file_input2" --}}
        {{-- data-desc="Вы можете загрузить фото пример" --}}
        {{-- name="photo_ex" accept="image/*,image/heif,image/heic" type="file" --}}
        {{-- class="file-input"/> --}}
        {{-- </div> --}}
    </div>
    {{-- <a href="#" class="file-add"> --}}
    {{-- <svg> --}}
    {{-- <use xlink:href="{{ asset(env('THEME').'sprite.svg#plus') }}"></use> --}}
    {{-- </svg> --}}
    {{-- <span>Добавить еще фото</span> --}}
    {{-- </a> --}}
</div>
