<div class="form-group">
    <p>Статус оплаты:</p>
    <select name="payment_status" placeholder="Статус оплаты" class="js_payed_update" data-order="{{ $order['id'] }}">
        <option @if ($order['payment_status'] == 'not_payed') selected @endif value="not_payed">Не оплачено
        </option>
        <option @if ($order['payment_status'] == 'prepayment') selected @endif value="prepayment">Предолата
        </option>
        <option @if ($order['payment_status'] == 'payed') selected @endif value="payed">Оплачено
        </option>
    </select>
    <br>
    <br>
</div>
