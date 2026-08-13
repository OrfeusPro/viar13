<div class="formalization-item">
    <div class="formalization-box">
        <div class="formalization-tab">
            <img width="25" height="25" src="{{ asset(config('theme.current') . '/images/sharj/new/format3.svg') }}" alt="">

            @lang("simpson.formalization-items.formalization-item5.formalization-tab")

            <span class="tab-icon"></span>
        </div>
        <div class="formalization-content">
            <div class="formalization-content--inner">
                <p class="underline-text sizesPopup-js">
                    @lang("simpson.formalization-items.formalization-item5.sizesPopup-js")
                </p>
                <div class="kviz-row kviz-c-group js_sizes active" data-form="1">
                    @php
                        $custom_sizes = explode(',', $item['custom_size_prices']);
                    @endphp
                    @if(is_array($custom_sizes) && !empty($custom_sizes))
                        @foreach ( collect($custom_sizes)->chunk(5) as $chunk_items)
                             <?php $loop->index == 0 ? $set_active = 1 : $set_active = 0; ?>
                            <div class="kviz-group__item">
                                @foreach($chunk_items as $size_item)
                                    @php
                                        $size_clear_vals = explode('x', substr($size_item, 0, strpos($size_item, '[')));
                                        $prices_vals = explode('-', get_string_between($size_item, '[', ']'));

                                        $sale_price = null;
                                        if(isset($prices_vals[1])){
                                            $sale_price = $prices_vals[1];
                                        }
                                    @endphp
                                    <div itemprop="offers" itemtype="https://schema.org/Offer" itemscope>
                                        <link itemprop="url" href="{{ url(Request::url()) }}" />
                                        <meta itemprop="availability" content="https://schema.org/InStock" />
                                        <meta itemprop="priceCurrency" content="EUR" />
                                        <meta itemprop="price" content="
                                            @if($sale_price != null)
                                       {{ $sale_price*$contry_mult }}
                                        @else
                                       {{ $prices_vals[0]*$contry_mult }}
                                        @endif
                                        "/>

                                        <meta itemprop="description"  content="{{ $cleanName.' '.$size_clear_vals[0] }}x{{ $size_clear_vals[1] }} {{ trans('gl.cm') }}" >
                                    </div>
                                    <label class="kv__size__item kviz-radio js-checkbox @if($set_active == 1 && $loop->index == 0) kviz-radio_active @endif"
                                         data-stock="1"
                                          @if($sale_price != null)
                                         data-price="{{ $sale_price*$contry_mult }}"
                                         @else
                                         data-price=" {{ $prices_vals[0]*$contry_mult }}"
                                        @endif
                                    >
                                        <div class="check"></div>
                                        <div>
                                                                                <span>{{ $size_clear_vals[0] }}x{{ $size_clear_vals[1] }} {{ trans('gl.cm') }} -
                                                                                    @if($sale_price != null) <span class="dec_lt"> @endif{{ $prices_vals[0]*$contry_mult }}€ @if($sale_price != null) </span>@endif
                                                                                    @if($sale_price != null)
                                                                                        <b>{{ $sale_price*$contry_mult }}€</b>
                                                                                    @endif
                                                                                </span>
                                            <input type="radio" name="size1"
                                                   @if($set_active == 1 && $loop->index == 0) checked @endif
                                                   data-price="{{ $prices_vals[0]*$contry_mult }}"
                                                   data-sale-price="{{ $sale_price*$contry_mult }}"
                                                   value="{{ substr($size_item, 0, strpos($size_item, '[')) }}"/>
                                        </div>
                                        {!! sale_icon($size_item) !!}
                                    </label>
                                @endforeach
                            </div>
                        @endforeach
                    @endif
                </div>

                <div class="kviz-row kviz-c-group js_sizes" data-form="2" style="display:none;">
                    @php
                        $custom_sizes = explode(',', $item['custom_size_prices_form2']);
                    @endphp
                    @if(is_array($custom_sizes) && !empty($custom_sizes))
                        @foreach ( collect($custom_sizes)->chunk(5) as $chunk_items)
                             <?php $loop->index == 0 ? $set_active = 1 : $set_active = 0; ?>
                            <div class="kviz-group__item">
                                @foreach($chunk_items as $size_item)
                                    @php
                                        $size_clear_vals = explode('x', substr($size_item, 0, strpos($size_item, '[')));
                                        $prices_vals = explode('-', get_string_between($size_item, '[', ']'));

                                        $sale_price = null;
                                        if(isset($prices_vals[1])){
                                            $sale_price = $prices_vals[1];
                                        }
                                    @endphp

                                    <label
                                         @if($sale_price != null)
                                         data-price="{{ $sale_price*$contry_mult }}"
                                         @else
                                         data-price=" {{ $prices_vals[0]*$contry_mult }}"
                                         @endif
                                         class="kv__size__item kviz-radio js-checkbox @if($set_active == 1 && $loop->index == 0) kviz-radio_active @endif"
                                         data-stock="1">
                                        <em class="check"></em>
                                        <label>
                                                                                <span>{{ $size_clear_vals[0] }}x{{ $size_clear_vals[1] }} {{ trans('gl.cm') }} -

                                                                                    @if($sale_price != null) <span class="dec_lt"> @endif{{ $prices_vals[0]*$contry_mult }}€ @if($sale_price != null) </span>@endif
                                                                                    @if($sale_price != null)
                                                                                        <b>{{ $sale_price*$contry_mult }}€</b>
                                                                                    @endif
                                                                                </span>
                                            <input type="radio" name="size2"
                                                   @if($set_active == 1 && $loop->index == 0) checked @endif
                                                   data-price="{{$prices_vals[0]*$contry_mult }}"
                                                   data-sale-price="{{ $sale_price*$contry_mult }}"
                                                   value="{{ substr($size_item, 0, strpos($size_item, '[')) }}"/>
                                        </label>
                                        {!! sale_icon($size_item) !!}
                                    </label>
                                @endforeach
                            </div>
                        @endforeach
                    @endif
                </div>
                <div class="kviz-row kviz-c-group js_sizes" data-form="3" style="display:none;">
                    @php
                        $custom_sizes = explode(',', $item['custom_size_prices_form3']);
                    @endphp
                    @if(is_array($custom_sizes) && !empty($custom_sizes))
                        @foreach ( collect($custom_sizes)->chunk(5) as $chunk_items)
                             <?php $loop->index == 0 ? $set_active = 1 : $set_active = 0; ?>
                            <div class="kviz-group__item">
                                @foreach($chunk_items as $size_item)
                                    @php
                                        $size_clear_vals = explode('x', substr($size_item, 0, strpos($size_item, '[')));
                                        $prices_vals = explode('-', get_string_between($size_item, '[', ']'));

                                        $sale_price = null;
                                        if(isset($prices_vals[1])){
                                            $sale_price = $prices_vals[1];
                                        }
                                    @endphp

                                    <label
                                        @if($sale_price != null)
                                         data-price="{{ $sale_price*$contry_mult }}"
                                         @else
                                         data-price=" {{ $prices_vals[0]*$contry_mult }}"
                                         @endif
                                        class="kv__size__item kviz-radio js-checkbox @if($set_active == 1 && $loop->index == 0) kviz-radio_active @endif"
                                         data-stock="1">
                                        <div class="check"></div>
                                        <label>
                                                                                <span>{{ $size_clear_vals[0] }}x{{ $size_clear_vals[1] }} {{ trans('gl.cm') }} -
                                                                                    @if($sale_price != null) <span class="dec_lt"> @endif{{ $prices_vals[0]*$contry_mult }}€ @if($sale_price != null) </span>@endif
                                                                                    @if($sale_price != null)
                                                                                        <b>{{ $sale_price*$contry_mult }}€</b>
                                                                                    @endif
                                                                                </span>
                                            <input type="radio" name="size3"
                                                   @if($set_active == 1 && $loop->index == 0) checked @endif
                                                   data-price="{{$prices_vals[0]*$contry_mult }}"
                                                   data-sale-price="{{ $sale_price*$contry_mult }}"
                                                   value="{{ substr($size_item, 0, strpos($size_item, '[')) }}"/>
                                        </label>
                                        {!! sale_icon($size_item) !!}
                                    </label>
                                @endforeach
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="formalization-prompt">
        <div class="formalization-prompt--wrapper">
            <div class="formalization-prompt--inner">
                <img src="{{ asset('images/prompt3.png') }}" alt=""/>
                <p>
                     {!! trans('portrait_buy_form.step3_bot_desc') !!}
                </p>
            </div>
        </div>
    </div>
</div>
