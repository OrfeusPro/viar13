<ul class="commentary commentary__bot commentary__bot__parts">
    @if(Auth::user()->role->name == 'painter')
    @php
        $painterDeadlineRaw = $order->painter_end_time ?? $order->painter_endtime ?? null;
        $day_diff = null;

        if ($painterDeadlineRaw) {
            try {
                $when_send = new DateTime($painterDeadlineRaw);
                $cur_date = new DateTime();
                $end_date = strtotime($cur_date->format('Y-m-d'));
                $datediff = strtotime($when_send->format('Y-m-d')) - $end_date;
                $df = floor($datediff / (60 * 60 * 24));
                $day_diff = (int) str_replace('-', '', (string) $df);
            } catch (\Throwable $e) {
                $day_diff = null;
            }
        }
    @endphp

    {{--
        <li class="t__list_item"><strong>Тип заказа: <span style="font-weight: normal;">
                    @if($day_diff<4) <span style="color:red;">Срочный</span>
                @else Обычный @endif
                </span></strong>
        </li>
    --}}
    @if($painterDeadlineRaw)
        <li>
            <strong>Выполнить до:</strong>
            @if($day_diff !== null && $day_diff < 4) <span style="color:red;"> @endif
                <small>{{ date('d.m.Y', strtotime($painterDeadlineRaw)) }}</small>
            @if($day_diff !== null && $day_diff < 4) </span> @endif
        </li>
    @endif
    @endif
    @if(Auth::user()->role->name != 'painter')
        @if(isset($order->items['total_terms_price']) && $order->items['total_terms_price'])
        <li>
            <strong>
                @lang('cart_new.general_production')
            </strong>

            {{ $order->items['total_terms_price'] }} €
        </li>
        <br>
        @endif
        <li>
            <strong>
                @isset($order->delivery['sposob'])
                    @if ($order->delivery['sposob'] == 'to_the_door')
                        @lang('mail.delivery_sposob_to_the_door')
                    @elseif($order->delivery['sposob'] == 'pickup_Riga')
                        @lang('mail.delivery_sposob_pickup_riga')
                    @elseif($order->delivery['sposob'] == 'pickup_Daugavplis')
                        @lang('mail.delivery_sposob_pickup_daugavplis')
                    @elseif($order->delivery['sposob'] == 'venipak')
                        @lang('cart_new.step_3_delivery_to_pick-up_point')
                    @elseif($order->delivery['sposob'] == 'pickup_at_viar_workshop')
                        @lang('cart_new.step_3_pick_up_at_viar_workshop')
                    @elseif($order->delivery['sposob'] == 'city_delivery')
                        <p>Доставка по {{$order->delivery['city']}}</p>
                    @endif

                @endisset
            </strong>

            @if(isset($order->delivery['deliv_price']) && $order->delivery['deliv_price'] && $order->delivery['deliv_price'] > 0)
                - {{ $order->delivery['deliv_price'] }}€
            @endif
        </li>
        <br>
        <li>
            <strong>
                @lang('account.index26')
            </strong>

            @isset($order->items['totalPrice'])
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
            @endisset
        </li>
    @endif
</ul>

<div class="comments__bot__full">
    <ul class="commentary commentary__bot commentary__bot__inline">
        <li><strong>Комментарий клиента: </strong><br>{{ $order->comment }}</li>
        @php
        $user_comments = \App\Models\User::get_user_acc_comments($order->id);
        @endphp

        @if($user_comments)
        @if(Auth::user()->role->name != 'user')
        <div class="commentary__painter">
            <li><strong>Комментарий клиента<br> к картинам: </strong><br>{{ $order->client_comment }}</li>
            <ul class="user__comments__list">
                @foreach($user_comments as $u_comment)
                <li>
                    <span>{{ date( 'd.m.Y', strtotime($u_comment->created_at)) }} - </span>{{ $u_comment->comment }}
                </li>
                @endforeach
            </ul>
        </div>
        @endif
        @endif


        @if(Auth::user()->role->name != 'user')
        @if($order->admin_comment != '' && $order->admin_comment != null)
        <li data-user={{ Auth::user()->role->name }}><strong>Комментарий админа:</strong><br>
            {{ $order->admin_comment }}</li>
        @endif
        @endif

        @if(Auth::user()->role->name == 'admin')
            <li ><strong>Комментарий админа:</strong><br>
                {{ $order->admin_comment }}</li>
        @endif


        @if(Auth::user()->role->name != 'user' && Auth::user()->role->name != 'painter')
        <div class="commentary__painter">
            <li><strong>Комментарии художника: </strong><br><span
                    class="js_painter_comment__item">{{ $order->painter_comment }}</span></li>
            @php
            $painter_all_comments = \App\Models\User::get_painter_all_comments($order->id);
            @endphp
            <ul class="painter__comments__list">
                @if($painter_all_comments)
                @foreach($painter_all_comments as $p_comm)
                <li>
                    <span>{{ date( 'd.m.Y', strtotime($p_comm->created_at)) }} - </span>{{ $p_comm->comment }}
                </li>
                @endforeach
                @endif
            </ul>
        </div>
        @endif
    </ul>
</div>
