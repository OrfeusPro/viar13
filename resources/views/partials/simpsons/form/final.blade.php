<div class="formalizaton__submit">
    <div class="formalization-price">
        {!! trans('portrait_buy_form.final_cost') !!} <span><span class="js_price">0</span>€</span>
    </div>
    <div class="js_submit_form formalization-btn" role="button" data-name="{{ $item['name'] }}" data-pid="{{ $item['id'] }}" data-action="{{ route('add_item_to_basket_portrait')  }}"> {!! trans('portrait_buy_form.final_add_to_bask') !!}</div>

    <div class="loading-bar">
        <span>Загрузка</span>
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
            <div class="set__item kviz-radio js_set_item js-checkbox @if($loop->last) kviz-radio_active @endif" data-stock="1">
                <div class="check"></div>
                <label>
                    <span>{{ $set->getTranslatedAttribute('name', app()->getLocale()) }}
                        <img src="{{ asset('images/icon/info.svg') }}" alt="" /></span>
                    <input class="js_set" @if($loop->last) checked @endif type="radio" name="equipment" data-id="{{ $set['id'] }}" value="{{ $set->price }}"/>
                </label>
            
                <div class="pic-pop">
                                    <picture>
                                        @php
                                            $simpsonsSetImageSources = image_picture_sources(data_get($set, 'image'), true);
                                        @endphp
                                        @if(!empty($simpsonsSetImageSources['src_webp']))
                                            <source srcset="{{ $simpsonsSetImageSources['src_webp'] }}" type="image/webp">
                                        @endif
                                        @if(!empty($simpsonsSetImageSources['src']) && !empty($simpsonsSetImageSources['type']))
                                            <source srcset="{{ $simpsonsSetImageSources['src'] }}" type="{{ $simpsonsSetImageSources['type'] }}">
                                        @endif
                                        <img loading="lazy" src="{{ $simpsonsSetImageSources['src'] }}" @altAttrs($set, 'image', data_get($set, 'image'))> 
                                    </picture>
                                </div>
            </div>
            @endforeach
            @endif
        </div>
    </div>
    <div class="kviz-wrap">
        <p>{!! trans('gl.srok_izg') !!}</p>
        <div class="kviz-c-row kviz-c-group">
            <div class="kviz-radio js_terms js-checkbox kviz-radio_active" data-stock="1">
                <div class="check"></div>
                <label>
                    <span class="js_term__selected">{!! $AProductionTime->standart_text !!} {{ $AProductionTime->standart_price }} €</span>
                    <input checked class="js_time" type="radio" name="time" value="{{ $AProductionTime->standart_price }}" />
                </label>
            </div>
            <div class="kviz-radio js_terms js-checkbox" data-stock="2">
                <div class="check"></div>
                <label>
                    <span class="js_term__selected">{!! $AProductionTime->express_text !!} {{ $AProductionTime->express_price }} €</span>
                    <input class="js_time" type="radio" name="time" value="{{ $AProductionTime->express_price }}" />
                </label>
            </div>
        </div>
    </div>
</div>
