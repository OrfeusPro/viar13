<section class="formalization__screen section-p">
                <div class="section-frame">
                    <div class="formalization__screen--inner">
                        <div class="section-title">
                            <h2 class="page-title h2_old">
                                @if ($h2_titles->third_block_h2!='')
                                    {!! $h2_titles->third_block_h2 !!}
                                @else
                                    {{ trans('portrait.order_steps_title') }}
                                @endif
                            </h2>

                            <div class="section-subtitle">
                                {{ trans('portrait.order_steps_desc') }}
                            </div>
                        </div>
                        <div class="stages__wrapper">
                            <div class="stages-row">
                                <div class="stage-item">
                                    <div class="stage-img">
                                        <img src="{{ asset('images/photo.png') }}" alt=""/>
                                        <div class="stage-num">1</div>
                                    </div>
                                    <div class="stage-name">{{ trans('portrait.order_step1_title') }}</div>
                                    <div class="stage-text">
                                        {!! trans('portrait.order_step1_desc') !!}
                                    </div>
                                </div>
                                <div class="stage-item">
                                    <div class="stage-img">
                                        <img src="{{ asset('images/conversation.png') }}" alt=""/>
                                        <div class="stage-num">2</div>
                                    </div>
                                    <div class="stage-name">{{ trans('portrait.order_step2_title') }}</div>
                                    <div class="stage-text">
                                        {!! trans('portrait.order_step2_desc') !!}
                                    </div>
                                </div>
                                <div class="stage-item">
                                    <div class="stage-img">
                                        <img src="{{ asset('images/portrait.png') }}" alt=""/>
                                        <div class="stage-num">3</div>
                                    </div>
                                    <div class="stage-name">{{ trans('portrait.order_step3_title') }}</div>
                                    <div class="stage-text">
                                        {!! trans('portrait.order_step3_desc') !!}
                                    </div>
                                </div>
                                <div class="stage-item">
                                    <div class="stage-img">
                                        <img src="{{ asset('images/canvas.png') }}" alt=""/>
                                        <div class="stage-num">4</div>
                                    </div>
                                    <div class="stage-name">{{ trans('portrait.order_step4_title') }}</div>
                                    <div class="stage-text">
                                        {!! trans('portrait.order_step4_desc') !!}
                                    </div>
                                </div>
                                <div class="stage-item">
                                    <div class="stage-img">
                                        <img src="{{ asset('images/delivery1.png') }}" alt=""/>
                                        <div class="stage-num">5</div>
                                    </div>
                                    <div class="stage-name">{{ trans('portrait.order_step5_title') }}</div>
                                    <div class="stage-text">
                                        {!! trans('portrait.order_step5_desc') !!}
                                    </div>
                                </div>
                            </div>
                            <div class="examples-arrow">
                                <a href="#" class="examples-prev examples-prev4 c-ex-prev" aria-label="examples prev">
                                    <i class="fa-arrow-prev"></i>
                                </a>
                                <a href="#" class="examples-next examples-next4 c-ex-next" aria-label="examples next">
                                    <i class="fa-arrow-next"></i>
                                </a>
                            </div>
                        </div>
                        <div class="work-time">
                            <div class="work-time__item">
                                <img src="{{ asset('images/work/work-4.svg') }}" alt="" loading="lazy"/>
                                <p>{!! trans('homepage_new.how_we_work_express') !!}</p>
                            </div>
                            <div class="work-time__item">
                                <img src="{{ asset('images/work/work-5.svg') }}" alt="" loading="lazy"/>
                                <p>{!! trans('homepage_new.how_we_work_standart') !!}</p>
                            </div>
                        </div>
                        <form>
                            <div class="formalization__block" id="generator">
                                <div class="formalization__block--top">
                                    <h2 class="block-title">
                                        @if ($h2_titles->fourth_block_h2!='')
                                            {!! $h2_titles->fourth_block_h2 !!}
                                        @else
                                            {!! trans('portrait.order_steps_bot_text') !!}
                                        @endif
                                    </h2>
                                    <div class="formalization-items">
                                        @include(env('THEME_RESOURCES') . 'pages.portrait.form.photo')
                                        @include(env('THEME_RESOURCES') . 'pages.portrait.form.form')
                                        @include(env('THEME_RESOURCES') . 'pages.portrait.form.sizes',['merchant'=>true])
                                        @include(env('THEME_RESOURCES') . 'pages.portrait.form.persons')
                                        @include(env('THEME_RESOURCES') . 'pages.portrait.form.newcanvas')
                                        @include(env('THEME_RESOURCES') . 'pages.portrait.form.art_decor')
                                        @include(env('THEME_RESOURCES') . 'pages.portrait.form.frames')
                                        @include(env('THEME_RESOURCES') . 'pages.portrait.form.comments')
                                    </div>
                                </div>
                                <div class="formalization__block--bottom">
                                    <div class="formalization__final">
                                         @include(env('THEME_RESOURCES') . 'pages.portrait.form.final')
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
