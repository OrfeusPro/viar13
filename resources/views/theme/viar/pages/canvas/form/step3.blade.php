<div class="accordion-title h2_old open" id="generator_sizes2">
    <span>{!! trans('portrait_buy_form.step3_title') !!}</span>
    <span class="tab-icon"></span>
</div>

<div class="accordion-content">
    <div class="accordion__sizes-blocks">
        <div class="accordion__sizes-block" data-id="1">
            <div class="kviz-row kviz-c-group">
                @php
                    $custom_sizes = explode(',', $canvas_head['sizes_30x40']);
                @endphp
                @if(is_array($custom_sizes) && !empty($custom_sizes))
                    @foreach ( collect($custom_sizes)->chunk(8) as $chunk_items)
                        <div class="kviz-group__item">
                            @foreach($chunk_items as $size_item)
                                @php
                                    $size_without_price = trim(substr($size_item, 0, strpos($size_item, '[')));

                                    $label_after_bracket = '';
                                    if (preg_match('/\]([hstHST])/', $size_item, $matches)) {
                                        $label_after_bracket = strtolower($matches[1]);
                                    }

                                    $size_clear_vals = explode('x', $size_without_price);
                                    $display_size_height = $size_clear_vals[0];
                                    $display_size_width = isset($size_clear_vals[1]) ? $size_clear_vals[1] : '';

                                    $clean_size_name = $size_without_price;

                                    $prices_vals = explode('-', get_string_between($size_item, '[', ']'));

                                    $sale_price = null;

                                    $check_price = $prices_vals[0];
                                    if(isset($prices_vals[1])){
                                        $sale_price = $prices_vals[1];
                                        $check_price = $sale_price;
                                    }

                                    $sale_price = $sale_price*$contry_mult;
                                    $check_price = $check_price*$contry_mult;
                                    $prices_vals[0] = $prices_vals[0]*$contry_mult;
                                @endphp
                                <div
                                    class="calcSize kviz-radio js-checkbox kviz-radio_active"
                                    data-stock="1" data-size="{{ $clean_size_name }}" data-price="{{ $check_price }}" data-price-label="{{ $label_after_bracket }}"
                                    onclick="setSize({{ $display_size_width }},{{ $display_size_height }})">
                                    <div class="check"></div>
                                    <label>
                                        <span>{{ $display_size_height }}x{{ $display_size_width }} {{ trans('gl.cm') }} -
                                            @if($sale_price != null)
                                                <del>{{ $prices_vals[0] }}€</del> <b>{{ $sale_price }}€</b>
                                            @else
                                                {{ $prices_vals[0] }}€
                                            @endif
                                        </span>

                                        <input type="radio" name="size"
                                               value="{{ $clean_size_name }}"
                                               data-price-label="{{ $label_after_bracket }}">
                                    </label>
                                    {!! sale_icon($size_item) !!}
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
        <div class="accordion__sizes-block hidden-block" data-id="2">
                @php
                    $custom_sizes2 = explode(',', $canvas_head['sizes_38x38']);
                @endphp
            <div class="kviz-row kviz-c-group">
                @if(is_array($custom_sizes2) && !empty($custom_sizes2))
                    @foreach ( collect($custom_sizes2)->chunk(8) as $chunk_items)
                        <div class="kviz-group__item">
                            @foreach($chunk_items as $size_item)
                                @php
                                    $size_without_price = trim(substr($size_item, 0, strpos($size_item, '[')));

                                    $label_after_bracket = '';
                                    if (preg_match('/\]([hstHST])/', $size_item, $matches)) {
                                        $label_after_bracket = strtolower($matches[1]);
                                    }

                                    $size_clear_vals = explode('x', $size_without_price);
                                    $display_size_height = $size_clear_vals[0];
                                    $display_size_width = isset($size_clear_vals[1]) ? $size_clear_vals[1] : '';

                                    $clean_size_name = $size_without_price;

                                    $prices_vals = explode('-', get_string_between($size_item, '[', ']'));

                                    $sale_price = null;

                                    $check_price = $prices_vals[0];
                                    if(isset($prices_vals[1])){
                                        $sale_price = $prices_vals[1];
                                        $check_price = $sale_price;
                                    }

                                    $sale_price = $sale_price*$contry_mult;
                                    $check_price = $check_price*$contry_mult;
                                    $prices_vals[0] = $prices_vals[0]*$contry_mult;
                                @endphp
                                <div
                                    class="calcSize kviz-radio js-checkbox kviz-radio_active"
                                    data-stock="1" data-size="{{ $clean_size_name }}" data-price="{{ $check_price }}" data-price-label="{{ $label_after_bracket }}"
                                    onclick="setSize({{ $display_size_width }},{{ $display_size_height }})">
                                    <div class="check"></div>
                                    <label>
                                        <span>{{ $display_size_height }}x{{ $display_size_width }} {{ trans('gl.cm') }} -
                                            @if($sale_price != null)
                                                <del>{{ $prices_vals[0] }}€</del> <b>{{ $sale_price }}€</b>
                                            @else
                                                {{ $prices_vals[0] }}€
                                            @endif
                                        </span>

                                        <input type="radio" name="size"
                                               value="{{ $clean_size_name }}"
                                               data-price-label="{{ $label_after_bracket }}">
                                    </label>
                                    {!! sale_icon($size_item) !!}
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
        <div class="accordion__sizes-block hidden-block" data-id="3">
            <div class="kviz-row kviz-c-group">
                @php $custom_sizes3 = explode(',', $canvas_head['sizes_40x30']); @endphp
                @if(is_array($custom_sizes3) && !empty($custom_sizes3))
                    @foreach ( collect($custom_sizes3)->chunk(8) as $chunk_items)
                        <div class="kviz-group__item">
                            @foreach($chunk_items as $size_item)
                                @php
                                    $size_without_price = trim(substr($size_item, 0, strpos($size_item, '[')));

                                    $label_after_bracket = '';
                                    if (preg_match('/\]([hstHST])/', $size_item, $matches)) {
                                        $label_after_bracket = strtolower($matches[1]);
                                    }

                                    $size_clear_vals = explode('x', $size_without_price);
                                    $display_size_height = $size_clear_vals[0];
                                    $display_size_width = isset($size_clear_vals[1]) ? $size_clear_vals[1] : '';

                                    $clean_size_name = $size_without_price;

                                    $prices_vals = explode('-', get_string_between($size_item, '[', ']'));

                                    $sale_price = null;

                                    $check_price = $prices_vals[0];
                                    if(isset($prices_vals[1])){
                                        $sale_price = $prices_vals[1];
                                        $check_price = $sale_price;
                                    }

                                    $sale_price = $sale_price*$contry_mult;
                                    $check_price = $check_price*$contry_mult;
                                    $prices_vals[0] = $prices_vals[0]*$contry_mult;
                                @endphp
                                <div
                                    class="calcSize kviz-radio js-checkbox kviz-radio_active"
                                    data-stock="1" data-size="{{ $clean_size_name }}" data-price="{{ $check_price }}" data-price-label="{{ $label_after_bracket }}"
                                    onclick="setSize({{ $display_size_width }},{{ $display_size_height }})">
                                    <div class="check"></div>
                                    <label>
                                        <span>{{ $display_size_height }}x{{ $display_size_width }} {{ trans('gl.cm') }} -
                                            @if($sale_price != null)
                                                <del>{{ $prices_vals[0] }}€</del> <b>{{ $sale_price }}€</b>
                                            @else
                                                {{ $prices_vals[0] }}€
                                            @endif
                                        </span>

                                        <input type="radio" name="size"
                                               value="{{ $clean_size_name }}"
                                               data-price-label="{{ $label_after_bracket }}">
                                    </label>
                                    {!! sale_icon($size_item) !!}
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
        <div class="accordion__sizes-block hidden-block" data-id="4">
            <div class="kviz-row kviz-c-group">
                @php $custom_sizes4 = explode(',', $canvas_head['sizes_60x30']); @endphp
                @if(is_array($custom_sizes4) && !empty($custom_sizes4))
                    @foreach ( collect($custom_sizes4)->chunk(8) as $chunk_items)
                        <div class="kviz-group__item">
                            @foreach($chunk_items as $size_item)
                                @php
                                    $size_without_price = trim(substr($size_item, 0, strpos($size_item, '[')));

                                    $label_after_bracket = '';
                                    if (preg_match('/\]([hstHST])/', $size_item, $matches)) {
                                        $label_after_bracket = strtolower($matches[1]);
                                    }

                                    $size_clear_vals = explode('x', $size_without_price);
                                    $display_size_height = $size_clear_vals[0];
                                    $display_size_width = isset($size_clear_vals[1]) ? $size_clear_vals[1] : '';

                                    $clean_size_name = $size_without_price;

                                    $prices_vals = explode('-', get_string_between($size_item, '[', ']'));

                                    $sale_price = null;

                                    $check_price = $prices_vals[0];
                                    if(isset($prices_vals[1])){
                                        $sale_price = $prices_vals[1];
                                        $check_price = $sale_price;
                                    }

                                    $sale_price = $sale_price*$contry_mult;
                                    $check_price = $check_price*$contry_mult;
                                    $prices_vals[0] = $prices_vals[0]*$contry_mult;
                                @endphp
                                <div
                                    class="calcSize kviz-radio js-checkbox kviz-radio_active"
                                    data-stock="1" data-size="{{ $clean_size_name }}" data-price="{{ $check_price }}" data-price-label="{{ $label_after_bracket }}"
                                    onclick="setSize({{ $display_size_width }},{{ $display_size_height }})">
                                    <div class="check"></div>
                                    <label>
                                        <span>{{ $display_size_height }}x{{ $display_size_width }} {{ trans('gl.cm') }} -
                                            @if($sale_price != null)
                                                <del>{{ $prices_vals[0] }}€</del> <b>{{ $sale_price }}€</b>
                                            @else
                                                {{ $prices_vals[0] }}€
                                            @endif
                                        </span>

                                        <input type="radio" name="size"
                                               value="{{ $clean_size_name }}"
                                               data-price-label="{{ $label_after_bracket }}">
                                    </label>
                                    {!! sale_icon($size_item) !!}
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
