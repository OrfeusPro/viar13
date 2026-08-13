@extends('layots.common')

@section('title', $mod_head['meta_title'])
@section('meta_desc', $mod_head['meta_desc'])

@section('og_tags')
    <meta property="og:title" content="{{ $mod_head['meta_title'] }}" />
    <meta property="og:description" content="{{ $mod_head['meta_desc'] }}">
    <meta property="og:image" content="{{ \URL::to('/') }}/img/logo.png" />
@endsection

@section('styles')
    <script src="{{ asset('js/InteriorGenerator.js') }}"></script>
    <script>
        window.is_image_modular = 1;

    </script>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/modular-pictures.css') }}">

    <script>
        var add_to_seo = function() {
            var cur_item__title = $('.h3__title').text();
            window.dataLayer = window.dataLayer || [];
            dataLayer.push({
                'ecommerce': {
                    'currencyCode': 'EUR',
                    'detail': {
                        'actionField': {
                            'list': 'Sezonas preces'
                        },
                        'products': [{
                            'name': cur_item__title,
                            'id': '0',
                            'categoryId': '0',
                            'category': '',
                        }]
                    }
                },
                'event': 'EE-event',
                'EE-event-category': 'Enhanced Ecommerce',
                'EE-event-action': 'ProductDetail',
                'EE-event-non-interaction': 'False'
            });
        }

    </script>

    <script>
        window.onShowInterior = function(callback) {
            var dataModular = window.saveModular();
            var file = dataModular.input[0].files[0];
            var reader = new FileReader();
            reader.onload = function(e) {
                var r = {};
                r.src = e.target.result;
                var el = document.createElement("div");
                el.innerHTML = dataModular.svg;
                var svg = el.querySelector('svg');
                var rects = svg.querySelectorAll('rect');

                r.size = {};
                r.size.w = parseFloat(svg.getAttribute("width").replace('cm', ''));
                r.size.h = parseFloat(svg.getAttribute("height").replace('cm', ''));
                r.clips = [];

                Object.values(rects).forEach(function(rect) {
                    console.log(rect);

                    let x = rect.getAttribute("x");
                    if (x) {
                        x = parseFloat(x.replace('%', '')) / 100;
                    } else {
                        x = 0;
                    }

                    let y = rect.getAttribute("y");

                    if (y) {
                        y = parseFloat(y.replace('%', '')) / 100;
                    } else {
                        y = 0;
                    }

                    let width = rect.getAttribute("width");

                    if (width) {
                        width = parseFloat(width.replace('%', '')) / 100;
                    } else {
                        width = 1;
                    }

                    let height = rect.getAttribute("height");

                    if (height) {
                        height = parseFloat(height.replace('%', '')) / 100;
                    } else {
                        height = 1;
                    }

                    var clip = {
                        x: x,
                        y: y,
                        width: width,
                        height: height
                    };

                    r.clips.push(clip);
                });

                callback(r);
            }

            reader.readAsDataURL(file);
        }

    </script>
@endsection

@section('content')
    <link rel="stylesheet" href="{{ asset('css/modular-pictures.css') }}">

    @include('partials.module_pics.header')
    @include('partials.module_pics.tabs')

    @include('partials.module_pics.generator')
    @include('partials.module_pics.popular')
    @include('partials.module_pics.components')
    @include('partials.module_pics.our_works')

    <link rel="stylesheet" href="{{ asset('css/interior.css') }}">
    <script src="{{ asset('js/jcf.min.js') }}"></script>
    <script src="{{ asset('js/jcf.file.min.js') }}"></script>
    <script src="{{ asset('js/jcf.radio.min.js') }}"></script>
    <script src="{{ asset('js/jcf.checkbox.min.js') }}"></script>
    <script src="{{ asset('js/jquery.collapse_storage.min.js') }}"></script>
    <script src="{{ asset('js/jquery.collapse.min.js') }}"></script>
    <script src="{{ asset('js/slick.min.js') }}"></script>
    <script src="{{ asset('js/modular-pictures.min.js') }}"></script>
    <script src="{{ asset('js/templates.js') }}"></script>
    <script src="{{ asset('js/modular-generator.js') }}"></script>
    <script src="{{ asset('js/interior.js') }}"></script>

    <style>
        .interior.tabs-item .interior-wrapper {
            display: none !important;
        }

        .interior.tabs-item>div:nth-of-type(1) {
            position: static;
            display: block !important;
        }

        img.img__repl {
            width: 109% !important;
            height: 109% !important;
            left: -5%;
            top: -3%;
            position: relative;
        }

        .product-download.pd-modules.mod__pd {
            display: block !important;
        }

        @media screen and (min-width: 1024px) {
            .modular .canv_cel>svg:not([viewBox]) {
                height: calc(100vh - 400px)
            }
        }

        .modular-banner .modular-banner-content .modular-tabs .tabs-content .order::after {
            content: '';
            background-size: 100% 100%;
        }

    </style>

    <script>
        var saved_price = 0;
        var dost_price = 0;
        var ram_price = 0;
        var price_hud_of = 0;

        var rp = 0;

        calcTotalPrice();

        $(document).on('click', '.delete', function(e) {
            $('.download .img').removeAttr('style');
            location.reload();
        });

        $(document).on('click', '.ramu-item', function(e) {
            setTimeout(() => {
                calcTotalPrice();
            }, 200);
        });

        $('input[name="whole-width"]').on('change', function() {
            calcTotalPrice();
        });

        $('input[name="whole-height"]').on('change', function() {
            calcTotalPrice();
        });

        $('.calcExecution').on('click', function() {
            calcTotalPrice($(this).data('execution'));
        });

        $('input[name="canvas_type"]').on('change', function() {
            calcTotalPrice();
        });

        $('input[name="decoration"]').on('change', function() {
            calcTotalPrice();
        });

        $('input[name="boxes[]"]').on('change', function() {
            calcTotalPrice();
        });

        function formData() {
            let height = $('input[name="whole-height"]').val();
            let width = $('input[name="whole-width"]').val();
            let sizeSize = height + "x" + width;

            let userImages = saveModular();
            let collageSvgImage = userImages.svg;
            let activeImage = userImages.input.prop('files')[0];

            let formData = new FormData();

            let size = sizeSize;
            let canvasId = $('input[name="canvas_type"]:checked').data('id');
            let executionId = $('.calcExecution.active').data('execution');
            let decorationId = $('input[name="decoration"]:checked').data('id');
            let userComment = $('#userComment').val();
            let formId = $('.modular-shapes button.js__selected').children().data('index');
            let boxIds = [];

            $('input[name="boxes[]"]:checked').each(function() {
                boxIds.push(parseInt($(this).data('id')));
            });

            formData.append('basketType', '{{ \App\Entity\BasketType::MODULAR_PICTURES_TYPE }}');
            if (size)
                formData.append('size', size);
            if (executionId)
                formData.append('executionId', executionId);
            if (canvasId)
                formData.append('canvasId', canvasId);
            if (decorationId)
                formData.append('decorationId', decorationId);
            if (boxIds)
                formData.append('boxIds', JSON.stringify(boxIds));
            if (userComment)
                formData.append('userComment', userComment);
            if (formId && formId != undefined)
                formData.append('formIndex', formId);

            formData.append('name', $('head title').text() + ' - ' + $('.tabs a.active').text());
            formData.append('activeImage', activeImage);
            formData.append('collageSvgImage', collageSvgImage);

            formData.append('collageSvgImage_hash', file_hash);
            console.log('hash:', file_hash);


            return formData;
        }

        function calcExecutionPrice(sizeSize, execution) {
            let executionPrice = 0;
            let height = sizeSize.split('x')[0];
            let length = sizeSize.split('x')[1];

            let area = height * length / 10000;

            area.toFixed(2);

            if (area <= 0.4)
                executionPrice = area * {{ setting('modulnye-kartiny.area_less_04') }};
            else if (area > 0.4 && area <= 1)
                executionPrice = area * {{ setting('modulnye-kartiny.area_less_1') }};
            else if (area > 1)
                executionPrice = area * {{ setting('modulnye-kartiny.area_more_01') }};

            if (execution == 1) {
                executionPrice = executionPrice * {{ setting('modulnye-kartiny.area_is_1') }};
            }

            return executionPrice;
        }

        $(document).on('click', '.tabs a:eq(1)', function(e) {
            calcTotalPrice();
        });

        $(document).on('click', '.dost_items label', function(e) {
            dost_price = $(this).find('input').val();
            calcTotalPrice();
        });

        function calcTotalPrice(execution = null) {
            let height = $('input[name="whole-height"]').val();
            let width = $('input[name="whole-width"]').val();
            let sizeSize = height + "x" + width;

            let totalPrice = 0;

            if (!execution)
                execution = $('.calcExecution.active').data('execution');

            ram_price = $(".ramu-item.active").data('price');

            // вид холста
            let ex_price = calcExecutionPrice(sizeSize, execution);
            let canvasTypePrice = parseFloat($('input[name="canvas_type"]:checked').val());
            let cur_sizes_add_coef = ex_price * canvasTypePrice;

            let cur_sizes_add = parseFloat(cur_sizes_add_coef).toFixed(2);

            totalPrice = parseFloat(totalPrice) + parseFloat(ex_price);

            if (cur_sizes_add != 0.00) {
                totalPrice = cur_sizes_add;
            }

            totalPrice = parseFloat(totalPrice).toFixed(2);

            if (typeof ram_price === 'undefined') {
                rp = 0;
            } else {
                if ($('.js_size.active'.length)) {
                    let height = parseInt($('input[name="whole-height"]').val());
                    let width = parseInt($('input[name="whole-width"]').val());
                    rp = ((height + height + width + width) / 100) * parseInt(ram_price);
                    if ($('.ramm__pr').length && rp == 0) {
                        $('.ramm__pr').remove();
                    }

                    if (rp != 0 && rp != undefined && rp != null) {
                        $('.ramm__pr').remove();
                        if ($('.modular-shapes').children('button').eq(0).hasClass('js__selected')) {
                            var rm_txt = $('body').data('for_ram');
                            $('#totalSum').append("<span class='ramm__pr'>+" + Math.round(rp) + "€ " + rm_txt + "</span>");
                            $('#tab_screen1 .sum').append("<span class='ramm__pr'>+" + Math.round(rp) + "€ " + rm_txt +
                                "</span>");
                        } else {
                            $('.ramu_zero_item').trigger('click');
                            rp = 0;
                        }
                    }

                } else {
                    rp = 0;
                }
            }


            totalPrice += rp;

            totalPrice += parseFloat(price_hud_of);
            totalPrice = calc_float_val(totalPrice);
            // endnew
            totalPrice = parseFloat(totalPrice) + parseFloat(dost_price);
            totalPrice = totalPrice.toFixed(2);

            // тип исполнения
            let ex_add_price = calcExecutionPrice(sizeSize, execution);
            totalPrice = parseFloat(totalPrice) + parseFloat(ex_add_price);

            totalPrice = parseFloat(totalPrice).toFixed(2);

            // худ. оформление
            totalPrice = parseFloat(totalPrice).toFixed(2);
            let coef_1 = parseInt($('input[name="decoration"]:checked').data('coef_sm'));
            let coef_2 = parseInt($('input[name="decoration"]:checked').data('coef_md'));
            let coef_3 = parseInt($('input[name="decoration"]:checked').data('coef_lg'));
            let s1 = $('input[name="whole-width"]').val();
            let s2 = $('input[name="whole-height"]').val();
            let area = s1 * s2 / 10000;

            if (area <= 0.4) {
                decorAddPrice = area * coef_1;
            } else if (area > 0.4 && area <= 1) {
                decorAddPrice = area * coef_2;
            } else if (area >= 1.01) {
                decorAddPrice = area * coef_3;
            }

            decorAddPrice = parseFloat(decorAddPrice);
            console.log(decorAddPrice);
            //

            if (isNaN(decorAddPrice)) {
                decorAddPrice = 0;
            }

            totalPrice = parseFloat(totalPrice);

            totalPrice += decorAddPrice;



            var total_without_rp = parseFloat(totalPrice) - rp;

            total_without_rp = calc_float_val(total_without_rp);

            if (isNaN(total_without_rp)) {
                return;
            }

            totalPrice = parseFloat(totalPrice).toFixed(2);

            var data_total_price = totalPrice;

            totalPrice = parseFloat(totalPrice).toFixed(2);
            total_without_rp = parseFloat(total_without_rp).toFixed(2);

            var mult = $('body').data('multiplier');

            if (isNaN(mult)) {
                mult = 1;
            }

            totalPrice = totalPrice * mult;
            total_without_rp = total_without_rp * mult;
            data_total_price = data_total_price * mult;

            totalPrice = parseFloat(totalPrice).toFixed(2);
            total_without_rp = parseFloat(total_without_rp).toFixed(2);
            data_total_price = parseFloat(data_total_price).toFixed(2);

            $('.sum #totalPrice').html(total_without_rp + ' &#8364');
            $('#totalSum #totalPrice').html(total_without_rp);

            $('.total_with_total').remove();
            if (total_without_rp != totalPrice) {
                $('#totalSum').append('<div class="total_with_total">' + totalPrice + '  &#8364</div>');
                $('#tab_screen1 .sum').append('<div class="total_with_total">' + totalPrice + '  &#8364</div>');
            }

            $('#totalSum #totalPrice').attr('data-price', data_total_price);
            $('.sum #totalPrice').attr('data-price', data_total_price);
            $('.sum__mod #glob_summ2').text(totalPrice);
        }

        $('.dost_items2 input, .dost_items1 input').change(function(e) {
            dost_price = $(this).val();

            if (dost_price == 0) {
                $('.tabs-item1 .dost_items').children('li').eq(1).find('label').trigger('click');
                $('.tabs-item2 .dost_items').children('li').eq(1).find('label').trigger('click');
            } else {
                $('.tabs-item1 .dost_items').children('li').eq(2).find('label').trigger('click');
                $('.tabs-item2 .dost_items').children('li').eq(2).find('label').trigger('click');
            }
            setTimeout(() => {
                calcTotalPrice();
            }, 200);
        });

        $(document).on('click', '.js_decor_item .jcf-radio', function(e) {
            price_hud_of = $(this).find('input').val();
            console.log(price_hud_of);
            setTimeout(() => {
                calcTotalPrice();
            }, 200);
        });

        $('.interior-item').click(function(e) {
            $('.ramu-item.active').trigger('click');
        });

        $(document).on('click', '.comments-item .jcf-radio', function(e) {
            setTimeout(() => {
                calcTotalPrice();
            }, 200);
        });

        $(document).on('click', '#t1_submit_btn , .js_add_basket_module', function(e) {
            e.preventDefault();
            calcTotalPrice();

            let userImages = saveModularSM();
            let collageSvgImage = userImages.svg;
            var sz = $('.active.calcSize').data('size');
            let height = $('input[name="whole-height"]').val();
            let width = $('input[name="whole-width"]').val();

            let form_data = new FormData();
            form_data.append('name', document.title);
            form_data.append('price', $('#totalPrice').data('price'));

            form_data.append('collageSvgImage_hash', file_hash);
            console.log('hash:', file_hash);

            if ($('.tabs-item1').hasClass('active')) {
                var cur_img = 'tab_screen1';
            } else {
                var cur_img = 'tab_screen2';
            }

            form_data.append('image', $('.imageFile').prop('files')[0]);

            form_data.append('collageSvgImage', collageSvgImage);
            form_data.append('is_orig_file', 1);

            if ($('input[name="photo_ex"]').length && $('input[name="photo_ex"').val() != '') {
                form_data.append('photo_ex', $('input[name="photo_ex"]').prop('files')[0]);
            } else {
                form_data.append('photo_ex', '');
            }

            var size1 = $('input[name="whole-width"]').val();
            var size2 = $('input[name="whole-height"]').val();

            form_data.append('size', width + 'x' + height);
            form_data.append('formId', $('.modular-shapes .js__selected').index() + 1);
            form_data.append('holst_id', $(".decoration-item .jcf-checked").find('input').data('id'));
            form_data.append('type', $(".execution .active").find('p').text());
            form_data.append('hud_of', $(".decoration-items .jcf-checked").find('input').data('name'));
            form_data.append('userComment', $('#userComment').val());
            form_data.append('pack', $('.comments-item .jcf-checked').children('input').data('name'));
            form_data.append('dost_time', $('.dost_items .jcf-label-active input').val());
            form_data.append('wall_size_mod', $('[name="wall_size"]').val());

            if (ram_price > 0) {
                var ram_id = $("input[name='ram_id']:checked").val();
                form_data.append('ram_id', ram_id);
            }

            $('.js_spinner').jmspinner('large');
            $.ajax({
                type: 'post',
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: $('#routes_links').data('bask_add_construct'),
                data: form_data,
                success: function(response) {
                    if (response) {
                        $('.js_spinner').jmspinner(false);
                        var data = jQuery.parseJSON(response);
                        if (data['Error']) {
                            alert(data['Error']);
                        } else if (data['success']) {
                            // seo
                            var total_seo__price = $('#totalPrice').data('price');
                            var page_name = $('.h3__title').text();
                            window.dataLayer = window.dataLayer || [];
                            dataLayer.push({
                                'ecommerce': {
                                    'currencyCode': 'EUR',
                                    'add': {
                                        'actionField': {
                                            'list': page_name
                                        },
                                        'products': [{
                                            'name': page_name,
                                            'id': '0',
                                            'price': total_seo__price,
                                            'categoryId': '0',
                                            'category': page_name,
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
                    }
                },
                error: function(error) {
                    if (error.responseText) {
                        let response = JSON.parse(error.responseText);
                        $.each(response.errors, function(key, value) {
                            alert(value[0])
                        });
                    } else {
                        alert('Server error');
                    }

                    $('.js_spinner').jmspinner(false);
                }
            });
        });

    </script>

@endsection
