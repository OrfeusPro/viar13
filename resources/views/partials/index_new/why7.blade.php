<section class="why">
    <div class="section-frame">
        <div class="top-title">
            <h2 class="page-title ">{!! trans('homepage_new.why_pic_title') !!}</h2>
            <p>{!! trans('homepage_new.why_pic_desc') !!}</p>
        </div>
        <div class="why-list">
            <div class="why-box">
                <div class="why-item">
                    <p>{!! trans('homepage_new.why_pic_text1') !!}</p>
                </div>
                <div class="why-item">
                    <p>{!! trans('homepage_new.why_pic_text2') !!}</p>
                </div>
                <div class="why-item">
                    <p>{!! trans('homepage_new.why_pic_text3') !!}</p>
                </div>
                <div class="why-item">
                    <p>{!! trans('homepage_new.why_pic_text4') !!}</p>
                </div>
                <div class="why-item">
                    <p>{!! trans('homepage_new.why_pic_text5') !!}</p>
                </div>
            </div>
            <div class="why-box">
                <div class="why-item">
                    <p>{!! trans('homepage_new.why_pic_text6') !!}</p>
                </div>
                <div class="why-item">
                    <p>{!! trans('homepage_new.why_pic_text7') !!}</p>
                </div>
                <div class="why-item">
                    <p>{!! trans('homepage_new.why_pic_text8') !!}</p>
                </div>
                <div class="why-item">
                    <p>{!! trans('homepage_new.why_pic_text9') !!}</p>
                </div>
                <div class="why-item">
                    <p>{!! trans('homepage_new.why_pic_text10') !!}</p>
                </div>
            </div>
            <div class="why-photo-box">
                <picture>
                    <source srcset="{{ asset('images/why-picture-girl.webp') }}" type="image/webp">
                    <source srcset="{{ asset('images/why-picture-girl.png') }}">
                    <img src="{{ asset('images/why-picture-girl.png') }}" class="why-photo" alt="img"
                        loading="lazy">
                </picture>
                <div class="why-gift">
                    <img src="{{ asset('images/icon/gift.svg') }}" alt="img" loading="lazy">
                    <p>{!! trans('homepage_new.why_pic_gift_text') !!}</p>
                </div>
            </div>
        </div>

		@include('partials.index_new.why7_form')

    </div>
</section>
