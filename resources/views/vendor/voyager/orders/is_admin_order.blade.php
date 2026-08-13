<td style="text-align: center;" class="td-0">
    <div class="orda__col">
        <p data-time="{{ $order['created_at'] }}">
            Дата заказа:
            {{ date('d/m/Y H:i', strtotime($order['created_at'])) }}
        </p>
        <div class="status"
             style="padding: 5px 10px; background-color: <?= $order['payment_status'] == 'not_payed' ? 'red' : ($order['payment_status'] == 'prepayment' ? '#c19300' : 'green') ?>;color: white;margin: 5px 0px; border-radius: 7px;">
            {{ $statusName[$order['payment_status']] }}
        </div>
        @include('voyager::orders.status')
    </div>
</td>
<td style="text-align: center;" class="td-1">
    {{ $order['created_at'] }}
</td>
<td style="text-align: center;" class="td-2 ord__hide__mob"></td>
<td style="text-align: center;" class="td-3">
    <div class="orda__col">
        <p>@if($order['price'] != 0) Стоимость: <b>{{ $order['price'] }}</b> @endif </p>
        @if ($order['items'])
            <ul>
                @foreach ($order['items'] as $product)
                    @if(isset($product['name']))
                        <p>{{ $product['name'] }}</p>
                    @endif

                    @if(isset($product['content']))
                        {!! $product['content'] !!}
                    @endif

                @endforeach
            </ul>
        @endif
    </div>
</td>
<td style="text-align: center;" class="td-4"></td>
<td style="text-align: center;" class="td-5"></td>
<td style="text-align: center;" class="">
    <div class="orda__col form-group">
        <label for="status">Статус заказа: </label>
        <select id="status" name="status"
                onchange="changeStatus(this.options[this.selectedIndex].value, {{ $order['id'] }})">
            <option value="watching" <?= $order['status'] == 'watching' ? 'selected' : '' ?>>На рассмотрении</option>
            <option value="pegging" <?= $order['status'] == 'pegging' ? 'selected' : '' ?>>В процессе</option>
            <option value="in_production" <?= $order['status'] == 'in_production' ? 'selected' : '' ?>>В производстве</option>
            <option value="sended" <?= $order['status'] == 'sended' ? 'selected' : '' ?>>Отправлен</option>
            <option value="send_lubanas" <?= $order['status'] == 'send_lubanas' ? 'selected' : '' ?>>Отправлен на Лубанас 65</option>
            <option value="completed" <?= $order['status'] == 'completed' ? 'selected' : '' ?>>Завершен</option>
        </select>
    </div>
    <br>
    <br>
    @php
        $order_date = date('d/m/Y H:i', strtotime($order['created_at']));
    @endphp
    <b>номер заказа:</b> <span>{{ $order['id'] }}</span><br>
    <b>дата заказа:</b> <span> {{ $order_date }}</span>
    <br>
    <a class="btn" style="text-align: right;float: right;background: orange;color: #fff;"
       href="{{ route('edit_admin_order', $order['id']) }}">Редактировать
        заказ</a>
    <br>
    <a href="javascript:;" onclick="deleteOrder( {{ $order['id'] }} )" title="Удалить" style="clear:both;"
       class="btn btn-sm btn-danger pull-right delete">
        <i class="voyager-trash"></i>
        <span class="hidden-xs hidden-sm text__btn__span">Удалить</span>
    </a>
</td>
