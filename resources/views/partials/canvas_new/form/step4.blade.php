{{-- unused tab --}}
<div class="accordion-title h2_old hidden">
    <span>{{ trans('canvas.form_step4_title') }}</span>
    <span class="tab-icon"></span>
</div>
<div class="accordion-content">
    <div class="execution">
        <div data-id="pechat" class="
                                              execution-item
                                              js__calc_ex
                                              calcExecution
                                              active
                                            " data-execution="2">
            <div class="execution-wrapper">
                <div class="selected-icon">
                    <svg width="12" height="9" viewBox="0 0 12 9"
                         fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M4.90391 8.96884C4.643 8.96884 4.3911 8.87105 4.19842 8.69487L0.337368 5.16245C-0.086973 4.77427 -0.114713 4.11735 0.275141 3.69409C0.665745 3.27307 1.32475 3.2447 1.74984 3.63213L4.83643 6.45688L10.1827 0.582689C10.5695 0.157186 11.23 0.125087 11.6574 0.510279C12.084 0.895472 12.117 1.55239 11.7293 1.97789L5.67762 8.62844C5.49019 8.83298 5.23003 8.9554 4.95189 8.96884C4.93539 8.96884 4.91965 8.96884 4.90391 8.96884Z"
                            fill="white"></path>
                    </svg>
                </div>
                <div class="img">
                    <img
                        src="{{ asset('images/canvas/execution-item2.svg') }}"
                        alt=""/>
                </div>
            </div>
            <p>{!! trans('canvas.form_print') !!}}</p>
        </div>
        <div data-id="maslom"
             class="execution-item js__calc_ex calcExecution"
             data-execution="1">
            <div class="execution-wrapper">
                <div class="selected-icon">
                    <svg width="12" height="9" viewBox="0 0 12 9"
                         fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M4.90391 8.96884C4.643 8.96884 4.3911 8.87105 4.19842 8.69487L0.337368 5.16245C-0.086973 4.77427 -0.114713 4.11735 0.275141 3.69409C0.665745 3.27307 1.32475 3.2447 1.74984 3.63213L4.83643 6.45688L10.1827 0.582689C10.5695 0.157186 11.23 0.125087 11.6574 0.510279C12.084 0.895472 12.117 1.55239 11.7293 1.97789L5.67762 8.62844C5.49019 8.83298 5.23003 8.9554 4.95189 8.96884C4.93539 8.96884 4.91965 8.96884 4.90391 8.96884Z"
                            fill="white"></path>
                    </svg>
                </div>
                <div class="img">
                    <i class="icon-down-arrow"></i>
                    <img
                        src="{{ asset('images/canvas/execution-item1.svg') }}"
                        alt=""/>
                </div>
            </div>
            <p>{!! trans('canvas.form_print_oil') !!}}</p>
        </div>
    </div>
</div>
