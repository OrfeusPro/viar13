    <!-- ellipse -->

    <div class="ellipse ellipse_black ellipse_top">
      <img  src="https://viarcanvas.com/theme/viar/images/icon/ellipse-black.svg" alt="img" loading="lazy">
    </div>


    <!-- collage order -->


    <div class="collage-order__screen">
      <div class="section-frame">
        <div class="collage-order--inner">
          <div class="page-title--row">
            <div class="page-title page-collage-title">
              {!! trans('collage_new.z2_order_screen_title') !!}
            </div>
            <div class="col-subtitle">
              {!! trans('collage_new.z2_order_screen_subtitle') !!}
            </div>
          </div>
          <div class="collage-order--content">
            <div class="o-abs-el">
              <div class="o-abs1">
                <picture>
                  <source media="(max-width: 700px)" srcset="{{ asset('images/collage/ord1M.svg') }}" type="image/jpeg">
                  <source srcset="{{ asset('images/collage/ord1.svg') }}" type="image/jpeg">
                  <img loading="lazy" width="415" height="433" src="{{ asset('images/collage/ord1.svg') }}" alt="">
                </picture>
                <p>{!! trans('collage_new.z2_order_screen_text1') !!}</p>
              </div>
              <div class="o-abs2">
                <picture>
                  <source media="(max-width: 700px)" srcset="{{ asset('images/collage/ord2M.svg') }}" type="image/jpeg">
                  <source srcset="{{ asset('images/collage/ord2.svg') }}" type="image/jpeg">
                  <img loading="lazy" width="415" height="433" src="{{ asset('images/collage/ord2.svg') }}" alt="">
                </picture>
                <p>{!! trans('collage_new.z2_order_screen_text2') !!}</p>
              </div>
              <div class="o-abs3">
                <picture>
                  <source media="(max-width: 700px)" srcset="{{ asset('images/collage/ord3M.svg') }}" type="image/jpeg">
                  <source srcset="{{ asset('images/collage/ord3.svg') }}" type="image/jpeg">
                  <img loading="lazy" width="415" height="433" src="{{ asset('images/collage/ord3.svg') }}" alt="">
                </picture>
                <p>{!! trans('collage_new.z2_order_screen_text3') !!}</p>
              </div>
              <div class="o-abs4">
                <picture>
                  <source media="(max-width: 700px)" srcset="{{ asset('images/collage/ord4M.svg') }}" type="image/jpeg">
                  <source srcset="{{ asset('images/collage/ord4.svg') }}" type="image/jpeg">
                  <img loading="lazy" width="255" height="433" src="{{ asset('images/collage/ord4.svg') }}" alt="">
                </picture>
                <p>{!! trans('collage_new.z2_order_screen_text4') !!}</p>
              </div>
              <div class="o-abs5">
                <picture>
                  <source media="(max-width: 700px)" srcset="{{ asset('images/collage/ord5M.svg') }}" type="image/jpeg">
                  <source srcset="{{ asset('images/collage/ord5.svg') }}" type="image/jpeg">
                  <img loading="lazy" width="415" height="433" src="{{ asset('images/collage/ord5.svg') }}" alt="">
                </picture>
                <p>{!! trans('collage_new.z2_order_screen_text5') !!}</p>
              </div>
              <div class="o-abs6">
                <picture>
                  <source media="(max-width: 700px)" srcset="{{ asset('images/collage/ord6M.svg') }}" type="image/jpeg">
                  <source srcset="{{ asset('images/collage/ord6.svg') }}" type="image/jpeg">
                  <img loading="lazy" width="415" height="433" src="{{ asset('images/collage/ord6.svg') }}" alt="">
                </picture>
                <p>{!! trans('collage_new.z2_order_screen_text6') !!}</p>
              </div>
              <div class="o-abs7">
                <picture>
                  <source media="(max-width: 700px)" srcset="{{ asset('images/collage/ord7M.svg') }}" type="image/jpeg">
                  <source srcset="{{ asset('images/collage/ord7.svg') }}" type="image/jpeg">
                  <img loading="lazy" width="415" height="433" src="{{ asset('images/collage/ord7.svg') }}" alt="">
                </picture>
                <p>{!! trans('collage_new.z2_order_screen_text7') !!}</p>
              </div>
            </div>
            <div class="collage-order-img">
			@if($ACollageOrderScreen)
              <picture>
			  {{--<source srcset="{{ format_webp($ACollageOrderScreen['image']) }}" type="image/webp">--}}
                <source srcset="{{ Voyager::image($ACollageOrderScreen['image']) }}" type="image/jpeg">
                <img width="589" height="616" loading="lazy" src="{{ Voyager::image($ACollageOrderScreen['image']) }}" @altAttrs(['type' => \App\Models\ACollageOrderScreen::class, 'id' => data_get($ACollageOrderScreen, 'id')], 'image', data_get($ACollageOrderScreen, 'image'))>
              </picture>
			@endif
            </div>
          </div>
          <a href="#collage-generator" class="c-order-btn">
            {!! trans('collage_new.z2_order_screen_btn') !!}
          </a>
        </div>
      </div>
    </div>



    <!-- ellipse -->

    <div class="ellipse ellipse_black">
      <img src="https://viarcanvas.com/theme/viar/images/icon/ellipse-black.svg" alt="img" loading="lazy">
    </div>
