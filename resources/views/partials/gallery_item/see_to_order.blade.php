<section class="other-offers">
    <div class="title">
        <h2>{{ trans('gl.get_time_to_order') }}</h2>
    </div>
    <div class="other-offers-content">
        <div class="container">
            @if($items_big_sale)
                @include('partials.stocks.all_big')
            @endif
        </div>
    </div>
</section>
