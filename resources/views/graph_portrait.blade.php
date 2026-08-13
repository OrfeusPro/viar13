@extends('layots.common')

@section('title', $canvas_head['meta_title'])

@section('og_tags')
    <meta property="og:title" content="{{ $canvas_head['meta_title'] }}" />
@endsection

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('css/canvas.css') }}">
@endsection

@section('content')

    @include('partials.canvas.header')
    @include('partials.canvas.tabs')
    @include('partials.canvas.generator')

    <section class="compositions">
        <div class="title">
            <h2>{{ $canv_bot['comp_title'] }}</h2>
        </div>
        <div class="compositions-block">
            <div class="compositions-content">
                <div class="text clearfix">
                    {!! $canv_bot['comp_top_text_block'] !!}
                </div>
                <div class="compositions-form">
                    <img class="" src="{{ asset('img/compositions-form-img.png') }}"
                        data-src="{{ asset('img/compositions-form-img.png') }}" alt="">
                    <div class="form">
                        <div class="title-form">
                            <h4>{!! $canv_bot['comp_right_form_title'] !!}</h4>
                        </div>
                        <form class="js_send_future_art" method="POST" action="" enctype="multipart/form-data">
                            <input required class="js_name" name="name" type="text" placeholder="{!! $canv_bot['comp_f_name_place'] !!}">
                            <input required class="js_phone" name="phone" type="text" placeholder="{!! $canv_bot['comp_f_tel_place'] !!}"
                                class="phone">
                            <input required class="js_email" name="email" type="text" placeholder="{!! $canv_bot['comp_f_mail_place'] !!}">
                            <div class="file">
                                <input required id="js_file_images" multiple type="file" name="image"
                                    accept=".jpg, .jpeg, .png, .heic, .heif">
                            </div>
                            <button>{!! $canv_bot['comp_f_get_btn'] !!}</button>
                            <p>{!! $canv_bot['comp_f_bot_text'] !!}</p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('partials.stages_everythin')

    <section class="components-pictures">
        <div class="title">
            <img src="{{ asset('img/components-img.png') }}" alt="">
            <h2>{!! $canv_bot['cart_title'] !!}</h2>
        </div>
        <div class="components-content">
            <img src="{{ asset('img/components-content-img.png') }}" alt="" class="components-content-img">
            <div class="components-items">
                <div class="components-item">
                    <img src="{{ asset('img/components-item-icon1.png') }}" alt="">
                    <p>{!! $canv_bot['cart1_title'] !!}</p>
                </div>
                <div class="components-item">
                    <img src="{{ asset('img/components-item-icon2.png') }}" alt="">
                    <p>{!! $canv_bot['cart2_title'] !!}</p>
                </div>
                <div class="components-item">
                    <img src="{{ asset('img/components-item-icon3.png') }}" alt="">
                    <p>{!! $canv_bot['cart3_title'] !!}</p>
                </div>
                <div class="components-item">
                    <img src="{{ asset('img/components-item-icon4.png') }}" alt="">
                    <p>{!! $canv_bot['cart4_title'] !!}</p>
                </div>
                <div class="components-item">
                    <img src="{{ asset('img/components-item-icon5.png') }}" alt="">
                    <p>{!! $canv_bot['cart5_title'] !!}</p>
                </div>
                <div class="components-item">
                    <img src="{{ asset('img/components-item-icon6.png') }}" alt="">
                    <p>{!! $canv_bot['cart6_title'] !!}</p>
                </div>
                <div class="components-item">
                    <img src="{{ asset('img/components-item-icon7.png') }}" alt="">
                    <p>{!! $canv_bot['cart7_title'] !!}</p>
                </div>
                <div class="components-item">
                    <img src="{{ asset('img/components-item-icon8.png') }}" alt="">
                    <p>{!! $canv_bot['cart8_title'] !!}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="packaging">
        <div class="title">
            {!! $canv_bot['up_title'] !!}
        </div>
        <div class="packaging-content">
            <div class="packaging-item clearfix">
                <div class="text">
                    {!! $canv_bot['up_left_text'] !!}
                </div>
                <img src="{{ asset('img/packaging-img.png') }}" alt="" class="packaging-img">
            </div>
            <div class="package-size clearfix">
                <h4>{!! $canv_bot['up_def_title'] !!}</h4>
                {!! $canv_bot['up_sizes_list'] !!}
            </div>
        </div>
    </section>

    <section class="our-work">
        <div class="our-work-content">
            <div class="title">
                <ul>
                    <li><i class="icon-icon6"></i></li>
                    <li><i class="icon-icon6"></i></li>
                    <li><i class="icon-icon6"></i></li>
                    <li><i class="icon-icon6"></i></li>
                    <li><i class="icon-icon6"></i></li>
                </ul>
                <h2>{!! trans('gl.our_w_title') !!}</h2>
            </div>
            @include('partials.reviews')
        </div>
    </section>

    <script src="{{ asset('js/graph_combine.js') }}"></script>

    <script>
        var preview, file, reader;

        function previewFile() {
            preview = document.getElementById('img__selected__tab1');
            file = document.getElementById('user_image').files[0];
            reader = new FileReader();

            reader.onloadend = function() {
                preview.src = reader.result;
            }

            if (file) {
                reader.readAsDataURL(file);
            } else {
                preview.src = "";
            }
        }

        calcTotalPrice();

        $(document).on('submit', '.js_send_future_art', function(e) {
            e.preventDefault();

            let form_data = new FormData();
            form_data.append('name', $(this).find('.js_name').val());
            form_data.append('tel', $(this).find('.js_phone').val());
            form_data.append('email', $(this).find('.js_email').val());

            let TotalImages = $('#js_file_images')[0].files.length;
            let images = $('#js_file_images')[0];
            for (let i = 0; i < TotalImages; i++) {
                form_data.append('images[]', images.files[i]);
            }

            form_data.append('TotalImages', TotalImages);


            $.ajax({
                type: 'post',
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('add_future_art') }}',
                data: form_data,
                success: function(response) {
                    if (response) {
                        $('.popup-bask-add').addClass('active');
                    }
                },
                error: function(error) {
                    console.log(error);
                }
            });

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

        $('input[name="boxes[]"]').on('change', function() {
            calcTotalPrice();
        });

        $('#t1_submit_btn').click(function() {

            if ($(this).attr('data-disabled') != '1') {
                $(this).attr('data-disabled', '1');
            } else {
                return;
            }

            let data = formData();

            for (let pair of data.entries()) {
                console.log(pair[0] + ', ' + pair[1]);
            }

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
                success: function(response) {
                    if (response) {
                        var data = jQuery.parseJSON(response);

                        if (data['Error']) {
                            alert(data['Error']);
                        } else if (data['success']) {
                            $('#smallCart').text(data['count']);
                            $('.popup-bask-add').addClass('active');
                        }

                        $('#t1_submit_btn').attr('data-disabled', '0');
                    }

                    $('.js_spinner').jmspinner(false);
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

                    $('#t1_submit_btn').attr('data-disabled', '0');
                }
            });
        });

        function formData() {
            let formData = new FormData();

            let userImage = $('input[name="user_image"]').prop('files')[0];
            let formId = $('.calcForm.active').data('id');
            let sizeId = $('.calcSize.active').data('id');
            let effectId = $('.calcEffect.active').data('id');
            let canvasId = $('input[name="canvas_type"]:checked').data('id');
            let executionId = $('.calcExecution.active').data('execution');
            let decorationId = $('input[name="decoration"]:checked').data('id');
            let userComment = $('#userComment').val();
            let boxIds = [];

            $('input[name="boxes[]"]:checked').each(function() {
                boxIds.push(parseInt($(this).data('id')));
            });


            formData.append('basketType', '{{ \App\Entity\BasketType::CANVAS_TYPE }}');
            if (userImage)
                formData.append('userImage', userImage);
            if (formId)
                formData.append('formId', formId);
            if (sizeId)
                formData.append('sizeId', sizeId);
            if (effectId)
                formData.append('effectId', effectId);
            if (executionId)
                formData.append('executionId', executionId);
            if (canvasId)
                formData.append('canvasId', canvasId);
            if (decorationId)
                formData.append('decorationId', decorationId);
            if (userComment)
                formData.append('userComment', userComment);
            if (boxIds)
                formData.append('boxIds', JSON.stringify(boxIds));


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

        function calcBoxesPrice() {
            let price = 0;

            $('input[name="boxes[]"]:checked').each(function() {
                price += parseInt($(this).val());
            });

            return price;
        }

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

            let decorationPrice = parseFloat($('input[name="decoration"]:checked').val());

            totalPrice += decorationPrice ? parseFloat(decorationPrice) : 0;

            totalPrice += parseFloat(calcBoxesPrice());

            if (isNaN(totalPrice)) return;

            totalPrice = parseFloat(totalPrice).toFixed(2);
            $('#totalPrice').html(totalPrice);
        }
        $(document).ready(function() {
            $('.jcf-button-content').text('{{ $canvas_head['load_btn_text'] }}');
            $('.jcf-fake-input').text('{{ $canvas_head['load_btn_text'] }}');
        });

    </script>

@endsection
