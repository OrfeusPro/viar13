<form id="item_opt_form">
    <input type="hidden" id="product_id" name="product_id" value="{{ $item->id }}">
    <div class="filter-accordion">
        <div class="accordion-title h2_old open"><span>1.</span> {!! $canvas_head['c_tab3_title'] !!}</span></div>
        <div class="accordion-content">
            @include('partials.custom_sizes_calc_cat')
        </div>
        <div class="accordion-title h2_old open"><span>2.</span> {!! $canvas_head['c_tab5_title'] !!}</div>
        <div class="accordion-content">
            <div class="execution">
                @include('pages._partials._includes._calc._executions')
            </div>
        </div>
        <div class="accordion-title h2_old"><span>3.</span> {!! $canvas_head['c_tab6_title'] !!}</span>
        </div>
        <div class="accordion-content">
            <div class="canvas-items js__holsts">
                @foreach($galleryHolsts as $holst)
                <div data-id="{{ $holst['id'] }}" class="canvas-item js__canv_radio form_tab_tpl"
                    data-price="{{ $holst['price'] }}" data-id="{{  $holst['id'] }}"
                    data-name="{{ $holst->getTranslatedAttribute('name', app()->getLocale()) }}"
                    data-ratio="{{ $holst->getTranslatedAttribute('density', app()->getLocale()) }}">
                    <input @if($loop->index == 1) checked @endif
                    name="q"
                    type="radio"
                    data-id="{{ $holst->id }}"
                    value="{{ $holst->price }}"
                    >
                    <label>{{ $holst['name'] }}
                        <span>({{ $holst['density'] }})</span>
                        @if($holst['hit'] == 1)<strong>Hit!</strong> @endif
                        <!--<i class="icon-icon1"></i>-->
                    </label>
                </div>
                @endforeach
            </div>
        </div>
        <div class="accordion-title h2_old"><span>4.</span> <span>{!! $canvas_head['c_tab8_title'] !!}</span></div>
        <div class="accordion-content">
            <div class="comments">
                <textarea class="js_text" name="user_comment" id="userComment"></textarea>
                <label class="lbl_upl" for="real_file_input">
                    <input id="real_file_input" data-desc="{{ trans('gl.load_btn_text') }}" name="photo_ex"
                        accept="image/*,image/heif,image/heic" type="file">
                </label>
                <div class="js_pick_items">
                    @include('pages._partials._includes._calc._boxes')
                </div>
            </div>
        </div>

    </div>

    <div data-price="{{$item->minSumPrice}} &euro; - {{$item->maxSumPrice}} &euro;" class="total-price table1">
        <div class="total-title"><span>@lang('modular_pictures.index47')</span></div>
        <div class="sum">
            <h4>
                <div id="glob_summ" style="display:inline-block;"></div>&euro;
            </h4>
        </div>
        <a href="javascript:void(0)" class="js_sbm__calc gall_item_btn"
            data-route="{{ route('add_item_to_basket_portrait') }}" data-id="{{ $item['id'] }}"
            data-name="{{ $item['name'] }}" id="t1_submit_btn"><span>@lang('modular_pictures.index37')</span></a>
        <ul class="dost_items">
            <li><strong>{!! trans('gl.srok_izg') !!}</strong></li>
            <li>
                <label>
                    <input checked type="radio" name="dost_time" value="{!! trans('gl.standart_price') !!}">
                    <span></span>
                    <span class="st_text">{!! trans('gl.standart_text') !!}</span>
                    <span>{!! trans('gl.standart_price') !!} €</span>
                </label>
            </li>

            <li>
                <label>
                    <input type="radio" name="dost_time" value="{!! trans('gl.express_price') !!}">
                    <span></span>
                    <span class="st_text">{!! trans('gl.express_text') !!}</span>
                    <span>{!! trans('gl.express_price') !!} €</span>
                </label>
            </li>
        </ul>
    </div>
</form>
