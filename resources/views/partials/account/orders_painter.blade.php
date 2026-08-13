@foreach($orders as $key=>$order)
@php
$order->delivery = json_decode($order->delivery, 1);
$order->items = json_decode($order->items, 1);

if( $order->status == 'completed')
{
$statusName = __('account.index13');
$statusClass = 'completed';
}
if( $order->status == 'watching')
{
$statusName = trans('gl.status_watching');
$statusClass = 'acting';
}
if( $order->status == 'sended')
{
$statusName = __('account.index14');
$statusClass = 'acting';
}
if( $order->status == 'pegging')
{
$statusName = trans('gl.status_pending');
$statusClass = 'acting';
}
@endphp
<div class="{{ $statusClass }} @if($key==count($orders)-1) last @endif">
    <div class="{{ $statusClass }}-content">
        <span class="status">{{ $statusName }}</span>
        <ul class="data">
            <li>
                <strong>@lang('account.index15') № {{ $order->id }}</strong>
                от {{ date( 'd.m.Y', strtotime($order->created_at)) }}
            </li>
            @if(Auth::user()->role->name != 'painter')
            <li><strong>@lang('account.index16') </strong>{{ $order->delivery['address'] }}</li>
            @endif
            <li><strong>@lang('account.index17') </strong>@lang('account.index18')</li>
            <li>
                @if($order->pdf_approved == 1)
                <a href="{{ $order->pdf_link }}">@lang('account.index19')</a>
                @else
                <a style="color:#000;" href="javascript:void(0)">@lang('account.index19')</a>
                @endif
            </li>

        </ul>
        <div>
            @php
            $i=0;
            @endphp
            @foreach($order->items as $product)
            @php
            if(!isset($product['sumPrice']))
            {
            continue;
            }
            @endphp

            @for($ii=0;$ii<$product['count'];$ii++) <div class="{{ $statusClass }}-items clearfix">
                <div class="acting-item clearfix">
                    <div class="img">
                        @include('partials.basket_img')
                    </div>
                    <div class="text">
                        <ul>
                            @include('partials.all_basket_order_items')
                        </ul>
                        <strong>{{ $product['formatedPrice'] }}</strong>
                    </div>
                </div>
        </div>
        @endfor
        @endforeach
    </div>

    <ul class="commentary">
        @if(isset($order->comment))
            <li><strong>@lang('account.index25') </strong>{{ $order->comment }}</li>
        @endif

        @if(Auth::user()->role->name == 'painter')
            @if($order["admin_comment"] != '' && $order["admin_comment"] != null)
                <em>{{ $order->admin_comment }}</em>
            @endif
        @endif

        <li>
            <strong>
                @lang('account.index26') 
                
                @if(isset($order->delivery['deliv_price']) && $order->delivery['deliv_price'] && isset($order->items['total_terms_price']) && $order->items['total_terms_price'])
                    {{ $order->items['totalPrice'] + $order->delivery['deliv_price'] + $order->items['total_terms_price'] }}
                @elseif(isset($order->delivery['deliv_price']) && $order->delivery['deliv_price'])
                    {{ $order->items['totalPrice'] + $order->delivery['deliv_price'] }}
                @elseif(isset($order->items['total_terms_price']) && $order->items['total_terms_price'])
                    {{ $order->items['totalPrice'] + $order->items['total_terms_price'] }}
                @else
                    {{ $order->items['totalPrice'] }}
                @endif

                €
            </strong>
        </li>
    </ul>
</div>
</div>
@endforeach
