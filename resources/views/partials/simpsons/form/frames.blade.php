<div class="formalization-item ">
    <div class="formalization-box">
        <div class="formalization-tab">
            <img width="25" height="25" src="{{ asset(config('theme.current') . '/images/sharj/new/format5.svg') }}" alt="">

            @lang("simpson.popup-wrapper.formalization-item5.formalization-tab")

            <span class="tab-icon"></span>
        </div>
        <div class="formalization-content">
            <div class="formalization-content--inner">
                <div class="kviz-row-single kviz-c-group">
                    <div class="kviz-group__item">
                        @if($frames)
                            @foreach($frames as $frame)
                                @if($loop->first)
                                <label class="kviz-radio js__frame_item js-checkbox kviz-radio_active" data-stock="1" data-price="{{ $frame->price }}">
                                    <em class="check"></em>
                                    <div>
                                        <span>{{ $frame->getTranslatedAttribute('name', app()->getLocale()) }}</span>
                                        <input class="js_frame" type="radio" name="frame" data-price="{{ $frame->price }}" value="{{ $frame->id }}"/>
                                    </div>
                                </label>
                                @else
                                    <label class="kviz-radio js__frame_item js-checkbox" data-stock="1" data-price="{{ $frame->price }}">
                                        <em class="check"></em>
                                        <div>
                                                <span>{{ $frame->getTranslatedAttribute('name', app()->getLocale()) }} + <b> {{ $frame->price }} €</b>
                                                    @php
                                                        $simpsonsFrameImageSources = image_picture_sources(data_get($frame, 'img_bg'), true);
                                                    @endphp
                                                    <picture>
                                                        @if(!empty($simpsonsFrameImageSources['src_webp']))
                                                            <source srcset="{{ $simpsonsFrameImageSources['src_webp'] }}" type="image/webp">
                                                        @endif
                                                        @if(!empty($simpsonsFrameImageSources['src']) && !empty($simpsonsFrameImageSources['type']))
                                                            <source srcset="{{ $simpsonsFrameImageSources['src'] }}" type="{{ $simpsonsFrameImageSources['type'] }}">
                                                        @endif
                                                        <img style="width:70px;height:18px;" src="{{ $simpsonsFrameImageSources['src'] }}" @altAttrs($frame, 'img_bg', data_get($frame, 'img_bg'))/>
                                                  </picture>
                                                </span>
                                            <input class="js_frame" type="radio" name="frame" data-price="{{ $frame->price }}" value="{{ $frame->id }}"/>
                                        </div>
                                    </label>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="formalization-prompt">
        <div class="formalization-prompt--wrapper">
            <div class="formalization-prompt--inner">
                <img src="{{ asset('images/prompt7.png') }}" alt=""/>
                <p>
                    {!! trans('portrait_buy_form.step7_bot_desc') !!}
                   </p>
            </div>
        </div>
    </div>
</div>
