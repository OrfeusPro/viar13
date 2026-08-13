@foreach($payed_orders as $key=>$order)
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
                <li class="t__list_item">
                    <strong>@lang('account.index15') № {{ $order->id }}</strong>
                    от {{ date( 'd.m.Y', strtotime($order->created_at)) }}
                </li>
                {{--<li><strong>@lang('account.index16') </strong>{{ $order->delivery['address'] }}</li>--}}
                <li><strong>@lang('account.index17') </strong>@lang('account.index18')</li>
                @if(Auth::user()->role->name != 'painter')
                    <li>
                        @if($order->pdf_approved == 1)
                            <a href="{{ $order->pdf_link }}">@lang('account.index19')</a>
                        @else
                            <a style="color:#000;" href="javascript:void(0)">@lang('account.index19')</a>
                        @endif
                    </li>
                @endif

                @if(Auth::user()->role->name == 'painter')
                    @if($order->painter_payed == 1)
                        <div class="painter__icon_compl">
                            <span> @lang('account.index13')</span>
                            <svg height="24px" viewBox="0 0 512 512" width="24px" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="m256 0c-141.164062 0-256 114.835938-256 256s114.835938 256 256 256 256-114.835938 256-256-114.835938-256-256-256zm0 0"
                                    fill="#2196f3"/>
                                <path
                                    d="m385.75 201.75-138.667969 138.664062c-4.160156 4.160157-9.621093 6.253907-15.082031 6.253907s-10.921875-2.09375-15.082031-6.253907l-69.332031-69.332031c-8.34375-8.339843-8.34375-21.824219 0-30.164062 8.339843-8.34375 21.820312-8.34375 30.164062 0l54.25 54.25 123.585938-123.582031c8.339843-8.34375 21.820312-8.34375 30.164062 0 8.339844 8.339843 8.339844 21.820312 0 30.164062zm0 0"
                                    fill="#fafafa"/>
                            </svg>
                        </div>
                    @endif
                @endif
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

                    <div class="{{ $statusClass }}-items clearfix">
                        @if($loop->first)
						{{--<div class="acting-item clearfix">--}}
                                @include('partials.account.left_order_info')
                                @if(Auth::user()->role->name == 'painter')
                                    @include('partials.account.painter_images')
                                @endif
								{{--</div>--}}
                        @endif
                        @for($ii=0;$ii<$product['count'];$ii++)
                            <div class="acting-item clearfix">
                                <div class="img">
                                    @include('partials.basket_img')
                                </div>
                                <div class="text">
                                    <ul>
                                        @include('partials.all_basket_order_items')
                                    </ul>
                                    @if(Auth::user()->role->name != 'painter')
                                        <strong>{{ $product['formatedPrice'] }}</strong>
                                    @endif
                                </div>
                            </div>
                        @endfor
                    </div>
                @endforeach
            </div>
            @include('partials.account.order__comments')
            @include('partials.account.painter_chats')
        </div>
    </div>
@endforeach

    @if (!is_array($payed_orders))
    <div class="pagination pagination_payed">
        {{ $payed_orders->links() }}
    </div>
    @endif
