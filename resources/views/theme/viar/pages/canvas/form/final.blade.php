<div class="formalization__block--bottom">
    <div class="formalization__final">
        <div class="formalizaton__submit">
            <div class="formalization-price" id="totalSum">
                {!! trans('portrait_buy_form.final_cost') !!} <span><span data-total="0.00" class="totalPriceNew">0</span>€</span>
            </div>
            <div class="formalization-btn" id="t3_submit_btn" data-pid="1" data-name="Canvas">{!! trans('portrait_buy_form.final_add_to_bask') !!}</div>
            <div class="loading-bar">
                <span>{{ trans('portrait_buy_form.loading') }}</span>
                <div class="l-progress">
                    <span></span><span></span><span></span><span></span><span></span><span></span>
                </div>
            </div>
        </div>
        <div class="formalization-bottom--check">
            <div class="kviz-wrap">
                <p> {!! trans('portrait_buy_form.final_pack') !!}</p>
                <div class="kviz-c-row kviz-c-group">
                    @if($sets)
                        @foreach($sets as $set)
                            <div class="kviz-radio js-checkbox @if($loop->last) kviz-radio_active @endif" data-stock="1">
                                <div class="check check-border"></div>
                                <label>
                              <span
                              >{{ $set->getTranslatedAttribute('name', app()->getLocale()) }}<img
                                      src="{{ asset('images/icon/info.svg') }}"
                                      alt=""
                                  /></span>
                                    <input
                                        name="boxes[]"
                                        type="radio"
                                        checked
                                        data-id="{{ $set['id'] }}"
                                        data-name="{{ $set->getTranslatedAttribute('name', app()->getLocale()) }}"
                                        value="{{ $set->price }}"
                                    />
                                </label>
                                <div class="pic-pop">
                                    <picture>
                                        @php
                                            $canvasSetImageSources = image_picture_sources(data_get($set, 'image'), true);
                                        @endphp
                                        @if(!empty($canvasSetImageSources['src_webp']))
                                            <source srcset="{{ $canvasSetImageSources['src_webp'] }}" type="image/webp">
                                        @endif
                                        @if(!empty($canvasSetImageSources['src']) && !empty($canvasSetImageSources['type']))
                                            <source srcset="{{ $canvasSetImageSources['src'] }}" type="{{ $canvasSetImageSources['type'] }}">
                                        @endif
                                        <img loading="lazy" src="{{ $canvasSetImageSources['src'] }}" @altAttrs($set, 'image', data_get($set, 'image'))> 
                                    </picture>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
            <div class="kviz-wrap delivery-inputs">
                <p>{!! trans('gl.srok_izg') !!}</p>
                <div class="kviz-c-row kviz-c-group">
                    <div class="kviz-radio js_terms js-checkbox kviz-radio_active" data-stock="1">
                        <div class="check"></div>
                        <label>
                            <span class="js_term__selected">{!! $AProductionTime->standart_text !!} {{ $AProductionTime->standart_price }} €</span>
                            <input checked class="js_time" type="radio" name="time" value="{{ $AProductionTime->standart_price }}"/>
                        </label>
                    </div>
                    <div class="kviz-radio js_terms js-checkbox" data-stock="2">
                        <div class="check"></div>
                        <label>
                            <span class="js_term__selected">{!! $AProductionTime->express_text !!} {{ $AProductionTime->express_price }} €</span>
                            <input class="js_time" type="radio" name="time" value="{{ $AProductionTime->express_price }}"/>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
