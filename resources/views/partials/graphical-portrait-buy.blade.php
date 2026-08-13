@extends('layots.common')

@section('title', $item['name'])

@section('styles')

<link rel="stylesheet" type="text/css" href="{{ asset('css/canvas.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('css/portrait-name.css') }}?v=0.01">
{{-- <link rel="stylesheet" type="text/css" href="{{ asset('css/modular-pictures.css') }}?v=0.01"> --}}

@endsection

@section('content')

<div class="bread-crumbs">
    <i class="icon-icon3"></i>

    <ul vocab="https://schema.org/" typeof="BreadcrumbList">
        <li property="itemListElement" typeof="ListItem">
          <a property="item" typeof="WebPage"
              href="{{ url('/') }}">
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

{{-- <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script> --}}




<script>
        var preview,file,reader;
        var user_price  = 0;
        var user_count = 1;
        var glob_price = 0;

        calcTotalPrice();

        $(document).on('click', '.download', function () {
            var url = $(this).find('img').attr('src');
            $('#tab1__generator').children('img').attr('src', url);
        });

        $('.calcSize').on('click', function () {

            let sizePrice = $(this).data('price');
            let sizeSize = $(this).data('size');

            calcTotalPrice(sizePrice, sizeSize);
        });

        $('.calcExecution').on('click', function () {
            calcTotalPrice(null, null, $(this).data('execution'));
        });

        $('input[name="canvas_type"]').on('change', function () {
            calcTotalPrice();
        });

        $('input[name="decoration"]').on('change', function () {
            calcTotalPrice();
        });

        $(document).on('click', '.comments-item .jcf-radio', function(e){
            setTimeout(() => {
                calcTotalPrice();
            }, 200);
        });

        $(document).on('click', '.js_size', function(e){

            calcTotalPrice();
        });

        $(document).on('click', '.js_peoples_nums button', function(e){
            e.preventDefault();
            console.log('click001');

            var price_per_one = $('.js_custom_user[data-count="1"]').data('price');

            user_count = $('.js_custom_user_count').val();
            $('.js_custom_user').each(function(e){
                var val = $(this).data('count');
                if(val == user_count){
                    $('.js_custom_user').children().removeClass('active');
                    $(this).children().addClass('active');
                    $(this).trigger('click');
                    return;
                }
            });

            user_price = user_count * price_per_one;

            // $('.js__sizes').find('li a').removeClass('active');

            $('.js_price_ppl').text(user_price);
            calcTotalPrice();
        });



        $(document).on('click', '.js_custom_user', function(e){

            user_price = $(this).data('price');
            user_count = $(this).data('count');
            $('.js__personal_checker').find('input').removeAttr('checked');
            $('.js__personal_checker').removeClass('jcf-label-active');
            $('.js__personal_checker').find('.jcf-radio').removeClass('jcf-checked');
            $(this).find('a').toggleClass('active');
            $(this).siblings().find('a').removeClass('active');
            calcTotalPrice();
        });

        $(document).on('click', '.js__personal_checker', function(e){
            $('.js__sizes').addClass('sizes_unactive');
            $('.js_peoples_nums').addClass('sizes_unactive');
            user_price = $('.js_custom_user[data-count="1"]').data('price');
            user_count = 1;
            calcTotalPrice();
        });


        $(document).on('click', '.js__group_checker', function(e){
            $('.js__sizes').removeClass('sizes_unactive');
            $('.js_peoples_nums').removeClass('sizes_unactive');
        });

        $(document).on('click', '.js_peoples_nums button', function(e){
            e.preventDefault();
            var price_per_one = $('.js_custom_user[data-count="1"]').data('price');

            user_count = $('.js_custom_user_count').val();
            $('.js_custom_user').each(function(e){
                var val = $(this).data('count');
                if(val == user_count){
                    $(this).trigger('click');
                    return;
                }
            });

            user_price = user_count * price_per_one;

            $('.js__sizes').find('li a').removeClass('active');

            $('.js_price_ppl').text(user_price);
            calcTotalPrice();
        });

        // вид холста
        $(document).on('click', '.js__canv_radio .jcf-radio', function(e){
            price_holst = $(this).find('input').val();
            calcTotalPrice();
        });

        // худ офрмл.
        $(document).on('click', '.js_decor_item .jcf-radio', function(e){
            price_hud_of = $(this).find('input').val();
            calcTotalPrice();
        });

        $('input[name="boxes[]"]').on('change', function () {
            calcTotalPrice();
        });



        function calcExecutionPrice(sizeSize, execution) {
            let executionPrice = 0;


            if (execution == 1) {
                if(sizeSize == undefined){
                    sizeSize =  $('.accordion-content .js_size.active').data('size');

                    var height = sizeSize.split('x')[0];
                    var length = sizeSize.split('x')[1];
                }else{
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

            $('input[name="boxes[]"]:checked').each(function () {
                price += parseInt($(this).val());
            });

            return price;
        }

        var rp  = 0;
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


            let canvasTypePrice = parseFloat($('input[name="canvas_type"]:checked').val());

            totalPrice += parseFloat(canvasTypePrice);
            totalPrice += parseFloat(user_price);

            let decorationPrice = parseFloat($('input[name="decoration"]:checked').val());


            if(isNaN(decorationPrice)){
                decorationPrice  = 0;
            }

            totalPrice += decorationPrice ? parseFloat(decorationPrice) : 0;

            totalPrice += parseFloat(calcBoxesPrice());

            totalPrice =  Math.round(totalPrice);


            var ram_price = $('.ramu-item.active').data('price');

            $('.total_with_total').remove();
            $('.ramm__pr').remove();

            if(typeof ram_price === 'undefined'){
                rp = 0;
            }else{
                if($('.js_size.active'.length)){
                    var cur_size  = $('.js_size.active').data('size');
                    if(typeof cur_size === 'undefined'){
                        return;
                    }
                    var cur_size_calc = cur_size.split("x");
                    var s1 = parseInt(cur_size_calc[0]);
                    var s2 = parseInt(cur_size_calc[1]);

                    rp = ((s1+s1+s2+s2)/100)*parseInt(ram_price);
                }else{
                    rp  = 0;
                }
            }


            if(rp != 0){
                var cur_summ_price =   Math.round(totalPrice) + Math.round(rp);

                $('.tabs-item #totalPrice').html(Math.round(totalPrice));
                $('.sum__mod #glob_summ2').html(Math.round(totalPrice));
                $('#glob_summ').html(Math.round(totalPrice));

                var rm_txt = $('body').data('for_ram');

                $('.sum').append("<span class='ramm__pr'>+"+Math.round(rp)+"€ "+rm_txt+"</span>");

                var vs_text = $('body').data('full');

                $('.sum')
                .append("<div class='total_with_total'><span class='vs_text'>"+vs_text+'</span> '+Math.round(cur_summ_price)+" €</div>");

                $('#glob_summ2').html(Math.round(cur_summ_price));
                $('#glob_summ').html(Math.round(totalPrice));
                $('.js_sbm__calc').attr('data-total',Math.round(cur_summ_price));
                glob_price = Math.round(cur_summ_price);
            }else{

                var cur_summ_price = totalPrice;
                cur_summ_price = Math.round(cur_summ_price);

                $('#glob_summ').html(cur_summ_price);
                $('#glob_summ2').html(cur_summ_price);
                $('.tabs-item #totalPrice').html(cur_summ_price);
                $('.js_sbm__calc').attr('data-total',cur_summ_price);
                glob_price = Math.round(cur_summ_price);
            }

        }

        $(document).on('click', '.ramu-item', function () {
            setTimeout(() => {
                calcTotalPrice();
            }, 200);
        });

</script>


<script>
    $(document).ready(function () {
        $('.jcf-button-content').text('{{ $canvas_head['load_btn_text'] }}');
        $('.jcf-fake-input').text('{{ $canvas_head['load_btn_text'] }}');
        let des = $('#real_file_input').data('desc');
        $(".comments .jcf-fake-input").text(des);
    });
</script>

@include('partials.grap_styl_paint_send')

@endsection
