@extends('layots.common')

@section('title', $item['name'])

@section('og_tags')
    <meta property="og:title" content="{{ $item['name'] }}" />
@endsection

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('css/canvas.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/portrait-name.css') }}?v=0.01">
@endsection

@section('content')

    <div itemtype="https://schema.org/Product" itemscope>
        <meta itemprop="name" content="{{ $item['name'] }}" />
        <div itemprop="offers" itemtype="https://schema.org/Offer" itemscope>
            <link itemprop="url" href="{{ url(Request::url()) }}" />
            <meta itemprop="availability" content="https://schema.org/InStock" />
            <meta itemprop="priceCurrency" content="EUR" />
            <meta itemprop="price" content="0" />
            @php
                $mt = Carbon\Carbon::now();
            @endphp
            <meta itemprop="priceValidUntil" content="{{ $mt->toDateTimeString() }}" />
        </div>
    </div>

    <div class="bread-crumbs">
        <i class="icon-icon3"></i>
        <ul vocab="https://schema.org/" typeof="BreadcrumbList">
            <li property="itemListElement" typeof="ListItem">
                <a property="item" typeof="WebPage" href="{{ url('/') }}">
                    <span property="name">@lang('account.index1')</span></a>
                <meta property="position" content="1">
            </li>
            <li property="itemListElement" typeof="ListItem">
                <span property="name">{{ $item['name'] }}</span>
                <meta property="position" content="2">
            </li>
        </ul>
    </div>

    @include('partials.graph_portrait.generator')

    <script src="{{ asset('js/jcf.min.js') }}"></script>
    <script src="{{ asset('js/jcf.file.min.js') }}"></script>
    <script src="{{ asset('js/jcf.radio.min.js') }}"></script>
    <script src="{{ asset('js/jcf.checkbox.min.js') }}"></script>
    <script src="{{ asset('js/jcf.range.min.js') }}"></script>
    <script src="{{ asset('js/jquery.collapse_storage.min.js') }}"></script>
    <script src="{{ asset('js/jquery.collapse.min.js') }}"></script>
    <script src="{{ asset('js/slick.min.js') }}"></script>
    <script src="{{ asset('js/templates.js') }}"></script>
    <script src="{{ asset('js/canvas.min.js') }}"></script>
    <script src="{{ asset('js/dropzone/dropzone.min.js') }}"></script>

    <script>
        var preview, file, reader;
        var user_price = 0;
        var user_count = 1;
        var glob_price = 0;
        var dost_price = 0;

        calcTotalPrice();

        $(document).on('click', '.download', function() {
            var url = $(this).find('img').attr('src');
            $('#tab1__generator').children('img').attr('src', url);
        });

        $('.calcSize').on('click', function() {
            let sizePrice = $(this).data('price');
            let sizeSize = $(this).data('size');
            calcTotalPrice(sizePrice, sizeSize);
        });

        $('.calcExecution').on('click', function() {
            calcTotalPrice(null, null, $(this).data('execution'));
        });

        $('input[name="canvas_type"]').on('change', function() {
            calcTotalPrice();
        });

        $('input[name="decoration"]').on('change', function() {
            calcTotalPrice();
        });

        $(document).on('click', '.comments-item .jcf-radio', function(e) {
            setTimeout(() => {
                calcTotalPrice();
            }, 200);
        });

        $(document).on('click', '.js_size', function(e) {
            $(this).closest('ul').find('.active').removeClass('active');
            $(this).toggleClass('active');
            calcTotalPrice();
        });

        $(document).on('click', '.js_custom_user', function(e) {
            user_price = $(this).data('price');
            user_count = $(this).data('count');
            $('.js__personal_checker').find('input').removeAttr('checked');
            $('.js__personal_checker').removeClass('jcf-label-active');
            $('.js__personal_checker').find('.jcf-radio').removeClass('jcf-checked');
            $(this).find('a').toggleClass('active');
            $(this).siblings().find('a').removeClass('active');
            $('.js_custom_user_count').val(user_count);
            $('.js_price_ppl').val(user_price);
            calcTotalPrice();
        });


        $(document).on('click', '.js_peoples_nums button', function(e) {
            e.preventDefault();
            var is_return = 0;
            var price_per_one = $('.js_custom_user[data-count="1"]').data('price');

            user_count = $('.js_custom_user_count').val();

            $('.js_custom_user').each(function(e) {
                var thh = $(this);
                var val = thh.data('count');

                if (parseInt(val) == parseInt(user_count)) {
                    console.log('cur', val);
                    console.log('selected', user_count);
                    user_price = $(this).data('price');
                    user_count = $(this).data('count');
                    $('.js_custom_user_count').val(user_count);
                    $('.js__personal_checker').find('input').removeAttr('checked');
                    $('.js__personal_checker').removeClass('jcf-label-active');
                    $('.js__personal_checker').find('.jcf-radio').removeClass('jcf-checked');
                    $(this).find('a').addClass('active');
                    $(this).siblings().find('a').removeClass('active');
                    calcTotalPrice();
                    is_return = 1;
                    return;
                }
            });

            if (user_count == 1 || user_count < 1 || isNaN(user_count)) {
                $('.js__personal_checker').trigger('click');
                $('.js__personal_checker').addClass('jcf-label-active');
                $('.js__personal_checker').find('.jcf-radio').addClass('jcf-checked');
                $('.js__group_checker').removeClass('jcf-label-active');
                $('.js__group_checker').find('.jcf-radio').removeClass('jcf-checked');
                is_return = 1;
            }

            if (is_return == 1) return;
            user_price = (user_count * price_per_one) - 10;
            $('.js__sizes').find('li a').removeClass('active');
            $('.js_price_ppl').text(user_price);
            calcTotalPrice();
        });

        $(document).on('click', '.js__personal_checker', function(e) {
            $('.js__sizes').addClass('sizes_unactive');
            $('.js_peoples_nums').addClass('sizes_unactive');
            user_price = 0;
            user_count = 1;
            calcTotalPrice();
        });

        $(document).on('click', '.js__group_checker', function(e) {
            $('.js__sizes').removeClass('sizes_unactive');
            $('.js_peoples_nums').removeClass('sizes_unactive');
        });

        // вид холста
        $(document).on('click', '.js__canv_radio .jcf-radio', function(e) {
            price_holst = $(this).find('input').val();
            calcTotalPrice();
        });

        // худ офрмл.
        $(document).on('click', '.js_decor_item .jcf-radio', function(e) {
            price_hud_of = $(this).find('input').val();
            calcTotalPrice();
        });

        $('input[name="boxes[]"]').on('change', function() {
            calcTotalPrice();
        });

        function calcExecutionPrice(sizeSize, execution) {
            let executionPrice = 0;

            if (execution == 1) {
                if (sizeSize == undefined) {
                    sizeSize = $('.accordion-content .js_size.active').data('size');

                    var height = sizeSize.split('x')[0];
                    var length = sizeSize.split('x')[1];
                } else {
                    var height = sizeSize.split('x')[0];
                    var length = sizeSize.split('x')[1];
                }

                let area = height * length / 10000;

                if (area <= 0.4)
                    executionPrice = area * 80;
                else if (area > 0.4 && area <= 1)
                    executionPrice = area * 60;
                else if (area >= 1.01)
                    executionPrice = area * 50;
            }

            return executionPrice;
        }

        function calcBoxesPrice() {
            let price = 0;
            $('input[name="boxes[]"]:checked').each(function() {
                price += parseInt($(this).val());
            });
            return price;
        }

        var rp = 0;

        function calcTotalPrice(sizePrice = null, sizeSize = null, execution = null) {
            if (!sizePrice)
                sizePrice = $('.calcSize.active').data('price');
            if (!sizeSize)
                sizeSize = $('.calcSize.active').data('size');
            if (!execution)
                execution = $('.calcExecution.active').data('execution');
            let totalPrice = 0;
            totalPrice += parseFloat(sizePrice);
            totalPrice += parseFloat(calcExecutionPrice(sizeSize, execution));

            if (typeof sizeSize == 'undefined') {
                return;
            }

            // холст
            let defPrice = $('.js_size.active').data('price');
            let canvasTypePrice = parseFloat($('input[name="canvas_type"]:checked').val());
            let cur_sizes_add_coef = defPrice * canvasTypePrice;

            let cur_sizes_add = parseFloat(cur_sizes_add_coef).toFixed(2);

            if (cur_sizes_add != 0.00) {
                totalPrice = cur_sizes_add;
            };

            totalPrice = parseFloat(totalPrice) + parseInt(user_price);
            toltaPrice = parseFloat(totalPrice).toFixed(2);

            totalPrice = parseFloat(totalPrice) + parseFloat(dost_price);
            totalPrice = totalPrice.toFixed(2);

            // худ. оформление
            totalPrice = parseFloat(totalPrice).toFixed(2);
            let coef_1 = parseInt($('input[name="decoration"]:checked').data('coef_sm'));
            let coef_2 = parseInt($('input[name="decoration"]:checked').data('coef_md'));
            let coef_3 = parseInt($('input[name="decoration"]:checked').data('coef_lg'));

            var cur_size = $('.js_size.active').data('size');
            var cur_size_calc = cur_size.split("x");
            var s1 = parseInt(cur_size_calc[0]);
            var s2 = parseInt(cur_size_calc[1]);

            let area = s1 * s2 / 10000;

            if (area <= 0.4) {
                decorAddPrice = area * coef_1;
            } else if (area > 0.4 && area <= 1) {
                decorAddPrice = area * coef_2;
            } else if (area >= 1.01) {
                decorAddPrice = area * coef_3;
            }
            decorAddPrice = parseFloat(decorAddPrice);

            if (isNaN(decorAddPrice)) {
                decorAddPrice = 0;
            }

            totalPrice = parseFloat(totalPrice);
            totalPrice += decorAddPrice;
            totalPrice = totalPrice.toFixed(2);
            totalPrice = parseFloat(totalPrice) + parseFloat(calcBoxesPrice());
            totalPrice = calc_float_val(totalPrice);

            var ram_price = $('.ramu-item.active').data('price');

            $('.total_with_total').remove();
            $('.ramm__pr').remove();

            if (typeof ram_price === 'undefined') {
                rp = 0;
            } else {
                if ($('.js_size.active'.length)) {
                    var cur_size = $('.js_size.active').data('size');
                    if (typeof cur_size === 'undefined') {
                        return;
                    }
                    var cur_size_calc = cur_size.split("x");
                    var s1 = parseInt(cur_size_calc[0]);
                    var s2 = parseInt(cur_size_calc[1]);

                    rp = ((s1 + s1 + s2 + s2) / 100) * parseInt(ram_price);
                } else {
                    rp = 0;
                }
            }

            if (rp != 0) {
                var cur_summ_price = parseFloat(totalPrice) + parseFloat(rp);
                cur_summ_price = cur_summ_price.toFixed(2);
                var tp = calc_float_val(totalPrice);

                $('.tabs-item #totalPrice').html(tp);
                $('.sum__mod #glob_summ2').html(tp);
                $('#glob_summ').html(tp);

                var rm_txt = $('body').data('for_ram');

                $('.sum').append("<span class='ramm__pr'>+" + Math.round(rp) + "€ " + rm_txt + "</span>");

                var vs_text = $('body').data('full');

                $('.sum')
                    .append("<div class='total_with_total'><span class='vs_text'>" + vs_text + '</span> ' + cur_summ_price +
                        " €</div>");

                $('#glob_summ2').html(cur_summ_price);
                $('#glob_summ').html(totalPrice);
                $('.js_sbm__calc').attr('data-total', cur_summ_price);
                glob_price = cur_summ_price;
            } else {
                var cur_summ_price = totalPrice;
                cur_summ_price = cur_summ_price;

                $('#glob_summ').html(cur_summ_price);
                $('#glob_summ2').html(cur_summ_price);
                $('.tabs-item #totalPrice').html(cur_summ_price);
                $('.js_sbm__calc').attr('data-total', cur_summ_price);
                glob_price = cur_summ_price;
            }
        }

        $(document).on('click', '.ramu-item', function() {
            setTimeout(() => {
                calcTotalPrice();
            }, 200);
        });

        $('.dost_items input').change(function(e) {
            dost_price = $(this).val();
            setTimeout(() => {
                calcTotalPrice();
            }, 200);
        });
        $(document).ready(function() {
            $('.jcf-button-content').text('{{ $canvas_head['load_btn_text'] }}');
            $('.jcf-fake-input').text('{{ $canvas_head['load_btn_text'] }}');
            let des = $('#real_file_input').data('desc');
            $(".comments .jcf-fake-input").text(des);
        });

    </script>

    @include('partials.grap_styl_paint_send')
    @include(  'partials.schema_reviews')
@endsection
