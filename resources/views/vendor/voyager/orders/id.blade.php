    <td style="text-align: center;" class="td__id">
        №: {{ $order['id'] }}<br><br>
        @php
            if(isset($_GET['order_filter'])){
                $order_vr_id  = \App\Models\Orders::getVRById($order['id']);
            }
        @endphp

        @if ($order_vr_id == null)
            <select name="nakl_num" id="" data-id="{{ $order['id'] }}">
                <option value="#" selected disabled>Накладная -</option>
                <option value="1">{{ $my_ord_strings["consignor_text"] }}</option>
                <option value="2">{{ $my_ord_strings["consignor_text_vrv"] }}</option>
                <option value="3">{{ $my_ord_strings["consignor_text_vrr"] }}</option>
                <option value="4">{{ $my_ord_strings["consignor_text_vra"] }}</option>
            </select>
        @endif
        <br><br>
        <div id="order_vr_{{ $order['id'] }}">
            @if ($order_vr_id != null)
                <input class="js_vr_num" type="text" value="{{ $order_vr_id }}">
                <br>
                @if($role != "printing")
                    <br>
                    <span class="n_btns_cover">

                        <button class="js_vr_sbm btn btn-sm btn-primary"
                                data-id={{ $order['id'] }}>Обновить</button>
                        <button class="js_vr_rem btn btn-sm btn-danger"
                                data-id={{ $order['id'] }} title="Удалить"><i class="voyager-trash" aria-hidden="true"></i></button>
                    </span>
                @endif
                <br>
                <button class="js_firm btn btn-secondary">данные
                    фирмы
                </button>
                <form method="POST" action="{{ route('update_order_firm', ['order_id' => $order['id']]) }}"
                      style="display:none;" class="js_firm_form">
                    @csrf
                    @php
                        if (strpos($order_vr_id, 'VR00') !== false) {
                            $name = 'SIA "ViarStudia"';
                            $reg_num = '';
                            $addr = 'Druvas iela4 , Daugavpils nov., LV-5459';
                            $bank = 'LV41503069660';
                            $vat_num = 'Swedbank';
                            $bank_code = 'Swedbank';
                            $office_addr = 'Lubānas 63/65, Rīga';
                            $acc_num = 'LV85HABA0551039079593';
                        } else {
                            $name = 'Viarcanvas';
                            $reg_num = '';
                            $addr = 'Sėlių a. 16-2, Zarasai';
                            $bank = '';
                            $vat_num = 'LT193250067283712555';
                            $bank_code = '';
                            $office_addr = '';
                            $acc_num = '';
                        }
                    @endphp
                    <input type="hidden" name="order_vr_id" value="{{ $order_vr_id }}">
                    <div class="form_group">
                        <label>Фирма
                            <input name="name" type="text" placeholder="Фирма" required value="{{ $name }}">
                        </label>
                    </div>
                    <div class="form_group">
                        <label>Рег. ном.
                            <input name="reg_num" type="text" placeholder="Рег. ном." required value="{{ $reg_num }}">
                        </label>
                    </div>
                    <div class="form_group">
                        <label>Адрес
                            <input name="addr" type="text" placeholder="Адрес" required value="{{ $addr }}">
                        </label>
                    </div>
                    <div class="form_group">
                        <label>Банк
                            <input name="bank" type="text" placeholder="Банк" value="{{ $vat_num }}">
                        </label>
                    </div>
                    <div class="form_group">
                        <label>Банк VAT
                            <input name="vat_num" type="text" placeholder="Банк" value="{{ $bank }}">
                        </label>
                    </div>
                    <div class="form_group">
                        <label>Код банка
                            <input name="bank_code" type="text" placeholder="Код банка" value={{ $bank_code }}>
                        </label>
                    </div>
                    <div class="form_group">
                        <label>Адрес офиса
                            <input name="office_addr" type="text" placeholder="Адрес офиса" value="{{ $office_addr }}">
                        </label>
                    </div>
                    <div class="form_group">
                        <label>Номер аккаунта
                            <input name="acc_num" type="text" placeholder="Номер аккаунта" value="{{ $acc_num }}">
                        </label>
                    </div>
                    @if($role != "printing")
                        <div class="form_group">
                            <button type="submit" class="btn btn-sm btn-primary">
                                Обновить
                            </button>
                        </div>
                    @endif
                </form>
            @endif
        </div>

        @if($role != "printing")
            <div class="order-column-compact-blocks">
                @include('voyager::orders.invoice')
                @include('voyager::orders.payment_request')
            </div>
        @endif
    </td>
