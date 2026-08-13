    <!-- ellipse -->


    <div class="ellipse el-desk ellipse-b-dbt">
        <img src="{{ asset('images/icon/ellipse-whete.svg') }}" alt="img" loading="eager" >
    </div>

    <!-- love collage -->


    <div class="collage-love__screen">
        <div class="section-frame">
            <div class="collage-love--inner">
                <div class="page-title--row love-title--row">
                    <div class="page-title page-collage-title">
                        {!! trans('collage_new.z5_love_screen_title') !!}
                    </div>
                </div>
                <div class="c-l-row">
                    <div class="c-l-col">
                        <div class="col-subtitle-m c-l-mtitle">
                            {!! trans('collage_new.z5_love_screen_text1') !!}
                        </div>
                        <div class="c-l-image">
                            <picture>
                                <source media="(max-width: 520px)" srcset="{{ asset('images/collage/11M.webp') }}" type="image/webp">
                                <source media="(max-width: 520px)" srcset="{{ asset('images/collage/11M.jpg') }}" type="image/jpeg">
                                <source srcset="{{ asset('images/collage/11.webp') }}" type="image/webp">
                                <source srcset="{{ asset('images/collage/11.jpg') }}" type="image/jpeg">
                                <img loading="lazy" width="377" height="586" src="{{ asset('images/collage/11.jpg') }}" alt="">
                            </picture>
                        </div>
                    </div>
                    <div class="c-l-col">
                        <div class="col-subtitle-m c-l-cmtitle">
                            {!! trans('collage_new.z5_love_screen_text2') !!}
                        </div>
						<a href="#generate" class="c-def-btn">
						  {!! trans('collage_new.z5_love_screen_text3') !!}
						</a>

                    </div>
                    <div class="c-l-col">
                        <div class="c-l-image">
                            <picture>
                                <source media="(max-width: 520px)" srcset="{{ asset('images/collage/12M.webp') }}" type="image/webp">
                                <source media="(max-width: 520px)" srcset="{{ asset('images/collage/12M.jpg') }}" type="image/jpeg">
                                <source srcset="{{ asset('images/collage/12.webp') }}" type="image/webp">
                                <source srcset="{{ asset('images/collage/12.jpg') }}" type="image/jpeg">
                                <img loading="lazy" width="377" height="586" src="{{ asset('images/collage/12.jpg') }}" alt="">
                            </picture>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="ellipse ellipse-b-ordbt">
        <img loading="lazy" src="{{ asset('images/collage/ellipse-orb.svg') }}" alt="img" loading="eager" >
    </div>
