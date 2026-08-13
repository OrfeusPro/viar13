<h3>@lang('account.index5')</h3>
<div class="bonuses-contenr">
    <h6>@lang('account.index27')</h6>
    <span>@lang('account.index28')</span>
    <strong>@if(Auth::user()->bonuses == null) 0 @else {{ Auth::user()->bonuses }} @endif €</strong>
    <p>@lang('account.index29')</p>

    <h6>Активные купоны</h6>
    <ul>
    @foreach($coupons as $coupon)
        <li>{{ $coupon->text }}</li>
    @endforeach
</ul>
    <a href="{{ route('new_stocks') }}">@lang('account.index30')</a>
</div>

@if(Auth::user()->has_sale_20eur == 1)
<div class="bonuses-item">
    <div class="text">
        <h5>@lang('account.index31') {{ $sales['custom_coupon_sale'] }} €</h5>
        <p>@lang('account.index32')</p>
    </div>
    <div class="img"
        style="background: url('{{ asset('img/bonuses-item-img1.png') }}') no-repeat 0 50%; background-size: cover;">
    </div>
</div>
@endif

@if(Auth::user()->is_coupon_dates != null)
<div class="bonuses-item">
    <div class="text">
        <h5>@lang('account.index33')</h5>
        @if(Auth::user()->date1)
        <p>{{ Auth::user()->date1 }} {{ Auth::user()->torj1 }} <strong></strong></p>
        @endif
        @if(Auth::user()->date2)
        <p>{{ Auth::user()->date2 }} {{ Auth::user()->torj2 }} <strong></strong></p>
        @endif
    </div>
    <div class="img"
        style="background: url('{{ asset('img/bonuses-item-img2.png') }}') no-repeat 0 50%; background-size: cover;">
    </div>
</div>
@endif

@if(Auth::user()->is_active_friend_inv == 1)
<div class="bonuses-item">
    <div class="text">
        <h5>@lang('account.index36') 5 €</h5>
        @if(Auth::user()->inv_sale_code == 'alredy_used')
        <h6>@lang('account.index37')</h6>
        @endif
    </div>
    <div class="img"
        style="background: url('{{ asset('img/bonuses-item-img3.png') }}') no-repeat 0 50%; background-size: cover;">
    </div>
</div>
@endif

<div class="coupons__grid">
    @if(Auth::user()->is_facebook_sale == 1)
    <div class="pay clearfix">
        <div class="pay-content">
            <div class="pay-sum">
                <h6>{{ trans('gl.sens_us_print_text_vac') }}</h6>
{{--                <span>{{ __('cart.summ_to_pay') }}: -{{ $stocks['facebook_sale'] }}%</span>--}}
            </div>
        </div>
    </div>
    @endif
</div>
