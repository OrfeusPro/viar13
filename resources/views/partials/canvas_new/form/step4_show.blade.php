<div class="accordion-title h2_old">
    <span>{!! trans('canvas.step4_title_show') !!}</span>
    <span class="tab-icon"></span>
</div>
<div class="accordion-content">
    <div class="kviz-row-single kviz-c-group">
        <div class="kviz-group__item">
            @if(isset($art_items))
                @foreach($art_items as $item)
                    <div class="kviz-radio js-checkbox @if($loop->last) kviz-radio_active @endif" data-stock="{{ $loop->iteration }}">
                            <div class="check"></div>
                            <label>
                                                    <span>{{ $item->getTranslatedAttribute('name', app()->getLocale()) }}
                                                      <img src="{{ asset('images/icon/info.svg') }}" alt=""></span>
                                <input name="decoration" type="radio"
                                       data-name="{{ $item->getTranslatedAttribute('name', app()->getLocale()) }}"
                                       data-id="{{ $item->id }}"
                                       data-coef_sm="{{ $item->coef_sm }}"
                                       data-coef_md="{{ $item->coef_md }}"
                                       data-coef_lg="{{ $item->coef_lg }}"
                                       value="{{ $item->price }}"/>
                                {{-- <div class="window-prompt"> --}}
    {{--                                Холст покрываем мазками--}}
    {{--                                геля. Холст приобретает--}}
    {{--                                объём. Подходит не для--}}
    {{--                                каждой картины. Прежде--}}
    {{--                                согласуйте с нами.--}}
                                {{-- </div> --}}
                                @if($item->image)
                                <div class="pic-pop">
                                    <picture>
                                        @php
                                            $canvasNewStepFourImageSources = image_picture_sources(data_get($item, 'image'), true);
                                        @endphp
                                        @if(!empty($canvasNewStepFourImageSources['src_webp']))
                                            <source srcset="{{ $canvasNewStepFourImageSources['src_webp'] }}" type="image/webp">
                                        @endif
                                        @if(!empty($canvasNewStepFourImageSources['src']) && !empty($canvasNewStepFourImageSources['type']))
                                            <source srcset="{{ $canvasNewStepFourImageSources['src'] }}" type="{{ $canvasNewStepFourImageSources['type'] }}">
                                        @endif
                                        <img loading="lazy" src="{{ $canvasNewStepFourImageSources['src'] }}" @altAttrs($item, 'image', data_get($item, 'image'))>
                                    </picture>
                                    <div class="hint">{!! $item->getTranslatedAttribute('hint', app()->getLocale()) !!}</div>
                                </div>
                                @endif
                            </label>
                        </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
