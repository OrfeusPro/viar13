<section class="compositions">
    <div class="compositions-block">
        <div class="compositions-content">
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