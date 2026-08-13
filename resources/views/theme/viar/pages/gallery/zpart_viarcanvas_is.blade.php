<div class="g-viar">
    <div class="section-frame">
        <div class="g-viar__inner">
                @if (Route::currentRouteName() == 'sizesprices')
                    <h2 class="g-viar-title page-title">
                    @lang('pages.sizeprices.viarcanvas_is')
                    </h2>
                 @elseif (Route::currentRouteName() == 'hb.gallery.index')
                <h2 class="g-viar-title page-title">
                    @lang('gallery.zakaz_h2')
                </h2>
            @elseif  (Route::currentRouteName() == 'hb.gallery.module' && $type == 'reproduction')
                <h2 class="g-viar-title page-title">
                @lang("gallery.reproduction_viarcanvas_is")
                </h2>
              @elseif  (Route::currentRouteName() == 'hb.gallery.module' && $type == 'photo')
                <h2 class="g-viar-title page-title">
                    @lang("gallery.photo_viarcanvas_is")
                </h2>
                @elseif  (Route::currentRouteName() == 'hb.gallery.module' && $type == 'module')
                    <h2 class="g-viar-title page-title">
                        @lang("gallery.module_viarcanvas_is")
                    </h2>
                @else
                    <div class="g-viar-title page-title">
                    @lang("contacts.viarcanvas_is_t_0")
                    </div>
                @endif

        </div>
        <div class="g-viar-blocks">
            <div class="g-viar-block">
                <img width="50" height="27" src="{{ asset(env('THEME') . 'images') }}/gallery/1.svg"
                    loading="lazy" alt="">
                <div class="g-viar-btitle">@lang("contacts.viarcanvas_is_t_1")</div>
                <p>@lang("contacts.viarcanvas_is_t_2")</p>
            </div>
            <div class="g-viar-block">
                <img width="50" height="27" src="{{ asset(env('THEME') . 'images') }}/gallery/2.svg"
                    loading="lazy" alt="">
                <div class="g-viar-btitle">@lang("contacts.viarcanvas_is_t_3")</div>
                <p>
                    @lang("contacts.viarcanvas_is_t_4")
                </p>
            </div>
            <div class="g-viar-block">
                <img width="50" height="27" src="{{ asset(env('THEME') . 'images') }}/gallery/3.svg"
                    loading="lazy" alt="">
                <div class="g-viar-btitle">@lang("contacts.viarcanvas_is_t_5")</div>
                <p>@lang("contacts.viarcanvas_is_t_6")</p>
            </div>
            <div class="g-viar-block">
                <img width="50" height="27" src="{{ asset(env('THEME') . 'images') }}/gallery/4.svg"
                    loading="lazy" alt="">
                <div class="g-viar-btitle">@lang("contacts.viarcanvas_is_t_7")</div>
                <p>
                    @lang("contacts.viarcanvas_is_t_8")
                </p>
            </div>
        </div>
    </div>
</div>
