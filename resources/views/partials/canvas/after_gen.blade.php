<section class="compositions">
    <div class="title">
        <h2>{{ $canv_bot['comp_title'] }}</h2>
    </div>
    <div class="compositions-block">
        <div class="compositions-content">
            <div class="text clearfix">
                <p><span>{!! $canv_bot['comp_text1'] !!}</span></p>
                <h4>
                    {!! $canv_bot['comp_text2'] !!}
                    <strong>{!! $canv_bot['comp_text3'] !!}</strong>
                </h4>
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
                        <input required class="js_name" name="name" type="text"
                            placeholder="{!! $canv_bot['comp_f_name_place'] !!}">
                        <input required class="js_phone" name="phone" type="text"
                            placeholder="{!! $canv_bot['comp_f_tel_place'] !!}" class="phone">
                        <input required class="js_email" name="email" type="text"
                            placeholder="{!! $canv_bot['comp_f_mail_place'] !!}">

                        <div class="js_spinner" style="text-align: center;"></div>
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
