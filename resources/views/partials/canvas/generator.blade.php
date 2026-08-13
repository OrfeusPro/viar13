<section class="generate" id="generator">
    <div class="generate-content">
        <div class="title">
            <p>ViarStudia</p>
            <h4>{{ $canvas_head['calc_sub_title'] }}</h4>
        </div>
        <div class="gallery-photo">
            <div class="photo-content">
                <div class="slider-tabs">
                    <ul class="tabs">
                        <li><a class="active" data-tabs="tabs-item1" href="javascript:void(0)">
                                {{ $canvas_head['calc_tab_1_main_title'] }}</a>
                        </li>
                        <li><a data-tabs="tabs-item2" href="javascript:void(0)">
                                {{ $canvas_head['calc_tab_2_main_title'] }}</a></li>
                    </ul>
                    <div class="tabs-content">
                        <div class="tabs-item tabs-item1 active" id="tab_screen1" style="padding:0;">
                            <div>
                                <div class="PhotoEditor" id="PhotoEditor">
                                    <div class="canvas_container">
                                        <canvas id='canvas'> </canvas>
                                        <h4 class="sum__mod"><span id="glob_summ2">0</span> €</h4>
                                    </div>

                                    <div class="tools">
                                        <div id="undo" class="tool icontool-undo"></div>
                                        <div id="redo" class="tool icontool-redo"></div>
                                        <div id="photo_zoom_minus" class="tool icontool-zoom_out"> </div>
                                        <div id="photo_zoom_plus" class="tool icontool-zoom_in"> </div>
                                        <div id="turn_right" class="tool icontool-turn_cw"></div>
                                        <div id="turn_left" class="tool icontool-turn_ccw"></div>
                                        <div id="cell_delete" class="tool icontool-delete_cell"></div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        {{-- <h4 class="sum__mod"><span id="glob_summ2">0</span> €</h4> --}}
                        {{-- <div class="interior tabs-item tabs-item2">
                            <img src="" alt="">
                        </div> --}}
                        @include('partials.module_pics.tab2')
                    </div>
                </div>
                <div class="photo-filter">
                    @include('partials.canvas.tab1')
                    {{-- @include('partials.canvas.tab2') --}}
                    @include('partials.module_pics.tab2_form')
                </div>
            </div>
        </div>
    </div>
</section>