<div class="filter-interior tabs-item tabs-item2">
    <form method="POST" enctype="multipart/form-data" class="js_inter_form"
        action="{{ route('add_item_to_basket_inter') }}">
        {{ csrf_field() }}

        <div class="filter-accordion">
            <div class="accordion-title h2_old open"><span>{{ $int_globs['def_int_title'] }}</span></div>
            <div class="accordion-content">

                <div class="interior-slider">
                    @foreach($def_inter_images as $def_int)
                    <div>
                        <div class="interior-item @if($loop->first) active @endif"
                            data-size="{{ $def_int->size_1 }},{{ $def_int->size_2 }}"
                            data-interior="{{ Voyager::image($def_int->inter_img) }}">
                            <i class="icon-down-arrow"></i>
                            <img src="{{ Voyager::image($def_int->inter_img) }}" @altAttrs($def_int, 'inter_img', data_get($def_int, 'inter_img'))>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="interior-sizes">
                    <div class="size-item">
                        <span>@lang('modular_pictures.index40')</span>

                        @foreach($def_inter_images as $def_int)
                        <div class="range__box" @if($loop->first) style="display:block;" @endif>
                            <input type="number" placeholder="{{ $def_int->size_1 }} {{ $int_globs['cm'] }}"
                                min="{{ $def_int->size_1 }}" max="{{ $def_int->size_2 }}" name="wall_size">

                            <div class="range_box">
                                <input type="range" class="zoomRange range-input" min="{{ $def_int->size_1 }}"
                                    max="{{ $def_int->size_2 }}" step="1" />
                                <div class="changeSm">
                                    <div><span class="js_s1">{{ $def_int->size_1 }}</span> <i>{{ $int_globs['cm'] }}</i>
                                    </div>
                                    <div><span class="js_s2">{{ $def_int->size_2 }}</span> <i>{{ $int_globs['cm'] }}</i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="accordion-title h2_old open"><span>2.</span> {!!$int_globs['rama_title'] !!}</div>
            <div class="accordion-content">
                @include('partials.rams')
            </div>
        </div>
        <div class="total-price">
            <div class="total-title"><span>{!!$int_globs['total_price_title'] !!}</span></div>
            <div class="sum">
                <h4 class="js_inter_text_summ" data-total="{{ $int_globs['price'] }}"><span></span> &euro;</h4>
            </div>
            <a href="#" class="js_add_basket_inter add_inter canvas-tab2" href="javascript:void(0)">
                <span>{!!$int_globs['add_to_bask_title'] !!}</span></a>
            {!!$int_globs['timings_text'] !!}
        </div>
    </form>
</div>
