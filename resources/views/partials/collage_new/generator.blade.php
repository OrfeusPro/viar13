<!-- ellipse -->

<div class="ellipse ellipse_black ellipse_top">
    <img src="https://viarcanvas.com/theme/viar/images/icon/ellipse-black.svg" alt="img" loading="lazy">
</div>


<div class="c-g-bottom" id="collage-generator">
    <div class="section-frame">

        <div class="collage__formalization" id="generate">
            <form action="#">
                <div class="formalization__main--wrapper">
                    <div class="formalization__block--top">
                        <div class="formalization__block--top-inner">
                            <div class="collage-gen--title">
                                <div class="vz-art page-title h2_old">
                                    {!! trans('collage_new.z7_generator_title') !!}
                                </div>
                                <div class="col-gen--element">
                                    <img loading="lazy" width="165" height="92" src="{{ asset('images/collage/clg.svg') }}" alt="">
                                    <p>{!! trans('collage_new.z7_generator_subtitle') !!}</p>
                                </div>
                            </div>
                            <div class="collage-formalization-row">
                                <section class="generate" id="generator">
                                    <div class="generate-content">
                                        <div class="gallery-photo">
                                            <div class="collage-tabs slider-tabs">
                                                <ul class="tabs">
                                                    <li>
                                                        <a class="active" data-tabs="tabs-item1">
                                                            {!! trans('collage_new.z7_generator_tab1') !!}
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a data-tabs="tabs-item2">
                                                            {!! trans('collage_new.z7_generator_tab2') !!}
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="photo-content">
                                                <div class="collage-settings">
                                                    <div class="filter-picture tabs-item tabs-item1 active">
                                                        <div class="cs-tabs-column--wrap">
                                                            <div class="cs-tabs-column">
                                                                <ul>
                                                                    <li class="active">
                                                                        <img loading="lazy" width="30" height="25" src="{{ asset('images/collage/ctc1.svg') }}" alt="">
                                                                        <p>{!! trans('collage_new.z7_generator_ctc1') !!}</p>
                                                                    </li>
                                                                    <li>
                                                                        <img loading="lazy" width="30" height="25" src="{{ asset('images/collage/ctc2.svg') }}" alt="">
                                                                        <p>{!! trans('collage_new.z7_generator_ctc2') !!}</p>
                                                                    </li>
                                                                    <li>
                                                                        <img loading="lazy" width="30" height="25" src="{{ asset('images/collage/ctc4.svg') }}" alt="">
                                                                        <p>{!! trans('collage_new.z7_generator_ctc3') !!}</p>
                                                                    </li>
                                                                    <li>
                                                                        <img loading="lazy" width="30" height="25" src="{{ asset('images/collage/ctc3.svg') }}" alt="">
                                                                        <p>{!! trans('collage_new.z7_generator_ctc4') !!}</p>
                                                                    </li>
                                                                    <li>
                                                                        <img loading="lazy" width="30" height="25" src="{{ asset('images/collage/ctc5.svg') }}" alt="">
                                                                        <p>{!! trans('collage_new.z7_generator_ctc5') !!}</p>
                                                                    </li>
                                                                    <li>
                                                                        <img loading="lazy" width="30" height="25" src="{{ asset('images/collage/ctc6.svg') }}" alt="">
                                                                        <p>{!! trans('collage_new.z7_generator_ctc6') !!}</p>
                                                                    </li>
                                                                    <li>
                                                                        <img loading="lazy" width="30" height="25" src="{{ asset('images/collage/ctc7.svg') }}" alt="">
                                                                        <p>{!! trans('collage_new.z7_generator_ctc7') !!}</p>
                                                                    </li>
                                                                </ul>
                                                            </div>

                                                        </div>
                                                        <div class="cs-controls cs-ds-controls">
                                                            <div class="csc-item" id="delete_background" data-tooltip="{!! trans('collage_new.z7_generator_controls1') !!}">
                                                                <img loading="lazy" width="30" height="30" src="{{ asset('images/collage/tool1.svg') }}" alt="">
                                                            </div>
                                                            <div class="csc-item" id="clear" data-tooltip="{!! trans('collage_new.z7_generator_controls2') !!}">
                                                                <img loading="lazy" width="30" height="30" src="{{ asset('images/collage/tool2.svg') }}" alt="">
                                                            </div>
                                                            <div class="csc-item" id="cell_delete" data-tooltip="{!! trans('collage_new.z7_generator_controls3') !!}">
                                                                <img loading="lazy" width="30" height="30" src="{{ asset('images/collage/tool3.svg') }}" alt="">
                                                            </div>
                                                            <div class="csc-item" id="img_delete" data-tooltip="{!! trans('collage_new.z7_generator_controls4') !!}">
                                                                <img loading="lazy" width="30" height="30" src="{{ asset('images/collage/tool4.svg') }}" alt="">
                                                            </div>
                                                            <div class="csc-item" id="redo" data-tooltip="{!! trans('collage_new.z7_generator_controls5') !!}">
                                                                <img loading="lazy" width="30" height="30" src="{{ asset('images/collage/tool5.svg') }}" alt="">
                                                            </div>
                                                            <div class="csc-item" id="undo" data-tooltip="{!! trans('collage_new.z7_generator_controls6') !!}">
                                                                <img loading="lazy" width="30" height="30" src="{{ asset('images/collage/tool6.svg') }}" alt="">
                                                            </div>
                                                            <div class="csc-item" id="photo_zoom_plus" data-tooltip="{!! trans('collage_new.z7_generator_controls7') !!}">
                                                                <img loading="lazy" width="30" height="30" src="{{ asset('images/collage/tool7.svg') }}" alt="">
                                                            </div>
                                                            <div class="csc-item" id="photo_zoom_minus" data-tooltip="{!! trans('collage_new.z7_generator_controls8') !!}">
                                                                <img loading="lazy" width="30" height="30" src="{{ asset('images/collage/tool8.svg') }}" alt="">
                                                            </div>
                                                            <div class="csc-item" id="turn_right" data-tooltip="{!! trans('collage_new.z7_generator_controls9') !!}">
                                                                <img loading="lazy" width="30" height="30" src="{{ asset('images/collage/tool9.svg') }}" alt="">
                                                            </div>
                                                            <div class="csc-item" id="turn_left" data-tooltip="{!! trans('collage_new.z7_generator_controls10') !!}">
                                                                <img loading="lazy" width="30" height="30" src="{{ asset('images/collage/tool10.svg') }}" alt="">
                                                            </div>
                                                            <div class="csc-item" id="loadPhotos_4" data-tooltip="{!! trans('collage_new.z7_generator_controls11') !!}">
                                                                <img loading="lazy" width="30" height="30" src="{{ asset('images/collage/tool11.svg') }}" alt="">
                                                            </div>
                                                        </div>
                                                        <div class="cs-wrapper">
                                                            <div class="cs-wrapper--inner">
                                                                <div class="cs-s-item cs-wrapper--form">

                                                                </div>
                                                                <div class="cs-s-item hidden-block">
                                                                    <p class="cs-title cs-ph-title">
                                                                        {!! trans('collage_new.z7_generator_foto_text1') !!}
                                                                    </p>
                                                                    <div class="cs-ph-container">
                                                                        <div class="product-download pd-family" id="imgs2">
                                                                            <div class="full-cs-title">
                                                                                {!! trans('collage_new.z7_generator_foto_text2') !!}
                                                                            </div>
                                                                            <div class="cs-btn-scroll heartbeat">
                                                                                <svg width="11" height="7" viewBox="0 0 11 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                    <path d="M0.676759 0.142211C0.75 0.0689688 0.834228 0.0323475 0.929443 0.0323475C1.02466 0.0323475 1.10889 0.0689688 1.18213 0.142211L5.49976 4.45984L9.81738 0.142211C9.89063 0.0689688 9.97485 0.0323475 10.0701 0.0323475C10.1653 0.0323475 10.2495 0.0689688 10.3228 0.142211L10.8721 0.691528C10.9453 0.76477 10.9819 0.848998 10.9819 0.944213C10.9819 1.03943 10.9453 1.12366 10.8721 1.1969L5.75244 6.31653C5.6792 6.38977 5.59497 6.42639 5.49976 6.42639C5.40454 6.42639 5.32031 6.38977 5.24707 6.31653L0.127441 1.1969C0.0541989 1.12366 0.0175781 1.03943 0.0175781 0.944213C0.0175781 0.848998 0.0541989 0.76477 0.127441 0.691528L0.676759 0.142211Z" fill="white" />
                                                                                </svg>
                                                                            </div>
                                                                            <div class="cs-zone-wrapper">
                                                                                <div class="js_zone">
                                                                                    <div class="img img__place">
                                                                                        <div class="file-save__item js-file-preview">
                                                                                            <svg>
                                                                                                <use xlink:href="{{ asset(env('THEME').'sprite.svg#save') }}"></use>
                                                                                            </svg>
                                                                                            <div class="file-save__title">
                                                                                                <p>{!! trans('collage_new.z7_generator_foto_text3') !!}</p>
                                                                                                <span>{!! trans('collage_new.z7_generator_foto_text4') !!}</span>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>

                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="cs-p-info">
                                                                        <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                            <path d="M19.1992 0.0780506C19.2725 0.117113 19.3555 0.190355 19.3848 0.244066C19.4141 0.297777 19.4336 0.761644 19.4336 1.39153V2.4511L19.292 2.5927C19.0527 2.83196 18.6768 2.7636 18.5303 2.45598C18.4619 2.31926 18.4521 2.12883 18.4668 1.28899L18.4814 0.283129L18.6426 0.141527C18.8184 -0.0147228 18.9941 -0.0391369 19.1992 0.0780506Z" fill="#FA7846" />
                                                                            <path d="M23.1055 1.68945C23.1788 1.72852 23.2764 1.82617 23.3204 1.90918C23.4571 2.18262 23.3838 2.30957 22.6612 3.04199C21.9727 3.74023 21.8262 3.85742 21.6016 3.85742C21.4307 3.85742 21.1426 3.56934 21.1426 3.39844C21.1426 3.17871 21.2598 3.02734 21.9336 2.3584C22.6905 1.61133 22.8223 1.5332 23.1055 1.68945Z" fill="#FA7846" />
                                                                            <path d="M15.21 1.65529C15.2637 1.6797 15.6299 2.02638 16.0254 2.42677C16.6992 3.10548 16.748 3.16896 16.748 3.33986C16.748 3.57423 16.6846 3.69142 16.4941 3.78908C16.2061 3.93556 16.0986 3.87209 15.293 3.06154C14.6094 2.36818 14.5508 2.29982 14.5508 2.13381C14.5508 1.80666 14.9316 1.53322 15.21 1.65529Z" fill="#FA7846" />
                                                                            <path d="M24.7705 5.6201C24.8779 5.68358 25 5.91795 25 6.0742C25 6.14256 24.9365 6.26951 24.8584 6.3574L24.7168 6.51854L23.7109 6.53319C22.8711 6.54783 22.6807 6.53807 22.5439 6.46971C22.2363 6.32322 22.168 5.94725 22.4072 5.70799L22.5488 5.56639H23.6182C24.248 5.56639 24.7168 5.5908 24.7705 5.6201Z" fill="#FA7846" />
                                                                            <path d="M19.292 5.70799C19.3896 5.80565 19.4336 5.89842 19.4336 6.00584C19.4336 6.09373 18.1348 10.3418 16.5479 15.4443C13.8818 24.0283 13.6523 24.7412 13.5156 24.873C13.3887 24.9902 13.335 25.0049 13.1738 24.9853C13.0713 24.9707 12.9492 24.9316 12.9053 24.8926C12.8613 24.8584 12.2559 23.6865 11.5625 22.29C10.8643 20.8935 10.2881 19.7412 10.2734 19.7314C10.2588 19.7168 9.08203 20.874 7.65137 22.2998C6.2207 23.7304 5 24.917 4.92676 24.9463C4.8584 24.9707 4.75098 24.9804 4.68262 24.9658C4.52149 24.9365 0.0732441 20.4883 0.0341816 20.3174C0.0195332 20.2539 0.0292988 20.1465 0.0537128 20.0732C0.0830097 20.0049 1.26953 18.7793 2.7002 17.3486C4.12598 15.918 5.2832 14.7412 5.27344 14.7265C5.25879 14.7168 4.10645 14.1357 2.70996 13.4375C1.31348 12.7392 0.136721 12.1338 0.102541 12.0947C0.0683613 12.0508 0.0292988 11.9287 0.0146503 11.8262C-0.00488091 11.665 0.00976753 11.6113 0.126955 11.4844C0.258791 11.3476 0.576174 11.2353 3.8086 10.2344C9.12109 8.58885 8.63281 8.73045 8.82324 8.79393C8.91113 8.82323 9.01856 8.89158 9.05762 8.95018C9.16504 9.10155 9.15039 9.38475 9.02832 9.541C8.91602 9.6826 9.05762 9.63377 4.64356 11.0058C3.12988 11.4795 1.87988 11.875 1.86524 11.8847C1.85547 11.8994 2.89063 12.4316 4.16992 13.0713C6.29883 14.1357 6.49902 14.2431 6.57227 14.3945C6.61621 14.4922 6.63574 14.6094 6.61621 14.6875C6.59668 14.7754 5.6543 15.7519 3.8916 17.5195L1.19629 20.2148L2.99317 22.0068L4.78516 23.8037L7.48047 21.1084C9.24805 19.3457 10.2246 18.4033 10.3125 18.3838C10.3906 18.3642 10.5078 18.3838 10.6055 18.4277C10.7617 18.501 10.8643 18.6865 11.9336 20.8349C12.5684 22.1094 13.1006 23.1445 13.1152 23.1347C13.1396 23.1055 18.208 6.81639 18.1982 6.80174C18.1934 6.79686 16.9238 7.18748 15.3809 7.67088C13.8379 8.1494 12.4902 8.5449 12.3877 8.5449C11.9482 8.5449 11.7578 7.96873 12.1143 7.70506C12.2607 7.59276 18.7842 5.56639 18.9941 5.56639C19.1016 5.56639 19.1943 5.61033 19.292 5.70799Z" fill="#FA7846" />
                                                                            <path d="M10.6933 8.18848C10.9668 8.2959 11.0986 8.62305 10.9668 8.87695C10.8691 9.0625 10.7519 9.13086 10.5224 9.13086C10.2783 9.13086 10.0586 8.91602 10.0586 8.67188C10.0586 8.36914 10.4297 8.08594 10.6933 8.18848Z" fill="#FA7846" />
                                                                            <path d="M22.6025 9.00879C23.3398 9.73633 23.3643 9.76562 23.3643 9.96582C23.3643 10.2295 23.1396 10.4492 22.8662 10.4492C22.7002 10.4492 22.6318 10.3906 21.9385 9.70703C21.1279 8.90137 21.0644 8.79395 21.2109 8.50586C21.3086 8.31543 21.4258 8.25195 21.6602 8.25195C21.8311 8.25195 21.8945 8.30078 22.6025 9.00879Z" fill="#FA7846" />
                                                                        </svg>
                                                                        <p>
                                                                            <span>*</span>{!! trans('collage_new.z7_generator_foto_text5') !!}
                                                                        </p>
                                                                    </div>

                                                                    <div class="additional-image">
                                                                        <div class="additional-row">
                                                                            <div class="additional-img">
                                                                                <img loading="lazy" src="{{ asset('images/canvas/additional.svg') }}" alt="">
                                                                            </div>
                                                                            <div class="additional-input">
                                                                                <div class="vz-art popup-log-check js-checkbox">
                                                                                    <span class="vz-art log-check">
                                                                                        <svg class="vz-art kviz-input__icon">
                                                                                            <use xlink:href="{{ asset(env('THEME').'sprite.svg#log') }}"></use>
                                                                                        </svg>
                                                                                    </span>
                                                                                    <p>{!! trans('collage_new.z7_generator_foto_text6') !!}</p>
                                                                                    <input type="checkbox" name="save" />
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="additional-hidden">
                                                                            <div class="kviz-wrap">
                                                                                <p>{!! trans('collage_new.z7_generator_foto_text7') !!}</p>
                                                                                <div class="kviz-c-row kviz-c-group">
                                                                                    <div class="kviz-radio js-checkbox kviz-radio_active" data-stock="1">
                                                                                        <div class="check check-border"></div>
                                                                                        <label>
                                                                                            <span>{!! trans('collage_new.z7_generator_foto_text8') !!}
                                                                                                <img loading="lazy" src="{{ asset('images/icon/info.svg') }}" alt="">
                                                                                            </span>
                                                                                            <input name="boxes2[]" type="radio" data-id="2" checked data-name="{!! trans('collage_new.z7_generator_foto_text8') !!}" value="0">
                                                                                            <div class="window-prompt">
                                                                                                {!! trans('collage_new.z7_generator_foto_text8') !!}
                                                                                            </div>
                                                                                        </label>
                                                                                    </div>
                                                                                    <div class="kviz-radio js-checkbox" data-stock="2">
                                                                                        <div class="check check-border"></div>
                                                                                        <label>
                                                                                            <span>{!! trans('collage_new.z7_generator_foto_text9') !!}<img loading="lazy" src="{{ asset('images/icon/info.svg') }}" alt="">
                                                                                            </span>
                                                                                            <input name="boxes2[]" type="radio" data-id="1" data-name="{!! trans('collage_new.z7_generator_foto_text9') !!}" value="3">
                                                                                            <div class="window-prompt">
                                                                                                {!! trans('collage_new.z7_generator_foto_text9') !!}
                                                                                            </div>
                                                                                        </label>
                                                                                    </div>
                                                                                    <div class="kviz-radio js-checkbox" data-stock="1">
                                                                                        <div class="check check-border"></div>
                                                                                        <label class="jcf-label-active">
                                                                                            <span>{!! trans('collage_new.z7_generator_foto_text10') !!}<img loading="lazy" src="{{ asset('images/icon/info.svg') }}" alt=""></span>
                                                                                            <input name="boxes2[]" type="radio" data-id="3" data-name="{!! trans('collage_new.z7_generator_foto_text10') !!}" value="4">
                                                                                            <div class="window-prompt">
                                                                                                {!! trans('collage_new.z7_generator_foto_text10') !!}
                                                                                            </div>
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="cs-s-item cs-s-sizes hidden-block">
                                                                    <p class="cs-title cs-sizes--title">
                                                                        {!! trans('collage_new.z7_generator_foto_price_text') !!}
                                                                    </p>
                                                                    <div class="cs-size size" id="sizes">

                                                                    </div>
                                                                </div>
                                                                <div class="cs-s-item hidden-block">
                                                                    <p class="cs-title">
                                                                        {!! trans('collage_new.z7_generator_foto_fon_text1') !!}
                                                                    </p>
                                                                    <div class="product-download pd-family" id="backgrounds">
                                                                        <div class="file-save__item cs-bg-add">
                                                                            <input type="file" class="cs-bg-input">
                                                                            <svg>
                                                                                <use xlink:href="{{ asset(env('THEME').'sprite.svg#save') }}"></use>
                                                                            </svg>
                                                                            <div class="file-save__title">
                                                                                <p>{!! trans('collage_new.z7_generator_foto_fon_text2') !!}</p>
                                                                                <span>{!! trans('collage_new.z7_generator_foto_fon_text3') !!}</span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="cs-bg-container">

                                                                        </div>
                                                                    </div>
                                                                    <div class="cs-bg-colors">
                                                                        <div class="cs-bg-title cs-title">
                                                                            {!! trans('collage_new.z7_generator_foto_fon_text4') !!}
                                                                        </div>
                                                                        <div class="cs-bg-group">
                                                                            @if($ACollageGeneratorColor)
                                                                            @foreach($ACollageGeneratorColor as $color)
                                                                            <div class="color-item" data-color="{{$color->color}}"></div>
                                                                            @endforeach
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                    <div class="cs-bg-own_color">
                                                                        <div class="cs-bg-title cs-title">
                                                                            {!! trans('collage_new.z7_generator_foto_fon_text5') !!}
                                                                        </div>
                                                                        <div class="color-item_own">
                                                                            <input type="color" value="#4ee7d8" id="bg-color">
                                                                        </div>
                                                                    </div>
                                                                    <div class="cs-bg-images">
                                                                        <div class="cs-bg-title cs-title">
                                                                            {!! trans('collage_new.z7_generator_foto_fon_text6') !!}
                                                                        </div>
                                                                        <div class="cs-bg-img_list">

                                                                            @if($ACollageFon)
                                                                            @foreach($ACollageFon as $fon)
                                                                            <div class="cs-bg-img_item">
                                                                                <img loading="lazy" width="109" height="109" src="{{  asset('storage/'.$fon->image)  }}" alt="">
                                                                            </div>
                                                                            @endforeach
                                                                            @endif

                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="cs-s-item hidden-block">
                                                                    <p class="cs-title">
                                                                        {!! trans('collage_new.z7_generator_foto_block_text1') !!}
                                                                    </p>
                                                                    <button type="button" class="user-txt-btn" id="add_text">{!! trans('collage_new.z7_generator_foto_block_add') !!}</button>
                                                                    <div class="text-settings">
                                                                        <div class="cs-txt-font">
                                                                            <p class="cs-title">
                                                                                {!! trans('collage_new.z7_generator_foto_block_font') !!}
                                                                            </p>
                                                                            <div class="font" data-tooltip="Шрифт текста">
                                                                                <select id="cur_text_font">

                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="cs-txt-colors">
                                                                        <div class="cs-txt-title cs-title">
                                                                            {!! trans('collage_new.z7_generator_foto_block_text2') !!}
                                                                        </div>
                                                                        <div class="cs-txt-group">
                                                                            @if($ACollageGeneratorColor)
                                                                            @foreach($ACollageGeneratorColor as $color)
                                                                            <div class="color-item-txt" data-color="{{$color->color}}"></div>
                                                                            @endforeach
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                    <div class="cs-txt-own_color">
                                                                        <div class="cs-txt-title cs-title">
                                                                            {!! trans('collage_new.z7_generator_foto_block_text3') !!}
                                                                        </div>
                                                                        <div class="color-item_own-text">
                                                                            <input type="color" value="#333333" id="cur_text_color">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="cs-s-item hidden-block">
                                                                    <p class="cs-title">
                                                                        {!! trans('collage_new.z7_generator_stiker_text1') !!}
                                                                    </p>
                                                                    <div class="cs-smiles_inner">
                                                                        <div class="cs-smiles_tabs">
                                                                            @if($ACollageStickerGroup)
                                                                            @foreach($ACollageStickerGroup as $sticker_group)
                                                                            <div class="cs-smiles_tab @if($loop->iteration === 1) active @endif">
                                                                                {{$sticker_group->title}}
                                                                            </div>
                                                                            @endforeach
                                                                            @endif

                                                                        </div>
                                                                        <div class="cs-smiles_column" id="smiles">
                                                                            @if($ACollageStickerGroup)
                                                                            @foreach($ACollageStickerGroup as $sticker_group)
                                                                            <div class="cs-smiles_block @if($loop->iteration === 1) active @else hidden-block @endif">
                                                                                @foreach($ACollageStiker as $sticker)
                                                                                @if($sticker->stiker_group_id == $sticker_group->id)
                                                                                <div class="cs-smiles_item">
                                                                                    <img loading="lazy" width="70" height="70" src="{{  asset('storage/'.$sticker->image) }}" alt="">
                                                                                </div>
                                                                                @endif
                                                                                @endforeach
                                                                            </div>
                                                                            @endforeach
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="cs-s-item hidden-block">
                                                                    <p class="cs-title">
                                                                        {!! trans('collage_new.z7_generator_stiker_text2') !!}
                                                                    </p>
                                                                    <div class="textarea-wrapper cs-txt-wrapper">
                                                                        <textarea placeholder="{!! trans('collage_new.z7_generator_stiker_text3') !!}" id="userComment" name="user_comment"></textarea>
                                                                    </div>
                                                                    {{--<button class="user-com-btn">{!! trans('collage_new.z7_generator_stiker_btn') !!}</button>--}}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="wrap-btn">
                                                            <img loading="lazy" src="{{ asset('images/collage/angle-close.svg') }}" alt="">
                                                        </div>
                                                        <div class="cs-btn-scroll" title="Scroll down">
                                                            <svg width="11" height="7" viewBox="0 0 11 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path d="M0.676759 0.142211C0.75 0.0689688 0.834228 0.0323475 0.929443 0.0323475C1.02466 0.0323475 1.10889 0.0689688 1.18213 0.142211L5.49976 4.45984L9.81738 0.142211C9.89063 0.0689688 9.97485 0.0323475 10.0701 0.0323475C10.1653 0.0323475 10.2495 0.0689688 10.3228 0.142211L10.8721 0.691528C10.9453 0.76477 10.9819 0.848998 10.9819 0.944213C10.9819 1.03943 10.9453 1.12366 10.8721 1.1969L5.75244 6.31653C5.6792 6.38977 5.59497 6.42639 5.49976 6.42639C5.40454 6.42639 5.32031 6.38977 5.24707 6.31653L0.127441 1.1969C0.0541989 1.12366 0.0175781 1.03943 0.0175781 0.944213C0.0175781 0.848998 0.0541989 0.76477 0.127441 0.691528L0.676759 0.142211Z" fill="white" />
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    <style>
                                                        .range__box {
                                                            display: none;
                                                        }
                                                    </style>
                                                    <div class="filter-interior tabs-item tabs-item2" style="width: 100%;">
                                                        <div class="filter-accordion">
                                                            <div class="accordion-title h2_old open">
                                                                <span>1. {!! trans('collage_new.z7_generator_interer_text1') !!}</span>
                                                                <span class="tab-icon"></span>
                                                            </div>
                                                            <div class="accordion-content">
                                                                <div class="interior-slider">
                                                                    <div>
                                                                        <div data-item="0" class="interior-item active" data-size="250,300" data-interior="{{ asset('images/canvas/interio1.jpg') }}">
                                                                            <div class="selected-icon">
                                                                                <svg width="12" height="9" viewBox="0 0 12 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                    <path d="M4.90391 8.96884C4.643 8.96884 4.3911 8.87105 4.19842 8.69487L0.337368 5.16245C-0.086973 4.77427 -0.114713 4.11735 0.275141 3.69409C0.665745 3.27307 1.32475 3.2447 1.74984 3.63213L4.83643 6.45688L10.1827 0.582689C10.5695 0.157186 11.23 0.125087 11.6574 0.510279C12.084 0.895472 12.117 1.55239 11.7293 1.97789L5.67762 8.62844C5.49019 8.83298 5.23003 8.9554 4.95189 8.96884C4.93539 8.96884 4.91965 8.96884 4.90391 8.96884Z" fill="white"></path>
                                                                                </svg>
                                                                            </div>
                                                                            <div class="interio-img">
                                                                                <picture>
                                                                                    <source media="(max-width: 550px)" srcset="{{ asset('images/canvas/interio1Min.webp') }}" type="image/webp">
                                                                                    <source media="(max-width: 550px)" srcset="{{ asset('images/canvas/interio1Min.jpg') }}" type="image/jpeg">
                                                                                    <source srcset="{{ asset('images/canvas/interio1.webp') }}" type="image/webp">
                                                                                    <source srcset="{{ asset('images/canvas/interio1.jpg') }}" type="image/jpeg">
                                                                                    <img loading="lazy" src="{{ asset('images/canvas/interio1.jpg') }}" alt="" />
                                                                                </picture>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div>
                                                                        <div data-item="1" class="interior-item" data-size="300,400" data-interior="{{ asset('images/canvas/interio2.jpg') }}">

                                                                            <div class="selected-icon">
                                                                                <svg width="12" height="9" viewBox="0 0 12 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                    <path d="M4.90391 8.96884C4.643 8.96884 4.3911 8.87105 4.19842 8.69487L0.337368 5.16245C-0.086973 4.77427 -0.114713 4.11735 0.275141 3.69409C0.665745 3.27307 1.32475 3.2447 1.74984 3.63213L4.83643 6.45688L10.1827 0.582689C10.5695 0.157186 11.23 0.125087 11.6574 0.510279C12.084 0.895472 12.117 1.55239 11.7293 1.97789L5.67762 8.62844C5.49019 8.83298 5.23003 8.9554 4.95189 8.96884C4.93539 8.96884 4.91965 8.96884 4.90391 8.96884Z" fill="white"></path>
                                                                                </svg>
                                                                            </div>
                                                                            <div class="interio-img">
                                                                                <picture>
                                                                                    <source media="(max-width: 550px)" srcset="{{ asset('images/canvas/interio2Min.webp') }}" type="image/webp">
                                                                                    <source media="(max-width: 550px)" srcset="{{ asset('images/canvas/interio2Min.jpg') }}" type="image/jpeg">
                                                                                    <source srcset="{{ asset('images/canvas/interio2.webp') }}" type="image/webp">
                                                                                    <source srcset="{{ asset('images/canvas/interio2.jpg') }}" type="image/jpeg">
                                                                                    <img loading="lazy" src="{{ asset('images/canvas/interio2.jpg') }}" alt="" />
                                                                                </picture>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div>
                                                                        <div data-item="2" class="interior-item" data-size="350,400" data-interior="{{ asset('images/canvas/interio3.jpg') }}">

                                                                            <div class="selected-icon">
                                                                                <svg width="12" height="9" viewBox="0 0 12 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                    <path d="M4.90391 8.96884C4.643 8.96884 4.3911 8.87105 4.19842 8.69487L0.337368 5.16245C-0.086973 4.77427 -0.114713 4.11735 0.275141 3.69409C0.665745 3.27307 1.32475 3.2447 1.74984 3.63213L4.83643 6.45688L10.1827 0.582689C10.5695 0.157186 11.23 0.125087 11.6574 0.510279C12.084 0.895472 12.117 1.55239 11.7293 1.97789L5.67762 8.62844C5.49019 8.83298 5.23003 8.9554 4.95189 8.96884C4.93539 8.96884 4.91965 8.96884 4.90391 8.96884Z" fill="white"></path>
                                                                                </svg>
                                                                            </div>
                                                                            <div class="interio-img">
                                                                                <picture>
                                                                                    <source media="(max-width: 550px)" srcset="{{ asset('images/canvas/interio3Min.webp') }}" type="image/webp">
                                                                                    <source media="(max-width: 550px)" srcset="{{ asset('images/canvas/interio3Min.jpg') }}" type="image/jpeg">
                                                                                    <source srcset="{{ asset('images/canvas/interio3.webp') }}" type="image/webp">
                                                                                    <source srcset="{{ asset('images/canvas/interio3.jpg') }}" type="image/jpeg">
                                                                                    <img loading="lazy" src="{{ asset('images/canvas/interio3.jpg') }}" alt="" />
                                                                                </picture>
                                                                            </div>

                                                                        </div>
                                                                    </div>
                                                                    <div>
                                                                        <div data-item="3" class="interior-item" data-size="250,300" data-interior="{{ asset('images/canvas/interio4.jpg') }}">

                                                                            <div class="selected-icon">
                                                                                <svg width="12" height="9" viewBox="0 0 12 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                    <path d="M4.90391 8.96884C4.643 8.96884 4.3911 8.87105 4.19842 8.69487L0.337368 5.16245C-0.086973 4.77427 -0.114713 4.11735 0.275141 3.69409C0.665745 3.27307 1.32475 3.2447 1.74984 3.63213L4.83643 6.45688L10.1827 0.582689C10.5695 0.157186 11.23 0.125087 11.6574 0.510279C12.084 0.895472 12.117 1.55239 11.7293 1.97789L5.67762 8.62844C5.49019 8.83298 5.23003 8.9554 4.95189 8.96884C4.93539 8.96884 4.91965 8.96884 4.90391 8.96884Z" fill="white"></path>
                                                                                </svg>
                                                                            </div>
                                                                            <div class="interio-img">
                                                                                <picture>
                                                                                    <source media="(max-width: 550px)" srcset="{{ asset('images/canvas/interio4Min.webp') }}" type="image/webp">
                                                                                    <source media="(max-width: 550px)" srcset="{{ asset('images/canvas/interio4Min.jpg') }}" type="image/jpeg">
                                                                                    <source srcset="{{ asset('images/canvas/interio4.webp') }}" type="image/webp">
                                                                                    <source srcset="{{ asset('images/canvas/interio4.jpg') }}" type="image/jpeg">
                                                                                    <img loading="lazy" src="{{ asset('images/canvas/interio4.jpg') }}" alt="" />
                                                                                </picture>
                                                                            </div>

                                                                        </div>
                                                                    </div>
                                                                    <div>
                                                                        <div data-item="4" class="interior-item" data-size="350,400" data-interior="{{ asset('images/canvas/interio5.jpg') }}">

                                                                            <div class="selected-icon">
                                                                                <svg width="12" height="9" viewBox="0 0 12 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                    <path d="M4.90391 8.96884C4.643 8.96884 4.3911 8.87105 4.19842 8.69487L0.337368 5.16245C-0.086973 4.77427 -0.114713 4.11735 0.275141 3.69409C0.665745 3.27307 1.32475 3.2447 1.74984 3.63213L4.83643 6.45688L10.1827 0.582689C10.5695 0.157186 11.23 0.125087 11.6574 0.510279C12.084 0.895472 12.117 1.55239 11.7293 1.97789L5.67762 8.62844C5.49019 8.83298 5.23003 8.9554 4.95189 8.96884C4.93539 8.96884 4.91965 8.96884 4.90391 8.96884Z" fill="white"></path>
                                                                                </svg>
                                                                            </div>
                                                                            <div class="interio-img">
                                                                                <picture>
                                                                                    <source media="(max-width: 550px)" srcset="{{ asset('images/canvas/interio5Min.webp') }}" type="image/webp">
                                                                                    <source media="(max-width: 550px)" srcset="{{ asset('images/canvas/interio5Min.jpg') }}" type="image/jpeg">
                                                                                    <source srcset="{{ asset('images/canvas/interio5.webp') }}" type="image/webp">
                                                                                    <source srcset="{{ asset('images/canvas/interio5.jpg') }}" type="image/jpeg">
                                                                                    <img loading="lazy" src="{{ asset('images/canvas/interio5.jpg') }}" alt="" />
                                                                                </picture>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="interior-sizes">
                                                                    <div class="size-item">
                                                                        <span>{!! trans('collage_new.z7_generator_interer_text2') !!}</span>
                                                                        <div class="range__box" style="display: block">
                                                                            <input class="main__input--wall" type="number" placeholder="250 {!! trans('collage_new.sm') !!}" min="250" max="300" name="wall_size" />
                                                                            <div class="range_box">
                                                                                <input type="range" class="zoomRange range-input" min="250" max="300" step="1" />
                                                                                <div class="changeSm">
                                                                                    <div>
                                                                                        <span class="js_s1">250</span> {!! trans('collage_new.sm') !!}
                                                                                    </div>
                                                                                    <div>
                                                                                        <span class="js_s2">300</span> {!! trans('collage_new.sm') !!}
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="range__box">
                                                                            <input class="main__input--wall" type="number" placeholder="300 {!! trans('collage_new.sm') !!}" min="300" max="400" name="wall_size" />
                                                                            <div class="range_box">
                                                                                <input type="range" class="zoomRange range-input" min="300" max="400" step="1" />
                                                                                <div class="changeSm">
                                                                                    <div>
                                                                                        <span class="js_s1">300</span> {!! trans('collage_new.sm') !!}
                                                                                    </div>
                                                                                    <div>
                                                                                        <span class="js_s2">400</span> {!! trans('collage_new.sm') !!}
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="range__box">
                                                                            <input class="main__input--wall" type="number" placeholder="350 {!! trans('collage_new.sm') !!}" min="350" max="400" name="wall_size" />
                                                                            <div class="range_box">
                                                                                <input type="range" class="zoomRange range-input" min="350" max="400" step="1" />
                                                                                <div class="changeSm">
                                                                                    <div>
                                                                                        <span class="js_s1">350</span> {!! trans('collage_new.sm') !!}
                                                                                    </div>
                                                                                    <div>
                                                                                        <span class="js_s2">400</span> {!! trans('collage_new.sm') !!}
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="range__box">
                                                                            <input class="main__input--wall" type="number" placeholder="250 {!! trans('collage_new.sm') !!}" min="250" max="300" name="wall_size" />
                                                                            <div class="range_box">
                                                                                <input type="range" class="zoomRange range-input" min="250" max="300" step="1" />
                                                                                <div class="changeSm">
                                                                                    <div>
                                                                                        <span class="js_s1">250</span> {!! trans('collage_new.sm') !!}
                                                                                    </div>
                                                                                    <div>
                                                                                        <span class="js_s2">300</span> {!! trans('collage_new.sm') !!}
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="range__box">
                                                                            <input class="main__input--wall" type="number" placeholder="350 {!! trans('collage_new.sm') !!}" min="350" max="400" name="wall_size" />
                                                                            <div class="range_box">
                                                                                <input type="range" class="zoomRange range-input" min="350" max="400" step="1" />
                                                                                <div class="changeSm">
                                                                                    <div>
                                                                                        <span class="js_s1">350</span> {!! trans('collage_new.sm') !!}
                                                                                    </div>
                                                                                    <div>
                                                                                        <span class="js_s2">400</span> {!! trans('collage_new.sm') !!}
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="accordion-title h2_old open frames" style="display: none;">
                                                                <span>2. {!! trans('collage_new.z7_generator_ramma_text1') !!}</span>
                                                                <span class="tab-icon"></span>
                                                            </div>
                                                            <div class="accordion-content frames" style="display: none;">
                                                                <div class="ramu-slider js_rams" style="display: none;">
                                                                    <label>
                                                                        <div data-price="0" ramu_zero_item="" data-interior="empty" class="ramu-item active">
                                                                            <input style="opacity: 0; position: absolute; pointer-events: none;" type="radio" name="ram_id" value="12" />
                                                                            <div class="selected-icon">
                                                                                <svg width="12" height="9" viewBox="0 0 12 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                    <path d="M4.90391 8.96884C4.643 8.96884 4.3911 8.87105 4.19842 8.69487L0.337368 5.16245C-0.086973 4.77427 -0.114713 4.11735 0.275141 3.69409C0.665745 3.27307 1.32475 3.2447 1.74984 3.63213L4.83643 6.45688L10.1827 0.582689C10.5695 0.157186 11.23 0.125087 11.6574 0.510279C12.084 0.895472 12.117 1.55239 11.7293 1.97789L5.67762 8.62844C5.49019 8.83298 5.23003 8.9554 4.95189 8.96884C4.93539 8.96884 4.91965 8.96884 4.90391 8.96884Z" fill="white"></path>
                                                                                </svg>
                                                                            </div>
                                                                            <span class="code">{!! trans('collage_new.z7_generator_ramma_text2') !!}</span>
                                                                            <span class="price">{!! trans('collage_new.z7_generator_ramma_text3') !!}</span>
                                                                        </div>
                                                                    </label>
                                                                    <label>
                                                                        <div data-price="25" class="ramu-item" data-src="{{ asset('images/canvas/ram1.jpg') }}" data-width="10">
                                                                            <input style="opacity: 0; position: absolute; pointer-events: none;" type="radio" name="ram_id" value="16" />
                                                                            <div class="selected-icon">
                                                                                <svg width="12" height="9" viewBox="0 0 12 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                    <path d="M4.90391 8.96884C4.643 8.96884 4.3911 8.87105 4.19842 8.69487L0.337368 5.16245C-0.086973 4.77427 -0.114713 4.11735 0.275141 3.69409C0.665745 3.27307 1.32475 3.2447 1.74984 3.63213L4.83643 6.45688L10.1827 0.582689C10.5695 0.157186 11.23 0.125087 11.6574 0.510279C12.084 0.895472 12.117 1.55239 11.7293 1.97789L5.67762 8.62844C5.49019 8.83298 5.23003 8.9554 4.95189 8.96884C4.93539 8.96884 4.91965 8.96884 4.90391 8.96884Z" fill="white"></path>
                                                                                </svg>
                                                                            </div>
                                                                            <span class="code">{!! trans('collage_new.z7_generator_ramma_text4') !!}</span>
                                                                            <div class="img">
                                                                                <picture>
                                                                                    <source srcset="{{ asset('images/canvas/rama1.webp') }}" type="image/webp">
                                                                                    <source srcset="{{ asset('images/canvas/rama1.jpg') }}">
                                                                                    <img loading="lazy" alt="{!! trans('collage_new.z7_generator_ramma_text4') !!}" title="{!! trans('collage_new.z7_generator_ramma_text4') !!}" src="{{ asset('images/canvas/rama1.jpg') }}" />
                                                                                </picture>
                                                                            </div>
                                                                            <span class="price"> + 25 &euro; </span>
                                                                        </div>
                                                                    </label>
                                                                    <label>
                                                                        <div data-price="25" class="ramu-item" data-src="{{ asset('images/canvas/ram2.jpg') }}" data-width="10">
                                                                            <input style="opacity: 0; position: absolute; pointer-events: none;" type="radio" name="ram_id" value="15" />
                                                                            <div class="selected-icon">
                                                                                <svg width="12" height="9" viewBox="0 0 12 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                    <path d="M4.90391 8.96884C4.643 8.96884 4.3911 8.87105 4.19842 8.69487L0.337368 5.16245C-0.086973 4.77427 -0.114713 4.11735 0.275141 3.69409C0.665745 3.27307 1.32475 3.2447 1.74984 3.63213L4.83643 6.45688L10.1827 0.582689C10.5695 0.157186 11.23 0.125087 11.6574 0.510279C12.084 0.895472 12.117 1.55239 11.7293 1.97789L5.67762 8.62844C5.49019 8.83298 5.23003 8.9554 4.95189 8.96884C4.93539 8.96884 4.91965 8.96884 4.90391 8.96884Z" fill="white"></path>
                                                                                </svg>
                                                                            </div>
                                                                            <span class="code">{!! trans('collage_new.z7_generator_ramma_text5') !!}</span>
                                                                            <div class="img">
                                                                                <picture>
                                                                                    <source srcset="{{ asset('images/canvas/rama2.webp') }}" type="image/webp">
                                                                                    <source srcset="{{ asset('images/canvas/rama2.jpg') }}">
                                                                                    <img loading="lazy" alt="{!! trans('collage_new.z7_generator_ramma_text5') !!}" title="{!! trans('collage_new.z7_generator_ramma_text5') !!}" src="{{ asset('images/canvas/rama2.jpg') }}" />
                                                                                </picture>
                                                                            </div>
                                                                            <span class="price"> + 25 &euro; </span>
                                                                        </div>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                            <div class="accordion-title h2_old open frames">
                                                                <span>2. {!! trans('collage_new.z7_generator_ramma_text6') !!}</span>
                                                                <span class="tab-icon"></span>
                                                            </div>
                                                            <div class="accordion-content accordion-content-sizes frames">


                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="canvas-wrapper">
                                                    <div class="picture tabs-item tabs-item1 active" id="tab_screen1" style="padding:0;">
                                                        <div class="PhotoEditor" id="PhotoEditor">
                                                            <div class="full-cs-title">
                                                                {!! trans('collage_new.z7_generator_controls12') !!}
                                                            </div>
                                                            <div class="wrapper">
                                                                <div class="canvas_container">
                                                                    <canvas id='canvas'> </canvas>
                                                                </div>
                                                                <div class="cs-canvs_settings settings">
                                                                    <div class="range">
                                                                        <div class="caption">{!! trans('collage_new.z7_generator_controls13') !!}</div>
                                                                        <input id="range_radius" class="slider" type="range" min="0" max="100" value="10">
                                                                    </div>
                                                                    <div class="range">
                                                                        <div class="caption">{!! trans('collage_new.z7_generator_controls14') !!}</div>
                                                                        <input id="range_between" class="slider" type="range" min="0" max="100" value="20">
                                                                    </div>
                                                                    <div class="fullscreen-change" id="fullscreen">
                                                                        <img loading="lazy" width="20" height="20" src="{{ asset('images/collage/tool12.svg') }}" alt="">
                                                                        <span>{!! trans('collage_new.z7_generator_controls15') !!}</span>
                                                                    </div>
                                                                    <div class="fullscreen-close" id="fullscreen-close">
                                                                        <img loading="lazy" width="20" height="20" src="{{ asset('images/collage/tool12.svg') }}" alt="">
                                                                        <span>{!! trans('collage_new.z7_generator_controls16') !!}</span>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="interior tabs-item tabs-item2">
                                                        <div id="interior_container" class="interior_container">
                                                            <canvas id="canvas_interior"> </canvas>
                                                            <div class="tools">
                                                                <div id="interior_fullscreen" class="tool icontool-fullscreen"> </div>
                                                                <div id="interior_zoom_minus" class="tool icontool-zoom_out"> </div>
                                                                <div id="interior_zoom_plus" class="tool icontool-zoom_in"> </div>
                                                            </div>
                                                        </div>
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
                <div class="formalization__block--bottom">
                    <div class="formalization__final">
                        <div class="formalizaton__submit">
                            <div class="formalization-price" id="totalSum">
                                {!! trans('collage_new.z7_generator_total_text1') !!} <span><span data-total="15.00" class="totalPriceNew">15</span>€</span>
                            </div>
                            <div class="formalization-btn sbm__collage" data-route="{{ route('add_item_to_basket_construct') }}" data-pid="2" data-name="Collage" href="javascript:void(0)">{!! trans('collage_new.z7_generator_total_text2') !!}</div>
                            <div class="loading-bar">
                                <span>{{ trans('portrait_buy_form.loading') }}</span>
                                <div class="l-progress">
                                    <span></span><span></span><span></span><span></span><span></span><span></span>
                                </div>
                            </div>
                        </div>
                        <div class="formalization-bottom--check">

                            <div class="kviz-wrap">
                                <p> {!! trans('portrait_buy_form.final_pack') !!}</p>
                                <div class="kviz-c-row kviz-c-group">
                                    @if($sets)
                                        @foreach($sets as $set)
                                            <div class="kviz-radio js-checkbox @if($loop->last) kviz-radio_active @endif" data-stock="1">
                                                <div class="check check-border"></div>
                                                <label>
                                              <span
                                              >{{ $set->getTranslatedAttribute('name', app()->getLocale()) }}<img
                                                      src="{{ asset('images/icon/info.svg') }}"
                                                      alt=""
                                                  /></span>
                                                    <input
                                                        name="boxes[]"
                                                        type="radio"
                                                        checked
                                                        data-id="{{ $set['id'] }}"
                                                        data-name="{{ $set->getTranslatedAttribute('name', app()->getLocale()) }}"
                                                        value="{{ $set->price*$contry_mult }}"
                                                    />
                                                    {{-- <div class="window-prompt">
                                                        {{ $set->getTranslatedAttribute('name', app()->getLocale()) }}
                                                    </div> --}}
                                                </label>
                                                <div class="pic-pop">
                                                    <picture>
                                                         <source srcset="" type="image/webp">
                                                        <source srcset="{{ Voyager::image($set->image) }}" type="image/jpeg">
                                                        <img loading="lazy" src="{{ Voyager::image($set->image) }}" @altAttrs($set, 'image', data_get($set, 'image'))>
                                                    </picture>
                                                    {{-- {{ $set->getTranslatedAttribute('hint', app()->getLocale()) }} --}}
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
{{--
                            <div class="kviz-wrap">
                                <p>{!! trans('collage_new.z7_generator_total_text3') !!}</p>
                                <div class="kviz-c-row kviz-c-group">
                                    <div class="kviz-radio js-checkbox kviz-radio_active" data-stock="1">
                                        <div class="check check-border"></div>
                                        <label>
                                            <span>{!! trans('collage_new.z7_generator_total_text4') !!}<img loading="lazy" width="17" height="16" src="{{ asset('images/icon/info.svg') }}" alt="" /></span>
                                            <input name="boxes[]" type="radio" checked data-id="3" data-name="{!! trans('collage_new.z7_generator_total_text4') !!}" value="0" />
                                            <div class="window-prompt">
                                                {!! trans('collage_new.z7_generator_total_text4') !!}
                                            </div>
                                        </label>

                                        <div class="pic-pop">

                                            <picture>
                                                <source srcset="" type="image/webp">
                                                <source srcset="{{ Voyager::image($set->image) }}" type="image/jpeg">
                                                <img loading="lazy" src="{{ Voyager::image($set->image) }}" @altAttrs($set, 'image', data_get($set, 'image'))>
                                            </picture>

                                        </div>
                                    </div>
                                    <div class="kviz-radio js-checkbox" data-stock="2">
                                        <div class="check check-border"></div>
                                        <label>
                                            <span>{!! trans('collage_new.z7_generator_total_text5') !!}<img loading="lazy" width="17" height="16" src="{{ asset('images/icon/info.svg') }}" alt="" />
                                            </span>
                                            <input name="boxes[]" type="radio" data-id="2" data-name="{!! trans('collage_new.z7_generator_total_text5') !!}" value="3" />
                                            <div class="window-prompt">
                                                {!! trans('collage_new.z7_generator_total_text5') !!}
                                            </div>
                                        </label>
                                    </div>
                                    <div class="kviz-radio js-checkbox" data-stock="1">
                                        <div class="check check-border"></div>
                                        <label>
                                            <span>{!! trans('collage_new.z7_generator_total_text6') !!}
                                                <img loading="lazy" src="{{ asset('images/icon/info.svg') }}" width="17" height="16" alt="" /></span>
                                            <input name="boxes[]" type="radio" data-id="1" data-name="{!! trans('collage_new.z7_generator_total_text6') !!}" value="4" />
                                            <div class="window-prompt">
                                                {!! trans('collage_new.z7_generator_total_text6') !!}
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
 --}}

                            <div class="kviz-wrap delivery-inputs">
                                <p>{!! trans('collage_new.z7_generator_total_text7') !!}</p>
                                <div class="kviz-c-row kviz-c-group">
                                    <div class="kviz-radio js-checkbox kviz-radio_active" data-stock="1">
                                        <div class="check check-border"></div>
                                        <label>
                                            <span>{!! $AProductionTime->standart_text !!} {{ $AProductionTime->standart_price }} €</span>
                                            <input type="radio" name="dost_time" value="{{ $AProductionTime->standart_price }}" />
                                        </label>
                                    </div>
                                    <div class="kviz-radio js-checkbox" data-stock="2">
                                        <div class="check check-border"></div>
                                        <label>
                                            <span>{!! $AProductionTime->express_text !!} {{ $AProductionTime->express_price }} €</span>
                                            <input type="radio" name="dost_time" value="{{ $AProductionTime->express_price }}" />
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <div class="collage-loading">
                <div class="loading-bar">
                    <span>
                        {{ trans('portrait_buy_form.loading') }}
                    </span>
                    <div class="l-progress">
                        <span></span><span></span><span></span><span></span><span></span><span></span>
                    </div>
                </div>
            </div>
        </div>



        <div class="reviews hidden">
            <div class="page-title h2_old">{!! trans('collage_new.z7_reviews_text') !!}</h2>
            <div class="reviews-subtitle">{!! trans('collage_new.z7_reviews_subtext') !!}</div>
            <div class="reviews-wrapper">
                <div class="reviews-slider">
                    @if($revs)
                    @foreach($revs as $item)
                    <div class="reviews-slide">
                        <div class="reviews-slide-box">
                            <div class="reviews-content">
                                <picture>
                                    <img src="{{ Voyager::image($item->img) }}" class="reviews-photo" alt="" loading="lazy">
                                </picture>
                                <div class="reviews-info">
                                    <div class="reviews-title">
                                        <picture>
                                            <img src="{{ Voyager::image($item->avatar) }}" alt="img" loading="lazy">
                                        </picture>
                                        <div class="reviews-name">
                                            <b>{{ $item->name }}</b>
                                            <p>{{ $item->city }}</p>
                                        </div>
                                    </div>
                                    <div class="reviews-text">{!! $item->text !!}</div>
                                </div>
                            </div>
                            @if ($item->a_player)
                            @php
                            if (isset(json_decode($item->a_player)[0])) {
                            $file = json_decode($item->a_player)[0]->download_link;
                            } else {
                            $file = '';
                            }

                            @endphp
                            @if(Voyager::image($file))
                            <audio controls src="{{ Voyager::image($file) }}"></audio>
                            @endif
                            @endif
                        </div>
                        {{-- @if($item->img)
                                        <picture>
                                            <img src="{{ Voyager::image($item->img) }}" class="reviews-bg"
                        alt="" loading="lazy">
                        </picture>
                        @endif --}}
                    </div>
                    @endforeach
                    @endif
                </div>
                <div class="reviews-arrow">
                    <a href="#" class="reviews-prev" aria-label="reviews-prev">
                        <i class="fa-arrow-prev"></i>
                    </a>
                    <a href="#" class="reviews-next" aria-label="reviews-next">
                        <i class="fa-arrow-next"></i>
                    </a>
                </div>
            </div>
            <div class="reviews-counter">
                <span class="counter-active">1</span>
                <span>/</span>
                <span class="reviews-all-slide">{{ count($revs) }}</span>
            </div>
        </div>

    </div>

</div>




<!-- ellipse -->
{{-- <div class="ellipse ellipse_black">
    <img src="https://viarcanvas.com/theme/viar/images/icon/ellipse-black.svg" alt="img" loading="lazy">
</div> --}}
