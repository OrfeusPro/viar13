<section class="everything">
    <img src="{{ asset('img/everything-img.png') }}"  class="everything-img" @frontendAlt('partials/family/evr.blade.php', (asset('img/everything-img.png')), '', '')>
        <div class="everything-content">
            <div class="title">     
                {!! $canv_bot['et_left_title'] !!}
            </div>
            {!! $canv_bot['et_left_right_text'] !!}
        </div>
</section>
