<div class="about__block about__fifth brief-v">
    <div class="about-deadline">
        <div class="deadline-block">
            <div class="deadline-title">
                <img src="{{ ver_asset('images/clock.png') }}" alt=""/>
                <p>{!! trans('canvas.tab5__inner__title') !!}</p>
            </div>
            <div class="deadline-items">
                <div class="deadline-item">
                    <div class="deadline-img">
                        <picture>
                            <source srcset="{{ ver_asset('images/three-days.webp') }}" type="image/webp"/>
                            <source srcset="{{ ver_asset('images/three-days.jpg') }}" type="image/jpg"/>
                            <img src="{{ ver_asset('images/three-days.jpg') }}" alt=""/>
                        </picture>
                    </div>
                    <div class="deadline-content">
                        <div class="deadline-desc">
                            {!! trans('canvas.tab5__inner__block1_title') !!}
                        </div>
                        <div class="deadline-text">
                            {!! trans('canvas.tab5__inner__block1_text') !!}
                        </div>
                    </div>
                </div>
                <div class="deadline-item">
                    <div class="deadline-img">
                        <picture>
                            <source srcset="{{ ver_asset('images/one-day.webp') }}" type="image/webp"/>
                            <source srcset="{{ ver_asset('images/one-day.jpg') }}" type="image/jpg"/>
                            <img src="{{ ver_asset('images/one-day.jpg') }}" alt=""/>
                        </picture>
                    </div>
                    <div class="deadline-content">
                        <div class="deadline-desc">
                             {!! trans('canvas.tab5__inner__block2_title') !!}
                        </div>
                        <div class="deadline-text">
                             {!! trans('canvas.tab5__inner__block2_text') !!}
                        </div>
                    </div>
                </div>
                <div class="deadline-item">
                    <div class="deadline-img">
                        <picture>
                            <source srcset="{{ ver_asset('images/on-date.webp') }}" type="image/webp"/>
                            <source srcset="{{ ver_asset('images/on-date.jpg') }}" type="image/jpg"/>
                            <img srcset="{{ ver_asset('images/on-date.jpg') }}" alt=""/>
                        </picture>
                    </div>
                    <div class="deadline-content">
                        <div class="deadline-desc">
                            {!! trans('canvas.tab5__inner__block3_title') !!}
                        </div>
                        <div class="deadline-text">
                            {!! trans('canvas.tab5__inner__block3_title_after') !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="deadline-block">
            <div class="deadline-title">
                <img src="{{ ver_asset('images/delivery.png') }}" alt=""/>
                <p>{!! trans('canvas.tab5__delivery__title') !!}</p>
            </div>
            <div class="deadline-items">
                <div class="deadline-item">
                    <div class="deadline-img">
                        <picture>
                            <source srcset="{{ ver_asset('images/van.webp') }}" type="image/webp"/>
                            <source srcset="{{ ver_asset('images/van.jpg') }}" type="image/jpg"/>
                            <img src="{{ ver_asset('images/van.jpg') }}" alt=""/>
                        </picture>
                    </div>
                    <div class="deadline-content">
                        <div class="deadline-text">
                            {!! trans('canvas.tab5__delivery__block1_title') !!}
                        </div>
						<div class="deadline-desc">{!! trans('canvas.tab5__delivery__block1_title_after') !!}</div>
                    </div>
                </div>
                <div class="deadline-item">
                    <div class="deadline-img">
                        <picture>
                            <source srcset="{{ ver_asset('images/on-adress.webp') }}" type="image/webp"/>
                            <source srcset="{{ ver_asset('images/on-adress.jpg') }}" type="image/jpg"/>
                            <img src="{{ ver_asset('images/on-adress.jpg') }}" alt=""/>
                        </picture>
                    </div>
                    <div class="deadline-content">
                        <div class="deadline-text">
                            {!! trans('canvas.tab5__delivery__block2_title') !!}
                        </div>
                        <div class="deadline-desc">{!! trans('canvas.tab5__delivery__block2_title_after') !!}</div>
                    </div>
                </div>
                <div class="deadline-item">
                    <div class="deadline-img">
                        <picture>
                            <source srcset="{{ ver_asset('images/abroad.webp') }}" type="image/webp"/>
                            <source srcset="{{ ver_asset('images/abroad.jpg') }}" type="image/jpg"/>
                            <img src="{{ ver_asset('images/abroad.jpg') }}" alt=""/>
                        </picture>
                    </div>
                    <div class="deadline-content">
                        <div class="deadline-text">
                            {!! trans('canvas.tab5__delivery__block3_title') !!}
                        </div>
                        <div class="deadline-desc">{!! trans('canvas.tab5__delivery__block3_title_after') !!}</div>
                    </div>
                </div>
            </div>
            <div class="courier-info">
                <div class="courier-text">
                    {!! trans('canvas.tab5__delivery__after_text') !!}
                </div>
                <div class="courier-items">
                    <div class="courier-item">
                        <div class="courier-inner">
                            <div class="courier-logo">
                                <img src="{{ ver_asset('images/venipak.png') }}" alt=""/>
                            </div>
                            <div class="courier-txt">
                                {!! trans('canvas.tab5__delivery__after_block1_text') !!}
                            </div>
                        </div>
                    </div>
                    <div class="courier-item">
                        <div class="courier-inner">
                            <div class="courier-logo">
                                <img src="{{ ver_asset('images/dpd.png') }}" alt=""/>
                                <img src="{{ ver_asset('images/omniva-logo-41B019A1E9-seeklogo.com.png') }}" style="max-width: 50px;" alt=""/>
                            </div>
                            <div class="courier-txt">
                                {!! trans('canvas.tab5__delivery__after_block2_text') !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="hidden-trigger">
        <a href="#examples" class="anchor ellipse-arrow ellipse-arrow_white" aria-label="anchor link">
            <i class="fa-arrow-down"></i>
        </a>
        <span>{{ trans('canvas.tabs_show_btn_text') }}</span>
        <span>{{ trans('canvas.tabs_hide_btn_text') }}</span>
    </div>
</div>
