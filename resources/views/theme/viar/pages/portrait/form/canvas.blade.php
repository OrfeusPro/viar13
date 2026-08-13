<div class="formalization-item ">
    <div class="formalization-box">
        <div class="formalization-tab">
            <p>{!! trans('portrait_buy_form.step5_title') !!}</p>
            <span class="tab-icon"></span>
        </div>
        <div class="formalization-content">
            <div class="formalization-content--inner">
                <div class="kviz-row-single kviz-c-group">
                    <div class="kviz-group__item">
                        @if(isset($canvas_items))
                            @foreach($canvas_items as $c_item)
                                <label class="kv__h__item kviz-radio js_canvas_type_item js-checkbox @if($loop->iteration === 2) kviz-radio_active @endif"
                                       data-stock="1"
                                       data-price="{{ $c_item['price'] }}"
                                       data-id="{{ $c_item['id'] }}"
                                >
                                    <em class="check"></em>

                                            <span>
                                                {{ $c_item->getTranslatedAttribute('name', app()->getLocale()) }}
                                                {{ $c_item->getTranslatedAttribute('density', app()->getLocale()) }}
                                              <img src="{{ asset('images/icon/info.svg') }}" alt=""/>
                                            </span>
                                        <input
                                            @if($loop->iteration === 2) checked @endif
                                            class="js_canvas_type"
                                            data-name="{{ $c_item->getTranslatedAttribute('name', app()->getLocale()) }}"
                                            data-price="{{ $c_item['price'] }}"
                                            type="radio"
                                            name="types"
                                            value="{{ $c_item['price'] }}"/>
                                </label>
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
                <img src="{{ asset('images/prompt5.png') }}" alt=""/>
                <p>
                    {!! trans('portrait_buy_form.step5_bot_desc') !!}</p>
            </div>
        </div>
    </div>
</div>
