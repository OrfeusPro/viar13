<section class="stages">
    <div class="stages-container">
        <h2>{!! $canv_bot['et_title'] !!}</h2>
        <div class="stages-items">
            <div class="stages-item">
                <div class="img">
                    <img src="{{ asset('img/stages-img1.png') }}" alt="" class="img-illustration">
                    <img src="{{ asset('img/img-after1.png') }}" alt="" class="img-after img-after1">
                </div>
                <div class="text">
                    <img src="{{ asset('img/stages-item-nubmer1.png') }}" alt="">
                    <p>{!! $canv_bot['et1_text'] !!}</p>
                </div>
            </div>
            <div class="stages-item">
                <div class="img">
                    <img src="{{ asset('img/stages-img2.png') }}" alt="" class="img-illustration">
                    <img src="{{ asset('img/img-after2.png') }}" alt="" class="img-after img-after2">
                </div>
                <div class="text">
                    <img src="{{ asset('img/stages-item-nubmer2.png') }}" alt="">
                    <p>{!! $canv_bot['et2_text'] !!}</p>
                </div>
            </div>
            <div class="stages-item">
                <div class="img">
                    <img src="{{ asset('img/stages-img3.png') }}" alt="" class="img-illustration">
                    <img src="{{ asset('img/img-after3.png') }}" alt="" class="img-after img-after3">
                </div>
                <div class="text">
                    <img src="{{ asset('img/stages-item-nubmer3.png') }}" alt="">
                    <p>{!! $canv_bot['et3_text'] !!}</p>
                </div>
            </div>
            <div class="stages-item">
                <div class="img">
                    <img src="{{ asset('img/stages-img4.png') }}" alt="" class="img-illustration">
                    <img src="{{ asset('img/img-after4.png') }}" alt="" class="img-after img-after4">
                </div>
                <div class="text">
                    <img src="{{ asset('img/stages-item-nubmer4.png') }}" alt="">
                    <p>{!! $canv_bot['et4_text'] !!}</p>
                </div>
            </div>
            <div class="stages-item">
                <div class="img">
                    <img src="{{ asset('img/stages-img5.png') }}" alt="" class="img-illustration">
                    <img src="{{ asset('img/img-after5.png') }}" alt="" class="img-after img-after5">
                </div>
                <div class="text">
                    <img src="{{ asset('img/stages-item-nubmer5.png') }}" alt="">
                    <p>{!! $canv_bot['et5_text'] !!}</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="everything">
    <img 
    @isset($collage_header)
    src="{{ asset('img/everything-img.png') }}" 
    @else
    src="{{ asset('img/everything-img.png') }}" 
    @endif
    alt="" class="everything-img">
    <div class="everything-content">
        <div class="title">
            @isset($collage_header)
                {!! $collage_header['et_left_title'] !!}
            @else
            {!! $canv_bot['et_left_title'] !!}
            @endif
        </div>
        @isset($collage_header)
            {!! $collage_header['et_bot_text'] !!}
        @else
        {!! $canv_bot['et_left_right_text'] !!}
        @endif
    </div>
</section>