<style>
    .range__box{
        display:none;
    }
</style>

<div class="filter-interior tabs-item tabs-item2">
    <form>
        <div class="filter-accordion">
            <div class="accordion-title h2_old open"><span>@lang('modular_pictures.index39')</span></div>
            <div class="accordion-content">

                <div class="interior-slider">
                   @foreach($def_inter_images as $def_int)
                   <div>
                       <div
                        data-item="{{ $loop->index }}"
                        class="interior-item @if($loop->first) active @endif"
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
                            <input type="number"
                                placeholder="{{ $def_int->size_1 }} {{ $int_globs['cm'] }}"
                                min="{{ $def_int->size_1 }}" max="{{ $def_int->size_2 }}" name="wall_size">

                                <div class="range_box">
                                    <input type="range" class="zoomRange range-input"
                                    min="{{ $def_int->size_1 }}" max="{{ $def_int->size_2 }}"
                                        step="1" />
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
            @if(!isset($no_rams))
                <div class="accordion-title h2_old open frames">
                    <span>2.</span> @lang('modular_pictures.index41')
                </div>
                <div class="accordion-content frames">
                    <div class="ramu-slider js_rams">
                        @foreach($def_inter_rams as $ram)
                        <label>
                            <div
                            data-price="{{ $ram->price }}"
                            data-id="{{ $ram->id }}"
                            @if($ram->price == 0) ramu_zero_item
                            data-interior="empty"
                            class="ramu-item active"
                            @else
                            class="ramu-item"
                            data-src="{{ Voyager::image($ram->img) }}" data-width="{{ $ram->css_params }}"
                            @endif>
                                <input
                                style="opacity:0;
                                position:absolute;pointer-events:none;"
                                type="radio"
                                name="ram_id" value="{{ $ram->id }}">
                                <span class="code">{{ $ram->name }}</span>
                                <div class="img">
                                    <i class="icon-down-arrow"></i>
                                    @if($ram->price != 0)
                                    <img @altAttrs($ram, 'img_bg', data_get($ram, 'img_bg')) src="{{ Voyager::image($ram->img_bg) }}">
                                    @endif
                                </div>
                                <span class="price">
                                    @if($ram->price != 0)
                                    + {{ $ram->price }} &euro;
                                    @else
                                    --
                                    @endif
                                </span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
        <div class="total-price">
            <div class="total-title"><span>{!!$int_globs['total_price_title'] !!}</span></div>
            <div class="sum">
            <h4 id="totalSum"><span id="totalPrice"></span> &euro;</h4>
                    <div class="js_spinner"></div>
                </div>
            <ul>
                {!!$int_globs['timings_text'] !!}
            </ul>
        </div>
    </form>
</div>
