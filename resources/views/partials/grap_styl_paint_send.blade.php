<script>
    // слдайдер
    $(".picture-js").slick({
            infinite: !0,
            slidesToShow: 1,
            slidesToScroll: 1,
            adaptiveHeight: true,
            dots: true,
            nextArrow:
                '<button class="slick-arrow next"><i class="icon-icon25"></i></button>',
            prevArrow:
                '<button class="slick-arrow prev"><i class="icon-icon22"></i></button>',
        });

    // дропзона
    var myDropzone;
    $(document).ready(function () {
        if($('.js_zone_no_drop').length){
                var route = $('.js_sbm__calc').data('route');

                var data = [];

                myDropzone = new Dropzone(".js_zone_no_drop", { 
                    headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                url : route,
                uploadMultiple:true,
                addRemoveLinks: true,
                previewTemplate: document.querySelector('#template-preview').innerHTML,
                autoDiscover: false,
                maxFilesize: 20,
                maxThumbnailFilesize: 20,
                maxFiles: 100,
                thumbnailWidth: 107,
                thumbnailHeight: 84,
                // previewTemplate: document.getElementById('preview-template').innerHTML,
                url: "/ajax_file_upload_handler/",
                autoProcessQueue: false,
                acceptedFiles: ".jpeg,.jpg,.png,.heic,.heif",
                init: function () {

                    this.on('addedfile', function(file){
                        var reader = new FileReader();
                        reader.onload = function(event) { 

                        $('.def__imgs_rem').each(function (e){
                            var rem_index = $(this).data('slick-index');
                            $('#tab1__generator').slick('slickRemove', rem_index);
                            $('#tab1__generator').slick('setPosition');   
                            $('#tab1__generator').slick('refresh');
                        });
                            
                        $('#tab1__generator').slick('slickAdd','<div data-name="'+file.upload.filename+'" class="def__tab__img_sms"><img src="'+event.target.result+'"></div>');
                        };
                        $('#tab1__generator').slick('setPosition');   
                        $('#tab1__generator').slick('refresh');
                        reader.readAsDataURL(file);

                    });

                    this.on('removedfile', function(file){
                        $('[data-name="'+file.upload.filename+'"]').each(function (e){
                            var cur_index = $(this).data('slick-index');
                            $('#tab1__generator').slick('slickRemove', cur_index);
                            $('#tab1__generator').slick('setPosition');   
                            $('#tab1__generator').slick('refresh');
                        });
                        
                    });
                    
                },

                });




        }

    });

    var form_data = new FormData();
       
    function FORMDATA() {

        let userImage = $('input[name="user_image"]').prop('files')[0];
        // let photo_ex = $('input[name="photo_ex"]').prop('files')[0];
        let formId = $('.calcForm.active').data('id');
        let sizeId = $('.calcSize.active').data('id');
        let effectId = $('.calcEffect.active').data('id');
        let canvasId = $('input[name="canvas_type"]:checked').data('id');
        let executionId = $('.calcExecution.active').data('execution');
        let decorationId = $('input[name="decoration"]:checked').data('id');
        let userComment = $('#userComment').val();
        let boxIds = [];

        $('input[name="boxes[]"]:checked').each(function () {
            boxIds.push(parseInt($(this).data('id')));
        });


        form_data.append('basketType', '{{ \App\Entity\BasketType::CANVAS_TYPE }}');
        // form_data.append('price', $('#glob_summ').data('price'));
        // console.log('setted price');


        if (userImage)
            form_data.append('userImage', userImage);
        if (formId)
            form_data.append('formId', formId);
        if (sizeId)
            form_data.append('sizeId', sizeId);
        if (effectId)
            form_data.append('effectId', effectId);
        if (executionId)
            form_data.append('executionId', executionId);
        if (canvasId)
            form_data.append('canvasId', canvasId);
        if (decorationId)
            form_data.append('decorationId', decorationId);
        if (userComment)
            form_data.append('userComment', userComment);
        if (boxIds)
            form_data.append('boxIds', JSON.stringify(boxIds));


        return form_data;
    }

    $(document).on('click', '.js_sbm__calc', function(e){
            e.preventDefault();
            calcTotalPrice();
        
            var th = $(this);

            $('.js_spinner').jmspinner('large');

            var name = $(this).data('name');
            var forma_id = $('.calcForm.active').data('id');
            var size = $('.js_size.active').data('size');
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
                $('.popup-inv-size__sel').addClass('active');
                $('.js_spinner').jmspinner(false);
                return;
            }

            if(isNaN(glob_price) || glob_price == 0 || glob_price == undefined || glob_price == '' ){
                var price_err_text  = $('body').data('err_price');
                alert(price_err_text);
                $('.js_spinner').jmspinner(false);
                return;
            }
       
            var pid = $(this).data('id');
            var route = $(this).data('route');
        
            if(glob_price>0){
                if(pid == undefined){
                    // портрет маслом
                    var image = $('#img__selected__tab1').attr('src');
                    var form_data = new FormData();
                    if( $('input[name="photo_ex"]').length && $('input[name="photo_ex"').val() != ''){
                        form_data.append('photo_ex', $('input[name="photo_ex"]').prop('files')[0]);
                    }else{
                        form_data.append('photo_ex', '');
                    }
                    form_data.append('pid', pid);
                    
                    form_data.append('price', glob_price);
                    form_data.append('name', name);

                    var dd_files = myDropzone.getAcceptedFiles();

                    for (let x = 0; x < dd_files.length; x++){ 
                        form_data.append('orig_images[]', dd_files[x]);
                    }


                    if( $('input[name="photo_ex"]').length && $('input[name="photo_ex"').val() != ''){
                        form_data.append('photo_ex', $('input[name="photo_ex"]').prop('files')[0]);
                    }else{
                        form_data.append('photo_ex', '');
                    }
                    
                    form_data.append('forma_id', forma_id);
                    form_data.append('show_orig_images', 1);
                    
                    
                    form_data.append('size', size);
                    form_data.append('users_count', users_count);
                    form_data.append('type', type);
                    form_data.append('holst_id', holst_id);
                    form_data.append('hud_of', hud_of);
                    form_data.append('decor_id', decor_id);
                    form_data.append('ram_id', ram_id);
                    form_data.append('compl_id', compl_id);
                    form_data.append('userComment', comm);
                    form_data.append('terms', terms);

                   
                    $.ajax({
                        type: 'POST',
                        processData: false,
                        contentType: false,
                        dataType: "json",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: route,
                        data: form_data,
                    }).then(function (data) {
                            $('.js_spinner').jmspinner(false);
                            if (data['Error']) {
                                alert(data['Error']);
                            } else if (data['success']) {
                                // seo
                                window.dataLayer = window.dataLayer || [];
                                dataLayer.push({
                                'ecommerce': {
                                    'currencyCode': 'EUR',
                                    'add': {
                                    'actionField': {'list': name},
                                    'products': [{
                                        'name': name,
                                        'id': '0',
                                        'price': glob_price,
                                        'categoryId': '0',  
                                        'category': name, 
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
                    });
                }
                else{                
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

                    var form_data = new FormData();
                    form_data.append('pid', pid);
                    form_data.append('price', glob_price);
                    form_data.append('name', name);
                    form_data.append('userComment', $('#userComment').val());

                    var dd_files = myDropzone.getAcceptedFiles();

                    for (let x = 0; x < dd_files.length; x++){ 
                        form_data.append('orig_images[]', dd_files[x]);
                    }

                    if( $('input[name="photo_ex"]').length && $('input[name="photo_ex"').val() != ''){
                        form_data.append('photo_ex', $('input[name="photo_ex"]').prop('files')[0]);
                    }else{
                        form_data.append('photo_ex', '');
                    }
                    
                    form_data.append('pack', $('.comments-item .jcf-checked').children('input').data('name'));
                    form_data.append('dost_time', $('.dost_items .jcf-label-active input').text());
                    
                    if($('#tab1__generator').children('img').length){
                        var img = $('#tab1__generator').children('img').attr('src');
                        form_data.append('is_uploaded_img', 1);
                        form_data.append('image', img);
                    }else{
                        form_data.append('image', image);
                    }

                    form_data.append('show_orig_images', 1);

                    form_data.append('is_gall_with_img', is_gall_with_img);
                    form_data.append('forma_id', forma_id);
                    form_data.append('size', size);
                    form_data.append('users_count', users_count);
                    form_data.append('type', type);
                    form_data.append('holst_id', holst_id);
                    form_data.append('hud_of', hud_of);
                    form_data.append('decor_id', decor_id);
                    form_data.append('ram_id', ram_id);
                    form_data.append('compl_id', compl_id);
                    form_data.append('terms', terms);


                    $.ajax({
                        type: 'POST',
                        dataType: 'json',
                        contentType: false,
                        processData: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: route,
                        data: form_data
                    }).then(function (data) {
                            $('.js_spinner').jmspinner(false);
                            if (data['Error']) {
                                alert(data['Error']);
                            } else if (data['success']) {
                                // seo
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
                                        'categoryId': '0',  
                                        'category': name, 
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
                    });
                }
            }
                 
        
        });













</script>