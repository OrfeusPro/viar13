<style>
    .checkout .checkout-content .checkout-item {
        width: 100% !important;
    }

    .js__sbm__btn.disabled{
        pointer-events: none;
    }

    @media(min-width:980px) {
        .checkout-form {
            display: grid;
            flex-wrap: wrap;
            grid-gap: 20px;
            grid-template-columns: 1fr 1fr;
        }
    }

    .js_no_select {
        opacity: .22;
        pointer-events: none;
    }

    .checkout input {
        position: relative;
        z-index: 2;
    }
</style>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css" integrity="sha512-yHknP1/AwR+yx26cB1y0cjvQUMvEa2PFzt1c9LlS4pRQ5NOTZFWbhBig+X9G9eYW/8m0/4OXNx8pxJ6z57x0dw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css" integrity="sha512-17EgCFERpgZKcm0j0fEq1YCJuyAWdz9KUtv1EjVuaOz8pDnh/0nZxmU6BBXwaaxqoi9PQXnRWqlcDB027hgv9A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js" integrity="sha512-XtmMtDEcNz2j7ekrtHvOVR4iwwaD6o/FUJe6+Zq+HgcCsk3kj4uSQQR8weQ2QVj1o0Pk6PwYLohm206ZzNfubg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
    var set_des_datepicker_val = function(){
        var sel_country  = $('.js_country').find(':selected').data('id');

        var date = new Date();
        var hrs = date.getHours();

        var id  = $('.js_country').find(':selected').data('id');
        var min_date = '0';

        // если до часу дня и 1-3 страны то сегодня
        if(hrs<=13 && hrs>=0){
            if(id == 1 || id == 2 || id == 3){
                min_date = '0';
            }
            // если 4-5 страны, то через 5 дней
            else{
                min_date = '+5';
            }
        // если после часу дня и 1-3 страны то завтра
        }else{
            if(id == 1 || id == 2 || id == 3){
                min_date = '+1';
            }
            // если 4-5 страны, то через 5 дней
            else{
                min_date = '+5';
            }
        }

        // товары в заказе
        $('.js_cart_prod').each(function (e){
            var cat_id = $(this).data('cat');
            // если в заказе есть граф. портрет, стил, картины маслом
            if(cat_id == 5 || cat_id == 6 || cat_id == 'oil'){
                // если выбраны 1-3 страны
                if(sel_country == 1 || sel_country == 2 || sel_country == 3){
                    min_date = '+5';
                }
                // если выбраны 4-5 страны
                else{
                    min_date  = '+8';
                    return true;
                }
            }

        });

        $('#date_pick_del').datepicker("destroy");
        $('#date_pick_del').datepicker({
            dateFormat : 'mm/dd/yy',
            minDate: min_date,
            beforeShowDay: $.datepicker.noWeekends,
            monthNames :
            ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
            dayNamesMin :
            ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
        });
        }

        set_des_datepicker_val();

    $('input[name="postal_index"], input[name="postal_index_rec"]').on('keyup', function () {
        var val = $(this).val();
        val = val.replace(/\D/g,'');
        $(this).val(val);
    });

    $('.js_toggle_user').on('click', function () {
        $('.js_user_data').stop().slideToggle('fast');
    });

    $('input[name="ur_name"]').on('change', function () {
        var val = $(this).is(":checked");
            if(val){
                $('.ur_info').stop().show();
            }else{
                $('.ur_info').stop().hide();
            }
    });

    // add price
    $(document).on('change', '.js_country', function () {
        var deliv_price = $(this).find(':selected').data('price');
        var high_price = $(this).find(':selected').data('high_price');
        var id  = $(this).find(':selected').data('id');

        $('#date_pick_del').datepicker('setDate', null);
        set_des_datepicker_val();

        var cur_checked_pay = $('input[name=payment]:checked').val();
        if(id != 1 && id != 2 && id != 3){

            $('.js_on_dev_inp').removeClass('js_no_select');
            $('.js_on_dev_inp').addClass('js_no_select');
            if(cur_checked_pay == 'on_delivery'){
                $('.js_trans').parent('.jcf-radio').trigger('click');
            }
        }else{
            $('.js_on_dev_inp').removeClass('js_no_select');
            if(cur_checked_pay == 'on_delivery'){
                deliv_price=deliv_price+high_price;
            }
        }

        // есл не латвия то самовывоз и наличные нельзя выбрать
        if(id != 1){
            var cur_deliv_selected = $('input[name=delivery]:checked').val();
            $('.js_check_home_dev__block .jcf-radio').trigger('click');
            $('.js_check_riga').addClass('js_no_select');
            $('.js_check_dau').addClass('js_no_select');
        }else{
            $('.js_check_riga').removeClass('js_no_select');
            $('.js_check_dau').removeClass('js_no_select');
        }

        $('.add_del_price span').text(deliv_price);
        $('[name="deliv_price"]').val(deliv_price);

    });


    $('input[name="delivery"]').on('change', function () {
        var th = $(this);

        var cur_checked = $('input[name=delivery]:checked').val();
        var pay_checked = $('input[name=payment]:checked').val();

        if(cur_checked == 'to_the_door'){
            $(".js_office__inp").addClass('js_no_select');
            if(pay_checked == 'cash_in_office'){
                $('.js_on_dev_inp .jcf-radio').trigger('click');
            }
            $('.add_del_price').show(0);
            $(".js_on_dev_inp").removeClass('js_no_select');
        }else{
            $(".js_office__inp").removeClass('js_no_select');
            $(".js_on_dev_inp").addClass('js_no_select');
            if(pay_checked == 'on_delivery'){
                $('.js_on_trans .jcf-radio').trigger('click');
            }
            $('.add_del_price').hide(0);
            $('.deliv_price').val(0);
            $('[name="deliv_price"]').val(0);
        }

        if(cur_checked == 'pickup_Riga'){
            $(".js_office__inp").addClass('js_no_select');
            if(pay_checked == 'cash_in_office'){
                $('.js_on_trans .jcf-radio').trigger('click');
            }
        }

        //
        setTimeout(() => {
            if (!$('.add_del_price').is(':visible')){
                var del_p = 0;
                $('[name="deliv_price"]').val(del_p);
            }else{
                var del_p = $('.add_del_price').find('span').text();
                $('[name="deliv_price"]').val(del_p);
            }
        }, 200);

    });

    $('input[name="payment"]').on('change', function () {
        var val = $(this).val();
        var cur_checked = $('input[name=delivery]:checked').val();
        setTimeout(() => {
            if(val == 'on_delivery'){
                var add_price = $('.js_country').find(':selected').data('high_price');
            }else{
                var add_price=0;
            }
            var def_price = $('.js_country').find(':selected').data('price');
            var spr = add_price+def_price;

            if(cur_checked !== 'to_the_door'){
                $('.add_del_price').hide(0);
                $('.deliv_price').val(0);
                $('[name="deliv_price"]').val(0);
            }else{
                $('.add_del_price').show(0);
                $('.add_del_price span').text(spr);
                $('[name="deliv_price"]').val(spr);
            }

        }, 200);




    });

</script>

<script src="{{ asset('js/jcf.min.js') }}"></script>
<script src="{{ asset('js/jcf.file.min.js') }}"></script>
<script src="{{ asset('js/jcf.radio.min.js') }}"></script>
<script src="{{ asset('js/jcf.select.min.js') }}"></script>
<script src="{{ asset('js/jcf.checkbox.min.js') }}"></script>
<script src="{{ asset('js/basket.min.js') }}"></script>

<script>
        function validateEmail(email) {
            var eml = email.replace(/\s/g, '');
            const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(String(eml).toLowerCase());
        }

        var a_msg = '{{ trans('gl.you_have_checked_only') }}';

        $(document).on('keyup', 'input[name="email"]', function(event){
            var th = $(this);
            var val  = th.val();

            if (validateEmail(val)) {
                th.removeClass('js_no_valid');
            }

        });

        $(document).on('keyup', '.js_phone_cc', function(event){
            var th = $(this);
            var val  = th.val();
            if(val.length<7){
                $('.js_phone_cc').addClass('js_no_valid');
            }else{
                $('.js_phone_cc').removeClass('js_no_valid');
            }
        });

        $(document).on('keyup', 'input[name="address"]', function(event){
            var th = $(this);
            var val  = th.val();
            if(val.length<2){
                $('input[name="address"]').addClass('js_no_valid');
            }else{
                $('input[name="address"]').removeClass('js_no_valid');
            }
        });

        $(document).on('keyup', 'input[name="city"]', function(event){
            var th = $(this);
            var val  = th.val();
            if(val.length<2){
                $('input[name="city"]').addClass('js_no_valid');
            }else{
                $('input[name="city"]').removeClass('js_no_valid');
            }
        });

        $(document).on('keyup', 'input[name="postal_index"]', function(event){
            var th = $(this);
            var val  = th.val();
            if(val.length<4 || val.length>5){
                $('input[name="postal_index"]').addClass('js_no_valid');
            }else{
                $('input[name="postal_index"]').removeClass('js_no_valid');
            }
        });

        // blur
        $("input[name='postal_index']").blur(function() {
            $('input[name="postal_index"]').removeClass('js_no_valid');
        });
        $("input[name='address']").blur(function() {
            $('input[name="address"]').removeClass('js_no_valid');
        });
        $("input[name='city']").blur(function() {
            $('input[name="city"]').removeClass('js_no_valid');
        });
        // endblur

        // submit
        var is_checkout = 0
        $(document).on('click', '.js__sbm__btn', function(event){
            event.preventDefault();

            $('.js__sbm__btn').prop("disabled", true).addClass('disabled');
            $('.js_preloader_status').removeAttr('style');

            var dost_check = $('.js_check_home_dev').prop("checked");
            var of_check = $('.js_office').prop("checked");

            if(of_check && dost_check){
                var txt = $('.js_on_dev_text').text();
                var txt2 = $('.js_per_text').text();
                alert(a_msg + ' '+txt+', '+txt2);
                $('.js__sbm__btn').prop("disabled", false).removeClass('disabled');
                $('.js_preloader_status').css('display','none');
                return;
            }

            // validate city
            var cur_city = $('.js_cur_city').val();
            if(cur_city.length<3){
                $('.js_cur_city').addClass('js_no_valid');
                document.getElementById('checkout-form').scrollIntoView({
                    behavior: 'smooth'
                });
                $('.js__sbm__btn').prop("disabled", false).removeClass('disabled');
                $('.js_preloader_status').css('display','none');
                return;
            }


            // validate phone
            var cur_phone = $('.js_phone_cc').val();
            if(cur_phone.length<7){
                $('.js_phone_cc').addClass('js_no_valid');
                document.getElementById('checkout-form').scrollIntoView({
                    behavior: 'smooth'
                });
                $('.js__sbm__btn').prop("disabled", false).removeClass('disabled');
                $('.js_preloader_status').css('display','none');
                return;
            }else{
                $('.js_phone_cc').removeClass('js_no_valid');
            }

            var index = $('input[name="postal_index"]').val();

            //validate email
            var mail = $('input[name="email"]').val();
            if (!validateEmail(mail)) {
                $('input[name="email"]').addClass('js_no_valid');
                document.getElementById('checkout-form').scrollIntoView({
                    behavior: 'smooth'
                });
                $('.js__sbm__btn').prop("disabled", false).removeClass('disabled');
                $('.js_preloader_status').css('display','none');
                return;
            }else{
                $('input[name="email"]').removeClass('js_no_valid');
            }

            var cur_mail = $('input[name="email"]').val();

            var new_cur_mail = cur_mail.replace(/\s+/g, '');
            $('input[name="email"]').val(new_cur_mail);

            // validate addr
            var h_check = $('.js_check_home_dev').is(":checked");
            if(h_check){
                var addr = $('input[name="address"]').val();

                if(addr == ''){
                    $('input[name="address"]').addClass('js_no_valid');
                    document.getElementById('checkout-form').scrollIntoView({
                        behavior: 'smooth'
                    });
                    $('.js__sbm__btn').prop("disabled", false).removeClass('disabled');
                    $('.js_preloader_status').css('display','none');
                    return;
                }
            }

            // validate index2
            if(index == '' || index.length<4 || index.length>5){
                $('input[name="postal_index"]').addClass('js_no_valid');
                document.getElementById('checkout-form').scrollIntoView({
                    behavior: 'smooth'
                });
                $('.js__sbm__btn').prop("disabled", false).removeClass('disabled');
                $('.js_preloader_status').css('display','none');
                return;
            }

            // check dated
            var country = $('.js_country').find(':selected').data('id');

            if(country != 1 && country != 2 && country != 3){
            }

            var number = Math.random();
            number.toString(36);
            var rand_id = number.toString(36).substr(2, 9);

            var tot_price = $('.js_toto_price').text();
            tot_price = tot_price.replace(" €", "");

            var del_price = parseFloat( $('.deliv_price').val());
            del_price = del_price.toFixed(2);

/*
            window.dataLayer = window.dataLayer || [];
            dataLayer.push({
            'ecommerce': {
            'currencyCode': 'EUR',
                'purchase': {
                    'actionField': {
                    'id': rand_id,
                    'affiliation': 'Online Store',
                    'revenue': $('.js_toto_price').text(),
                    'tax': null,
                    'shipping': del_price,
                    'coupon': null,
                },
                    'products': prod_seo
                }
            },
            'event': 'EE-event',
            'EE-event-category': 'Enhanced Ecommerce',
            'EE-event-action': 'Purchase',
            'EE-event-non-interaction': 'False'
            });
*/
            is_checkout = 1;
            $('#checkout-form')[0].submit();
        });

        function deleteFromBasket(btn, basketId) {
            var items_count = $('.basket-item').length;
            var item_name = $(btn).closest('.basket-item').find('.js_item__name__orig').text();
            item_name = item_name.replace(/ +(?= )/g,'');

            var item_price = $(btn).closest('.basket-item').find('.js_item__price').text();
            item_price = item_price.replace(/[^0-9.]/g, "");

            var categoryId = $(btn).closest('.basket-item').data('cat');

            $.ajax({
                type: 'post',
                dataType: 'html',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('remove_item_from_basket') }}',
                data: {
                    basketId: basketId
                },
                success: function (response) {

                    if (response) {
                        item_price = item_price.replace(" €", "");
                        item_price = parseFloat(item_price);
                        item_price = item_price.toFixed(2);
                        window.dataLayer = window.dataLayer || [];
                        dataLayer.push({
                        'ecommerce': {
                            'remove': {                     		// 'remove' actionFieldObject measures.
                            'actionField': {'list': 'Sezonas preces'},
                            'products': [{
                            'name': item_name,
                                'id': '0',
                                'price': item_price,
                                'quantity': 1,
                            }]
                            }
                        },
                        'event': 'EE-event',
                        'EE-event-category': 'Enhanced Ecommerce',
                        'EE-event-action': 'Remove From Cart',
                        'EE-event-non-interaction': 'False'
                        });



                        var data = jQuery.parseJSON(response);

                        if (data['Error']) {
                            alert(data['Error']);
                        } else if (data['success']) {

                            document.location.reload(true);
                        }
                    }
                }
            });
        }

</script>
