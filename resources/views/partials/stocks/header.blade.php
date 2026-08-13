<div class="stocks-content">
    <div class="container">
        <div class="stocks-items clearfix">
            <div class="stocks-item">
                <img src="{{ asset('img/stocks-item-img1.png') }}" alt="">
                <h3>{!! $head['dates_title'] !!}<i>i<span>{!! $head['dates_tip'] !!}</span></i></h3>
                {!! $head['dates_sales_text'] !!}
                <a @guest class="js_open_reg" @endguest @auth class="datas_js" @endauth
                    href="javascript:void(0)"><span>{!! $head['dates_sales_btn_text'] !!}</span></a>
            </div>
            <div class="stocks-item">
                <img src="{{ asset('img/stocks-item-img2.png') }}" alt="">
                <h3>{!! $head['friend_title'] !!}<i>i<span>{!! $head['friend_tip'] !!}</span></i></h3>
                {!! $head['friend_sale_text'] !!}

                <a @guest class="js_open_reg" @endguest @auth class="friend_js" @endauth
                    href="javascript:void(0)"><span>{!! $head['friend_sale_btn_text'] !!}</span></a>
            </div>
            <div class="stocks-item">
                <img src="{{ asset('img/stocks-item-img3.png') }}" alt="">
                <h3>{!! $head['print_title'] !!}<i>i<span>{!! $head['print_tip'] !!}</span></i></h3>
                {!! $head['print_text'] !!}
                <a @guest class="js_open_reg" @endguest @auth class="printScreen_js" @endauth
                    href="javascript:void(0)"><span>{!! $head['print_btn_text'] !!}</span></a>
            </div>
        </div>
        <div class="send-data">
            <div class="text">
                {!! $head['foto_free_text'] !!}
                <a @guest class="js_open_reg" @endguest @auth class="photo_js" @endauth
                    href="javascript:void(0)"><span>{!! $head['foto_free_btn_text'] !!}</span></a>
            </div>
            <img src="{{ asset('img/send-data-img.png') }}" alt="">
        </div>
    </div>
</div>

<script>
    $(document).on('click', '.js_open_reg',function(e){
        $('.office-basket-transfer a').trigger('click');
    });

</script>
