<p data-time="{{ $order['created_at'] }}">
    Дата заказа:
    {{ date('d/m/Y H:i', strtotime($order['created_at'])) }}
</p>
<div class="status"
     style="padding: 5px 10px; background-color: <?= $order['payment_status'] == 'not_payed' ? 'red' : ($order['payment_status'] == 'prepayment' ? '#c19300' : 'green') ?>;color: white;margin: 5px 0px; border-radius: 7px;">
    {{ $statusName[$order['payment_status']] }}
</div>
@if($role != "printing")

    <div class="form-group">
        <p>Статус оплаты:</p>
        <select name="payment_status" placeholder="Статус оплаты" class="js_payed_update" data-order="{{ $order['id'] }}">
            <option @if ($order['payment_status'] == 'not_payed') selected @endif value="not_payed">Не оплачено
            </option>
            <option @if ($order['payment_status'] == 'prepayment') selected @endif value="prepayment">Предоплата
            </option>
            <option @if ($order['payment_status'] == 'payed') selected @endif value="payed">Оплачено
            </option>
        </select>
        <br>
        <br>
    </div>
    @if ($order['payment_status'] == 'prepayment')
    <div class="form-group">
        <p>Сумма предоплаты:</p>
        <input class="form-control js_prepayment_price_{{ $order['id'] }}" type="number" name="prepayment_price" value=@if($order['prepayment_price'])"{{$order['prepayment_price']}}" @else "0" @endif>
        <button class="prepayment_price_send btn btn-sm btn-primary" data-id="{{ $order['id'] }}">Обновить</button>
        <br>
        <br>
    </div>
    @endif
@endif

<div>
    <a href="#" class="eticet-create-btn" data-id="#order-{{ $order['id'] }}">
        <img class="img__et" src="{{ asset('images/venipak.png') }}" alt=""/>
        <span>СОЗДАНИЕ ЭТИКЕТКИ</span>
    </a>
</div>
<div>
    <div class="nums-list">
        Номера этикеток:<br/>
        <?php
        $e_list = explode(',',
            rtrim($order['labels'], ','));
        foreach ($e_list as $lbl) { ?>
        <form class="f_print" action="{{ route('print_label') }}" method="POST">
            <br/>
            {{ csrf_field() }}
            <span>{{ $lbl }}</span>
            <input name="label_code" type="hidden" value="{{ $lbl }}" required/>
            <button type="submit">На печать</button>
        </form>
        <?php }
        ?>
    </div>
</div>
