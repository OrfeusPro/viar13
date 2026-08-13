<script>
    var CanvasMainImg = null;
    var base_64_img = null;
    var saved_tab2_img;
    var preview,file,reader;
    var dost_price = 0;

    $(document).on("click", "span.delete", function () {
        var btn = $(this).closest('button');
        var eq = btn.index();

        btn.removeClass('is-load');
        btn.find('img').attr('src', '(unknown)').removeAttr('style');
        btn.find('input').val('');
    });

    $(".slider-tabs").on("click", ".slider-tabs a", function () {
        $(".ramu-slider").get(0).slick.setPosition(),
        $(".ramu-item .img").matchHeight({
            byRow: !1,
        });
    }),

    $(document).on('click', '.interior-item', function(e){
        var size = $(this).data('size');
        var size = size.split(',');

        $('.js_s1').text(size[0]);
        $('.js_s2').text(size[1]);

        var int = $(this).data('interior');
        $('.tab2-item-child').children('img').attr('src', int);

        var index = $(this).data('item');

        $('.range__box').hide(0);
        $('.range__box').eq(index).show(0);
    });

    $('input[name="wall_size"]').on('keyup',function(){
        v = parseInt($(this).val());
        min = parseInt($(this).attr('min'));
        max = parseInt($(this).attr('max'));
        if (v > max){
            $(this).val(max);
        }
        if(v<min){
            $(this).val(min);
        }

    })

    $('.interior-sizes input').on('input', function(e){
        var val = this.value;
        $('.size-item').find('input').val(this.value);
        var cacl_val = this.value/30;
        $('.int-draggable').css({ 'width': 'calc(230px - ' + cacl_val+ '%)' });
    });

    function previewFile() {
        preview = document.getElementById('img__selected__tab1');
        file    = document.getElementById('user_image').files[0];
        reader  = new FileReader();

        reader.onloadend = function () {
            preview.src = reader.result;
        }

        if (file) {
            reader.readAsDataURL(file);
        } else {
            preview.src = "";
        }
    }

    // SUBMIT
    $('#t1_submit_btn').click(function () {
        if(CanvasMainImg == null){
            $('.popup-inv-size').addClass('active');
            return;
        }

        let data = formData();

        $('.js_spinner').jmspinner('large');
        $.ajax({
            type: 'post',
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('add_item_to_basket') }}',
            data: data,
            success: function (response) {
                if (response) {
                    var data = jQuery.parseJSON(response);

                    if (data['Error']) {
                        alert(data['Error']);
                    } else if (data['success']) {
                    // seo
                    var total_seo__price = $('.tabs-item #totalPrice').data('total');
                    window.dataLayer = window.dataLayer || [];
                    dataLayer.push({
                    'ecommerce': {
                        'currencyCode': 'EUR',
                        'add': {
                        'actionField': {'list': 'Kanvas'},
                        'products': [{
                            'name': 'Kanvas',
                            'price': total_seo__price,
                            'quantity': 1,
                        }]
                        }
                    },
                    'event': 'EE-event',
                    'EE-event-category': 'Enhanced Ecommerce',
                    'EE-event-action': 'Add To Cart',
                    'EE-event-non-interaction': 'False'
                    });

                    // endseo
                        $('.popup-bask-add').addClass('active');
                        var cur_count = $('#smallCart').text();
                        cur_count = parseInt(cur_count);
                        cur_count++;
                        $('#smallCart').text(cur_count);
                    }

                    $('#t1_submit_btn').attr('data-disabled', '0');
                }

                $('.js_spinner').jmspinner(false);
            },
            error: function (error) {

                if (error.responseText) {
                    let response = JSON.parse(error.responseText);
                    $.each(response.errors, function (key, value) {
                        alert(value[0])
                    });
                } else {
                    alert('Server error');
                }

                $('.js_spinner').jmspinner(false);
                $('#t1_submit_btn').attr('data-disabled', '0');
            }
        });
    });

    // выберите размер
    $(document).on('click', '.js_add_basket_module', function(e){
        $('#t1_submit_btn').trigger('click');
    });

    $(document).on('click', '.calcSize', function(e){
        let sizePrice = $(this).data('price');
        let sizeSize = $(this).data('size');
        calcTotalPrice(sizePrice, sizeSize);
    });

    // тип исполнения
    $('.calcExecution').on('click', function () {
        calcTotalPrice(null, null, $(this).data('execution'));
    });

    // вид холста
    $('input[name="canvas_type"]').on('change', function () {
        calcTotalPrice();
    });

    // худ. оформление
    $('input[name="decoration"]').on('change', function () {
        calcTotalPrice();
    });

    $('input[name="boxes[]"]').on('change', function () {
        calcTotalPrice();
    });

    // доставка
    $('input[name="dost_time"]').on('change', function () {
        calcTotalPrice();
    });

    function formData() {
        let formData = new FormData();
        let userImage = CanvasMainImg;
        let formId = $('.calcForm.active').data('id');
        let sizeId = $('.calcSize.active').data('size');
        let canvasId = $('input[name="canvas_type"]:checked').data('id');
        let executionId = $('.calcExecution.active').data('execution');
        let decorationId = $('input[name="decoration"]:checked').data('id');
        let userComment = $('#userComment').val();
        let boxIds = [];
        let terms = $('.tabs-item1 .dost_items').find('.jcf-label-active').find('.st_text').text();
        let terms_price = $('.tabs-item1 .dost_items').find('.jcf-label-active input').val();

        let Image3d = '';
        let canvas = document.getElementById("canvas");

        if (canvas && canvas.width > 0 && canvas.height > 0) {
            let resizedCanvas = document.createElement("canvas");
            let resizedContext = resizedCanvas.getContext("2d");
            resizedCanvas.height = 200;
            resizedCanvas.width = 200;
            resizedContext.drawImage(canvas, 0, 0, 200, 200);
            Image3d = resizedCanvas.toDataURL();
        } else if (typeof editor !== 'undefined' && editor && typeof editor.out === 'function') {
            try {
                Image3d = editor.out(200, 200, false);
            } catch (e) {
                Image3d = '';
            }
        }

        $('input[name="boxes[]"]:checked').each(function () {
            boxIds.push(parseInt($(this).data('id')));
        });

        formData.append('name', cur_name);
        formData.append('price',  $('.tabs-item #totalPrice').data('total'));

        if( $('input[name="photo_ex"]').length && $('input[name="photo_ex"').val() != ''){
            formData.append('photo_ex', $('input[name="photo_ex"]').prop('files')[0]);
        }else{
            formData.append('photo_ex', '');
        }

        if($('.ramu-item.active').length){
            var $activeRam = $('.ramu-item.active').last();
            var activeRamPrice = parseFloat($activeRam.data('price')) || 0;
            if(activeRamPrice > 0){
                var ram_id = $activeRam.data('id') || $activeRam.find('input[name="ram_id"]').val();
            }
        }

        formData.append('basketType', '{{ \App\Entity\BasketType::CANVAS_TYPE }}');

        if (userImage)
            formData.append('userImage', userImage);
        if (Image3d)
            formData.append('Image3d', Image3d);
        if (formId)
            formData.append('formId', formId);
        if (sizeId)
            formData.append('sizeId', sizeId);
        if (name)
            formData.append('name', name);
        if (ram_id)
            formData.append('ram_id', ram_id);
        if (executionId)
            formData.append('executionId', executionId);
        if (canvasId)
            formData.append('canvasId', canvasId);
        if (decorationId)
            formData.append('decorationId', decorationId);
        if (userComment)
            formData.append('userComment', userComment);
        if (terms)
            formData.append('terms', terms);
        if (terms_price)
            formData.append('terms_price', terms_price);
        if (boxIds)
            formData.append('boxIds', JSON.stringify(boxIds));

        formData.append('is_canvas_collage', 1);
        formData.append('is_with_orig_image', 1);

        return formData;
    }

    function calcExecutionPrice(sizeSize, execution) {
        let executionPrice = 0;

        if (execution == 1) {
            let height = sizeSize.split('x')[0];
            let length = sizeSize.split('x')[1];
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

    var rp = 0;
    function calcTotalPrice(sizePrice = null, sizeSize = null, execution = null) {
        sizePrice = $('.calcSize.active').data('price');

        if (!sizeSize)
            sizeSize = $('.calcSize.active').data('size');

        if (!execution)
            execution = $('.calcExecution.active').data('execution');

        let dostTimePrice = parseFloat(dost_price);

        let totalPrice = 0;

        totalPrice += parseFloat(sizePrice);

        var ram_price = $('.ramu-item.active').data('price');

        var cur_size  = $('.js_size.active').data('size');
        var cur_size_calc = cur_size.split("x");
        var s1 = parseInt(cur_size_calc[0]);
        var s2 = parseInt(cur_size_calc[1]);
        if(typeof ram_price === 'undefined'){
            rp = 0;
        }else{
            if($('.js_size.active'.length)){
                rp = ((s1+s1+s2+s2)/100)*parseInt(ram_price);
            }else{
                rp  = 0;
            }
        }

        // холст
        let defPrice = $('.js_size.active').data('price');
        let canvasTypePrice = parseFloat($('input[name="canvas_type"]:checked').val());
        let cur_sizes_add_coef = defPrice * canvasTypePrice;

        let cur_sizes_add = parseFloat(cur_sizes_add_coef).toFixed(2);

        if(cur_sizes_add != 0.00){
            totalPrice = cur_sizes_add;
        }
        //

        totalPrice = parseFloat(totalPrice) + parseFloat(dostTimePrice);
        totalPrice = parseFloat(totalPrice).toFixed(2);

        // тип исполнения
        let ex_add_price = calcExecutionPrice(sizeSize, execution);
        console.log('+ за тип исполнения',ex_add_price);
        totalPrice = parseFloat(totalPrice) + parseFloat(ex_add_price);

        totalPrice = parseFloat(totalPrice).toFixed(2);

        // худ. оформление
        totalPrice = parseFloat(totalPrice).toFixed(2);
        let coef_1 = parseInt($('input[name="decoration"]:checked').data('coef_sm'));
        let coef_2 = parseInt($('input[name="decoration"]:checked').data('coef_md'));
        let coef_3 = parseInt($('input[name="decoration"]:checked').data('coef_lg'));
        let area = s1 * s2 / 10000;

        if (area <= 0.4) {
            decorAddPrice = area * coef_1;
        }
        else if (area > 0.4 && area <= 1) {
            decorAddPrice = area * coef_2;
        }
        else if (area >= 1.01) {
            decorAddPrice = area * coef_3;
        }

        if(isNaN(decorAddPrice)){
            decorAddPrice = 0;
        }

        decorAddPrice = parseFloat(decorAddPrice);

        totalPrice = parseFloat(totalPrice);

        totalPrice += decorAddPrice;

        totalPrice = parseFloat(totalPrice) + parseFloat($('input[name="boxes[]"]:checked').val());

        totalPrice = parseFloat(totalPrice).toFixed(2);

        if(undefined == sizePrice){
            sizePrice = $('.js__hidden_sizes>li:first').find('a').data('price');
        }

        if(isNaN(totalPrice)) return;

        totalPrice = calc_float_val(totalPrice);

        $('.total_with_total').remove();
        $('.ramm__pr').remove();

        if(rp != 0){
            var cur_summ_price = parseFloat(totalPrice) + parseFloat(rp);
            cur_summ_price = cur_summ_price.toFixed(2);

            $('.tabs-item #totalPrice').html(calc_float_val(totalPrice));
            $('.sum__mod #glob_summ2').html(calc_float_val(totalPrice));

            var rm_txt = $('body').data('for_ram');

            $('#totalSum').append("<span class='ramm__pr'>+"+calc_float_val(rp)+"€ "+rm_txt+"</span>");
            $('.tabs-item2 #totalSum').append("<span class='ramm__pr'>+"+calc_float_val(rp)+"€ "+rm_txt+"</span>");

            var vs_text = $('body').data('full');

            $('.tabs-item2 #totalSum')
            .append("<div class='total_with_total'><span class='vs_text'>"+vs_text+'</span> '+calc_float_val(cur_summ_price)+" €</div>");
            $('#totalSum')
            .append("<div class='total_with_total'><span class='vs_text'>"+vs_text+'</span> '+calc_float_val(cur_summ_price)+" €</div>");

            $('#PhotoEditor #glob_summ2').html(calc_float_val(cur_summ_price));
            $('.tabs-item #totalPrice').attr('data-total',calc_float_val(cur_summ_price));
        }else{
            var cur_summ_price = totalPrice;
            cur_summ_price = calc_float_val(cur_summ_price);
            $('.tabs-item #totalPrice').html(cur_summ_price);
            $('.sum__mod #glob_summ2').html(cur_summ_price);
            $('.tabs-item #totalPrice').attr('data-total',cur_summ_price);
        }
    }

    var cur_name = $('.js_add_basket_stena').data('name');

    $('.jcf-button-content').text('{{ $canvas_head['load_btn_text'] }}');
    $('.jcf-fake-input').text('{{ $canvas_head['load_btn_text'] }}');

    $(document).on('click', '.interior-item', function(e){
        var img_border = $('.ramu-item.active').find('img').attr('src');
        var bi_full = 'url('+img_border+') 65 68 / 5em stretch';
    })

    $('.ramu-item').click(function(e){
        setTimeout(() => {
        calcTotalPrice();
        }, 200);
    });

    $('.dost_items2 input, .dost_items1 input').change(function(e){
        dost_price = $(this).val();
        if(dost_price == 0){
            $('.tabs-item1 .dost_items').children('li').eq(1).find('label').trigger('click');
            $('.tabs-item2 .dost_items').children('li').eq(1).find('label').trigger('click');
        }else{
            $('.tabs-item1 .dost_items').children('li').eq(2).find('label').trigger('click');
            $('.tabs-item2 .dost_items').children('li').eq(2).find('label').trigger('click');
        }
        setTimeout(() => {
        calcTotalPrice();
        }, 200);
    });
</script>
