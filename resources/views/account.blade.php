@extends('layots.common')

@section('title', $sales['lk_title'])

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ ver_asset('css/personal-area.css') }}">
    @include('partials.account.styles')
@endsection

@section('content')
    @error('images')
    <script>
        alert('{{ $message }}');
    </script>
    @enderror

    @if (Session::has('message'))
        <script>
            alert('{{ Session::get('message') }}');
        </script>
    @endif

    <div class="bread-crumbs">
        <i class="icon-icon3"></i>
        <ul vocab="https://schema.org/" typeof="BreadcrumbList">
            <li property="itemListElement" typeof="ListItem">
                <a property="item" typeof="WebPage" href="{{ route('home') }}">
                    <span property="name">@lang('account.index1')</span></a>
                <meta property="position" content="1">
            </li>
            <li property="itemListElement" typeof="ListItem">
                <span property="name">@lang('account.index2')</span>
                <meta property="position" content="2">
            </li>
        </ul>
    </div>

    <section class="personal-area @if (Auth::user()->role->name == 'painter') is_painter_area @else is_user_area @endif">
        <div class="title">
            <h2>@lang('account.index2')</h2>
        </div>
        <div class="personal-area-content">
            <div class="personal-filter clearfix">
                <ul class="filter">
                    <li data-item="0" @if(!isset($_GET['orders']) && !isset($_GET['payed_orders'])) class="active" @endif><a href="javascript:void(0)">@lang('account.index3')</a></li>
                    <li
                        @if(isset($_GET['orders']))
                            class="active"
                        @endif
                        data-item="orders-click"><a href="javascript:void(0)">@lang('account.index4')</a></li>
                    @if (Auth::user()->role->name != 'painter')
                        <li data-item="bonuses-click"><a href="javascript:void(0)">@lang('account.index5')</a></li>
                    @else
                        <li
                            @if(isset($_GET['payed_orders']))
                                class="active"
                            @endif
                            data-item="payed-orders-click"><a href="javascript:void(0)">@lang('account.payed_orders')</a>
                        </li>
                        <li
                            @if(isset($_GET['orders_completed']))
                                class="active"
                            @endif
                            data-item="orders-completed-click"><a href="javascript:void(0)">@lang('account.orders_completed')</a>
                        </li>
                    @endif
                    <li data-item="settings-click"><a href="javascript:void(0)">@lang('account.index6')</a></li>
                </ul>
                <div class="filter-content">
                    <div class="filter-title">
                        <h3>
                            @lang('account.index3')

                            <a role="button" href="#" class="js__logout__click" data-href="{{ route('logout') }}" style="color: #000; font-size: 14px;">
                                [ @lang('account.index7') ]</a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                                <button type="submit"></button>
                            </form>

                        </h3>

                        @if (!empty(Auth::user()->first_name))
                            <p>@lang('account.index8') <strong> {{ Auth::user()->first_name }}
                                    {{ Auth::user()->last_name }}
                                    ({{ Auth::user()->email }})</strong></p>
                        @else
                            <p>@lang('account.index8') <strong>{{ Auth::user()->email }}</strong></p>
                        @endif

                        <p>
                            @if (!empty(Auth::user()->address))
                                {{ Auth::user()->address }} @endif
                            @if (!empty(Auth::user()->phone))
                                , @lang('account.index9') - {{ Auth::user()->phone }} @endif
                        </p>
                    </div>
                    <ul>
                        <li class="orders-click">
                            <img src="{{ asset('img/filter-img1.png') }}" alt="">
                            <h3>@lang('account.index4')</h3>
                            <p>@lang('account.index10')</p>
                        </li>
                        <li class="bonuses-click">
                            <img src="{{ asset('img/filter-img2.png') }}" alt="">
                            <h3>@lang('account.index5')</h3>
                            <p>@lang('account.index11')
                            </p>
                        </li>
                        <li class="settings-click">
                            <img src="{{ asset('img/filter-img3.png') }}" alt="">
                            <h3>@lang('account.index6')</h3>
                            <p>@lang('account.index12')</p>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="personal-item orders orders-click @if(isset($_GET['orders'])) active @endif">
                <h3>@lang('account.index4')</h3>
                @include('partials.account.orders')
            </div>

            @if (Auth::user()->role->name == 'painter')
                <div class="personal-item orders payed-orders-click @if(isset($_GET['payed_orders'])) active @endif">
                    <h3>@lang('account.payed_orders')</h3>
                    <div class="acting">
                        <div class="acting-content">
                            @include('partials.account.payed_orders')
                        </div>
                    </div>
                </div>
                <div class="personal-item orders orders-completed-click @if(isset($_GET['orders_completed'])) active @endif">
                    <h3>@lang('account.orders_completed')</h3>
					<div class="acting-content">
						@include('partials.account.orders_completed')
					</div>
                </div>
            @endif

            @if (Auth::user()->role->name != 'painter')
                <div class="personal-item bonuses bonuses-click bonuses__items">
                    @include('partials.account.bonuses')
                </div>
            @endif

            <div class="personal-item settings settings-click">
                @include('partials.account.personal')
            </div>
    </section>

    <script src="{{ asset('js/jcf.min.js') }}"></script>
    <script src="{{ asset('js/jcf.select.min.js') }}"></script>
    <script src="{{ asset('js/jcf.checkbox.min.js') }}"></script>
    <script src="{{ asset('js/personal-area.min.js') }}?v=99"></script>

    <script>
        $(function () {



        $(document).on('click', '.js__logout__click', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            document.getElementById('logout-form').submit();
        });

        $(document).on('submit', '.js_painter_admin_chat', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            $('.js_spinner').jmspinner('large');

            var th = $(this);

            var order_id = $(this).data('id');
            var painter_msg = $(this).find('.painter_msg').val();

            $.ajax({
                method: 'POST',
                url: "{{ route('update_order_chat') }}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    order_id: order_id,
                    painter_msg: painter_msg,
                },
                dataType: 'json',
                success: function (msg) {
                    if (msg.status = 1) {
                        var you_text = 'Вы';
                        var d = new Date();
                        var curr_date = d.getDate();
                        var curr_month = d.getMonth() + 1;
                        var curr_year = d.getFullYear();

                        curr_month = curr_month+='';
                        if(curr_month.length == 1){
                            curr_month = '0'+curr_month;
                        }

                        var date = curr_year+'/'+curr_month+'/'+curr_date;

                        var text = painter_msg;
                        $('.comments__main__list').append(`
                        <li>
                            <div class="comment__user">${you_text}</div>
                            <div class="comment__date">${date}</div>
                            <div class="comment">${painter_msg}</div>
                        </li>
                        `);

                        th.find('.painter_msg').val("");

                    }

                    $('.js_spinner').jmspinner(false);
                },
                error: function (jqXHR, exception) {
                    $('.js_spinner').jmspinner(false);

                }
            })

            return false
        })

            $(document).on('submit', '.js_send_user_add_images', function (e) {
                e.preventDefault();
                var th = $(this);

                $('.js_spinner').jmspinner('large');
                th.find('.painter_btn').attr('disabled', true);

                let formData = new FormData();
                let order_id = th.find('[name="order_id"]').val();
                let user_comment = th.find('[name="user_comment"]').val();

                if (user_comment == null || user_comment == 'null') {
                    user_comment = '';
                }

                formData.append('order_id', order_id);
                formData.append('client_comment', user_comment);

                var totalfiles = document.getElementById('js_painter_images_' + order_id).files.length;

                for (var index = 0; index < totalfiles; index++) {
                    formData.append("client_images[]", document.getElementById('js_painter_images_' +
                        order_id).files[index]);
                }

                $.ajax({
                    type: 'post',
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route('send_client_painter_comments') }}',
                    data: formData,
                    success: function (response) {
                        $('.js_spinner').removeClass('spinner');
                        th.find('.painter_btn').removeAttr('disabled');
                        th.find('.js_u_comm_textarea').val('');
                        if (response.status == 1 || response.status == true) {
                            var d = new Date();
                            var curr_date = d.getDate();
                            var curr_month = d.getMonth() + 1;
                            var curr_year = d.getFullYear();

                            curr_month = curr_month += '';
                            if (curr_month.length == 1) {
                                curr_month = '0' + curr_month;
                            }

                            var date = curr_date + '.' + curr_month + '.' + curr_year;
                            th.find('.js_order_user_comments').append(`
                                                                    <li>
                                                                        <span>${date} - </span>${response.comment}
                                                                    </li>
                                                                `);

                            if (response.images) {
                                th.next('.js_user_all_images').find(
                                    '.user__painter__imgs__list').children().remove();
                                var cur_images = response.images.split(",");

                                for (let index = 0; index < cur_images.length; index++) {
                                    const el = cur_images[index];
                                    th.next('.js_user_all_images').find(
                                        '.user__painter__imgs__list').append(`
                                                                        <li>
                                                                            <a href="${el}" target="_blank">
                                                                                <img src="${el}" alt="" class="img__user_upl">
                                                                            </a>
                                                                        </li>
                                                                            `);

                                }


                            }
                        } else {
                            alert('Error');
                        }

                        th.trigger('reset');
                        document.getElementById("js_painter_images_" + order_id).value = "";
                        $('.js_spinner').jmspinner(false);
                    },
                    error: function (error) {
                        $('.js_spinner').removeClass('spinner');
                        th.find('.painter_btn').removeAttr('disabled');
                        th.trigger('reset');
                        document.getElementById("js_painter_images_" + order_id).value = "";
                        $('.js_spinner').jmspinner(false);
                        console.log(error);
                    }
                });

            });

            $(document).on('submit', '.js_painter_form_images_upd', function (e) {
                e.preventDefault();

                var th = $(this);
                th.find('.js_spinner').jmspinner('large');
                th.find('button').attr('disabled', true);

                let formData = new FormData();
                let order_id = th.find('[name="order_id"]').val();

                formData.append('order_id', order_id);

                var totalfiles = document.getElementById('painter_imgs_order_' + order_id).files.length;

                for (var index = 0; index < totalfiles; index++) {
                    formData.append("painter_images[]", document.getElementById('painter_imgs_order_' +
                        order_id).files[index]);
                }

                $.ajax({
                    type: 'post',
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route('update_painter_order_images') }}',
                    data: formData,
                    success: function (response) {
                        th.find('button').removeAttr('disabled');
                        th.find('.js_spinner').removeClass('spinner');
                        if (response.status === 1) {
                            if (response.images) {
                                $('.js_hb_painter_' + response.order_id).find('.painter_images_group').children().remove();
                                var cur_images = response.images.split(",");

                                for (let index = 0; index < cur_images.length; index++) {
                                    const el = cur_images[index];
									if(el){
                                    $('.js_hb_painter_' + response.order_id).find('.painter_images_group')
                                        .append(`
                                                                        <div class="painter_img">
                                                                            <a target="_blank"
                                                                            href="${el}">
                                                                                <img style="max-width:100%;max-height:100px;"
                                                                                src="${el}" alt="">
                                                                            </a>
                                                                        </div>
                                                                            `);
									}
                                }
                            }
                            alert('Success');
                        } else {
                            alert('Error');
                        }
                        th.find('.js_spinner').jmspinner(false);
                    },
                    error: function (error) {
                        th.find('.js_spinner').removeClass('spinner');
                        th.find('button').removeAttr('disabled');
                    }
                });

            });


            $(document).on('submit', '.js_painter_form_images_upd_sketch', function (e) {
                e.preventDefault();

                var th = $(this);
                th.find('.js_spinner').jmspinner('large');
                th.find('button').attr('disabled', true);

                let formData = new FormData();
                let order_id = th.find('[name="order_id"]').val();

                formData.append('order_id', order_id);

                var totalfiles = document.getElementById('painter_sketch_imgs_order_' + order_id).files.length;

                for (var index = 0; index < totalfiles; index++) {
                    formData.append("painter_sketch_images[]", document.getElementById('painter_sketch_imgs_order_' + order_id).files[index]);
                }

                $.ajax({
                    type: 'post',
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route('update_painter_sketch_order_images') }}',
                    data: formData,
                    success: function (response) {
                        th.find('button').removeAttr('disabled');
                        th.find('.js_spinner').removeClass('spinner');
                        if (response.status === 1) {
                            if (response.images) {


                                $('.js_hb_sketch_painter_' + response.order_id).find('.painter_images_group').children().remove();
                                var cur_images = response.images.split(",");

                                for (let index = 0; index < cur_images.length; index++) {
                                    const el = cur_images[index];
									if(el)
									{
                                    $('.js_hb_sketch_painter_' + response.order_id).find('.painter_images_group')
                                        .append(`
                                                                        <div class="painter_img" style="text-align: center;">
                                                                            <a target="_blank"
                                                                            href="${el}">
                                                                                <img style="max-width:100%;max-height:100px;"
                                                                                src="${el}" alt="">
                                                                            </a>
                                                                        </div>
                                                                            `);
									}
                                }
                            }
                            alert('Success');
                        } else {
                            alert('Error');
                        }
                        th.find('.js_spinner').jmspinner(false);
                    },
                    error: function (error) {
                        th.find('.js_spinner').removeClass('spinner');
                        th.find('button').removeAttr('disabled');
                    }
                });

            });



            $(document).on('submit', '.js_painter_form_with_comment', function (e) {
                e.preventDefault();
                var th = $(this);
                th.find('.js_spinner').jmspinner('large');

                let formData = new FormData();
                let order_id = th.find('[name="order_id"]').val();
                let comment = th.find('[name="comment"]').val();

                formData.append('order_id', order_id);
                formData.append('client_comment', comment);

                $.ajax({
                    type: 'post',
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route('update_painter_comment') }}',
                    data: formData,
                    success: function (response) {
                        th.find('.js_spinner').removeClass('spinner');
                        th.find('.painter_msg').val('');
                        if (response.status == 1 || response.status == true) {
                            var d = new Date();
                            var curr_date = d.getDate();
                            var curr_month = d.getMonth() + 1;
                            var curr_year = d.getFullYear();

                            curr_month = curr_month += '';
                            if (curr_month.length == 1) {
                                curr_month = '0' + curr_month;
                            }

                            var date = curr_date + '.' + curr_month + '.' + curr_year;

                            th.closest('.acting-content').find('.painter__comments__list')
                                .append(`<li>
                                                                <span>${date} - </span> ${comment}
                                                            </li>`);
                        } else {
                            alert('Error');
                        }
                        th.find('.js_spinner').jmspinner(false);
                    },
                    error: function (error) {
                        th.find('.js_spinner').removeClass('spinner');
                        console.log(error);
                    }
                });
            });

            $('body').on('click', '#AccountFormChangePasswordAndContact button', function () {
                var inputs = {}

                var data = $(this).parent().find('input')

                for (obj in data) {
                    if ($.isNumeric(obj)) {
                        var key = $(data[obj]).attr('name')
                        var type = $(data[obj]).attr('type')
                        var value = $(data[obj]).val()

                        value = (type === 'checkbox' && $(data[obj]).is(':checked')) ?
                            'YES' :
                            (type === 'checkbox') ? 'NO' : value

                        inputs[key] = value
                    }
                }

                $.ajax({
                    method: 'POST',
                    url: "{{ url('/ajax_change_information') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: inputs,
                    dataType: 'json',
                    success: function (msg) {
                        if (msg.status = 'ok') {
                            location.reload();
                        }
                    },
                    error: function (jqXHR, exception) {
                        var msg = JSON.parse(jqXHR.responseText)
                        console.log(msg)
                        $('#AccountFormChangePasswordAndContact div.error').html('')

                        for (key in msg.errors) {
                            for (key2 in msg.errors[key]) {
                                $('#AccountFormChangePasswordAndContact div.error[name=' + key +
                                    ']').append('<p style="color: red;">' + msg.errors[key][
                                    key2
                                    ] + '</p>')
                            }
                        }
                    }
                })

                return false
            })
        })

    </script>

    <style>
        .popup {
            height: auto!important;
        }
        ul.pagination .page-item{
            margin: 0 5px;
        }
        ul.pagination {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0 auto;
                }
        .pagination.pagination_payed {
            padding: 15px 0;
        }


		.personal-area .acting-item img
		{
			max-width:250px;
		}

    </style>
@endsection

