<div class="canvas__formalization" id="generate">
    <form>
        <div class="formalization__main--wrapper">
        <div class="formalization__block--top">
            <div class="formalization__block--top-inner">
                @if (Route::currentRouteName() == 'canvas')
                    <h2 class="vz-art page-c-title sharj-popup--title">
                        {!! trans('canvas.form_title') !!}
                    </h2>
                @else
                    <h2 class="vz-art page-c-title sharj-popup--title">
                        {!! trans('canvas.form_title') !!}
                    </h2>
                @endif
                <p class="hidden">
                    {{ trans('canvas.form_seo_text') }}
                </p>

                <div class="canvas-formalization-row">
                    <section class="generate" id="generator">
                        <div class="generate-content">
                            <div class="gallery-photo">
                                <div class="photo-content">
                                    <div class="photo-filter">

                                        @include(env('THEME_RESOURCES') . 'pages.canvas.form.tab1')
                                        <style>
                                            .range__box {
                                                display: none;
                                            }
                                        </style>
                                        @include(env('THEME_RESOURCES') . 'pages.canvas.form.tab2')
                                    </div>
                                    <div class="slider-tabs">
                                        <ul class="tabs">
                                            <li>
                                                <a class="active" data-tabs="tabs-item1">
                                                    {{ trans('canvas.form_your_pic') }}</a>
                                            </li>
                                            <li>
                                                <a data-tabs="tabs-item2">
                                                    {{ trans('canvas.form_interiour') }}</a>
                                            </li>
                                        </ul>
                                        <div class="tabs-content">
                                            <div class="tabs-item tabs-item1 active" id="tab_screen1"
                                                 style="padding: 0">
                                                <div>
                                                    <div class="PhotoEditor" id="PhotoEditor">
                                                        <div class="canvas_container">
                                                            <canvas id="canvas"></canvas>
                                                        </div>
                                                        <div class="tools">
                                                            <div id="undo" class="tool icontool-undo"></div>
                                                            <div id="redo" class="tool icontool-redo"></div>
                                                            <div id="photo_zoom_minus"
                                                                 class="tool icontool-zoom_out"></div>
                                                            <div id="photo_zoom_plus"
                                                                 class="tool icontool-zoom_in"></div>
                                                            <div id="turn_right" class="tool icontool-turn_cw"></div>
                                                            <div id="turn_left" class="tool icontool-turn_ccw"></div>
                                                            <div id="cell_delete"
                                                                 class="tool icontool-delete_cell"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="interior tabs-item tabs-item2">
                                                <div id="interior_container" class="interior_container">
                                                    <div class="tools tools__desk">
                                                        <div id="interior_fullscreen"
                                                             class="js_top_fulll tool icontool-fullscreen"></div>
                                                        <div id="interior_zoom_minus"
                                                             class="js_top_zoom_min tool icontool-zoom_out"></div>
                                                        <div id="interior_zoom_plus1"
                                                             class="js_top_zoom_plus tool icontool-zoom_in"></div>
                                                    </div>
                                                    <canvas id="canvas_interior"></canvas>
                                                </div>
                                            </div>
                                            {{--<div class="tools tools__mob">
                                                <div id="interior_fullscreen1"
                                                     class="js_full tool icontool-fullscreen"></div>
                                                <div id="interior_zoom_minus1"
                                                     class="js_min tool icontool-zoom_out"></div>
                                                <div id="interior_zoom_plus"
                                                     class="js_plus tool icontool-zoom_in"></div>
                                            </div>
											--}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>

        </div>
        @include(env('THEME_RESOURCES') . 'pages.canvas.form.final')
    </form>
</div>
