$(document).ready(function () {


    $(document).on('change, keyup', '.js_users_count', function(e) {
        var val = $(this).val();
        var cat_data_price = $('.js_persons_all').data('var');
        var size_price = $(this).next('input').val();

        var total = parseInt(val) * parseInt(cat_data_price) * parseInt(size_price);
        $(this).closest('.item').find('.js__total').text(total);
        
    });

    $(document).on('change', '.js_select_cc', function(e) {

        var tel_place = $(this).find(":selected").data('tel');

        $(this).closest('.select').next('.js_phone_cc').val(tel_place).focus();

    });


// forget password
$(document).on('submit', '.js_forget_pass', function(e){
    e.stopImmediatePropagation();
    e.preventDefault();    
    
    $('.js_forget_pass').find('button').attr("disabled", true);
    var route_forget = $(this).attr('action');
    var user_email = $('.js__mail_forget').val();
    $.ajax({
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: route_forget,
            data: {
                email: user_email
            },
            success: function (response) {
                $('.js_forget_pass button').removeAttr("disabled");
                if (response) {
                    console.log(response);
                    
                    if (response.msg) {
                        $('.popup').removeClass('active');
                        $('.popup-print').addClass('active');
                    }
                }
            },
            error: function (error) { 
                $('.js_forget_pass button').removeAttr("disabled");
            }
    });
});

    $(document).on('click', '.js_scroll_calc',function(e){
        e.preventDefault();
        var id = $(this).attr('href');
        $("html, body").animate({ scrollTop: $(id).offset().top }, 'fast');
    });

    // copy coupon
    $(document).on('click', '.js_user_copy',function(e){

        var code = $('.js_code_cup').val();

        const el = document.createElement('textarea');
        el.value = code;
        el.setAttribute('readonly', '');
        el.style.position = 'absolute';
        el.style.left = '-9999px';
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        // alert('Успешно скопировано!');

    });

    $(document).on('keyup', '.js_prod_count', function(e){
        var val = $(this).val();
        var index = $(this).data('index');

        if(val == 0) return;

        var form_data = new FormData;
        form_data.append('count', val);
        form_data.append('index', index);

       
        // update count
        $.ajax({
            processData: false,
            contentType: false,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '/basket/update/count',
            data: form_data,
            success: function (response) {
                if (response) {
                    var data = jQuery.parseJSON(response);
                    
                    if(data.success){
                        location.reload();
                    }
                }       
            },
            error: function (error) {
                console.log(error);
            }
        });

        
    });

    // пришлите нам принтскрин
    $(document).on('submit', '.js_print_sale', function(e){
        e.preventDefault();    

        var $input = $("#js_print_sale");
        var form_data = new FormData;
        form_data.append('image', $input.prop('files')[0]);

        $('.js_spinner').addClass('spinner');
        
        var cur_btn =  $(this).find('button');
        cur_btn.attr('disabled', true);

        $.ajax({
            processData: false,
            contentType: false,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '/user/send_screen',
            data: form_data,
            success: function (response) {
                cur_btn.removeAttr('disabled');
                $('.js_spinner').removeClass('spinner');
                if (response) {
                    if(response.message == 'Invalid filesize or extension.'){
                        var msg = $('body').data('err_file');
                        alert(msg);
                    }

                    if(response.message == 'The image must be a file of type: png, bmp, jpg, jpeg.'){
                        var msg = $('body').data('err_file');
                        alert(msg);
                    }

                    if(response.message == 'Success'){
                        var msg = $('body').data('suc_send_fb_sale');
                        $('.popup-print-screen').removeClass('active');
                        $('.popup-print').addClass('active');
                        $('.popup-print').find('.js_mod_text').text(msg);
                    }
                }       
            },
            error: function (error) {
                cur_btn.removeAttr('disabled');
                $('.js_spinner').removeClass('spinner');
                console.log(error);
            }
        });

    });

    // 2 даты
    $(document).on('submit', '.js_dates_form', function(e){
        e.preventDefault();   
        var date1 = $('.js_date1').val();
        var torj1 = $('.js_torj1').val();
        var date2 = $('.js_date2').val();
        var torj2 = $('.js_torj2').val();
       
        var form_data = new FormData;
        form_data.append('date1', date1);
        form_data.append('torj1', torj1);
        form_data.append('date2', date2);
        form_data.append('torj2', torj2);

        $.ajax({
            processData: false,
            contentType: false,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: $('#routes_links').data('send_dates'),
            data: form_data,
            success: function (response) {      
                if (response) {
                  if(response[1].suxess){
                  $('.coupon-code1').val(response[1].code)
                 $('.coupon-code1').removeClass('hide');
                  $('.coupon-code1').addClass('show');
                  }   
                  $('.message1').removeClass('hide');
                  $('.message1').addClass('show');
                  $('.message1').html(response[1].message)
                  
                if(response[2].suxess){
               $('.coupon-code2').val(response[2].code)
                 $('.coupon-code2').removeClass('hide');
                  $('.coupon-code2').addClass('show');
                  }   
                  $('.message2').removeClass('hide');
                  $('.message2').addClass('show');
                  $('.message2').html(response[2].message)
                }       
            },
            error: function (error) {
                    alert(error)
                  console.log(response)
                console.log(error);
            }
        });
    });
    
    
    
    

    // картинка 30x40 бесплатно
    $(document).on('submit', '.js_free_image', function(e){
        e.preventDefault();
        var form_data = new FormData;
        var is_return = 0;

        $('.js_spinner').jmspinner('large');
            
        let TotalImages = $('#js_free_img')[0].files.length; 
        let images = $('#js_free_img')[0]; 
        for (let i = 0; i < TotalImages; i++) {
            var file_size =  images.files[i].size/1024/1024;
            if( (file_size>50) || (TotalImages>10) ){
                var  err_msg = $('body').data('err_file');
                alert(err_msg);
                is_return = 1;
                break;
            }
            form_data.append('images[]', images.files[i]);
        }
        
        if(is_return == 1){
            return;
        }


        $.ajax({
            processData: false,
            contentType: false,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: $('#routes_links').data('user_free_img'),
            data: form_data,
            success: function (response) {
                $('.js_spinner').jmspinner(false);
                if (response) {
                    if(response.message == 'Invalid filesize or extension.'){
                        var msg = $('body').data('err_file');
                        alert(msg);
                    }

                    if(response.message == 'Success'){
                        var msg = $('body').data('suc_send');
                        $('.popup-photo').removeClass('active');
                        $('.popup-act-conf').find('.p__mod__title').html(msg);
                        $('.popup-act-conf').addClass('active');
                    }
                }       
            },
            error: function (error) {
                $('#boxSpinner').jmspinner(false);
                console.log(error);
            }
        });

    });

    // купон
    $(document).on('submit', '.js_coupon_form', function(e){
        alert("3");
        e.preventDefault();

        var coupon = $(".js_coupon").val();
        var form_data = new FormData;
        form_data.append('coupon', coupon);

        $.ajax({
            processData: false,
            contentType: false,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: $('#routes_links').data('coupon_use'),
            data: form_data,
            success: function (response) {
                if (response) {
                    if(response.finded == 1){
                        document.location.reload(true);
                    }

                }       
            },
            error: function (error) {
                console.log(error);
            }
        });
        
    });

    // подрарочная карта
    $(document).on('submit', '.js_gift_form', function(e){
        e.preventDefault();
        var form_data = new FormData;
        

        var summ = $('.js_summ').val();
        if(summ == 0){
            summ = $('.js_custom_summ').val();
        }

        var name = $(this).data('name');
        var date = $('.js_date').val();
        var sender = $('.js_sender').val();
        var res = $('.js_receiver').val();
        var torjname = $('.js_torj_title').val();
        var torjtext = $('.js_torj_text').val();
        var card_type = $('input[name="el_type"]:checked').val();
        var hide_nom = $('.js_hide_nom').is(":checked");

        form_data.append('name', name);
        form_data.append('summ', summ);
        form_data.append('date', date);
        form_data.append('sender', sender);
        form_data.append('res', res);
        form_data.append('torjname', torjname);
        form_data.append('torjtext', torjtext);
        form_data.append('card_type', card_type);
        form_data.append('hide_nom', hide_nom); // false/true

        $.ajax({
            processData: false,
            contentType: false,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: $('#routes_links').data('send_gift_card'),
            data: form_data,
            success: function (response) {
                if (response) {
                    var res = JSON.parse(response);
                    if(res.success){
                        $('.popup-bask-add').addClass('active');
                        var cur_count = $('#smallCart').text();
                        var new_val = parseInt(cur_count);
                        $('#smallCart').text(++new_val);
                    }else{
                        alert(res.message);
                    }

                }       
            },
            error: function (error) {
                console.log(error);
            }
        });

        
        
        
        
    });

    // класс
    $(document).on('click', '.modular-shapes button',function(e){
        $(this).addClass('js__selected');
        $(this).siblings().removeClass('js__selected');
    });
    
    
   
});