<div class="accordion-title h2_old open">
    <span>{!! trans('portrait_buy_form.step1_title') !!}</span>
    <span class="tab-icon"></span>
</div>

<div class="accordion-content">
    <p class="vz-art kviz-input__title">
        {!! trans('portrait_buy_form.step1_desc') !!}</p>
    <div class="product-download pd-canvas" id="imgs">
        <div class="loader-canvas">
            <button type="button">
                <img src="{{ asset('images/icon/info.svg') }}" alt="" onload="this.style.opacity=1" />
                <span class="delete"></span>
            </button>
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
                <div class="file-save__item js-file-multiple">
                    <svg>
                        <use xlink:href="{{ asset(env('THEME').'sprite.svg#check') }}"></use>
                    </svg>
                    <div class="file-save__title">
                        <p class="file-title_green">{{ trans('portrait.form_files_loaded') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- <a href="#" class="file-add canvas-loader--add">--}}
    {{-- <svg>--}}
    {{-- <use xlink:href="{{ asset(env('THEME').'sprite.svg#plus') }}"></use>--}}
    {{-- </svg>--}}
    {{-- <span>{!! trans('portrait_buy_form.step1_load_more') !!}</span>--}}
    {{-- </a>--}}
    <div class="additional-image">
        <div class="additional-row">
            <div class="additional-img">
                <img src="{{ asset('images/canvas/additional.svg') }}" alt="">
            </div>
            <div class="additional-input">
                <div class="vz-art popup-log-check js-checkbox">
                    <span class="vz-art log-check">
                        <svg class="vz-art kviz-input__icon">
                            <use xlink:href="{{ asset(env('THEME').'sprite.svg#log') }}"></use>
                        </svg>
                    </span>
                    <p>{!! trans('canvas.form_step1_need_imp_photo') !!}</p>
                    <input type="checkbox" name="save" />
                </div>
            </div>
        </div>
        <div class="additional-hidden">
            <div class="kviz-wrap">
                <p>{{ trans('canvas.form_step1_tarif_title') }}</p>
                @php
                    $photoImprovements = $photo_improvements ?? collect();
                    $hasDefaultImprove = $photoImprovements->contains(function ($item) {
                        return (string) $item->is_default === '1';
                    });
                @endphp
                <div class="kviz-c-row kviz-c-group">
                    @if($photoImprovements->isNotEmpty())
                        @foreach($photoImprovements as $improve)
                            @php
                                $improveName = $improve->name;
                                $improveHint = $improve->hint;
                                $improvePrice = rtrim(rtrim(number_format((float) $improve->price, 2, '.', ''), '0'), '.');
                                $improveLabel = trim($improveName . ' ' . $improvePrice . ' EUR');
                                $isDefault = ((string) $improve->is_default === '1') || (!$hasDefaultImprove && $loop->first);
                            @endphp
                            <div class="kviz-radio js-checkbox @if($isDefault) kviz-radio_active @endif" data-stock="{{ $loop->iteration }}">
                                <div class="check check-border"></div>
                                <label>
                                    <span>
                                        <span class="kviz-label__title">{{ $improveName }}</span>&nbsp;
                                        <span class="kviz-label__price">{{ $improvePrice }} EUR</span>
                                        <img src="{{ asset('images/icon/info.svg') }}" alt="">
                                    </span>
                                    <input name="boxes2[]" type="radio"
                                        data-id="{{ $improve->id }}"
                                        data-name="{{ $improveLabel }}"
                                        value="{{ $improve->price }}"
                                        @if($isDefault) checked="checked" @endif>
                                    @if($improve->image)
                                        <div class="pic-pop">
                                            <picture>
                                                @php
                                                    $canvasNewImproveImageSources = image_picture_sources(data_get($improve, 'image'), true);
                                                @endphp
                                                @if(!empty($canvasNewImproveImageSources['src_webp']))
                                                    <source srcset="{{ $canvasNewImproveImageSources['src_webp'] }}" type="image/webp">
                                                @endif
                                                @if(!empty($canvasNewImproveImageSources['src']) && !empty($canvasNewImproveImageSources['type']))
                                                    <source srcset="{{ $canvasNewImproveImageSources['src'] }}" type="{{ $canvasNewImproveImageSources['type'] }}">
                                                @endif
                                                <img loading="lazy" src="{{ $canvasNewImproveImageSources['src'] }}" @altAttrs($improve, 'image', data_get($improve, 'image'))>
                                            </picture>
                                            @if($improveHint)
                                                <div class="hint" style="width:100%;">{!! $improveHint !!}</div>
                                            @endif
                                        </div>
                                    @endif
                                </label>
                            </div>
                        @endforeach
                    @else
                        <div class="kviz-radio js-checkbox kviz-radio_active" data-stock="1">
                            <div class="check check-border"></div>
                            <label>
                                <span>{{ trans('canvas.form_step1_tarif_1') }}
                                    <img src="{{ asset('images/icon/info.svg') }}" alt="">
                                </span>
                                <input name="boxes2[]" type="radio" data-id="2" checked data-name="base" value="{{ $canvas_head['ob_price1'] }}">

                                <div class="window-prompt">
                                    {{ trans('canvas.form_step1_tarif_1') }}
                                </div>
                            </label>
                        </div>
                        <div class="kviz-radio js-checkbox" data-stock="2">
                            <div class="check check-border"></div>
                            <label>
                                <span> {{ trans('canvas.form_step1_tarif_2') }}<img src="{{ asset('images/icon/info.svg') }}" alt="">
                                </span>
                                <input name="boxes2[]" type="radio" data-id="1" data-name="standart" value="{{ $canvas_head['ob_price2'] }}">
                                <div class="window-prompt">
                                    {{ trans('canvas.form_step1_tarif_2') }}
                                </div>
                            </label>
                        </div>
                        <div class="kviz-radio js-checkbox" data-stock="1">
                            <div class="check check-border"></div>
                            <label class="jcf-label-active">
                                <span>{{ trans('canvas.form_step1_tarif_3') }}<img src="{{ asset('images/icon/info.svg') }}" alt=""></span>
                                <input name="boxes2[]" type="radio" data-id="3" data-name="premium" value="{{ $canvas_head['ob_price3'] }}">
                                <div class="window-prompt">
                                    {{ trans('canvas.form_step1_tarif_3') }}
                                </div>
                            </label>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
