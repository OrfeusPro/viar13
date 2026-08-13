<div class="formalization-item">
    <div class="formalization-box">
        <div class="formalization-tab">
             <p>{!! trans('portrait_buy_form.step8_title') !!}</p>
            <span class="tab-icon"></span>
        </div>
        <div class="formalization-content">
            <div class="formalization-content--inner">
                <div class="kviz-row-single kviz-c-group">
                    <div class="kviz-group__item">
                        @if($sets)
                            @foreach($sets as $set)
                                <div class="kviz-radio js_set_item js-checkbox @if($loop->last) kviz-radio_active @endif" data-price="{{ $set->price }}" data-stock="1">
                                    <div class="check"></div>
                                    <label>
                                        <span>{{ $set->getTranslatedAttribute('name', app()->getLocale()) }}
                                            <img src="{{ asset('images/icon/info.svg') }}" alt=""/></span>
                                        <input @if($loop->last) checked @endif class="js_set" type="radio" name="equipment" data-id="{{ $set['id'] }}" value="{{ $set->price }}"/>
                                    </label>
                                </div>
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
                <img src="{{ asset('images/prompt8.png') }}" alt=""/>
                <p>
                    {!! trans('portrait_buy_form.step8_bot_desc') !!}
                </p>
            </div>
        </div>
    </div>
</div>
