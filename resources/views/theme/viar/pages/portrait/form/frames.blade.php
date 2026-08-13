<div class="formalization-item hidden">
    <div class="formalization-box">
        <div class="formalization-tab">
              <p>{!! trans('portrait_buy_form.step7_title') !!}</p>
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
                                                        $portraitFrameImageSources = image_picture_sources(data_get($frame, 'img_bg'), true);
                                                    @endphp
                                                    <picture>
                                                        @if(!empty($portraitFrameImageSources['src_webp']))
                                                            <source srcset="{{ $portraitFrameImageSources['src_webp'] }}" type="image/webp">
                                                        @endif
                                                        @if(!empty($portraitFrameImageSources['src']) && !empty($portraitFrameImageSources['type']))
                                                            <source srcset="{{ $portraitFrameImageSources['src'] }}" type="{{ $portraitFrameImageSources['type'] }}">
                                                        @endif
                                                        <img style="width:70px;height:18px;" src="{{ $portraitFrameImageSources['src'] }}" @altAttrs($frame, 'img_bg', data_get($frame, 'img_bg'))/>
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
