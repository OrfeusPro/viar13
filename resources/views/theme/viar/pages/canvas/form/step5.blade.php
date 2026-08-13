{{-- unused tab --}}
<div class="accordion-title h2_old hidden">
    <span>{{ trans('canvas.form_step5_title') }}</span>
    <span class="tab-icon"></span>
</div>
<div class="accordion-content">
    <div class="kviz-row-single kviz-c-group">
        <div class="kviz-group__item">
            <div class="kviz-radio js-checkbox kviz-radio_active"
                 data-id="1" data-name="Эконом "
                 data-ratio="(плотность 280г/м2)" data-stock="1">
                <div class="check"></div>
                <label>
                                                <span>Эконом (плотность 280г/м2)
                                                  <img src="{{ asset('images/icon/info.svg') }}" alt=""></span>
                    <input name="canvas_type" type="radio" data-id="1"
                           data-text="Эконом " value="0.97"/>
                </label>
            </div>
            <div class="kviz-radio js-checkbox" data-id="2"
                 data-name="Интерьерный "
                 data-ratio="(плотность 500г/м2)" data-stock="2">
                <div class="check"></div>
                <label>
                                                <span>Интерьерный (плотность 500г/м2)
                                                  <img src="{{ asset('images/icon/info.svg') }}" alt="">
                                                </span>
                    <input name="canvas_type" type="radio" data-id="2"
                           data-text="Интерьерный " checked=""
                           value="0"/>
                </label>
            </div>
            <div class="kviz-radio js-checkbox" data-id="3"
                 data-name="Синтетический "
                 data-ratio="(плотность 230г/м2)" data-stock="1">
                <div class="check"></div>
                <label>
                                                                                            <span>Синтетический (плотность 230г/м2)<img src="{{ asset('images/icon/info.svg') }}"
                                                                                                                                        alt=""></span>
                    <input name="canvas_type" type="radio" data-id="3"
                           data-text="Синтетический " value="0.97"/>
                </label>
            </div>
            <div class="kviz-radio js-checkbox" data-id="4"
                 data-name="Хлопковый " data-ratio="(плотность 340г/м2)"
                 data-stock="2">
                <div class="check"></div>
                <label>
                                                                                            <span>Хлопковый (плотность 340г/м2)<img
                                                                                                    src="{{ asset('images/icon/info.svg') }}"
                                                                                                    alt=""></span>
                    <input name="canvas_type" type="radio" data-id="4"
                           data-text="Хлопковый " value="1.05"/>
                </label>
            </div>
            <div class="kviz-radio js-checkbox" data-id="5"
                 data-name="Глянцевый " data-ratio="(плотность 280г/м2)"
                 data-stock="2">
                <div class="check"></div>
                <label>
                                                                                            <span>Глянцевый (плотность 280г/м2)<img
                                                                                                    src="{{ asset('images/icon/info.svg') }}"
                                                                                                    alt=""></span>
                    <input name="canvas_type" type="radio" data-id="5"
                           data-text="Глянцевый " value="1.05"/>
                </label>
            </div>
        </div>
    </div>
</div>
