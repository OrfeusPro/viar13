$(function () {
  $(".so-slider").slick({
    slidesToShow: 1,
    variableWidth: true,
    infinite: false,
    slidesToScroll: 1,
    prevArrow: ".so-arr",
    nextArrow: ".so-next",
  });

  $(".js-popup-msg").click(function (e) {
    e.preventDefault();
    $("body").addClass("open-frame");
    $(".popup-frame").css("display", "flex").hide().fadeIn();
    $(".stock-data-input").fadeIn();
  });






  $(".js-popup-bonus").click(function (e) {
    e.preventDefault();
    $("body").addClass("open-frame");
    $(".popup-frame").css("display", "flex").hide().fadeIn();
    $(".stock-screen-input").fadeIn();
  });

  $(".js--copy").on("click", function (e) {
    e.preventDefault();
    let text = $(".promo-block b").text();
    navigator.clipboard.writeText(text);
  });





///  TODO: 2 даты функция отправки дат
     $(document).on('click', '.new_js_dates_form', function(e) {
            e.preventDefault();
            var date1 = $('.js_date1').val();
            var torj1  = $('.js_date_text1').val();
//            var torj1 = "Первая дата1"
            var date2 = $('.js_date2').val();
            var torj2 = $('.js_date_text2').val();
//            var torj2 = "Вторая дата2"
            var locale = $('.locale').val();


            // console.log(date1);
            // console.log(date2);

            var form_data = new FormData;
            form_data.append('date1', date1);
            form_data.append('torj1', torj1);
            form_data.append('date2', date2);
            form_data.append('torj2', torj2);
            form_data.append('locale', locale);

            $.ajax({
                processData: false,
                contentType: false,
                type: 'POST',
                 headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-Locale': locale // Add the locale to the headers
        },
                //url: $('#routes_links').data('send_dates'),
                url: "/user/send_dates",
                data: form_data,
                success: function(response) {
                 if (response) {

                  if(response[1].suxess){
                  // $('.coupon-code1').val(response[1].code)
                  // $('.coupon-code1').removeClass('hide');
                  // $('.coupon-code1').addClass('show');
                  }
                  $('.message1').removeClass('hide');
                  $('.message1').addClass('show');
                  $('.message1').html(response[1].message)

                  if(response[2].suxess){
                  // $('.coupon-code2').val(response[2].code)
                  // $('.coupon-code2').removeClass('hide');
                  // $('.coupon-code2').addClass('show');
                  }
                  $('.message2').removeClass('hide');
                  $('.message2').addClass('show');
                  $('.message2').html(response[2].message)


                     if(response[1].suxess && response[2].suxess) {

                         $(".stock-data-input").fadeOut(0);
                         $(".stock-date-js").fadeIn();
                     }
                }

                },
                error: function(error) {
                    console.log(error);
                }
            });
        });

    $(".js-popup-friend").click(function (e) {
        e.preventDefault();

        $("body").addClass("open-frame");
        $(".popup-frame").css("display", "flex").hide().fadeIn();
        $(".stock-promo-input").fadeIn();
    });


    $(document).on("click", ".send_mail", function (e) {

        promo_code=$('#friend_email').attr('code');
        femail=$('#friend_email').val();

        e.preventDefault();
        console.log(promo_code);
        console.log(femail);
        $.ajax({
            type: "post",
            dataType: "json",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            url: "/send_frend_email",
            data: {
                'promoCode': promo_code,
                'email': femail,
            },
            success: function (response) {
                $("body").addClass("open-frame");
                $(".stock-promo-input").fadeOut(0);
                $(".popup-frame").css("display", "flex").hide().fadeIn();
                $(".friend-thx").fadeIn();



            },
        });
    });

    // TODO: Top-mail-button click
    $(document).on("click", ".top-mail-button", function (e) {
        price=$(this).attr('data-price');
        size=$(this).attr('data-size');
        name=$(this).attr('data-name');
        catid=$(this).attr('data-catid');


        $("#new_catid").val(catid);
        $("#new_size").val(size);
        $("#new_price").val(price);
        $("#new_name").val(name);
        $("#new_overall_price").val(price);
        $("#new_styles").val(name);


        console.log(name)
        console.log(price)
        console.log(size)
        console.log(catid)

        e.preventDefault();
        $(".canvas_select_sizes option").attr("data-price",price);
        $(".canvas_select_sizes option").attr("value",size);
        $(".canvas_select_sizes option").text(size);

        $(".new_title").text(name);
        $(".kviz-price span").text(price);

        $("body").addClass("open-frame");
        $(".popup-frame").css("display", "flex").hide().fadeIn();
        $("#fastorder_form").fadeIn();


    });





});
