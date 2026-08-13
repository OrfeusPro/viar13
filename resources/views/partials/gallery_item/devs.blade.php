<script>
    $(document).on('click', '.js_add_basket_inter', function(e){
        $('.js_sbm__calc').trigger('click');
    });



$(document).ready(function () {
    var size_price = 0;
    var user_price = $('.js_custom_user[data-count="1"]').data('price');
    var price_holst = 0;
    var price_hud_of = 0;
    var rama_price = 0;
    var comp_price = 0;
    var dost_price = 0;
    var ex_price  = 0;
    var user_count = 1;

    rp = 0;
    var glob_price = 0;

    var CalcTotalPrice = function(){       
    var pack_price = $('.comments-item .jcf-checked').find('input').val();

    if(pack_price == undefined){
        pack_price = 0;
    }

    var ram_price = $('.ramu-item.active').data('price');

    if(user_price == undefined){
        user_price = 0;
    }

    let defPrice = $('.js_size.active').data('price');
    let canvasTypePrice = parseFloat($('input[name="q"]:checked').val());
    let cur_sizes_add_coef = defPrice * canvasTypePrice;
    let cur_sizes_add = parseFloat(cur_sizes_add_coef).toFixed(2);

    if(cur_sizes_add != 0.00){
        price_host = cur_sizes_add;
    }else{
        price_host = defPrice;
    }

    // console.log('holst',parseFloat(price_host));
    // console.log('user',parseFloat(user_price));

    
                 
    glob_price = parseFloat(price_host)+parseFloat(user_price)
    +parseFloat(price_hud_of)+parseFloat(comp_price)+parseFloat(dost_price)
    +parseFloat(pack_price)
    +parseFloat(ex_price);

    glob_price = glob_price.toFixed(2);

   

    $('.ramm__pr').remove();
    $('.total_with_total').remove();

    if(typeof ram_price === 'undefined'){ rp = 0; }
    else{
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
            var cur_summ_price =  parseFloat(glob_price) + parseFloat(rp);
            cur_summ_price = cur_summ_price.toFixed(2);
            console.log('1  '+cur_summ_price);
            

            var saved_cacl = cur_summ_price;
            cur_summ_price = cur_summ_price;
            cur_summ_price = cur_summ_price.toFixed(2);
            
            
            $('.tabs-item #totalPrice').html(glob_price);
            $('.sum__mod #glob_summ2').html(glob_price);
            $('#glob_summ').html(glob_price);
            
            var rm_txt = $('body').data('for_ram');
            
            $('.sum').append("<span class='ramm__pr'>+"+Math.round(rp)+"€ "+rm_txt+"</span>");
            
            var vs_text = $('body').data('full');
            
            $('.sum')
            .append("<div class='total_with_total'><span class='vs_text'>"+vs_text+'</span> '+cur_summ_price+" €</div>"); 
            console.log(cur_summ_price);
            
            $('#glob_summ2').html(cur_summ_price);     
            $('#glob_summ').html(glob_price);
            $('.js_sbm__calc').attr('data-total',saved_cacl);
            glob_price = cur_summ_price;
        }else{

            var cur_summ_price = glob_price;
            cur_summ_price = parseFloat(cur_summ_price).toFixed(2);
            console.log('2  '+cur_summ_price);
            

            var saved_cacl = cur_summ_price;
            cur_summ_price = cur_summ_price;
            
            console.log('3 '+cur_summ_price);
            
            cur_summ_price = parseFloat(cur_summ_price).toFixed(2);

            console.log(cur_summ_price);
            
            $('#glob_summ').html(cur_summ_price);
            $('#glob_summ2').html(cur_summ_price);
            $('.tabs-item #totalPrice').html(cur_summ_price);
            $('.js_sbm__calc').attr('data-total',saved_cacl);
            glob_price = saved_cacl;
        }

}


$(document).on('click', '.comments-item .jcf-radio', function(e){
    setTimeout(() => {
        CalcTotalPrice();
    }, 200);
});

// размер#devs
$(document).on('click', '.js_size', function(e){
    size_price = $(this).data('price');
    console.log(size_price); 

    $('.js__calc_ex.active').trigger('click');

    CalcTotalPrice();
});


if( $('.js_size.active').length){
    $('.js_size.active').trigger('click');
}

// к-во человек
$(document).on('click', '.js_custom_user', function(e){
    user_price = $(this).data('price');
    user_count = $(this).data('count');
    console.log(user_price);
    $('.js__personal_checker').find('input').removeAttr('checked');
    $('.js__personal_checker').removeClass('jcf-label-active');
    $('.js__personal_checker').find('.jcf-radio').removeClass('jcf-checked');
    $(this).find('a').toggleClass('active');
    $(this).siblings().find('a').removeClass('active');
    CalcTotalPrice();
});

// если персонально
$(document).on('click', '.js__personal_checker', function(e){
    $('.js__sizes').addClass('sizes_unactive');
    $('.js_peoples_nums').addClass('sizes_unactive');
    user_price = $('.js_custom_user[data-count="1"]').data('price');
    user_count = 1;
    CalcTotalPrice();
});

$(document).on('click', '.js__group_checker', function(e){
    $('.js__sizes').removeClass('sizes_unactive');
    $('.js_peoples_nums').removeClass('sizes_unactive');
});

$(document).on('click', '.js_peoples_nums button', function(e){
            // return;
            e.preventDefault();
            // console.log('click002');
            var is_return = 0;
            var price_per_one = $('.js_custom_user[data-count="1"]').data('price');
            
            user_count = $('.js_custom_user_count').val();       

            $('.js_custom_user').each(function(e){
                var thh = $(this);
                var val = thh.data('count');
                
                
                if(parseInt(val) == parseInt(user_count)){
                    console.log('cur',val);
                    console.log('selected',user_count);
                    user_price = $(this).data('price');
                    console.log('user price:',user_price);
                    
                    user_count = $(this).data('count');
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
            if(is_return == 1) return;
            
            user_price = user_count * price_per_one;
            console.log('up:',user_price);
            

            $('.js__sizes').find('li a').removeClass('active');
            
            $('.js_price_ppl').text(user_price);
            calcTotalPrice();
});

// тип исполнения
$(document).on('click', '.js__calc_ex', function(e){
    if ($(this).data('execution') == 1){
        var hl = $('.calcSize.js_size.active').data('size');

        if(hl == undefined){
            $('.js__calc_ex').removeClass('active');
            $('.js_ex_2').addClass('active');
            return;
        }
        var height = hl.split('x')[0];
        var length = hl.split('x')[1];

        var area = height * length / 10000;

        if (area <= 0.4) ex_price = area * 80;
        else if (area > 0.4 && area <= 1) ex_price = area * 60;
        else if (area >= 1.01) ex_price = area * 50;
    }else{
        ex_price = 0;
    }

    CalcTotalPrice();
});

// вид холста
$(document).on('click', '.js__canv_radio .jcf-radio', function(e){
    price_holst = $(this).find('input').val();
    CalcTotalPrice();
});

// худ офрмл.
$(document).on('click', '.js_decor_item .jcf-radio', function(e){
    price_hud_of = $(this).find('input').val();
    console.log(price_hud_of);
    CalcTotalPrice();
});    

// рама
$(document).on('click', '.ramu-item', function(e){
    rama_price = $(this).data('price');
    console.log(rama_price);
    CalcTotalPrice();
});    

// комплектация
$(document).on('click', '.picking-item .jcf-checkbox', function(e){
    comp_price = $(this).find('input').val();
    $(this).parent().siblings().find('input').prop('checked', false);
    $(this).parent().siblings().find('.jcf-checkbox').removeClass('jcf-checked');
    console.log(comp_price);
    CalcTotalPrice();
});    

// сроки доставки
$(document).on('click', '.dost_items label', function(e){
    dost_price = $(this).find('input').val();
    console.log(dost_price);
    CalcTotalPrice();
});


$(document).on('click', '.js_sbm__calc', function(e){
    e.preventDefault();
    CalcTotalPrice();
    
    var th = $(this);

    var name = $(this).data('name');
    var forma_id = $('.calcForm.active').data('id');


    
    var size = $('.js_size.active').data('size');
    
    if(typeof size === 'undefined'){
        $('.popup-inv-size').addClass('active');
        return;
    }

    if($('.js_custom_user').length){
        var users_count = user_count;
    }else{
        users_count = undefined;
    }

    var type =  $('.active.js__calc_ex').find('p').text();
    var holst_id = $('.js__holsts').find('.jcf-checked').parent().data('id');
    var hud_of = $('.js_decors').find('.jcf-checked').parent().find('label').text();
    var decor_id = $('.js_decors').find('.jcf-checked').data('id');
    // var ram_id = $('.js_rams').find('.jcf-checked').parent().data('id');
    var compl_id = $('.js_pick_items').find('.jcf-checked').parent().data('id');
    var comm = $('#userComment').val();
    var terms = $('.dost_items').find('.jcf-label-active').find('.st_text').text();
    var ram_id = $('.ramu-item.active').find('input').val();

   

    if(size == undefined){
        var size_err_text  = $('body').data('err_size');
        $('.popup-inv-size__sel').addClass('active');
        return;
    }


    if(isNaN(glob_price) || glob_price == 0 || glob_price == undefined || glob_price == '' ){
        var price_err_text  = $('body').data('err_price');
        alert(price_err_text);
        return;
    }
   
    var pid = $(this).data('id');
    var route = $(this).data('route');
    
    if(glob_price>0){             
            // если есть загрузка фото
            if( th.hasClass('generator_btn')){
                if(preview != null && preview != undefined){
                    var image = preview.src
                    var is_gall_with_img = 1;
                }
            }else{
                var image = null;
                var is_gall_with_img = 0;
            }

            var formData = new FormData();
            formData.append('pid', pid);
            formData.append('price', glob_price);
            formData.append('name', name);
            formData.append('userComment', $('#userComment').val());

            if( $('input[name="photo_ex"]').length && $('input[name="photo_ex"').val() != ''){
                formData.append('photo_ex', $('input[name="photo_ex"]').prop('files')[0]);
            }else{
                formData.append('photo_ex', '');
            }
            
            formData.append('pack', $('.comments-item .jcf-checked input').data('name'));
            formData.append('dost_time', $('.dost_items .jcf-label-active input').text());


            
            
            if($('#tab1__generator').children('img').length){
                var img = $('#tab1__generator').children('img').attr('src');
                formData.append('is_uploaded_img', 1);
                formData.append('image', img);
            }else{
                formData.append('image', image);
            }

            formData.append('is_gall_with_img', is_gall_with_img);
            formData.append('forma_id', forma_id);
            formData.append('size', size);
            formData.append('users_count', users_count);
            formData.append('type', type);
            formData.append('holst_id', holst_id);
            formData.append('hud_of', hud_of);
            formData.append('decor_id', decor_id);
            formData.append('ram_id', ram_id);
            formData.append('compl_id', compl_id);
            formData.append('terms', terms);

            $.ajax({
                type: 'POST',
                dataType: 'json',
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: route,
                data: formData
            }).then(function (data) {
                    if (data['Error']) {
                        alert(data['Error']);
                    } else if (data['success']) {

                        var name = $('.js_item_name').text();
                        var glob_price = $('#glob_summ').text();
                        window.dataLayer = window.dataLayer || [];
                        dataLayer.push({
                        'ecommerce': {
                            'currencyCode': 'EUR',
                            'add': {
                            'actionField': {'list': name},
                            'products': [{
                                'name': name,
                                'id': pid,
                                'price': glob_price,
                                'quantity': 1,
                            }]
                            }
                        },
                        'event': 'EE-event',
                        'EE-event-category': 'Enhanced Ecommerce',
                        'EE-event-action': 'Add To Cart',
                        'EE-event-non-interaction': 'False'
                        });

                        $('.popup-bask-add').addClass('active');
                        var cur_count = $('#smallCart').text();
                        cur_count = parseInt(cur_count);
                        cur_count++;
                        $('#smallCart').text(cur_count);
                    }
            });
    }
   
           


    
});


});

    

</script>