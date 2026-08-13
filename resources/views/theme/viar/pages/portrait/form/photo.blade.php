<div class="formalization-item">
    <div class="formalization-box">
        <div class="formalization-tab">
            <p>{!! trans('portrait_buy_form.step1_title') !!}</p>
            <span class="tab-icon"></span>
        </div>
        <div class="formalization-content">
            <div class="formalization-content--inner">
                <div class="vz-art kviz-input">
                    <p class="vz-art kviz-input__title">
                        {!! trans('portrait_buy_form.step1_desc') !!}</p>
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
            <div class="images-container">
                    
            </div>
        </div>
    </div>
    <div class="formalization-prompt">
        <div class="formalization-prompt--wrapper">
            <div class="formalization-prompt--inner">
                <img src="{{ asset('images/prompt1.png') }}" alt="" />
                <p>
                    {!! trans('portrait_buy_form.step1_bot_desc') !!}
                </p>
            </div>
        </div>
    </div>
</div>