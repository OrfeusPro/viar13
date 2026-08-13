<form method="POST" action="{{ route('send_all_styles_form') }}" class="banner__block" enctype="multipart/form-data">
    @csrf
    <input type="hidden" id="new_name" name="new_name" value="banner">
    <div class="f__errs">
        @if ($errors)
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif
    </div>
    <picture class="banner__pinned-photo">
        <source srcset="{{ asset('img/pinned-photo.webp') }}" type="image/webp">
        <source srcset="{{ asset('img/pinned-photo.webp') }}" type="image/png">
        <img src="{{ asset('img/pinned-photo.webp') }}" alt="">
    </picture>
    <h2 class="banner__title">
        {!! $data['f_title'] !!}
    </h2>
    <div class="banner__form">
        <div class="first__item banner__item_file">
            <input type="file" name="images" id="input__file" class="banner__input-hide" required
                accept=".png, .jpg, .jpeg, .bmp, .webp, .psd, .heic, .heif">
            <label for="input__file" class="banner__upload">
                <img src="{{ asset('img/icons/upload.svg') }}" alt="" class="img-svg">
                <img src="{{ asset('img/icons/image-gallery.svg') }}" alt="" class="img-svg image-gallary">
                <span class="form-send__text">
                    <span>{!! $data['f_desc1'] !!}</span>
                    <span class="form-send__lbl-text">
                        {!! $data['f_desc2'] !!}
                    </span>
                </span>
            </label>
        </div>
        <div class="banner__item">
            <label for="email-banner" class="banner__name">
                {!! $data['f_email'] !!}
            </label>
            <div class="banner__input-item">
                <input type="text" id="email-banner" name="email" class="banner__input" placeholder="E-mail" required>
                <img src="{{ asset('img/icons/mail.svg') }}" alt="" class="img-svg">
            </div>
        </div>
        <div class="banner__item">
            <label for="phone-banner" class="banner__name">
                {!! $data['f_phone'] !!}
            </label>
            <div class="banner__input-item">
                <input type="text" id="phone-banner" name="phone" class="banner__input phone" required>
                <img src="{{ asset('img/icons/phone.svg') }}" alt="" class="img-svg">
            </div>
        </div>
        <div class="banner__item">
            <button class="banner__btn btn" type="submit">
                {!! $data['f_send'] !!}
            </button>
        </div>
    </div>
</form>
